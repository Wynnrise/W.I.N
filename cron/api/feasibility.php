<?php
/**
 * api/feasibility.php
 * Gate 2 JSON endpoint — full feasibility data for side panel.
 * All secondary table queries wrapped in try/catch so empty/missing
 * tables never kill the endpoint — they return null/defaults instead.
 *
 * Override URL params supported (all optional):
 *   STRATA (Session 08, preserved):
 *     land_override        — manual land cost override ($)
 *     build_psf_override   — manual build $/sqft override
 *     psf_override         — manual avg sold $/sqft override
 *     psf_mode             — 'duplex' (default) | 'detached'  (HPI toggle)
 *
 *   STRATA CONSTRUCTION FINANCING:
 *     strata_cfin_ltc      — 0.00 to 0.85 (fraction, e.g. 0.65 = 65%)
 *     strata_cfin_rate     — annual interest rate as fraction (0.07 = 7%)
 *     strata_cfin_term     — months (e.g. 15)
 *     strata_all_cash      — '1' or 'true' → zero out strata construction financing (Session 16)
 *
 *   RENTAL (editable panel):
 *     rental_land_override     — manual land cost override for rental path (independent from strata)
 *     rental_build_psf_override — manual build $/sqft override for rental path
 *
 *   RENTAL FINANCING SCENARIO (Session B):
 *     financing_scenario       — 'cmhc_mli' (default) | 'conventional' | 'private' | 'all_cash'
 *     fin_ltc                  — override LTC as fraction (e.g. 0.70 = 70%)
 *     fin_rate                 — override interest rate as fraction (e.g. 0.06 = 6%)
 *     fin_amort                — override amortization in years (e.g. 30)
 *     fin_premium              — override insurance premium as fraction (e.g. 0.04 = 4%)
 *
 *   RENTAL (existing):
 *     vacancy_override         — decimal (e.g. 0.08 = 8%)
 *     op_expense_override      — decimal (e.g. 0.28 = 28%)
 *     density_bonus_override   — $/sqft on bonus area
 *     rent_1br_override        — $/mo, bypasses weighted midpoint
 *     rent_2br_override        — $/mo
 *     rent_3br_override        — $/mo
 *     market_cap_rate_override — decimal (e.g. 0.042 = 4.2%)
 */

require_once __DIR__ . '/../dev-auth.php';
require_once __DIR__ . '/../includes/plex-calculator.php';
require_once __DIR__ . '/../includes/slug_map.php';

if (!isset($_SESSION['dev_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Login required']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store');

$pid  = trim($_GET['pid']  ?? '');
$path = trim($_GET['path'] ?? 'strata');
if (empty($pid)) { http_response_code(400); echo json_encode(['error' => 'PID required']); exit; }
if (!in_array($path, ['strata','rental'])) $path = 'strata';

// ── 1. Core lot data (must succeed) ──────────────────────────
try {
    $stmt = $pdo->prepare("SELECT * FROM plex_properties WHERE pid = ? LIMIT 1");
    $stmt->execute([$pid]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'detail' => $e->getMessage()]);
    exit;
}

if (!$property) {
    http_response_code(404);
    echo json_encode(['error' => 'Property not found']);
    exit;
}

if ((float)$property['lot_area_sqm'] > 3000) {
    http_response_code(403);
    echo json_encode(['error' => 'Lot exceeds maximum area for residential multiplex assessment']);
    exit;
}

$slug = wynston_resolve_slug($property['neighbourhood_slug']);
$city = strtolower($property['city'] ?? 'vancouver');
if (empty($slug)) $slug = 'metro-vancouver';

// ── 2. Construction costs ─────────────────────────────────────
$build_cost_psf = 420.00;
try {
    $stmt = $pdo->prepare("
        SELECT cost_standard_low, cost_standard_high
        FROM construction_costs
        WHERE neighbourhood_slug = ?
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $costs = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($costs && $costs['cost_standard_low']) {
        $build_cost_psf = ((float)$costs['cost_standard_low'] + (float)$costs['cost_standard_high']) / 2;
    }
} catch (PDOException $e) {}

// ── 3. Market stats — REBGV sold duplex $/sqft (used in pro forma) ───────────
$market_sold = null;
$market_window = 'none';

try {
    $stmt = $pdo->prepare("
        SELECT
            SUM(avg_sold_psf_duplex * sales_count_duplex) / NULLIF(SUM(sales_count_duplex),0) AS avg_sold_psf_duplex,
            SUM(sales_count_duplex)    AS sales_count,
            MAX(data_month)            AS data_month,
            MIN(data_month)            AS earliest_month,
            AVG(days_on_market_duplex) AS dom_duplex
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ?
          AND csv_type IN ('hpi_duplex','duplex')
          AND is_active = 1
          AND avg_sold_psf_duplex > 0
          AND sales_count_duplex  > 0
          AND data_month >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
    ");
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['sales_count'] > 0) {
        $market_sold = $row;
        $market_window = '24mo';
    }
} catch (PDOException $e) {}

if (!$market_sold) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                SUM(avg_sold_psf_duplex * sales_count_duplex) / NULLIF(SUM(sales_count_duplex),0) AS avg_sold_psf_duplex,
                SUM(sales_count_duplex)    AS sales_count,
                MAX(data_month)            AS data_month,
                MIN(data_month)            AS earliest_month,
                AVG(days_on_market_duplex) AS dom_duplex
            FROM monthly_market_stats
            WHERE neighbourhood_slug = ?
              AND csv_type IN ('hpi_duplex','duplex')
              AND is_active = 1
              AND avg_sold_psf_duplex > 0
              AND sales_count_duplex  > 0
              AND data_month >= '2020-01-01'
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['sales_count'] > 0) {
            $market_sold = $row;
            $market_window = 'fallback_2020';
        }
    } catch (PDOException $e) { $market_sold = null; }
}

