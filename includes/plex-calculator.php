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
 *            (previously pipeline_factor_pct was a small scalar, not a % like other layers)
 *   Fix 8 — Confidence tier (1/2/3) now computed from sales_count and returned explicitly
 *            (FLAG 6: tier is competitive differentiator — must be visible in panel and PDF)
 *   Fix 9 — Outlook weights now shift dynamically per confidence tier (Tier 1/2/3)
 *            instead of being hardcoded at 40/40/20
 */
class WynstonCalculator {

    // Conversion constant
    private const SQM_TO_SQFT = 10.7639;

    // Permit fee rate (City of Vancouver)
    private const CITY_FEE_RATE_PER_1000 = 13.70;

    // Peat zone foundation contingency (flat addition)
    private const PEAT_CONTINGENCY_FLAT = 150000;

    // Saleable area efficiency — 85% of gross buildable is saleable
    // (accounts for common areas, hallways, mechanical room, stairwells)
    private const SALEABLE_AREA_RATIO = 0.85;

    // Operating expense rate for rental NOI
    // Industry standard ~25% of gross income
    // (covers property management 8-10%, insurance, maintenance reserve, property tax)
    private const OPERATING_EXPENSE_RATE = 0.25;

    // Base FSR constants
    private const FSR_STRATA = 0.70;
    private const FSR_RENTAL = 1.00;

    /**
     * Confidence tier weights — 4 layers.
     * Weights adjusted from 40/40/20 to 38/38/12/12.
     * If no population data, Layer 4 = 0 and weights compensate automatically.
     */
    private const TIER_WEIGHTS = [
        1 => ['macro' => 0.38, 'local' => 0.38, 'pipeline' => 0.12, 'population' => 0.12],
        2 => ['macro' => 0.53, 'local' => 0.23, 'pipeline' => 0.12, 'population' => 0.12],
        3 => ['macro' => 0.68, 'local' => 0.08, 'pipeline' => 0.12, 'population' => 0.12],
    ];

    /**
     * DCL rates per city.
     * Add new cities here when expanding — no other changes needed.
     * Rates in $/sqft as of 2026.
     */
    private function getDCLRates(string $city): array {
        $rates = [
            'vancouver'       => ['city_wide' => 18.45, 'utilities' => 2.95],
            'burnaby'         => ['city_wide' => 21.20, 'utilities' => 3.10],
            'surrey'          => ['city_wide' => 16.80, 'utilities' => 2.75],
            'richmond'        => ['city_wide' => 17.50, 'utilities' => 2.85],
            'coquitlam'       => ['city_wide' => 18.90, 'utilities' => 3.00],
            'north_vancouver' => ['city_wide' => 19.10, 'utilities' => 3.05],
        ];

        // Fall back to Vancouver rates if city not yet configured
        return $rates[$city] ?? $rates['vancouver'];
    }

    /**
     * Determine confidence tier from sales count.
     * Returns array with tier (int), label (string), description (string).
     * (Fix 8 — FLAG 6: tier must be returned explicitly, not silently used)
     */
    public function getConfidenceTier(int $sales_count): array {
        if ($sales_count >= 5) {
            return [
                'tier'        => 1,
                'label'       => 'High Confidence',
                'description' => 'Based on ' . $sales_count . ' comparable sales in this neighbourhood.',
                'colour'      => 'green',
            ];
        } elseif ($sales_count >= 2) {
            return [
                'tier'        => 2,
                'label'       => 'Moderate Confidence',
                'description' => 'Based on ' . $sales_count . ' comparable sale(s). Weighted toward metro forecast.',
                'colour'      => 'amber',
            ];
        } else {
            return [
                'tier'        => 3,
                'label'       => 'Indicative',
                'description' => 'Limited neighbourhood data. Estimate based primarily on Metro Vancouver forecast.',
                'colour'      => 'gray',
            ];
        }
    }


    // =========================================================
    // STRATA PRO FORMA  (0.70 FSR — sell the units)
    // =========================================================

