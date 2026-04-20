<?php

/**
 * api/search.php
 *
 * Address and PID search endpoint for the Wynston Plex Map.
 * Called as user types in the search box — no auth required (Gate 1 visible).
 *
 * GET params:
 *   q  (required) — search term: partial address, full address, or PID
 *
 * Returns JSON array of up to 10 matching lots, ordered by match quality.
 *
 * Handles:
 *   PID format  "013-092-839" or "013092839" → exact PID match
 *   Full addr   "1125 Vimy Crescent"         → starts-with match first
 *   Partial     "Vimy"                        → LIKE match on address
 *
 * Usage:
 *   /api/search.php?q=vimy
 *   /api/search.php?q=013-092-839
 *   /api/search.php?q=1125+Vimy
 */

// ── Bootstrap ────────────────────────────────────────────────
$host = 'localhost';
$db   = 'u990588858_Property';
$user = 'u990588858_Multiplex';
$pass = 'Concac1979$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store');

// ── Input ─────────────────────────────────────────────────────
$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// ── Detect PID pattern ────────────────────────────────────────
// Accepts: "013-092-839" or "013092839" (9 digits, with or without dashes)
$is_pid = false;
$pid_clean = preg_replace('/[^0-9]/', '', $q);

if (strlen($pid_clean) === 9 && ctype_digit($pid_clean)) {
    $is_pid = true;
    // Normalise to dashed format stored in plex_properties
    $pid_formatted = substr($pid_clean, 0, 3) . '-' . substr($pid_clean, 3, 3) . '-' . substr($pid_clean, 6, 3);
}

// ── Query ─────────────────────────────────────────────────────
try {

    if ($is_pid) {
        // Exact PID lookup
        $stmt = $pdo->prepare("
            SELECT pid,
                   address,
                   lat,
                   lng,
                   lot_width_m,
                   lot_area_sqm,
                   transit_proximate,
                   lane_access,
                   neighbourhood_slug
            FROM   plex_properties
            WHERE  pid = ?
              AND  lat IS NOT NULL
            LIMIT  1
        ");
        $stmt->execute([$pid_formatted]);

    } else {
        // Address search — three match tiers ordered by quality:
        //   Score 1: address starts with search term (e.g. "1125 Vimy")
        //   Score 2: address contains term after a space (word boundary)
        //   Score 3: address contains term anywhere (substring)
        //
        // LIMIT 10 — enough for a clean dropdown without flooding results
        $like_exact = $q . '%';            // starts with
        $like_word  = '% ' . $q . '%';    // word-starts with
        $like_any   = '%' . $q . '%';     // contains anywhere

        $stmt = $pdo->prepare("
            SELECT pid,
                   address,
                   lat,
                   lng,
                   lot_width_m,
                   lot_area_sqm,
                   transit_proximate,
                   lane_access,
                   neighbourhood_slug,
                   CASE
                     WHEN address LIKE ? THEN 1
                     WHEN address LIKE ? THEN 2
                     ELSE 3
                   END AS match_score
            FROM   plex_properties
            WHERE  address LIKE ?
              AND  lat IS NOT NULL
            ORDER BY match_score ASC, address ASC
            LIMIT  10
        ");
        $stmt->execute([$like_exact, $like_word, $like_any]);
    }

    $rows = $stmt->fetchAll();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'detail' => $e->getMessage()]);
    exit;
}

// ── Format response ───────────────────────────────────────────
$results = array_map(function($row) {
    return [
        'pid'               => $row['pid'],
        'address'           => $row['address'],
        'lat'               => (float)$row['lat'],
        'lng'               => (float)$row['lng'],
        'lot_width_m'       => (float)$row['lot_width_m'],
        'lot_area_sqm'      => (float)$row['lot_area_sqm'],
        'transit_proximate' => (int)$row['transit_proximate'],
        'lane_access'       => (int)$row['lane_access'],
        'neighbourhood_slug'=> $row['neighbourhood_slug'],
    ];
}, $rows);

echo json_encode($results);
