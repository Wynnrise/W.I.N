<?php

/**
 * WynstonCalculator
 *
 * Handles all feasibility logic for the Wynston Plex Map portal.
 * Covers: Strata Pro Forma, Rental Pro Forma, Wynston Outlook.
 *
 * All calculations run server-side. Raw formulas never exposed to front-end.
 * Returns structured arrays consumed by api/feasibility.php JSON endpoint.
 *
 * Fix log vs original version:
 *   Fix 1 — Minimum forecast count guard (outlook returns error if < 4 sources)
 *   Fix 2 — Local momentum capped at ±15% (prevents extreme HPI ratio distortion)
 *   Fix 3 — Saleable area efficiency factor (85% of gross — more defensible exit value)
 *   Fix 4 — Operating expense deduction added to rental NOI (25% of gross — industry standard)
 *   Fix 5 — Dynamic confidence band using forecast standard deviation (not flat ±2%)
 *   Fix 6 — City-configurable DCL rates (future-proofs for Burnaby, Surrey expansion)
 *
 * Session 04 fixes:
 *   Fix 7 — Pipeline weighting corrected: signal expressed as % before weighting applied
 *   Fix 8 — Confidence tier (1/2/3) now computed from sales_count and returned explicitly
 *   Fix 9 — Outlook weights now shift dynamically per confidence tier
 *
 * Session 09 fixes:
 *   Fix 10 — TIER_WEIGHTS updated to 5-layer formula: 30/35/10/10/15
 *   Fix 11 — Bank forecast outlier removal changed from simple min/max strip to IQR method
 *   Fix 12 — 3-comparable rental mid-point: weighted average of liv.rent + REBGV + CMHC
 *
 * Session NEW (Rental/Hold audit) fixes:
 *   Fix 13 — Cap rate (yield on cost) added to rental return
 *   Fix 14 — Fallback-used flag added to calculateRentalMidPoint return (transparency)
 *   Fix 15 — Vacancy rate, operating expense rate, density bonus rate now all overrideable
 *            via feasibility.php URL params (matches Session 08 strata editable pattern)
 *   Fix 16 — Year 1 / Year 5 / Year 10 cash flow projection helper added
 *            (rent growth + opex growth + optional mortgage stress at Y5 renewal)
 *   Fix 17 — Stabilized asset value + day-1 equity creation added (calculator-level,
 *            so panel and report both consume from one source)
 *   Fix 18 — Break-even occupancy added
 */
class WynstonCalculator {

    // Conversion constant
    private const SQM_TO_SQFT = 10.7639;

    // Permit fee rate (City of Vancouver)
    private const CITY_FEE_RATE_PER_1000 = 13.70;

    // Metro Vancouver DCC per dwelling unit — 2026 rate.
    // Covers Water DCC ($16,926) + Liquid Waste DCC ($11,290) + Parkland DCC ($981).
    // Source: metrovancouver.org — Vancouver Sewerage Area, residential category.
    // Rates: 2025 = $21,941 | 2026 = $29,197 | 2027 = $34,133
    private const METRO_DCC_PER_UNIT = 29197;

    // Peat zone foundation contingency (flat addition)
    private const PEAT_CONTINGENCY_FLAT = 150000;

    // Saleable area efficiency — 85% of gross buildable is saleable
    private const SALEABLE_AREA_RATIO = 0.85;

    // Operating expense rate for rental NOI — industry standard ~25% of gross income
    // DEFAULT only — can be overridden per-call via $operating_expense_rate param.
    private const DEFAULT_OPERATING_EXPENSE_RATE = 0.25;

    // Default vacancy rate
    private const DEFAULT_VACANCY_RATE = 0.05;

    // Default density bonus rate ($/sqft on bonus area — master brief says $30–$50, midpoint)
    private const DEFAULT_DENSITY_BONUS_PSF = 40.00;

    // Base FSR constants
    private const FSR_STRATA = 0.70;
    private const FSR_RENTAL = 1.00;

    /**
     * Confidence tier weights — 5 layers.
     *
     * Layer 1 — Macro (bank/broker forecasts):    30%
     * Layer 2 — Local HPI momentum + DOM:         35%
     * Layer 3 — Pipeline signal (multi_2025):     10%
     * Layer 4 — Population supply-demand gap:     10%
     * Layer 5 — Supply signal (CMHC starts):      15%
     */
    private const TIER_WEIGHTS = [
        1 => ['macro' => 0.30, 'local' => 0.35, 'pipeline' => 0.10, 'population' => 0.10, 'supply' => 0.15],
        2 => ['macro' => 0.45, 'local' => 0.25, 'pipeline' => 0.10, 'population' => 0.10, 'supply' => 0.10],
        3 => ['macro' => 0.60, 'local' => 0.15, 'pipeline' => 0.10, 'population' => 0.10, 'supply' => 0.05],
    ];