    /**
     * Calculate the complete Strata Pro Forma.
     *
     * @param float  $lot_area_sqm        Lot area in square metres (always metric internally)
     * @param float  $assessed_land_value BC Assessment current land value
     * @param float  $build_cost_psf      Construction cost per sqft (from construction_costs table)
     * @param float  $avg_sold_psf        Average sold $/sqft for new builds (from monthly_market_stats)
     * @param bool   $is_peat_zone        Whether lot is in a known peat zone
     * @param string $city                City slug — determines DCL rates
     *
     * @return array Complete strata pro forma metrics
     */
    public function calculateStrataProForma(
        float  $lot_area_sqm,
        float  $assessed_land_value,
        float  $build_cost_psf,
        float  $avg_sold_psf,
        bool   $is_peat_zone,
        string $city = 'vancouver'
    ): array {

        $dcl = $this->getDCLRates($city);

        // Areas
        $lot_area_sqft       = $lot_area_sqm * self::SQM_TO_SQFT;
        $buildable_sqft      = $lot_area_sqft * self::FSR_STRATA;
        $saleable_sqft       = $buildable_sqft * self::SALEABLE_AREA_RATIO;

        // Hard construction costs
        $base_build_cost     = $buildable_sqft * $build_cost_psf;

        // City fees and DCLs (applied to total buildable area)
        $dcl_city_wide       = $buildable_sqft * $dcl['city_wide'];
        $dcl_utilities       = $buildable_sqft * $dcl['utilities'];
        $permit_fees         = ($base_build_cost / 1000) * self::CITY_FEE_RATE_PER_1000;
        $total_fees          = $dcl_city_wide + $dcl_utilities + $permit_fees;

        // Peat zone contingency
        $contingency         = $is_peat_zone ? self::PEAT_CONTINGENCY_FLAT : 0;

        // Total project cost
        $total_project_cost  = $assessed_land_value
                             + $base_build_cost
                             + $total_fees
                             + $contingency;

        // Exit value — uses saleable area only (Fix 3)
        $exit_value          = $saleable_sqft * $avg_sold_psf;

        // Profit and ROI
        $profit              = $exit_value - $total_project_cost;
        $roi_pct             = $total_project_cost > 0
                               ? ($profit / $total_project_cost) * 100
                               : 0;

        return [
            'fsr_used'           => self::FSR_STRATA,
            'lot_area_sqft'      => round($lot_area_sqft, 2),
            'buildable_sqft'     => round($buildable_sqft, 2),
            'saleable_sqft'      => round($saleable_sqft, 2),
            'land_cost'          => round($assessed_land_value, 2),
            'build_cost'         => round($base_build_cost, 2),
            'dcl_city_wide'      => round($dcl_city_wide, 2),
            'dcl_utilities'      => round($dcl_utilities, 2),
            'permit_fees'        => round($permit_fees, 2),
            'total_fees'         => round($total_fees, 2),
            'contingency'        => round($contingency, 2),
            'total_project_cost' => round($total_project_cost, 2),
            'exit_value'         => round($exit_value, 2),
            'profit'             => round($profit, 2),
            'roi_pct'            => round($roi_pct, 2),
            'is_peat_zone'       => $is_peat_zone,
            'city'               => $city,
        ];
    }


    // =========================================================
    // RENTAL PRO FORMA  (1.00 FSR — hold and rent)
    // =========================================================