// ── 3b. Detached benchmark — reference only, NOT used in any calculation ─────
$detached_benchmark = null;
$detached_window = 'none';
$det_row = null;

try {
    $stmt = $pdo->prepare("
        SELECT
            SUM(avg_sold_psf_duplex * sales_count_duplex) / NULLIF(SUM(sales_count_duplex),0) AS avg_sold_psf_detached,
            SUM(sales_count_duplex)    AS sales_count_detached,
            MAX(data_month)            AS detached_data_month
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ?
          AND csv_type = 'detached'
          AND is_active = 1
          AND avg_sold_psf_duplex > 0
          AND sales_count_duplex  > 0
          AND data_month >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
    ");
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['sales_count_detached'] > 0) {
        $det_row = $row; $detached_window = '24mo';
    }
} catch (PDOException $e) {}

if (!$det_row) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                SUM(avg_sold_psf_duplex * sales_count_duplex) / NULLIF(SUM(sales_count_duplex),0) AS avg_sold_psf_detached,
                SUM(sales_count_duplex)    AS sales_count_detached,
                MAX(data_month)            AS detached_data_month
            FROM monthly_market_stats
            WHERE neighbourhood_slug = ?
              AND csv_type = 'detached'
              AND is_active = 1
              AND avg_sold_psf_duplex > 0
              AND sales_count_duplex  > 0
              AND data_month >= '2020-01-01'
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['sales_count_detached'] > 0) {
            $det_row = $row; $detached_window = 'fallback_2020';
        }
    } catch (PDOException $e) { $det_row = null; }
}

if ($det_row && (float)$det_row['avg_sold_psf_detached'] > 0) {
    $detached_benchmark = [
        'avg_psf'     => round((float)$det_row['avg_sold_psf_detached']),
        'sales_count' => (int)$det_row['sales_count_detached'],
        'data_month'  => $det_row['detached_data_month'],
        'window'      => $detached_window,
    ];
}

