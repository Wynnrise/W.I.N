<?php
// Parse JSON body and merge into $_POST so existing code works unchanged
$_json_body = json_decode(file_get_contents("php://input"), true);
if (is_array($_json_body)) {
    foreach ($_json_body as $k => $v) { $_POST[$k] = $v; }
}
// ============================================================
// api/plex_outlook.php  —  Wynston Outlook Quarterly Input
// Session 03 — Admin Upload Channels
//
// Accepts: POST with action=save_inputs or action=calculate
//
// save_inputs:
//   Saves the 6 bank/broker forecast rows to wynston_outlook_inputs.
//   Fields per row: source_name, forecast_psf_yoy, forecast_date
//
// calculate:
//   Reads wynston_outlook_inputs (latest quarter).
//   Runs three-layer Wynston Outlook formula for all neighbourhoods.
//   Stores results in wynston_outlook.
//   Returns JSON {success, neighbourhoods_updated, results[]}
//
// Three-layer formula (from master brief):
//   Macro signal:   RBC/TD/BMO/BCREA/RE-MAX/Royal LePage YoY forecasts
//                   — remove outliers (>1.5 IQR), average remainder
//   Local momentum: neighbourhood HPI vs metro HPI (from neighbourhood_hpi_history)
//                   DOM trend for neighbourhood vs city avg
//                   Sales volume trend
//                   — capped at ±15%
//   Pipeline signal: active multi_2025 projects vs neighbourhood average
//                   — hardcoded at 3 until Session 04 wires live data (FLAG 3)
//
// Confidence tiers (weights from master brief):
//   Tier 1 (5+ comps):  Macro 40% / Local 40% / Pipeline 20%
//   Tier 2 (2-4 comps): Macro 55% / Local 25% / Pipeline 20%
//   Tier 3 (0-1 comps): Macro 70% / Local 10% / Pipeline 20%
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

$action = $_POST['action'] ?? 'save_inputs';