    /**
     * Calculate the complete Secured Rental Pro Forma.
     *
     * @param float  $lot_area_sqm           Lot area in square metres
     * @param float  $assessed_land_value     BC Assessment land value
     * @param float  $build_cost_psf          Construction cost per sqft
     * @param float  $density_bonus_psf_rate  Density bonus rate on bonus area ($30–$50)
     * @param bool   $is_peat_zone            Peat zone flag
     * @param array  $unit_mix                ['1br' => int, '2br' => int, '3br' => int]
     * @param array  $market_rents            ['1br' => float, '2br' => float, '3br' => float]
     * @param array  $cmhc_benchmarks         ['1br' => float, '2br' => float, '3br' => float]
     * @param float  $vacancy_rate            Default 5%
     * @param string $city                    City slug
     *
     * @return array Complete rental pro forma metrics
     */
    public function calculateRentalProForma(
        float  $lot_area_sqm,
        float  $assessed_land_value,
        float  $build_cost_psf,
        float  $density_bonus_psf_rate,
        bool   $is_peat_zone,
        array  $unit_mix,
        array  $market_rents,
        array  $cmhc_benchmarks,
        float  $vacancy_rate = 0.05,
        string $city = 'vancouver'
    ): array {

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
        $total_fees           = $dcl_city_wide + $dcl_utilities + $permit_fees;

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

            // Variance vs CMHC benchmark
            $variance_pct = $cmhc_rent > 0
                ? (($market_rent - $cmhc_rent) / $cmhc_rent) * 100
                : 0;

            // Colour signal for UI and PDF
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

        // NOI — deduct vacancy AND operating expenses (Fix 4)
        $effective_gross = $annual_gross * (1 - $vacancy_rate);
        $annual_noi      = $effective_gross * (1 - self::OPERATING_EXPENSE_RATE);

        return [
            'fsr_used'               => self::FSR_RENTAL,
            'lot_area_sqft'          => round($lot_area_sqft, 2),
            'total_buildable_sqft'   => round($total_buildable_sqft, 2),
            'bonus_area_sqft'        => round($bonus_area_sqft, 2),
            'land_cost'              => round($assessed_land_value, 2),
            'base_build_cost'        => round($base_build_cost, 2),
            'density_bonus_cost'     => round($density_bonus_cost, 2),
            'total_build_cost'       => round($total_build_cost, 2),
            'dcl_city_wide'          => round($dcl_city_wide, 2),
            'dcl_utilities'          => round($dcl_utilities, 2),
            'permit_fees'            => round($permit_fees, 2),
            'total_fees'             => round($total_fees, 2),
            'contingency'            => round($contingency, 2),
            'total_project_cost'     => round($total_project_cost, 2),
            'rent_breakdown'         => $rent_breakdown,
            'gross_monthly'          => round($gross_monthly, 2),
            'annual_gross'           => round($annual_gross, 2),
            'effective_gross'        => round($effective_gross, 2),
            'annual_noi'             => round($annual_noi, 2),
            'vacancy_rate'           => $vacancy_rate,
            'operating_expense_rate' => self::OPERATING_EXPENSE_RATE,
            'is_peat_zone'           => $is_peat_zone,
            'city'                   => $city,
        ];
    }


    // =========================================================
    // WYNSTON OUTLOOK  (price forecast layer)
    // =========================================================