// ── 3b. Rental data — all 3 sources ──────────────────────────
$rent_livrent = null;
$rent_rebgv   = null;
try {
    $stmt = $pdo->prepare("
        SELECT avg_rent_1br, avg_rent_2br, avg_rent_3br
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ?
          AND csv_type = 'rental_livrent'
          AND is_active = 1
        ORDER BY data_month DESC LIMIT 1
    ");
    $stmt->execute([$slug]);
    $rent_livrent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmt = $pdo->prepare("
        SELECT avg_rent_1br, avg_rent_2br, avg_rent_3br
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ?
          AND csv_type IN ('rental_rebgv','rental')
          AND is_active = 1
        ORDER BY data_month DESC LIMIT 1
    ");
    $stmt->execute([$slug]);
    $rent_rebgv = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) {}

// ── 4. Previous month DOM from neighbourhood_hpi_history ─────
$dom_current  = 0;
$dom_previous = 0;
try {
    $stmt = $pdo->prepare("
        SELECT h.dom_duplex, h.month_year
        FROM neighbourhood_hpi_history h
        JOIN neighbourhoods n ON n.id = h.neighbourhood_id
        WHERE n.slug = ?
          AND h.dom_duplex IS NOT NULL
        ORDER BY h.month_year DESC
        LIMIT 2
    ");
    $stmt->execute([$slug]);
    $dom_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($dom_rows) >= 1) $dom_current  = (int)$dom_rows[0]['dom_duplex'];
    if (count($dom_rows) >= 2) $dom_previous = (int)$dom_rows[1]['dom_duplex'];
} catch (PDOException $e) {}

// ── 5. CMHC benchmarks ────────────────────────────────────────
$cmhc = null;
try {
    $stmt = $pdo->prepare("
        SELECT benchmark_1br, benchmark_2br, benchmark_3br
        FROM cmhc_benchmarks
        WHERE neighbourhood_slug = ?
        ORDER BY year DESC LIMIT 1
    ");
    $stmt->execute([$slug]);
    $cmhc = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $cmhc = null; }

// ── 6. Bank forecasts ─────────────────────────────────────────
$bank_forecasts = [];
try {
    $stmt = $pdo->prepare("
        SELECT forecast_psf_yoy
        FROM wynston_outlook_inputs
        WHERE quarter = (
            SELECT quarter FROM wynston_outlook_inputs
            WHERE is_active = 1
            ORDER BY created_at DESC LIMIT 1
        )
        AND is_active = 1
    ");
    $stmt->execute();
    $bank_forecasts = array_map('floatval',
        array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'forecast_psf_yoy')
    );
} catch (PDOException $e) { $bank_forecasts = []; }

// ── 7. Pipeline count ─────────────────────────────────────────
$pipeline_count = 0;
$pipeline_avg   = 3;
try {
    $stmt = $pdo->prepare("SELECT AVG(sub.cnt) AS pipeline_avg FROM (SELECT neighbourhood_slug, COUNT(*) AS cnt FROM multi_2025 WHERE show_on_plex_map = 1 AND submit_status IN ('approved','live') GROUP BY neighbourhood_slug) sub");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pipeline_avg = max(1, (int)round($row['pipeline_avg'] ?? 3));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM multi_2025 WHERE neighbourhood_slug = ? AND show_on_plex_map = 1 AND submit_status IN ('approved','live')");
    $stmt->execute([$slug]);
    $pipeline_count = (int)$stmt->fetchColumn();
} catch (PDOException $e) {}

// ── 8. Design match ───────────────────────────────────────────
$design = null;
try {
    $w = (float)$property['lot_width_m'];
    $stmt = $pdo->prepare("SELECT * FROM design_catalogue WHERE min_lot_width_m <= ? AND max_lot_width_m >= ? AND (transit_required = 0 OR (transit_required = 1 AND ? = 1)) AND is_active = 1 ORDER BY min_lot_width_m DESC LIMIT 1");
    $stmt->execute([$w, $w, (int)$property['transit_proximate']]);
    $design = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $design = null; }

// ── 9. Load financing scenario (Session B) ───────────────────────
// Builder selects financing path via URL param &financing_scenario=...
// Defaults to the scenario marked is_default=1 in admin (CMHC MLI Select).
// Valid keys: cmhc_mli | conventional | private | all_cash
$requested_scenario = trim($_GET['financing_scenario'] ?? '');
$valid_scenarios    = ['cmhc_mli','conventional','private','all_cash'];
if (!in_array($requested_scenario, $valid_scenarios, true)) {
    $requested_scenario = '';  // empty = use default
}

$fa = null;
try {
    if ($requested_scenario !== '') {
        $stmt = $pdo->prepare("SELECT * FROM financing_assumptions WHERE scenario_key = ? LIMIT 1");
        $stmt->execute([$requested_scenario]);
        $fa = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Fallback chain: requested → default → any row
    if (!$fa) {
        $fa = $pdo->query("SELECT * FROM financing_assumptions WHERE is_default = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }
    if (!$fa) {
        $fa = $pdo->query("SELECT * FROM financing_assumptions ORDER BY sort_order ASC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $fa = null; }

// Scenario identity for response
$fa_scenario_key       = $fa ? ($fa['scenario_key']   ?? 'cmhc_mli')          : 'cmhc_mli';
$fa_scenario_label     = $fa ? ($fa['scenario_label'] ?? 'CMHC MLI Select')   : 'CMHC MLI Select';
$fa_requires_covenant  = $fa ? ((int)($fa['requires_covenant'] ?? 0) === 1)   : false;
$fa_is_all_cash        = ($fa_scenario_key === 'all_cash');

// Operating assumptions (shared across all scenarios — admin-set per scenario)
$fa_market_cap_rate   = $fa ? ((float)$fa['market_cap_rate_pct'] / 100) : 0.040;
$fa_vacancy           = $fa ? ((float)$fa['vacancy_rate_pct']    / 100) : 0.050;
$fa_rent_growth_pct   = $fa ? (float)($fa['rent_growth_pct']     ?? 3.0) : 3.0;
$fa_opex_growth_pct   = $fa ? (float)($fa['opex_growth_pct']     ?? 2.5) : 2.5;
$fa_mortgage_stress_mode = $fa ? ($fa['mortgage_stress_mode']    ?? 'fixed') : 'fixed';
$fa_mortgage_stress_bps  = $fa ? (int)($fa['mortgage_stress_bps']?? 100)    : 100;

// ── DOM trend ─────────────────────────────────────────────────
$dom_diff = $dom_current - $dom_previous;
if      ($dom_diff < -1) { $dom_arrow = '↓'; $dom_colour = 'green'; $dom_label = 'Faster than last month'; }
elseif  ($dom_diff >  1) { $dom_arrow = '↑'; $dom_colour = 'amber'; $dom_label = 'Slower than last month'; }
else                      { $dom_arrow = '—'; $dom_colour = 'gray';  $dom_label = 'Stable'; }

// ── Eligibility ───────────────────────────────────────────────
$lot_width = !empty($property['frontage_override_m'])
    ? (float)$property['frontage_override_m']
    : (float)$property['lot_width_m'];
$lot_area  = (float)$property['lot_area_sqm'];
$transit   = (bool)$property['transit_proximate'];
$lane      = (bool)$property['lane_access'];
$is_peat   = (bool)$property['peat_zone'];

$eligibility = [
    '3_unit' => ($lot_width >= 7.5  && $lot_area >= 200 && $lane),
    '4_unit' => ($lot_width >= 10.0 && $lot_area >= 306 && $lane),
    '6_unit' => ($lot_width >= 15.1 && $lot_area >= 557 && $lane && $transit),
    '8_unit' => ($lot_width >= 15.1 && $lot_area >= 557 && $lane && $transit),
];
$max_units = match(true) {
    $eligibility['8_unit'] => 8,
    $eligibility['6_unit'] => 6,
    $eligibility['4_unit'] => 4,
    $eligibility['3_unit'] => 3,
    default                 => 0,
};
$warning_149 = ($lot_width >= 14.5 && $lot_width < 15.1);

// ── Calculations ──────────────────────────────────────────────
$calculator   = new WynstonCalculator();
$avg_sold_psf = (float)($market_sold['avg_sold_psf_duplex'] ?? 985.00);
$sales_count  = (int)($market_sold['sales_count']           ?? 0);
$land_value   = (float)($property['assessed_land_value']    ?? 1500000);

$unit_mix = match(true) {
    $max_units >= 6 => ['1br' => 2, '2br' => 3, '3br' => 1],
    $max_units >= 4 => ['1br' => 1, '2br' => 2, '3br' => 1],
    $max_units >= 3 => ['1br' => 1, '2br' => 2, '3br' => 0],
    default         => ['1br' => 0, '2br' => 2, '3br' => 0],
};

// ── 3-comparable rental mid-point ────────────────────────────
$rent_midpoint = $calculator->calculateRentalMidPoint(
    livrent: [
        '1br' => (float)($rent_livrent['avg_rent_1br'] ?? 0),
        '2br' => (float)($rent_livrent['avg_rent_2br'] ?? 0),
        '3br' => (float)($rent_livrent['avg_rent_3br'] ?? 0),
    ],
    rebgv: [
        '1br' => (float)($rent_rebgv['avg_rent_1br'] ?? 0),
        '2br' => (float)($rent_rebgv['avg_rent_2br'] ?? 0),
        '3br' => (float)($rent_rebgv['avg_rent_3br'] ?? 0),
    ],
    cmhc: [
        '1br' => (float)($cmhc['benchmark_1br'] ?? 0),
        '2br' => (float)($cmhc['benchmark_2br'] ?? 0),
        '3br' => (float)($cmhc['benchmark_3br'] ?? 0),
    ]
);

// ── Rental override URL params (NEW — matches Session 08 pattern) ─────
// Each override is optional; if not present, the computed defaults are used.
$vacancy_override         = (isset($_GET['vacancy_override'])         && $_GET['vacancy_override']         !== '') ? (float)$_GET['vacancy_override']         : null;
$op_expense_override      = (isset($_GET['op_expense_override'])      && $_GET['op_expense_override']      !== '') ? (float)$_GET['op_expense_override']      : null;
$density_bonus_override   = (isset($_GET['density_bonus_override'])   && $_GET['density_bonus_override']   !== '') ? (float)$_GET['density_bonus_override']   : null;
$rent_1br_override        = (isset($_GET['rent_1br_override'])        && $_GET['rent_1br_override']        !== '') ? (float)$_GET['rent_1br_override']        : null;
$rent_2br_override        = (isset($_GET['rent_2br_override'])        && $_GET['rent_2br_override']        !== '') ? (float)$_GET['rent_2br_override']        : null;
$rent_3br_override        = (isset($_GET['rent_3br_override'])        && $_GET['rent_3br_override']        !== '') ? (float)$_GET['rent_3br_override']        : null;
$market_cap_rate_override = (isset($_GET['market_cap_rate_override']) && $_GET['market_cap_rate_override'] !== '') ? (float)$_GET['market_cap_rate_override'] : null;

// Session NEW — rental has its own independent land + build overrides
$rental_land_override     = (isset($_GET['rental_land_override'])     && $_GET['rental_land_override']     !== '') ? (float)$_GET['rental_land_override']     : null;
$rental_build_psf_override= (isset($_GET['rental_build_psf_override'])&& $_GET['rental_build_psf_override']!== '') ? (float)$_GET['rental_build_psf_override']: null;

// Session NEW — strata construction financing overrides
$strata_cfin_ltc          = (isset($_GET['strata_cfin_ltc'])          && $_GET['strata_cfin_ltc']          !== '') ? (float)$_GET['strata_cfin_ltc']          : null;
$strata_cfin_rate         = (isset($_GET['strata_cfin_rate'])         && $_GET['strata_cfin_rate']         !== '') ? (float)$_GET['strata_cfin_rate']         : null;
$strata_cfin_term         = (isset($_GET['strata_cfin_term'])         && $_GET['strata_cfin_term']         !== '') ? (int)  $_GET['strata_cfin_term']         : null;

// Session 16 — strata All Cash flag (zero out construction financing when builder has no loan)
$strata_all_cash          = isset($_GET['strata_all_cash']) && ($_GET['strata_all_cash'] === '1' || $_GET['strata_all_cash'] === 'true');

// Strata overrides (Session 08, preserved)
$land_override            = (isset($_GET['land_override'])            && $_GET['land_override']            !== '') ? (float)$_GET['land_override']            : null;
$build_psf_override       = (isset($_GET['build_psf_override'])       && $_GET['build_psf_override']       !== '') ? (float)$_GET['build_psf_override']       : null;
$psf_override             = (isset($_GET['psf_override'])             && $_GET['psf_override']             !== '') ? (float)$_GET['psf_override']             : null;

// Per-bedroom rent: override > midpoint > hardcoded fallback
$market_rents_final = [
    '1br' => $rent_1br_override ?? ($rent_midpoint['1br'] ?: 2100.0),
    '2br' => $rent_2br_override ?? ($rent_midpoint['2br'] ?: 2750.0),
    '3br' => $rent_3br_override ?? ($rent_midpoint['3br'] ?: 3200.0),
];
$cmhc_rents_final = [
    '1br' => (float)($cmhc['benchmark_1br'] ?? 1875),
    '2br' => (float)($cmhc['benchmark_2br'] ?? 2400),
    '3br' => (float)($cmhc['benchmark_3br'] ?? 2900),
];

// Market cap rate: override > financing_assumptions row > hardcoded 4.0%
$market_cap_rate_used = $market_cap_rate_override ?? $fa_market_cap_rate;
// Vacancy: override > financing_assumptions row > calculator default 5%
$vacancy_rate_used    = $vacancy_override        ?? $fa_vacancy;

// Strata uses its own land/build (existing override mechanism)
$strata_land_value = $land_override      ?? $land_value;
$strata_build_psf  = $build_psf_override ?? $build_cost_psf;
// psf_override (strata HPI toggle — duplex vs detached) still applied to avg_sold_psf
$strata_avg_sold_psf = $psf_override ?? $avg_sold_psf;

// Rental has independent land + build overrides (Session NEW)
$rental_land_value = $rental_land_override     ?? $land_value;
$rental_build_psf  = $rental_build_psf_override ?? $build_cost_psf;

$strata = $calculator->calculateStrataProForma(
    lot_area_sqm:         $lot_area,
    assessed_land_value:  $strata_land_value,
    build_cost_psf:       $strata_build_psf,
    avg_sold_psf:         $strata_avg_sold_psf,
    is_peat_zone:         $is_peat,
    city:                 $city,
    construction_fin_ltc:  $strata_cfin_ltc,
    construction_fin_rate: $strata_cfin_rate,
    construction_fin_term: $strata_cfin_term,
    use_all_cash_construction: $strata_all_cash
);

$rental = $calculator->calculateRentalProForma(
    lot_area_sqm:           $lot_area,
    assessed_land_value:    $rental_land_value,
    build_cost_psf:         $rental_build_psf,
    density_bonus_psf_rate: $density_bonus_override,
    is_peat_zone:           $is_peat,
    unit_mix:               $unit_mix,
    market_rents:           $market_rents_final,
    cmhc_benchmarks:        $cmhc_rents_final,
    vacancy_rate:           $vacancy_rate_used,
    operating_expense_rate: $op_expense_override,
    city:                   $city,
    market_cap_rate:        $market_cap_rate_used
);

// ── Population data (Layer 4) ────────────────────────────────
$household_growth_pct = 0.0;
$unit_growth_pct      = 0.0;
try {
    $stmt = $pdo->prepare("
        SELECT census_year, total_households, housing_units_total
        FROM   neighbourhood_population
        WHERE  neighbourhood_slug = ?
        ORDER BY census_year ASC
    ");
    $stmt->execute([$slug]);
    $pop_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($pop_rows) >= 2) {
        $y_old = $pop_rows[0];
        $y_new = $pop_rows[count($pop_rows) - 1];

        if ((int)$y_old['total_households'] > 0) {
            $household_growth_pct = (($y_new['total_households'] - $y_old['total_households'])
                / $y_old['total_households']) * 100;
        }
        if ((int)$y_old['housing_units_total'] > 0) {
            $unit_growth_pct = (($y_new['housing_units_total'] - $y_old['housing_units_total'])
                / $y_old['housing_units_total']) * 100;
        }
    }
} catch (PDOException $e) {}

// ── Wynston Outlook — read pre-calculated row from wynston_outlook ────────
$outlook = null;
try {
    $stmt = $pdo->prepare("
        SELECT weighted_outlook, confidence_tier, confidence_band_low,
               confidence_band_high, macro_signal, local_momentum,
               pipeline_signal, comp_count, quarter, calculated_at
        FROM wynston_outlook
        WHERE neighbourhood_slug = ?
          AND is_active = 1
        ORDER BY calculated_at DESC
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $outlook_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($outlook_row) {
        $w_outlook        = (float)$outlook_row['weighted_outlook'];
        $current_psf      = (float)($market_sold['avg_sold_psf_duplex'] ?? 985.00);
        $outlook_psf      = round($current_psf * (1 + $w_outlook / 100), 2);
        $current_margin   = round($current_psf - $build_cost_psf, 2);
        $projected_margin = round($outlook_psf  - $build_cost_psf, 2);
        $tier             = (int)$outlook_row['confidence_tier'];

        $weights_map = [
            1 => ['macro' => 0.40, 'local' => 0.40, 'pipeline' => 0.20],
            2 => ['macro' => 0.55, 'local' => 0.25, 'pipeline' => 0.20],
            3 => ['macro' => 0.70, 'local' => 0.10, 'pipeline' => 0.20],
        ];
        $weights_used = $weights_map[$tier] ?? $weights_map[3];

        $outlook = [
            'outlook_pct'            => $w_outlook,
            'outlook_psf'            => $outlook_psf,
            'current_finished_psf'   => $current_psf,
            'current_build_psf'      => $build_cost_psf,
            'current_margin_psf'     => $current_margin,
            'projected_margin_psf'   => $projected_margin,
            'margin_improvement_psf' => round($projected_margin - $current_margin, 2),
            'confidence_tier'        => $tier,
            'confidence_low_pct'     => (float)$outlook_row['confidence_band_low'],
            'confidence_high_pct'    => (float)$outlook_row['confidence_band_high'],
            'macro_signal_pct'       => (float)$outlook_row['macro_signal'],
            'local_momentum_pct'     => (float)$outlook_row['local_momentum'],
            'pipeline_signal_pct'    => (float)$outlook_row['pipeline_signal'],
            'weights_used'           => $weights_used,
            'comp_count'             => (int)$outlook_row['comp_count'],
            'forecasts_used'         => count($bank_forecasts),
            'quarter'                => $outlook_row['quarter'],
            'calculated_at'          => $outlook_row['calculated_at'],
        ];
    }
} catch (PDOException $e) { $outlook = null; }

$confidence_tier = $calculator->getConfidenceTier($sales_count, $market_window);

// ── Year 1/5/10 cash flow projection ──────────────────────────
// Scenario-driven financing (Session B). Defaults come from the selected
// scenario row; builder can override any field via URL params:
//   fin_ltc, fin_rate, fin_amort, fin_premium
// All Cash scenario short-circuits: zero debt, 100% equity.

$fa_ltc_default      = $fa ? ((float)$fa['ltc_pct']           / 100) : 0.75;
$fa_rate_default     = $fa ? ((float)$fa['interest_rate_pct'] / 100) : 0.0525;
$fa_amort_default    = $fa ? (int)  $fa['amortization_years']         : 40;
$fa_ins_prem_default = $fa ? ((float)$fa['insurance_prem_pct'] / 100) : 0.04;

// Per-field overrides from URL. Only apply if explicitly provided.
$fa_ltc      = isset($_GET['fin_ltc'])     ? max(0, min(0.95, (float)$_GET['fin_ltc']))     : $fa_ltc_default;
$fa_rate     = isset($_GET['fin_rate'])    ? max(0, min(0.20, (float)$_GET['fin_rate']))    : $fa_rate_default;
$fa_amort    = isset($_GET['fin_amort'])   ? max(0, min(50,   (int)  $_GET['fin_amort']))   : $fa_amort_default;
$fa_ins_prem = isset($_GET['fin_premium']) ? max(0, min(0.10, (float)$_GET['fin_premium'])) : $fa_ins_prem_default;

// All Cash: force debt to zero regardless of scenario LTC
if ($fa_is_all_cash) {
    $fa_ltc      = 0.0;
    $fa_rate     = 0.0;
    $fa_amort    = 0;
    $fa_ins_prem = 0.0;
}

$r_loan_base_est   = $rental['total_project_cost'] * $fa_ltc;
$r_loan_total_est  = $r_loan_base_est * (1 + $fa_ins_prem);
$r_monthly_rate    = $fa_rate / 12;
$r_n_payments      = $fa_amort * 12;

if ($fa_is_all_cash || $r_loan_total_est <= 0 || $r_n_payments <= 0) {
    $r_monthly_pmt_est = 0.0;
} elseif ($r_monthly_rate > 0) {
    $r_monthly_pmt_est = $r_loan_total_est * ($r_monthly_rate * pow(1+$r_monthly_rate, $r_n_payments)) / (pow(1+$r_monthly_rate, $r_n_payments) - 1);
} else {
    $r_monthly_pmt_est = $r_loan_total_est / $r_n_payments;
}
$r_annual_debt_est = $r_monthly_pmt_est * 12;

$cash_flow_projection = $calculator->projectRentalCashFlow(
    year1_gross_monthly:  $rental['gross_monthly'],
    year1_opex_amount:    $rental['opex_amount'],
    year1_debt_service:   $r_annual_debt_est,
    vacancy_rate:         $rental['vacancy_rate'],
    rent_growth_pct:      $fa_rent_growth_pct,
    opex_growth_pct:      $fa_opex_growth_pct,
    mortgage_stress_mode: $fa_is_all_cash ? 'fixed' : $fa_mortgage_stress_mode,
    mortgage_stress_bps:  $fa_is_all_cash ? 0       : $fa_mortgage_stress_bps,
    loan_balance_approx:  $r_loan_total_est
);

// Attach debt/equity summary so panel can show them without recalculating.
// Session B: includes scenario identity + all_cash flag for conditional UI.
$rental_financing_summary = [
    'scenario_key'       => $fa_scenario_key,
    'scenario_label'     => $fa_scenario_label,
    'is_all_cash'        => $fa_is_all_cash,
    'requires_covenant'  => $fa_requires_covenant,
    'loan_total_est'     => round($r_loan_total_est, 2),
    'monthly_pmt_est'    => round($r_monthly_pmt_est, 2),
    'annual_debt_est'    => round($r_annual_debt_est, 2),
    'equity_required'    => $fa_is_all_cash
                              ? round($rental['total_project_cost'], 2)
                              : round($rental['total_project_cost'] * (1 - $fa_ltc), 2),
    'ltc_pct'            => round($fa_ltc * 100, 1),
    'interest_rate_pct'  => round($fa_rate * 100, 2),
    'amort_years'        => (int)$fa_amort,
    'insurance_prem_pct' => round($fa_ins_prem * 100, 2),
];

// ── Response ──────────────────────────────────────────────────
echo json_encode([
    'property' => [
        'pid'               => $property['pid'],
        'address'           => $property['address'],
        'city'              => $property['city'],
        'neighbourhood'     => $slug,
        'lot_width_m'       => $lot_width,
        'lot_width_ft'      => round($lot_width / 0.3048, 1),
        'lot_area_sqm'      => $lot_area,
        'lot_area_sqft'     => round($lot_area * 10.7639, 0),
        'zoning'            => $property['zoning'],
        'lane_access'       => $lane,
        'transit_proximate' => $transit,
        'nearest_ftn_m'     => $property['nearest_ftn_stop_m'],
        'assessed_value'    => $property['assessed_land_value'],
        'heritage_category' => $property['heritage_category'],
        'peat_zone'         => $is_peat,
        'floodplain_risk'   => $property['floodplain_risk'] ?? 'none',
        'covenant_present'  => (bool)($property['covenant_present'] ?? false),
        'covenant_types'    => $property['covenant_types'] ?? null,
        'easement_present'  => (bool)($property['easement_present'] ?? false),
        'easement_types'    => $property['easement_types'] ?? null,
    ],
    'eligibility' => [
        'max_units'    => $max_units,
        'breakdown'    => $eligibility,
        'warning_149m' => $warning_149,
        'parking_req'  => $transit ? 0 : round($max_units * 0.5),
    ],
    'confidence' => $confidence_tier,
    'dom' => [
        'duplex_current'  => $dom_current,
        'duplex_previous' => $dom_previous,
        'diff'            => $dom_diff,
        'arrow'           => $dom_arrow,
        'colour'          => $dom_colour,
        'label'           => $dom_label,
    ],
    'strata'  => $strata,
    'rental'  => array_merge($rental, [
        'rent_sources'   => $rent_midpoint['detail'] ?? [],
        'sources_used'   => $rent_midpoint['sources_used'] ?? [],
        'fallback_used'  => $rent_midpoint['fallback_used'] ?? ['1br'=>false,'2br'=>false,'3br'=>false],
        'financing'      => $rental_financing_summary,
        'cash_flow'      => $cash_flow_projection,
    ]),
    'outlook' => $outlook,
    'design'  => $design ? [
        'design_id'    => $design['design_id'],
        'design_name'  => $design['design_name'],
        'catalogue'    => $design['catalogue'],
        'external_url' => $design['external_url'],
        'thumbnail'    => $design['thumbnail_img'],
        'cost_low_psf' => $design['cost_low_psf'],
        'cost_high_psf'=> $design['cost_high_psf'],
        'saving_note'  => $design['saving_note'],
    ] : null,
    'model_3d' => [
        'recommended_type' => $lot_width >= 15.1 ? '6plex' : ($lot_width >= 10.0 ? '4plex' : 'duplex'),
        'preferred_style'  => $property['preferred_model_style'] ?? 'modern',
    ],
    'path_requested' => $path,
    'market_data' => [
        'avg_sold_psf'        => $avg_sold_psf,
        'sales_count'         => $sales_count,
        'data_month'          => $market_sold['data_month'] ?? null,
        'earliest_month'      => $market_sold['earliest_month'] ?? null,
        'data_window'         => $market_window,
        'using_fallback'      => ($sales_count === 0),
        'detached_benchmark'  => $detached_benchmark,
    ],
], JSON_PRETTY_PRINT);