    /**
     * Estimate unit count from lot area for Metro DCC calculation.
     * Matches the eligibility thresholds used in feasibility.php and generate-report.php.
     */
    private function _estimateUnitCount(float $lot_area_sqm): int {
        if ($lot_area_sqm >= 557) return 6;
        if ($lot_area_sqm >= 306) return 4;
        if ($lot_area_sqm >= 200) return 3;
        return 2;
    }

    /**
     * DCL rates per city.
     * Vancouver rates reflect the 20% temporary reduction approved Dec 10 2025,
     * effective until Sept 30 2026 (Bylaws 9755 and 12183, residential ≤1.2 FSR).
     */
    private function getDCLRates(string $city): array {
        $rates = [
            'vancouver'       => ['city_wide' => 4.63, 'utilities' => 3.63],
            'burnaby'         => ['city_wide' => 21.20, 'utilities' => 3.10],
            'surrey'          => ['city_wide' => 16.80, 'utilities' => 2.75],
            'richmond'        => ['city_wide' => 17.50, 'utilities' => 2.85],
            'coquitlam'       => ['city_wide' => 18.90, 'utilities' => 3.00],
            'north_vancouver' => ['city_wide' => 19.10, 'utilities' => 3.05],
        ];
        return $rates[$city] ?? $rates['vancouver'];
    }

    /**
     * Determine confidence tier from sales count + data window.
     */
    public function getConfidenceTier(int $sales_count, string $window = '24mo'): array {
        if ($window === 'fallback_2020') {
            return [
                'tier'        => 3,
                'label'       => 'Indicative',
                'description' => 'Limited recent duplex activity in this neighbourhood. Figures are indicative — verify against current market conditions.',
                'colour'      => 'gray',
                'window'      => $window,
            ];
        }

        if ($window === 'none' || $sales_count === 0) {
            return [
                'tier'        => 3,
                'label'       => 'Indicative',
                'description' => 'Thin recent data for this neighbourhood. Figures are indicative and should be independently verified.',
                'colour'      => 'gray',
                'window'      => $window,
            ];
        }

        if ($sales_count >= 10) {
            return [
                'tier'        => 1,
                'label'       => 'High Confidence',
                'description' => 'Strong recent market signal in this neighbourhood.',
                'colour'      => 'green',
                'window'      => $window,
            ];
        } elseif ($sales_count >= 4) {
            return [
                'tier'        => 2,
                'label'       => 'Moderate Confidence',
                'description' => 'Moderate recent activity in this neighbourhood. Figures carry wider variance.',
                'colour'      => 'amber',
                'window'      => $window,
            ];
        } else {
            return [
                'tier'        => 3,
                'label'       => 'Indicative',
                'description' => 'Thin recent data for this neighbourhood. Figures are indicative and should be independently verified.',
                'colour'      => 'gray',
                'window'      => $window,
            ];
        }
    }


    // =========================================================
    // 3-COMPARABLE RENTAL MID-POINT
    // Weighted average of liv.rent + REBGV + CMHC
    // =========================================================