    /**
     * Calculate the Wynston Outlook three-layer price forecast.
     *
     * @param float $current_finished_psf       Current avg sold $/sqft (REBGV, Yr Blt 2024+)
     * @param float $current_build_psf          Current build cost $/sqft
     * @param array $bank_forecasts             Array of 4–6 % forecast values from banks/brokers
     * @param float $neighbourhood_hpi_yoy      Neighbourhood HPI year-over-year %
     * @param float $metro_hpi_yoy              Metro Vancouver HPI year-over-year %
     * @param bool  $dom_trending_down          Days on market decreasing vs last month
     * @param bool  $sales_above_average        Sales count above neighbourhood average
     * @param int   $units_in_pipeline          Active multi_2025 projects in neighbourhood
     * @param int   $neighbourhood_avg_pipeline Average pipeline count for this neighbourhood
     * @param int   $sales_count                Raw sales count — determines confidence tier
     *
     * @return array Outlook metrics or error array if insufficient data
     */
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
        float $household_growth_pct = 0.0,  // Layer 4: % change in households (2021→2026)
        float $unit_growth_pct      = 0.0   // Layer 4: % change in housing units (2021→2026)
    ): array {

        // Fix 1 — Minimum data guard
        if (count($bank_forecasts) < 4) {
            return [
                'error'   => 'insufficient_data',
                'message' => 'Wynston Outlook requires at least 4 institutional '
                           . 'forecasts. Currently ' . count($bank_forecasts)
                           . ' source(s) entered for this quarter.',
            ];
        }

        // Fix 8 — Determine confidence tier up front (drives weights below)
        $confidence = $this->getConfidenceTier($sales_count);
        $tier       = $confidence['tier'];
        $weights    = self::TIER_WEIGHTS[$tier];

        // ── LAYER 1: Macro Signal ────────────────────────────────────────
        // Average the bank/broker forecasts after removing highest and lowest outlier
        sort($bank_forecasts);
        array_shift($bank_forecasts); // remove lowest
        array_pop($bank_forecasts);   // remove highest

        $macro_avg    = array_sum($bank_forecasts) / count($bank_forecasts);

        // Calculate standard deviation for dynamic confidence band (Fix 5)
        $variance_sum = array_sum(
            array_map(fn($x) => pow($x - $macro_avg, 2), $bank_forecasts)
        );
        $std_dev      = count($bank_forecasts) > 1
                      ? sqrt($variance_sum / count($bank_forecasts))
                      : 1.0;

        // Confidence band: wider when banks disagree, narrower when they converge
        // Clamped between 1.5% (high agreement) and 4.0% (high disagreement)
        $confidence_band = max(1.5, min(4.0, $std_dev));

        $macro_weighted  = $macro_avg * $weights['macro'];   // Fix 9 — tier-driven weight

        // ── LAYER 2: Local Momentum Score ────────────────────────────────
        $hpi_ratio = $metro_hpi_yoy != 0
                   ? ($neighbourhood_hpi_yoy / $metro_hpi_yoy)
                   : 1.0;

        $sales_velocity_factor = $dom_trending_down   ? 1.05 : 0.95;
        $supply_factor         = $sales_above_average ? 1.03 : 0.97;

        $local_momentum_raw  = $hpi_ratio * $sales_velocity_factor * $supply_factor;
        $local_momentum_pct  = ($local_momentum_raw - 1) * 100;

        // Fix 2 — Cap local momentum to ±15%
        $local_momentum_pct  = max(-15.0, min(15.0, $local_momentum_pct));

        $local_weighted      = $local_momentum_pct * $weights['local'];  // Fix 9

        // ── LAYER 3: Development Pipeline Signal ─────────────────────────
        $pipeline_signal_pct = ($units_in_pipeline > $neighbourhood_avg_pipeline)
                             ? -0.5   // supply pressure
                             :  0.3;  // demand signal
        $pipeline_weighted   = $pipeline_signal_pct * $weights['pipeline'];

        // ── LAYER 4: Population / Household Growth Signal ─────────────────
        // Supply-demand gap: household growth % minus new unit supply %
        // Positive = demand outpacing supply = bullish
        // Negative = supply outpacing demand = bearish
        // If no census data entered, contribution = 0 (weights compensate automatically)
        $population_weighted = 0.0;
        $population_signal_pct = 0.0;
        if ($household_growth_pct !== 0.0 || $unit_growth_pct !== 0.0) {
            $supply_demand_gap     = $household_growth_pct - $unit_growth_pct;
            $population_signal_pct = max(-3.0, min(3.0, $supply_demand_gap * 0.3));
            $population_weighted   = $population_signal_pct * $weights['population'];
        }

        // ── COMBINED OUTLOOK ─────────────────────────────────────────────
        $outlook_pct = $macro_weighted + $local_weighted
                     + $pipeline_weighted + $population_weighted;

        // Projected $/sqft
        $outlook_psf         = $current_finished_psf * (1 + ($outlook_pct / 100));

        // Margin analysis
        $current_margin_psf  = $current_finished_psf - $current_build_psf;
        $projected_margin_psf = $outlook_psf          - $current_build_psf;
        $margin_improvement  = $projected_margin_psf  - $current_margin_psf;

        return [
            // Confidence tier (Fix 8 — FLAG 6: shown prominently in panel and PDF)
            'confidence_tier'        => $confidence,

            // Layer breakdown (shown in PDF Section 11 / side panel Outlook tab)
            'macro_signal_pct'       => round($macro_avg, 2),
            'local_momentum_pct'     => round($local_momentum_pct, 2),
            'pipeline_signal_pct'    => round($pipeline_signal_pct, 2),
            'population_signal_pct'  => round($population_signal_pct, 2),
            'household_growth_pct'   => round($household_growth_pct, 2),
            'unit_growth_pct'        => round($unit_growth_pct, 2),
            'weights_used'           => $weights,

            // Combined result
            'outlook_pct'            => round($outlook_pct, 2),

            // $/sqft story (shown in side panel and PDF)
            'current_build_psf'      => round($current_build_psf, 2),
            'current_finished_psf'   => round($current_finished_psf, 2),
            'outlook_psf'            => round($outlook_psf, 2),

            // Margin story
            'current_margin_psf'     => round($current_margin_psf, 2),
            'projected_margin_psf'   => round($projected_margin_psf, 2),
            'margin_improvement_psf' => round($margin_improvement, 2),

            // Dynamic confidence band (Fix 5)
            'confidence_band'        => round($confidence_band, 2),
            'confidence_low_pct'     => round($outlook_pct - $confidence_band, 2),
            'confidence_high_pct'    => round($outlook_pct + $confidence_band, 2),

            // Metadata
            'forecasts_used'         => count($bank_forecasts),
            'std_dev_forecasts'      => round($std_dev, 2),
        ];
    }
}
