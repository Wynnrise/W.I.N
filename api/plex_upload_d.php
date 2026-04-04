<?php
// ============================================================
// api/plex_upload_d.php  —  Channel D: CMHC Annual Benchmarks
// Session 03 — Admin Upload Channels
//
// Accepts: POST JSON or form fields:
//   neighbourhood_slug, year (YYYY),
//   benchmark_1br, benchmark_2br, benchmark_3br
//
// Upsert logic: one row per neighbourhood per year.
// Returns: JSON {success, id, message}
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

$input  = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$nb_slug     = trim($input['neighbourhood_slug'] ?? '');
$year        = (int)($input['year'] ?? 0);
$bench_1br   = isset($input['benchmark_1br']) && $input['benchmark_1br'] !== '' ? (int)$input['benchmark_1br'] : null;
$bench_2br   = isset($input['benchmark_2br']) && $input['benchmark_2br'] !== '' ? (int)$input['benchmark_2br'] : null;
$bench_3br   = isset($input['benchmark_3br']) && $input['benchmark_3br'] !== '' ? (int)$input['benchmark_3br'] : null;

if (!$nb_slug) {
    echo json_encode(['success'=>false,'error'=>'neighbourhood_slug required.']); exit;
}
if ($year < 2020 || $year > 2035) {
    echo json_encode(['success'=>false,'error'=>'year must be a valid 4-digit year.']); exit;
}
if (!$bench_1br && !$bench_2br && !$bench_3br) {
    echo json_encode(['success'=>false,'error'=>'At least one benchmark rent required.']); exit;
}

// ── Upsert: one row per neighbourhood per year ────────────────────────────────
$existing = $pdo->prepare("SELECT id FROM cmhc_benchmarks WHERE neighbourhood_slug=? AND year=?");
$existing->execute([$nb_slug, $year]);
$exists_id = $existing->fetchColumn();

if ($exists_id) {
    $pdo->prepare("
        UPDATE cmhc_benchmarks
        SET benchmark_1br = COALESCE(:b1, benchmark_1br),
            benchmark_2br = COALESCE(:b2, benchmark_2br),
            benchmark_3br = COALESCE(:b3, benchmark_3br),
            updated_at    = NOW()
        WHERE id = :id
    ")->execute([':b1'=>$bench_1br, ':b2'=>$bench_2br, ':b3'=>$bench_3br, ':id'=>$exists_id]);
    $result_id = $exists_id;
    $action    = 'updated';
} else {
    $pdo->prepare("
        INSERT INTO cmhc_benchmarks
            (neighbourhood_slug, year, benchmark_1br, benchmark_2br, benchmark_3br, created_at)
        VALUES
            (:slug, :year, :b1, :b2, :b3, NOW())
    ")->execute([
        ':slug' => $nb_slug,
        ':year' => $year,
        ':b1'   => $bench_1br,
        ':b2'   => $bench_2br,
        ':b3'   => $bench_3br,
    ]);
    $result_id = (int)$pdo->lastInsertId();
    $action    = 'inserted';
}

echo json_encode([
    'success' => true,
    'id'      => (int)$result_id,
    'action'  => $action,
    'message' => "CMHC benchmark {$action} for {$nb_slug} — {$year}.",
]);