    /**
     * Calculate the Wynston rental mid-point from 3 data sources.
     *
     * Sources and weights (Fix 12):
     *   liv.rent  — 45%  (most current asking rent, updated monthly)
     *   REBGV     — 35%  (actual leased prices from board data, updated monthly)
     *   CMHC      — 20%  (annual benchmark — used as floor/sanity check)
     *
     * If a source is missing (0 or null), its weight is redistributed
     * proportionally to the remaining sources so total always = 100%.
     *
     * Fix 14: Returns fallback_used flag per bedroom type so panel/report
     * can show "◇ estimated" badge when a bedroom had NO source data.
     */
    public function calculateRentalMidPoint(
        array $livrent,
        array $rebgv,
        array $cmhc
    ): array {
        $base_weights = [
            'livrent' => 0.45,
            'rebgv'   => 0.35,
            'cmhc'    => 0.20,
        ];

        $result = [
            'sources_used'  => [],
            'detail'        => [],
            'fallback_used' => ['1br' => false, '2br' => false, '3br' => false],  // Fix 14
        ];

        foreach (['1br', '2br', '3br'] as $type) {
            $sources = [
                'livrent' => (float)($livrent[$type] ?? 0),
                'rebgv'   => (float)($rebgv[$type]   ?? 0),
                'cmhc'    => (float)($cmhc[$type]    ?? 0),
            ];

            // Filter out missing sources (0 = not entered)
            $active = array_filter($sources, fn($v) => $v > 0);

            if (empty($active)) {
                // No source data for this bedroom type — flag as fallback
                $result[$type] = 0;
                $result['detail'][$type] = ['mid_point' => 0, 'sources' => []];
                $result['fallback_used'][$type] = true;  // Fix 14
                continue;
            }

            // Redistribute weights proportionally among active sources
            $active_weight_sum = array_sum(
                array_intersect_key($base_weights, $active)
            );
            $adjusted_weights = [];
            foreach ($active as $src => $val) {
                $adjusted_weights[$src] = $base_weights[$src] / $active_weight_sum;
            }

            // Weighted average
            $mid_point = 0;
            foreach ($active as $src => $val) {
                $mid_point += $val * $adjusted_weights[$src];
            }

            $result[$type] = round($mid_point, 0);
            $result['detail'][$type] = [
                'mid_point'    => round($mid_point, 0),
                'livrent'      => $sources['livrent'],
                'rebgv'        => $sources['rebgv'],
                'cmhc'         => $sources['cmhc'],
                'weights_used' => $adjusted_weights,
                'sources'      => array_keys($active),
            ];

            foreach (array_keys($active) as $src) {
                if (!in_array($src, $result['sources_used'])) {
                    $result['sources_used'][] = $src;
                }
            }
        }

        return $result;
    }


    // =========================================================
    // STRATA PRO FORMA  (0.70 FSR — sell the units)
    // =========================================================
    // Session 08: Land, Build PSF editable
    // Session NEW: Construction financing now included as separate editable cost block

    public function calculateStrataProForma(
        float  $lot_area_sqm,
        float  $assessed_land_value,
        float  $build_cost_psf,
        float  $avg_sold_psf,
        bool   $is_peat_zone,
        string $city = 'vancouver',
        ?float $construction_fin_ltc  = null,     // default 65%
        ?float $construction_fin_rate = null,     // default 7.0%
        ?int   $construction_fin_term = null,     // default 15 months
        bool   $use_all_cash_construction = false // Session 16: true = zero construction financing
    ): array {

        $dcl = $this->getDCLRates($city);

        // Construction financing defaults
        $cfin_ltc  = $construction_fin_ltc  ?? 0.65;
        $cfin_rate = $construction_fin_rate ?? 0.07;
        $cfin_term = $construction_fin_term ?? 15;

        $lot_area_sqft       = $lot_area_sqm * self::SQM_TO_SQFT;
        $buildable_sqft      = $lot_area_sqft * self::FSR_STRATA;
        $saleable_sqft       = $buildable_sqft * self::SALEABLE_AREA_RATIO;

        $base_build_cost     = $buildable_sqft * $build_cost_psf;

        $dcl_city_wide       = $buildable_sqft * $dcl['city_wide'];
        $dcl_utilities       = $buildable_sqft * $dcl['utilities'];
        $permit_fees         = ($base_build_cost / 1000) * self::CITY_FEE_RATE_PER_1000;

        $unit_count          = $this->_estimateUnitCount($lot_area_sqm);
        $metro_dcc           = self::METRO_DCC_PER_UNIT * $unit_count;

        $total_fees          = $dcl_city_wide + $dcl_utilities + $permit_fees + $metro_dcc;

        $contingency         = $is_peat_zone ? self::PEAT_CONTINGENCY_FLAT : 0;

        // Pre-financing project cost
        $cost_before_fin     = $assessed_land_value
                             + $base_build_cost
                             + $total_fees
                             + $contingency;

        // Construction financing cost
        // Session 16: if all-cash, no construction debt → zero financing cost
        // Otherwise standard interest-only convention: avg outstanding balance = full draw / 2
        // (starts at 0, ramps linearly to full by end of term)
        //   cost = cost_before_fin × LTC × rate × (term/12) × 0.5
        if ($use_all_cash_construction) {
            $construction_fin_cost = 0.0;
        } else {
            $construction_fin_cost = $cost_before_fin * $cfin_ltc * $cfin_rate * ($cfin_term / 12) * 0.5;
        }

        $total_project_cost  = $cost_before_fin + $construction_fin_cost;

        $exit_value          = $saleable_sqft * $avg_sold_psf;

        $profit              = $exit_value - $total_project_cost;
        $roi_pct             = $total_project_cost > 0
                               ? ($profit / $total_project_cost) * 100
                               : 0;

        return [
            'fsr_used'               => self::FSR_STRATA,
            'lot_area_sqft'          => round($lot_area_sqft, 2),
            'buildable_sqft'         => round($buildable_sqft, 2),
            'saleable_sqft'          => round($saleable_sqft, 2),
            'land_cost'              => round($assessed_land_value, 2),
            'build_cost'             => round($base_build_cost, 2),
            'dcl_city_wide'          => round($dcl_city_wide, 2),
            'dcl_utilities'          => round($dcl_utilities, 2),
            'permit_fees'            => round($permit_fees, 2),
            'metro_dcc'              => round($metro_dcc, 2),
            'unit_count'             => $unit_count,
            'total_fees'             => round($total_fees, 2),
            'contingency'            => round($contingency, 2),
            // Construction financing (Session NEW)
            'cost_before_fin'        => round($cost_before_fin, 2),
            'construction_fin_ltc'   => round($cfin_ltc, 4),
            'construction_fin_rate'  => round($cfin_rate, 4),
            'construction_fin_term'  => $cfin_term,
            'construction_fin_cost'  => round($construction_fin_cost, 2),
            'construction_fin_all_cash' => (bool)$use_all_cash_construction, // Session 16
            //
            'total_project_cost'     => round($total_project_cost, 2),
            'exit_value'             => round($exit_value, 2),
            'profit'                 => round($profit, 2),
            'roi_pct'                => round($roi_pct, 2),
            'is_peat_zone'           => $is_peat_zone,
            'city'                   => $city,
        ];
    }


