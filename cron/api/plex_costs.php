<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (empty($_SESSION['admin_logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
    'u990588858_Multiplex','Concac1979$',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = trim($input['action'] ?? $_GET['action'] ?? 'get_costs');

if ($action === 'get_costs') {
    $rows = $pdo->query("SELECT * FROM construction_costs ORDER BY neighbourhood_slug")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'costs'=>$rows]); exit;
}

if ($action === 'save_cost') {
    $slug = trim($input['neighbourhood_slug'] ?? '');
    if (!$slug) { echo json_encode(['success'=>false,'error'=>'neighbourhood_slug required']); exit; }

    $fn = fn($k,$d=null) => isset($input[$k])&&$input[$k]!==''?(float)$input[$k]:$d;
    $fields = [
        'cost_standard_low'  => $fn('cost_standard_low',380),
        'cost_standard_high' => $fn('cost_standard_high',450),
        'cost_luxury_low'    => $fn('cost_luxury_low',480),
        'cost_luxury_high'   => $fn('cost_luxury_high',550),
        'dcl_city'           => $fn('dcl_city',18.45),
        'dcl_utilities'      => $fn('dcl_utilities',2.95),
        'peat_contingency'   => $fn('peat_contingency',150000),
        'notes'              => trim($input['notes'] ?? ''),
    ];

    $existing = $pdo->prepare("SELECT id FROM construction_costs WHERE neighbourhood_slug=?");
    $existing->execute([$slug]);
    $exists_id = $existing->fetchColumn();

    if ($exists_id) {
        $set = implode(',',array_map(fn($k)=>"`$k`=:$k",array_keys($fields)));
        $params = array_combine(array_map(fn($k)=>":$k",array_keys($fields)),array_values($fields));
        $params[':id'] = $exists_id;
        $pdo->prepare("UPDATE construction_costs SET $set, updated_at=NOW() WHERE id=:id")->execute($params);
        echo json_encode(['success'=>true,'action'=>'updated','slug'=>$slug]); exit;
    } else {
        $cols = '`neighbourhood_slug`,'.implode(',',array_map(fn($k)=>"`$k`",array_keys($fields)));
        $vals = ':slug,'.implode(',',array_map(fn($k)=>":$k",array_keys($fields)));
        $params = array_combine(array_map(fn($k)=>":$k",array_keys($fields)),array_values($fields));
        $params[':slug'] = $slug;
        $pdo->prepare("INSERT INTO construction_costs ($cols,updated_at) VALUES ($vals,NOW())")->execute($params);
        echo json_encode(['success'=>true,'action'=>'inserted','slug'=>$slug,'id'=>(int)$pdo->lastInsertId()]); exit;
    }
}

echo json_encode(['success'=>false,'error'=>'Unknown action']);
