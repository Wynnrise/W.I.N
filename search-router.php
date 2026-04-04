<?php
// ── Search Router ─────────────────────────────────────────────────────────────
// Reads the "st" (search type) param from the index.php search form
// and sends user to the correct page

$st    = $_GET['st']    ?? 'presale';
$city  = $_GET['city']  ?? '';
$ptype = $_GET['ptype'] ?? '';

// Build query string with only non-empty params
$params = http_build_query(array_filter([
    'city'  => $city,
    'ptype' => $ptype,
]));

if ($st === 'active') {
    // ── Active MLS listings (DDF API data) ──────────────────────────────
    header("Location: active-listings.php" . ($params ? "?$params" : ''));
} else {
    // ── Pre-sale listings (your multi_2025 database) ─────────────────────
    header("Location: half-map.php" . ($params ? "?$params" : ''));
}
exit;