    // =========================================================
    // RENTAL PRO FORMA  (1.00 FSR — hold and rent)
    // =========================================================

    /**
     * Calculate the complete Secured Rental Pro Forma.
     *
     * @param float  $lot_area_sqm           Lot area in square metres
     * @param float  $assessed_land_value    BC Assessment land value
     * @param float  $build_cost_psf         Construction cost per sqft
     * @param float  $density_bonus_psf_rate Density bonus rate on bonus area (default $40)
     * @param bool   $is_peat_zone           Peat zone flag
     * @param array  $unit_mix               ['1br' => int, '2br' => int, '3br' => int]
     * @param array  $market_rents           ['1br' => float, '2br' => float, '3br' => float]
     * @param array  $cmhc_benchmarks        ['1br' => float, '2br' => float, '3br' => float]
     * @param float  $vacancy_rate           Default 5% (Fix 15 — overrideable)
     * @param float  $operating_expense_rate Default 25% (Fix 15 — overrideable)
     * @param string $city                   City slug
     * @param float  $market_cap_rate        For stabilized asset value calc (Fix 17, default 4.0%)
     *
     * @return array Complete rental pro forma metrics
     */
    public function calculateRentalProForma(
        float  $lot_area_sqm,
        float  $assessed_land_value,
        float  $build_cost_psf,
        ?float $density_bonus_psf_rate = null,
        bool   $is_peat_zone = false,
        array  $unit_mix = [],
        array  $market_rents = [],
        array  $cmhc_benchmarks = [],
        ?float $vacancy_rate = null,
        ?float $operating_expense_rate = null,
        string $city = 'vancouver',
        float  $market_cap_rate = 0.04
    ): array {

        // Fix 15 — resolve overrideable defaults
        $vacancy_rate           = $vacancy_rate           ?? self::DEFAULT_VACANCY_RATE;
        $operating_expense_rate = $operating_expense_rate ?? self::DEFAULT_OPERATING_EXPENSE_RATE;
        $density_bonus_psf_rate = $density_bonus_psf_rate ?? self::DEFAULT_DENSITY_BONUS_PSF;

        $dcl = $this->getDCLRates($city);

        // Areas
        $lot_area_sqft        = $lot_area_sqm * self::SQM_TO_SQFT;
        $total_buildable_sqft = $lot_area_sqft * self::FSR_RENTAL;
        $base_buildable_sqft  = $lot_area_sqft * self::FSR_STRATA;
        $bonus_area_sqft      = $total_buildable_sqft - $base_buildable_sqft;

        // Density bonus cost (only on the incremental area above 0.70 FSR)
        $density_bonus_cost   = $bonus_area_sqft * $density_bonus_psf_rate;

        // Hard construction costs
        $base_build_cost      = $total_buildable_sqft * $build_cost_psf;
        $total_build_cost     = $base_build_cost + $density_bonus_cost;

        // City fees and DCLs
        $dcl_city_wide        = $total_buildable_sqft * $dcl['city_wide'];
        $dcl_utilities        = $total_buildable_sqft * $dcl['utilities'];
        $permit_fees          = ($total_build_cost / 1000) * self::CITY_FEE_RATE_PER_1000;

        // Metro Vancouver DCC — per unit (rental path allows more units)
        $unit_count           = array_sum($unit_mix);
        if ($unit_count < 1) $unit_count = $this->_estimateUnitCount($lot_area_sqm);
        $metro_dcc            = self::METRO_DCC_PER_UNIT * $unit_count;

        $total_fees           = $dcl_city_wide + $dcl_utilities + $permit_fees + $metro_dcc;

        // Peat zone contingency
        $contingency          = $is_peat_zone ? self::PEAT_CONTINGENCY_FLAT : 0;

        // Total project cost
        $total_project_cost   = $assessed_land_value
                              + $total_build_cost
                              + $total_fees
                              + $contingency;

        // Rental income by bedroom type
        $gross_monthly        = 0;
        $rent_breakdown       = [];

        foreach (['1br', '2br', '3br'] as $type) {
            $count        = $unit_mix[$type]        ?? 0;
            $market_rent  = $market_rents[$type]    ?? 0;
            $cmhc_rent    = $cmhc_benchmarks[$type] ?? 0;

            $type_monthly  = $count * $market_rent;
            $gross_monthly += $type_monthly;

            $variance_pct = $cmhc_rent > 0
                ? (($market_rent - $cmhc_rent) / $cmhc_rent) * 100
                : 0;

            if ($variance_pct > 2) {
                $variance_colour = 'green';
                $variance_label  = 'Above CMHC benchmark — hot rental market';
            } elseif ($variance_pct < -2) {
                $variance_colour = 'amber';
                $variance_label  = 'Below CMHC benchmark — verify with liv.rent data';
            } else {
                $variance_colour = 'gray';
                $variance_label  = 'At CMHC benchmark';
            }

            $rent_breakdown[$type] = [
                'unit_count'      => $count,
                'market_rent'     => $market_rent,
                'cmhc_rent'       => $cmhc_rent,
                'variance_pct'    => round($variance_pct, 1),
                'variance_colour' => $variance_colour,
                'variance_label'  => $variance_label,
                'type_monthly'    => $type_monthly,
            ];
        }

        // Annual income calculations
        $annual_gross    = $gross_monthly * 12;

        // NOI — deduct vacancy AND operating expenses
        $effective_gross = $annual_gross * (1 - $vacancy_rate);
        $opex_amount     = $effective_gross * $operating_expense_rate;
        $annual_noi      = $effective_gross - $opex_amount;

        // Fix 13 — Cap rate (yield on cost) = NOI ÷ Total Project Cost × 100
        $cap_rate_pct = $total_project_cost > 0
                      ? ($annual_noi / $total_project_cost) * 100
                      : 0;

        // Fix 17 — Stabilized asset value + day-1 equity creation
        $stabilized_value = $market_cap_rate > 0
                          ? $annual_noi / $market_cap_rate
                          : 0;
        $value_created    = $stabilized_value - $total_project_cost;

        // Fix 18 — Break-even occupancy
        // The occupancy % at which NOI exactly covers total project cost's required return.
        // For a simpler, more intuitive measure: % of units that must be occupied for
        // rental income (after opex) to equal the target NOI needed to hit market cap rate.
        // target_noi_for_breakeven = total_project_cost * market_cap_rate
        // But more commonly: occupancy at which income exactly covers opex only
        // (i.e., property doesn't bleed cash operationally, ignoring debt).
        // Formula: break_even = total_opex / (annual_gross * (1 - opex_rate))
        // Simpler version used here: what % of gross rent is needed to cover opex at full-year operation
        $break_even_occupancy = $annual_gross > 0
                              ? ($opex_amount / $annual_gross) * 100
                              : 0;

        return [
            'fsr_used'               => self::FSR_RENTAL,
            'lot_area_sqft'          => round($lot_area_sqft, 2),
            'total_buildable_sqft'   => round($total_buildable_sqft, 2),
            'bonus_area_sqft'        => round($bonus_area_sqft, 2),
            'land_cost'              => round($assessed_land_value, 2),
            'base_build_cost'        => round($base_build_cost, 2),
            'density_bonus_cost'     => round($density_bonus_cost, 2),
            'density_bonus_psf_rate' => round($density_bonus_psf_rate, 2),  // Fix 15 — report back
            'total_build_cost'       => round($total_build_cost, 2),
            'dcl_city_wide'          => round($dcl_city_wide, 2),
            'dcl_utilities'          => round($dcl_utilities, 2),
            'permit_fees'            => round($permit_fees, 2),
            'metro_dcc'              => round($metro_dcc, 2),
            'unit_count'             => $unit_count,
            'total_fees'             => round($total_fees, 2),
            'contingency'            => round($contingency, 2),
            'total_project_cost'     => round($total_project_cost, 2),
            'rent_breakdown'         => $rent_breakdown,
            'gross_monthly'          => round($gross_monthly, 2),
            'annual_gross'           => round($annual_gross, 2),
            'effective_gross'        => round($effective_gross, 2),
            'opex_amount'            => round($opex_amount, 2),
            'annual_noi'             => round($annual_noi, 2),
            'vacancy_rate'           => round($vacancy_rate, 4),
            'operating_expense_rate' => round($operating_expense_rate, 4),
            // Fix 13 — new fields below
            'cap_rate_pct'           => round($cap_rate_pct, 2),
            // Fix 17 — stabilized asset value + value creation
            'market_cap_rate'        => round($market_cap_rate, 4),
            'stabilized_value'       => round($stabilized_value, 2),
            'value_created'          => round($value_created, 2),
            // Fix 18 — break-even occupancy
            'break_even_occupancy'   => round($break_even_occupancy, 1),
            'is_peat_zone'           => $is_peat_zone,
            'city'                   => $city,
        ];
    }