// ══════════════════════════════════════════════════════════════════════════════
// ACTION: save_inputs — store the 6 source forecasts
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'save_inputs') {
    $quarter = trim($_POST['quarter'] ?? ''); // e.g. "2026-Q1"
    if (!preg_match('/^\d{4}-Q[1-4]$/', $quarter)) {
        echo json_encode(['success'=>false,'error'=>'quarter must be YYYY-QN format (e.g. 2026-Q1).']); exit;
    }

    $sources = $_POST['sources'] ?? [];
    if (empty($sources) || !is_array($sources)) {
        echo json_encode(['success'=>false,'error'=>'sources array required.']); exit;
    }

    // Deactivate previous inputs for this quarter
    $pdo->prepare("UPDATE wynston_outlook_inputs SET is_active=0 WHERE quarter=?")->execute([$quarter]);

    $saved = 0;
    $stmt  = $pdo->prepare("
        INSERT INTO wynston_outlook_inputs
            (quarter, source_name, forecast_psf_yoy, forecast_date, is_active, created_at)
        VALUES
            (:quarter, :source, :forecast, :date, 1, NOW())
    ");

    foreach ($sources as $src) {
        $name     = trim($src['source_name'] ?? '');
        $forecast = isset($src['forecast_psf_yoy']) && $src['forecast_psf_yoy'] !== ''
                    ? (float)$src['forecast_psf_yoy'] : null;
        $date     = trim($src['forecast_date'] ?? '');
        if (!$name || $forecast === null) continue;

        $stmt->execute([
            ':quarter'  => $quarter,
            ':source'   => $name,
            ':forecast' => $forecast,
            ':date'     => $date ?: null,
        ]);
        $saved++;
    }

    echo json_encode([
        'success' => true,
        'saved'   => $saved,
        'quarter' => $quarter,
        'message' => "{$saved} forecast inputs saved for {$quarter}. Click Calculate to run the Outlook.",
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION: calculate — run three-layer formula → store in wynston_outlook
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'calculate') {
    $quarter = trim($_POST['quarter'] ?? '');
    if (!preg_match('/^\d{4}-Q[1-4]$/', $quarter)) {
        echo json_encode(['success'=>false,'error'=>'quarter must be YYYY-QN format.']); exit;
    }

    // ── Layer 1: Macro signal (bank/broker forecasts) ─────────────────────────
    $inputs = $pdo->prepare("
        SELECT forecast_psf_yoy FROM wynston_outlook_inputs
        WHERE quarter=? AND is_active=1
        ORDER BY created_at DESC
    ");
    $inputs->execute([$quarter]);
    $forecasts_raw = $inputs->fetchAll(PDO::FETCH_COLUMN);

    if (count($forecasts_raw) < 2) {
        echo json_encode(['success'=>false,'error'=>'Need at least 2 forecast inputs to calculate.']); exit;
    }

    $macro_signal = _outlier_trimmed_mean($forecasts_raw);

    // ── Load all active Vancouver neighbourhoods ───────────────────────────────
    $nbs = $pdo->query("
        SELECT id, slug, name FROM neighbourhoods WHERE is_active=1 ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($nbs)) {
        echo json_encode(['success'=>false,'error'=>'No active neighbourhoods found.']); exit;
    }

    // ── Metro avg DOM and HPI (for local momentum benchmark) ─────────────────
    // Use the most recent month available in hpi_history across all neighbourhoods
    $metro_hpi = $pdo->query("
        SELECT AVG(hpi_benchmark) as avg_hpi, AVG(dom_duplex) as avg_dom
        FROM neighbourhood_hpi_history
        WHERE month_year = (SELECT MAX(month_year) FROM neighbourhood_hpi_history)
    ")->fetch(PDO::FETCH_ASSOC);

    $metro_hpi_val = (float)($metro_hpi['avg_hpi'] ?? 0);
    $metro_dom     = (float)($metro_hpi['avg_dom'] ?? 30);

    // ── Determine quarter date range for sold comps count ─────────────────────
    [$qyear, $qnum] = explode('-Q', $quarter);
    $q_month_start  = date('Y-m-01', mktime(0,0,0, (((int)$qnum - 1) * 3) + 1, 1, (int)$qyear));
    $q_month_end    = date('Y-m-t',  mktime(0,0,0, ((int)$qnum * 3), 1, (int)$qyear));

    $results = [];
    $updated = 0;

    $ins = $pdo->prepare("
        INSERT INTO wynston_outlook
            (neighbourhood_slug, quarter, macro_signal, local_momentum,
             pipeline_signal, weighted_outlook, confidence_tier,
             confidence_band_low, confidence_band_high,
             comp_count, is_active, calculated_at)
        VALUES
            (:slug, :quarter, :macro, :local, :pipeline, :outlook,
             :tier, :band_low, :band_high, :comps, 1, NOW())
        ON DUPLICATE KEY UPDATE
            macro_signal        = VALUES(macro_signal),
            local_momentum      = VALUES(local_momentum),
            pipeline_signal     = VALUES(pipeline_signal),
            weighted_outlook    = VALUES(weighted_outlook),
            confidence_tier     = VALUES(confidence_tier),
            confidence_band_low = VALUES(confidence_band_low),
            confidence_band_high= VALUES(confidence_band_high),
            comp_count          = VALUES(comp_count),
            is_active           = 1,
            calculated_at       = NOW()
    ");

    foreach ($nbs as $nb) {
        $slug = $nb['slug'];

        // ── Comp count (for confidence tier) ──────────────────────────────────
        $comp_q = $pdo->prepare("
            SELECT COUNT(*) FROM monthly_market_stats
            WHERE neighbourhood_slug = ?
              AND data_month BETWEEN ? AND ?
              AND is_active = 1
              AND csv_type IN ('duplex','detached')
        ");
        $comp_q->execute([$slug, $q_month_start, $q_month_end]);
        $comp_count = (int)$comp_q->fetchColumn();

        // Confidence tier
        if ($comp_count >= 5)      $tier = 1;
        elseif ($comp_count >= 2)  $tier = 2;
        else                       $tier = 3;

        // Tier weights
        $weights = match($tier) {
            1 => ['macro'=>0.40, 'local'=>0.40, 'pipeline'=>0.20],
            2 => ['macro'=>0.55, 'local'=>0.25, 'pipeline'=>0.20],
            3 => ['macro'=>0.70, 'local'=>0.10, 'pipeline'=>0.20],
        };

        // ── Layer 2: Local momentum ────────────────────────────────────────────
        $hpi_q = $pdo->prepare("
            SELECT hpi_benchmark, dom_duplex, hpi_change_yoy
            FROM neighbourhood_hpi_history
            WHERE neighbourhood_id = ?
            ORDER BY month_year DESC
            LIMIT 2
        ");
        $hpi_q->execute([$nb['id']]);
        $hpi_rows = $hpi_q->fetchAll(PDO::FETCH_ASSOC);

        $local_momentum = 0.0;
        if (!empty($hpi_rows)) {
            $nb_hpi = (float)($hpi_rows[0]['hpi_benchmark'] ?? 0);
            $nb_dom = (float)($hpi_rows[0]['dom_duplex']    ?? $metro_dom);
            $nb_yoy = (float)($hpi_rows[0]['hpi_change_yoy']?? 0);

            // HPI relative to metro
            $hpi_rel = $metro_hpi_val > 0 ? (($nb_hpi - $metro_hpi_val) / $metro_hpi_val) * 100 : 0;

            // DOM relative to metro (lower DOM = faster market = positive signal)
            $dom_rel = $metro_dom > 0 ? (($metro_dom - $nb_dom) / $metro_dom) * 15 : 0;

            // YoY trend signal
            $yoy_signal = $nb_yoy;

            $local_momentum = ($hpi_rel * 0.4) + ($dom_rel * 0.3) + ($yoy_signal * 0.3);

            // Cap at ±15% as per master brief
            $local_momentum = max(-15, min(15, $local_momentum));
        }

        // ── Layer 3: Pipeline signal ───────────────────────────────────────────
        // FLAG 3: hardcoded at 3 until Session 04 wires live multi_2025 data
        $pipeline_avg     = 3;
        $nb_pipeline      = 3; // will be replaced with live query in Session 04
        $pipeline_signal  = ($nb_pipeline - $pipeline_avg) * 0.5; // small delta signal

        // ── Weighted outlook ───────────────────────────────────────────────────
        $weighted_outlook = round(
            ($macro_signal   * $weights['macro'])   +
            ($local_momentum * $weights['local'])   +
            ($pipeline_signal* $weights['pipeline']),
            2
        );

        // ── Confidence band (±std of inputs, scaled by tier) ──────────────────
        $std_dev    = _std_dev($forecasts_raw);
        $band_mult  = match($tier) { 1 => 1.0, 2 => 1.5, 3 => 2.0 };
        $band_low   = round($weighted_outlook - ($std_dev * $band_mult), 2);
        $band_high  = round($weighted_outlook + ($std_dev * $band_mult), 2);

        $ins->execute([
            ':slug'      => $slug,
            ':quarter'   => $quarter,
            ':macro'     => round($macro_signal, 4),
            ':local'     => round($local_momentum, 4),
            ':pipeline'  => round($pipeline_signal, 4),
            ':outlook'   => $weighted_outlook,
            ':tier'      => $tier,
            ':band_low'  => $band_low,
            ':band_high' => $band_high,
            ':comps'     => $comp_count,
        ]);
        $updated++;

        $results[] = [
            'neighbourhood' => $nb['name'],
            'slug'          => $slug,
            'outlook'       => $weighted_outlook,
            'tier'          => $tier,
            'comp_count'    => $comp_count,
            'band'          => "{$band_low}% — {$band_high}%",
        ];
    }

    echo json_encode([
        'success'               => true,
        'quarter'               => $quarter,
        'macro_signal'          => round($macro_signal, 2),
        'neighbourhoods_updated'=> $updated,
        'results'               => $results,
    ]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action.']);

// ── Stats helpers ─────────────────────────────────────────────────────────────
function _outlier_trimmed_mean(array $vals): float {
    if (count($vals) < 3) return array_sum($vals) / count($vals);
    sort($vals);
    $q1  = $vals[(int)floor(count($vals) * 0.25)];
    $q3  = $vals[(int)floor(count($vals) * 0.75)];
    $iqr = $q3 - $q1;
    $filtered = array_filter($vals, fn($v) => $v >= ($q1 - 1.5*$iqr) && $v <= ($q3 + 1.5*$iqr));
    if (empty($filtered)) return array_sum($vals) / count($vals);
    return array_sum($filtered) / count($filtered);
}

function _std_dev(array $vals): float {
    if (count($vals) < 2) return 0;
    $mean = array_sum($vals) / count($vals);
    $sq   = array_map(fn($v) => ($v - $mean) ** 2, $vals);
    return sqrt(array_sum($sq) / count($vals));
}
