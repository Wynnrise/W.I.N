<?php
// ============================================================
// api/plex_costs.php  —  Construction Cost Overrides
// Session 03 — Admin Upload Channels
//
// Accepts: POST with action=save_cost or action=get_costs
//
// save_cost: upsert one neighbourhood cost row
//   Fields: neighbourhood_slug, cost_standard_low, cost_standard_high,
//           cost_luxury_low, cost_luxury_high, dcl_city, dcl_utilities,
//           peat_contingency (default 150000)
//
// get_costs: returns all active cost rows as JSON array
//
// Returns: JSON {success, ...}
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>'DB: '.$e->getMessage()]); exit;
}

$action = $_POST['action'] ?? ($_GET['action'] ?? 'get_costs');

// ── GET all costs ─────────────────────────────────────────────────────────────
if ($action === 'get_costs') {
    $rows = $pdo->query("
        SELECT * FROM construction_costs ORDER BY neighbourhood_slug
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'costs'=>$rows]);
    exit;
}

// ── SAVE / UPSERT one neighbourhood ──────────────────────────────────────────
if ($action === 'save_cost') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $slug = trim($input['neighbourhood_slug'] ?? '');
    if (!$slug) { echo json_encode(['success'=>false,'error'=>'neighbourhood_slug required.']); exit; }

    $fn = fn($k, $default=null) => isset($input[$k]) && $input[$k] !== '' ? (float)$input[$k] : $default;

    // Defaults from master brief if not overridden:
    // Standard: $380–$450, Luxury: $480–$550
    // DCL city: $18.45/sqft, DCL utilities: $2.95/sqft
    $fields = [
        'cost_standard_low'  => $fn('cost_standard_low',  380),
        'cost_standard_high' => $fn('cost_standard_high', 450),
        'cost_luxury_low'    => $fn('cost_luxury_low',    480),
        'cost_luxury_high'   => $fn('cost_luxury_high',   550),
        'dcl_city'           => $fn('dcl_city',           18.45),
        'dcl_utilities'      => $fn('dcl_utilities',      2.95),
        'peat_contingency'   => $fn('peat_contingency',   150000),
        'notes'              => trim($input['notes'] ?? ''),
    ];

    $existing = $pdo->prepare("SELECT id FROM construction_costs WHERE neighbourhood_slug=?");
    $existing->execute([$slug]);
    $exists_id = $existing->fetchColumn();

    if ($exists_id) {
        $set    = implode(', ', array_map(fn($k) => "`$k`=:$k", array_keys($fields)));
        $params = array_combine(array_map(fn($k) => ":$k", array_keys($fields)), array_values($fields));
        $params[':id'] = $exists_id;
        $pdo->prepare("UPDATE construction_costs SET $set, updated_at=NOW() WHERE id=:id")->execute($params);
        echo json_encode(['success'=>true,'action'=>'updated','slug'=>$slug]);
    } else {
        $cols   = '`neighbourhood_slug`, ' . implode(', ', array_map(fn($k) => "`$k`", array_keys($fields)));
        $vals   = ':slug, ' . implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
        $params = array_combine(array_map(fn($k) => ":$k", array_keys($fields)), array_values($fields));
        $params[':slug'] = $slug;
        $pdo->prepare("INSERT INTO construction_costs ($cols, created_at) VALUES ($vals, NOW())")->execute($params);
        echo json_encode(['success'=>true,'action'=>'inserted','slug'=>$slug,'id'=>(int)$pdo->lastInsertId()]);
    }
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action.']);