    // =========================================================
    // YEAR 1 / YEAR 5 / YEAR 10 CASH FLOW PROJECTION  (Fix 16)
    // =========================================================

    /**
     * Project cash flow over a 10-year horizon for the rental/hold path.
     *
     * Compounds:
     *   - Gross rental income at $rent_growth_pct / year
     *   - Operating expenses at $opex_growth_pct / year
     *   - Debt service: fixed OR stepped up at Year 5 renewal by $stress_bps basis points
     *
     * Inputs mirror the rental pro forma at Year 1 exactly, then compound forward.
     * No assumptions about mortgage amortization schedule — treats debt service as
     * level payment for 5-year term blocks. Principal paydown is NOT modelled here
     * (requires an amortization schedule — out of scope for this projection).
     *
     * @param float $year1_gross_monthly  From rental pro forma (gross_monthly field)
     * @param float $year1_opex_amount    From rental pro forma (opex_amount field)
     * @param float $year1_debt_service   Annual debt service in Year 1 (from generate-report)
     * @param float $vacancy_rate         Same rate applied all 10 years (default 5%)
     * @param float $rent_growth_pct      % annual rent growth (default 3%)
     * @param float $opex_growth_pct      % annual opex growth (default 2.5%)
     * @param string $mortgage_stress_mode 'fixed' | 'stress_y5'
     * @param int $mortgage_stress_bps    Basis points added at Y5 renewal (100 = +1%)
     * @param float $loan_balance_approx  Approximate outstanding balance for stress calc
     *
     * @return array Year 1 / Year 5 / Year 10 snapshot + metadata
     */
    public function projectRentalCashFlow(
        float  $year1_gross_monthly,
        float  $year1_opex_amount,
        float  $year1_debt_service,
        float  $vacancy_rate          = 0.05,
        float  $rent_growth_pct       = 3.0,
        float  $opex_growth_pct       = 2.5,
        string $mortgage_stress_mode  = 'fixed',
        int    $mortgage_stress_bps   = 100,
        float  $loan_balance_approx   = 0.0
    ): array {

        $rent_gr = $rent_growth_pct / 100;
        $opex_gr = $opex_growth_pct / 100;

        // Calculate debt service at each checkpoint
        $debt_y1  = $year1_debt_service;
        $debt_y5  = $year1_debt_service;
        $debt_y10 = $year1_debt_service;

        // If stress mode is active, apply rate increase at Year 5 renewal
        // Simplification: treat the stress as a percentage increase of annual debt service
        // proportional to the rate change. This avoids needing full amortization recalc.
        // For a typical 40-yr amort at ~5% rate, a 100bps increase raises payment ~12-14%.
        // We use a rough but conservative estimator: 1% rate increase = ~13% debt service bump.
        if ($mortgage_stress_mode === 'stress_y5' && $mortgage_stress_bps > 0) {
            $stress_pct_bump = ($mortgage_stress_bps / 100) * 0.13;  // 13% bump per 1%
            $debt_y5  = $year1_debt_service * (1 + $stress_pct_bump);
            $debt_y10 = $debt_y5;  // rate stays elevated after Y5 renewal
        }

        // Build Year 1, Year 5, Year 10 snapshots
        $years = [1, 5, 10];
        $projections = [];

        foreach ($years as $y) {
            // Compound rent and opex forward (Year 1 = 0 years of growth)
            $years_elapsed = $y - 1;
            $gross_annual  = ($year1_gross_monthly * 12) * pow(1 + $rent_gr, $years_elapsed);
            $opex_annual   = $year1_opex_amount * pow(1 + $opex_gr, $years_elapsed);

            $effective_gross = $gross_annual * (1 - $vacancy_rate);
            $vacancy_amount  = $gross_annual * $vacancy_rate;
            $noi             = $effective_gross - $opex_annual;

            $debt_service = match($y) {
                1  => $debt_y1,
                5  => $debt_y5,
                10 => $debt_y10,
            };

            $cash_flow = $noi - $debt_service;

            $projections['year_' . $y] = [
                'year'            => $y,
                'gross_annual'    => round($gross_annual, 2),
                'vacancy_amount'  => round($vacancy_amount, 2),
                'effective_gross' => round($effective_gross, 2),
                'opex_amount'     => round($opex_annual, 2),
                'noi'             => round($noi, 2),
                'debt_service'    => round($debt_service, 2),
                'cash_flow'       => round($cash_flow, 2),
            ];
        }

        // Find year-to-positive (the first year where cash_flow > 0)
        // Uses a linear search on compounded rent/opex growth for early detection.
        $year_to_positive = null;
        $running_gross_monthly = $year1_gross_monthly;
        $running_opex          = $year1_opex_amount;
        $running_debt          = $year1_debt_service;

        for ($y = 1; $y <= 30; $y++) {
            $gross_annual  = $running_gross_monthly * 12;
            $eg            = $gross_annual * (1 - $vacancy_rate);
            $noi_check     = $eg - $running_opex;

            // Apply stress if applicable
            if ($y == 5 && $mortgage_stress_mode === 'stress_y5' && $mortgage_stress_bps > 0) {
                $stress_pct_bump = ($mortgage_stress_bps / 100) * 0.13;
                $running_debt    = $year1_debt_service * (1 + $stress_pct_bump);
            }

            $cf_check = $noi_check - $running_debt;
            if ($cf_check > 0) { $year_to_positive = $y; break; }

            // Compound for next year
            $running_gross_monthly *= (1 + $rent_gr);
            $running_opex          *= (1 + $opex_gr);
        }

        return [
            'projections'           => $projections,
            'rent_growth_pct'       => $rent_growth_pct,
            'opex_growth_pct'       => $opex_growth_pct,
            'vacancy_rate'          => $vacancy_rate,
            'mortgage_stress_mode'  => $mortgage_stress_mode,
            'mortgage_stress_bps'   => $mortgage_stress_bps,
            'year_to_positive'      => $year_to_positive,  // null if never within 30 yrs
        ];
    }


