<?php
// api/hpi_bulk_save.php — standalone endpoint, no HTML output ever
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'error'=>'Not authorised']);
    exit;
}

header('Content-Type: application/json');

$host = 'localhost'; $db = 'u990588858_Property';
$user = 'u990588858_Multiplex'; $pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>'DB error: '.$e->getMessage()]);
    exit;
}

$rows_json = $_POST['hpi_rows_json'] ?? '[]';
$rows      = json_decode($rows_json, true) ?: [];
$saved = 0;
foreach ($rows as $r) {
    if (empty($r['nb_id']) || empty($r['month_dt'])) continue;
    $nb_id    = (int)$r['nb_id'];
    $nb_slug  = $r['nb_slug'] ?? '';
    $month_dt = $r['month_dt'];
    $psf_dup  = isset($r['psf_duplex']) && $r['psf_duplex'] !== '' ? (int)$r['psf_duplex'] : null;

    $fields = [
        'avg_price'       => $r['price_duplex'] ?? null,
        'price_detached'  => $r['price_detached'] ?? null,
        'price_condo'     => $r['price_condo'] ?? null,
        'price_townhouse' => $r['price_townhouse'] ?? null,
        'price_duplex'    => $r['price_duplex'] ?? null,
        'psf_duplex'      => $psf_dup,
        'hpi_change_mom'  => null,
        'hpi_change_yoy'  => $r['yoy_duplex'] ?? null,
        'dom_detached'    => $r['dom_detached'] ?? null,
        'dom_duplex'      => $r['dom_duplex'] ?? null,
        'dom_condo'       => $r['dom_condo'] ?? null,
        'dom_townhouse'   => $r['dom_townhouse'] ?? null,
        'sales_detached'  => $r['sales_detached'] ?? null,
        'sales_duplex'    => $r['sales_duplex'] ?? null,
        'sales_condo'     => $r['sales_condo'] ?? null,
        'sales_townhouse' => $r['sales_townhouse'] ?? null,
    ];
    try {
        // ── neighbourhood_hpi_history ──────────────────────────────────
        $exists = $pdo->prepare("SELECT id FROM neighbourhood_hpi_history WHERE neighbourhood_id=? AND month_year=?");
        $exists->execute([$nb_id, $month_dt]);
        if ($exists->fetchColumn()) {
            $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($fields)));
            $pdo->prepare("UPDATE neighbourhood_hpi_history SET $sets WHERE neighbourhood_id=:nb_id AND month_year=:month")
                ->execute(array_merge($fields, [':nb_id'=>$nb_id,':month'=>$month_dt]));
        } else {
            $cols = implode(',', array_keys($fields));
            $vals = ':' . implode(',:', array_keys($fields));
            $pdo->prepare("INSERT INTO neighbourhood_hpi_history (neighbourhood_id,month_year,$cols) VALUES (:nb_id,:month,$vals)")
                ->execute(array_merge($fields, [':nb_id'=>$nb_id,':month'=>$month_dt]));
        }

        // ── monthly_market_stats (hpi_duplex) ─────────────────────────
        // Only save if we have a psf value — this drives the confidence tier
        if ($psf_dup && $nb_slug) {
            // Check if a row already exists for this slug + month
            $chk = $pdo->prepare("SELECT id FROM monthly_market_stats
                WHERE neighbourhood_slug=? AND data_month=? AND csv_type='hpi_duplex'");
            $chk->execute([$nb_slug, $month_dt]);
            $existing_id = $chk->fetchColumn();

            if ($existing_id) {
                // Update existing row and make sure it stays active
                $pdo->prepare("UPDATE monthly_market_stats
                    SET price_per_sqft=?, sales_count=?, is_active=1
                    WHERE id=?")
                    ->execute([$psf_dup, $r['sales_duplex'] ?? null, $existing_id]);
            } else {
                // Insert new row as active
                $pdo->prepare("INSERT INTO monthly_market_stats
                    (neighbourhood_slug, data_month, csv_type, price_per_sqft, sales_count, is_active, created_at)
                    VALUES (?, ?, 'hpi_duplex', ?, ?, 1, NOW())")
                    ->execute([$nb_slug, $month_dt, $psf_dup, $r['sales_duplex'] ?? null]);
            }
        }
        $saved++;
    } catch (Exception $e) { /* skip bad rows */ }
}

echo json_encode([
    'success' => true,
    'saved'   => $saved,
    'msg'     => "✅ Upload complete — {$saved} neighbourhoods updated (HPI history + pro forma $/sqft)"
]);
