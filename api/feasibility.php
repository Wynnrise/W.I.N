<?php
/**
 * api/feasibility.php
 * Gate 2 JSON endpoint — full feasibility data for side panel.
 * All secondary table queries wrapped in try/catch so empty/missing
 * tables never kill the endpoint — they return null/defaults instead.
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
        // Use midpoint of standard low/high
        $build_cost_psf = ((float)$costs['cost_standard_low'] + (float)$costs['cost_standard_high']) / 2;
    }
} catch (PDOException $e) {}

// ── 3. Market stats — REBGV sold $/sqft ──────────────────────
// Queries monthly_market_stats by COV neighbourhood_slug.
// Multiple REBGV sub-areas already consolidated to COV slug at upload time.
// AVG weighted by row count (more sales = more rows = more weight automatically).
$market_sold = null;
try {
    $stmt = $pdo->prepare("
        SELECT
            AVG(price_per_sqft)  AS avg_sold_psf_duplex,
            COUNT(*)             AS sales_count,
            MAX(data_month)      AS data_month,
            MAX(days_on_market)  AS dom_duplex
        FROM monthly_market_stats
        WHERE neighbourhood_slug = ?
          AND csv_type IN ('duplex','detached')
          AND is_active = 1
          AND price_per_sqft > 0
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $market_sold = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $market_sold = null; }

// ── 3b. Rental data — all 3 sources ──────────────────────────
$rent_livrent = null;
$rent_rebgv   = null;
try {
    // liv.rent
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

    // REBGV rental (also check old 'rental' type for backward compat)
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

// ── DOM trend ─────────────────────────────────────────────────
$dom_diff = $dom_current - $dom_previous;
if      ($dom_diff < -1) { $dom_arrow = '↓'; $dom_colour = 'green'; $dom_label = 'Faster than last month'; }
elseif  ($dom_diff >  1) { $dom_arrow = '↑'; $dom_colour = 'amber'; $dom_label = 'Slower than last month'; }
else                      { $dom_arrow = '—'; $dom_colour = 'gray';  $dom_label = 'Stable'; }

// ── Eligibility ───────────────────────────────────────────────
$lot_width = (float)$property['lot_width_m'];
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
// Weighted average: liv.rent 45% + REBGV 35% + CMHC 20%
// Missing sources have their weight redistributed automatically
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

// Fallback defaults if no rental data at all
$market_rents_final = [
    '1br' => $rent_midpoint['1br'] ?: 2100.0,
    '2br' => $rent_midpoint['2br'] ?: 2750.0,
    '3br' => $rent_midpoint['3br'] ?: 3200.0,
];
$cmhc_rents_final = [
    '1br' => (float)($cmhc['benchmark_1br'] ?? 1875),
    '2br' => (float)($cmhc['benchmark_2br'] ?? 2400),
    '3br' => (float)($cmhc['benchmark_3br'] ?? 2900),
];

$strata = $calculator->calculateStrataProForma(
    lot_area_sqm: $lot_area, assessed_land_value: $land_value,
    build_cost_psf: $build_cost_psf, avg_sold_psf: $avg_sold_psf,
    is_peat_zone: $is_peat, city: $city
);

$rental = $calculator->calculateRentalProForma(
    lot_area_sqm: $lot_area, assessed_land_value: $land_value,
    build_cost_psf: $build_cost_psf, density_bonus_psf_rate: 40.00,
    is_peat_zone: $is_peat, unit_mix: $unit_mix,
    market_rents:    $market_rents_final,
    cmhc_benchmarks: $cmhc_rents_final,
    vacancy_rate: 0.05, city: $city
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
} catch (PDOException $e) { /* table not yet populated — Layer 4 = 0 */ }

// ── Wynston Outlook ───────────────────────────────────────────
$outlook = null;
if (count($bank_forecasts) >= 4 && $market) {
    $nb_hpi = 5.0; $metro_hpi = 5.0;
    try {
        $stmt = $pdo->prepare("SELECT hpi_change_yoy FROM neighbourhood_hpi_history h JOIN neighbourhoods n ON h.neighbourhood_id = n.id WHERE n.slug = ? ORDER BY month_year DESC LIMIT 1");
        $stmt->execute([$slug]);
        $nb_hpi = (float)($stmt->fetchColumn() ?: 5.0);
        $stmt = $pdo->prepare("SELECT hpi_change_yoy FROM neighbourhood_hpi_history h JOIN neighbourhoods n ON h.neighbourhood_id = n.id WHERE n.slug = 'metro-vancouver' ORDER BY month_year DESC LIMIT 1");
        $stmt->execute();
        $metro_hpi = (float)($stmt->fetchColumn() ?: 5.0);
    } catch (PDOException $e) {}

    $outlook = $calculator->calculateWynstonOutlook(
        current_finished_psf: $avg_sold_psf, current_build_psf: $build_cost_psf,
        bank_forecasts: $bank_forecasts, neighbourhood_hpi_yoy: $nb_hpi,
        metro_hpi_yoy: $metro_hpi, dom_trending_down: ($dom_diff < -1),
        sales_above_average: ($sales_count > 5), units_in_pipeline: $pipeline_count,
        neighbourhood_avg_pipeline: $pipeline_avg, sales_count: $sales_count,
        household_growth_pct: $household_growth_pct,
        unit_growth_pct: $unit_growth_pct
    );
}

$confidence_tier = $calculator->getConfidenceTier($sales_count);

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
        // Attach rent source detail so side panel can show per-source values
        'rent_sources' => $rent_midpoint['detail'] ?? [],
        'sources_used' => $rent_midpoint['sources_used'] ?? [],
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
        'avg_sold_psf'  => $avg_sold_psf,
        'sales_count'   => $sales_count,
        'data_month'    => $market_sold['data_month'] ?? null,
        'using_fallback'=> ($sales_count === 0),
    ],
], JSON_PRETTY_PRINT);