    // =========================================================
    // WYNSTON OUTLOOK  (price forecast layer)
    // =========================================================
    // UNCHANGED from Session 08 version — outlook logic is locked.

    public function calculateWynstonOutlook(
        float $current_finished_psf,
        float $current_build_psf,
        array $bank_forecasts,
        float $neighbourhood_hpi_yoy,
        float $metro_hpi_yoy,
        bool  $dom_trending_down,
        bool  $sales_above_average,
        int   $units_in_pipeline,
        int   $neighbourhood_avg_pipeline,
        int   $sales_count = 0,
        float $household_growth_pct = 0.0,
        float $unit_growth_pct      = 0.0,
        float $cmhc_starts_yoy      = 0.0
    ): array {

        if (count($bank_forecasts) < 4) {
            return [
                'error'   => 'insufficient_data',
                'message' => 'Wynston Outlook requires at least 4 institutional '
                           . 'forecasts. Currently ' . count($bank_forecasts)
                           . ' source(s) entered for this quarter.',
            ];
        }

        $confidence = $this->getConfidenceTier($sales_count);
        $tier       = $confidence['tier'];
        $weights    = self::TIER_WEIGHTS[$tier];

        // LAYER 1: Macro Signal (IQR outlier filter)
        sort($bank_forecasts);
        $n = count($bank_forecasts);

        if ($n >= 4) {
            $q1 = $bank_forecasts[(int)floor(($n - 1) * 0.25)];
            $q3 = $bank_forecasts[(int)floor(($n - 1) * 0.75)];
            $iqr = $q3 - $q1;
            $filtered = array_values(array_filter(
                $bank_forecasts,
                fn($x) => $x >= ($q1 - 1.5 * $iqr) && $x <= ($q3 + 1.5 * $iqr)
            ));
            $bank_forecasts = count($filtered) >= 3 ? $filtered : $bank_forecasts;
        }

        $macro_avg    = array_sum($bank_forecasts) / count($bank_forecasts);

        $variance_sum = array_sum(
            array_map(fn($x) => pow($x - $macro_avg, 2), $bank_forecasts)
        );
        $std_dev      = count($bank_forecasts) > 1
                      ? sqrt($variance_sum / count($bank_forecasts))
                      : 1.0;

        $confidence_band = max(1.5, min(4.0, $std_dev));

        $macro_weighted  = $macro_avg * $weights['macro'];

        // LAYER 2: Local Momentum Score
        $hpi_ratio = $metro_hpi_yoy != 0
                   ? ($neighbourhood_hpi_yoy / $metro_hpi_yoy)
                   : 1.0;

        $sales_velocity_factor = $dom_trending_down   ? 1.05 : 0.95;
        $supply_factor         = $sales_above_average ? 1.03 : 0.97;

        $local_momentum_raw  = $hpi_ratio * $sales_velocity_factor * $supply_factor;
        $local_momentum_pct  = ($local_momentum_raw - 1) * 100;
        $local_momentum_pct  = max(-15.0, min(15.0, $local_momentum_pct));

        $local_weighted      = $local_momentum_pct * $weights['local'];

        // LAYER 3: Development Pipeline Signal
        $pipeline_signal_pct = ($units_in_pipeline > $neighbourhood_avg_pipeline)
                             ? -0.5
                             :  0.3;
        $pipeline_weighted   = $pipeline_signal_pct * $weights['pipeline'];

        // LAYER 4: Population / Household Growth Signal
        $population_weighted = 0.0;
        $population_signal_pct = 0.0;
        if ($household_growth_pct !== 0.0 || $unit_growth_pct !== 0.0) {
            $supply_demand_gap     = $household_growth_pct - $unit_growth_pct;
            $population_signal_pct = max(-3.0, min(3.0, $supply_demand_gap * 0.3));
            $population_weighted   = $population_signal_pct * $weights['population'];
        }

        // LAYER 5: Supply Signal (CMHC Housing Starts)
        $supply_weighted = 0.0;
        $supply_signal_pct = 0.0;
        if ($cmhc_starts_yoy !== 0.0) {
            $supply_signal_pct = max(-3.0, min(3.0, $cmhc_starts_yoy * -0.25));
            $supply_weighted   = $supply_signal_pct * $weights['supply'];
        }

        // COMBINED OUTLOOK
        $outlook_pct = $macro_weighted + $local_weighted
                     + $pipeline_weighted + $population_weighted
                     + $supply_weighted;

        $outlook_psf         = $current_finished_psf * (1 + ($outlook_pct / 100));

        $current_margin_psf  = $current_finished_psf - $current_build_psf;
        $projected_margin_psf = $outlook_psf          - $current_build_psf;
        $margin_improvement  = $projected_margin_psf  - $current_margin_psf;

        return [
            'confidence_tier'        => $confidence,
            'macro_signal_pct'       => round($macro_avg, 2),
            'local_momentum_pct'     => round($local_momentum_pct, 2),
            'pipeline_signal_pct'    => round($pipeline_signal_pct, 2),
            'population_signal_pct'  => round($population_signal_pct, 2),
            'supply_signal_pct'      => round($supply_signal_pct, 2),
            'household_growth_pct'   => round($household_growth_pct, 2),
            'unit_growth_pct'        => round($unit_growth_pct, 2),
            'cmhc_starts_yoy'        => round($cmhc_starts_yoy, 2),
            'weights_used'           => $weights,
            'outlook_pct'            => round($outlook_pct, 2),
            'current_build_psf'      => round($current_build_psf, 2),
            'current_finished_psf'   => round($current_finished_psf, 2),
            'outlook_psf'            => round($outlook_psf, 2),
            'current_margin_psf'     => round($current_margin_psf, 2),
            'projected_margin_psf'   => round($projected_margin_psf, 2),
            'margin_improvement_psf' => round($margin_improvement, 2),
            'confidence_band'        => round($confidence_band, 2),
            'confidence_low_pct'     => round($outlook_pct - $confidence_band, 2),
            'confidence_high_pct'    => round($outlook_pct + $confidence_band, 2),
            'forecasts_used'         => count($bank_forecasts),
            'std_dev_forecasts'      => round($std_dev, 2),
        ];
    }
}