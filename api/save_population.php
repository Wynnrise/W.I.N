<?php
// api/save_population.php
// POST: save Census population data per neighbourhood per year
// Updates both neighbourhood_population AND neighbourhoods table
// Body: { neighbourhood_slug, census_year, total_population,
//         total_households, housing_units_total, housing_units_owned,
//         housing_units_rented, median_age, median_household_income }

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_domain'   => 'wynston.ca',
    'cookie_samesite' => 'Lax',
]);

header('Content-Type: application/json');
header('Cache-Control: no-store');

// Admin auth — same as plex-data.php
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$slug        = trim($input['neighbourhood_slug'] ?? '');
$year        = (int)($input['census_year'] ?? 0);
$population  = $input['total_population']        !== '' ? (int)$input['total_population']        : null;
$households  = $input['total_households']        !== '' ? (int)$input['total_households']        : null;
$units_total = $input['housing_units_total']     !== '' ? (int)$input['housing_units_total']     : null;
$units_owned = $input['housing_units_owned']     !== '' ? (int)$input['housing_units_owned']     : null;
$units_rent  = $input['housing_units_rented']    !== '' ? (int)$input['housing_units_rented']    : null;
$med_age     = $input['median_age']              !== '' ? (float)$input['median_age']            : null;
$med_income  = $input['median_household_income'] !== '' ? (int)$input['median_household_income'] : null;

if (!$slug) {
    echo json_encode(['success' => false, 'error' => 'Neighbourhood slug required']);
    exit;
}
if ($year < 2016 || $year > 2030) {
    echo json_encode(['success' => false, 'error' => 'Invalid census year']);
    exit;
}
if ($population === null && $households === null && $units_total === null) {
    echo json_encode(['success' => false, 'error' => 'Enter at least population, households, or housing units']);
    exit;
}

// ── 1. Upsert neighbourhood_population ───────────────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO neighbourhood_population
        (neighbourhood_slug, census_year, total_population, total_households,
         housing_units_total, housing_units_owned, housing_units_rented,
         median_age, median_household_income)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        total_population        = VALUES(total_population),
        total_households        = VALUES(total_households),
        housing_units_total     = VALUES(housing_units_total),
        housing_units_owned     = VALUES(housing_units_owned),
        housing_units_rented    = VALUES(housing_units_rented),
        median_age              = VALUES(median_age),
        median_household_income = VALUES(median_household_income),
        updated_at              = NOW()
");
$stmt->execute([
    $slug, $year, $population, $households,
    $units_total, $units_owned, $units_rent,
    $med_age, $med_income
]);

$action = $pdo->lastInsertId() ? 'inserted' : 'updated';

// ── 2. Also update neighbourhoods table (for map-facing pages + PDF) ─────────
// Only update if this is the most recent census year we have for this slug
$latest_year = $pdo->prepare("
    SELECT MAX(census_year) FROM neighbourhood_population
    WHERE neighbourhood_slug = ?
");
$latest_year->execute([$slug]);
$max_year = (int)$latest_year->fetchColumn();

if ($year >= $max_year) {
    // Update the main neighbourhoods table so all existing pages reflect real data
    $nb_update = $pdo->prepare("
        UPDATE neighbourhoods SET
            population          = ?,
            median_income       = ?,
            housing_units_total = ?,
            census_year         = ?
        WHERE slug = ?
    ");
    $nb_update->execute([$population, $med_income, $units_total, $year, $slug]);
    $nb_rows = $nb_update->rowCount();
} else {
    $nb_rows = 0;
}

// ── 3. Calculate supply-demand gap if both years exist ────────────────────────
$gap_data = null;
$both = $pdo->prepare("
    SELECT census_year, total_households, housing_units_total
    FROM neighbourhood_population
    WHERE neighbourhood_slug = ?
    ORDER BY census_year ASC
");
$both->execute([$slug]);
$rows = $both->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) >= 2) {
    $old = $rows[0];
    $new = $rows[count($rows) - 1];

    $hh_growth = $old['total_households'] > 0
        ? round((($new['total_households'] - $old['total_households']) / $old['total_households']) * 100, 1)
        : null;
    $unit_growth = $old['housing_units_total'] > 0
        ? round((($new['housing_units_total'] - $old['housing_units_total']) / $old['housing_units_total']) * 100, 1)
        : null;

    if ($hh_growth !== null && $unit_growth !== null) {
        $gap = round($hh_growth - $unit_growth, 1);
        $gap_data = [
            'from_year'    => (int)$old['census_year'],
            'to_year'      => (int)$new['census_year'],
            'hh_growth'    => $hh_growth,
            'unit_growth'  => $unit_growth,
            'gap'          => $gap,
            'signal'       => $gap > 1 ? 'bullish' : ($gap < -1 ? 'bearish' : 'neutral'),
        ];
    }
}

echo json_encode([
    'success'      => true,
    'action'       => $action,
    'slug'         => $slug,
    'census_year'  => $year,
    'nb_updated'   => $nb_rows > 0,
    'gap_data'     => $gap_data,
    'message'      => ucfirst($action) . " Census data for {$slug} ({$year})" .
                      ($nb_rows > 0 ? ' — neighbourhood page updated' : '') .
                      ($gap_data ? " — supply-demand gap: {$gap_data['gap']}%" : ''),
]);
