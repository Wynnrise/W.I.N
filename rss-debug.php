<?php
// ── RSS Debug — DELETE after testing ─────────────────────────────────────────
// Visit: yourdomain.com/rss-debug.php
// DELETE this file once events are showing correctly

$url = 'https://www.trumba.com/calendars/city-of-vancouver-events.rss';

echo '<h2>RSS Debug — City of Vancouver</h2>';
echo '<pre style="font-family:monospace;font-size:12px;background:#f4f4f4;padding:16px;border-radius:6px;">';

// Test 1: cURL
echo "=== TEST 1: cURL fetch ===\n";
if (!function_exists('curl_init')) {
    echo "❌ cURL not available on this server\n";
} else {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Wynston/1.0)',
    ]);
    $result    = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error     = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL error: $error\n";
    } elseif ($http_code !== 200) {
        echo "❌ HTTP $http_code returned\n";
    } else {
        echo "✅ cURL success — " . strlen($result) . " bytes received\n";
        $xml_raw = $result;
    }
}

// Test 2: file_get_contents
echo "\n=== TEST 2: file_get_contents ===\n";
$fc = @file_get_contents($url);
if ($fc === false) {
    echo "❌ file_get_contents failed (likely allow_url_fopen=Off or blocked)\n";
} else {
    echo "✅ file_get_contents success — " . strlen($fc) . " bytes received\n";
    if (empty($xml_raw)) $xml_raw = $fc;
}

// Test 3: Parse XML
echo "\n=== TEST 3: XML parsing ===\n";
if (empty($xml_raw)) {
    echo "❌ No data to parse — both fetch methods failed\n";
    echo "\nThis means your server blocks outbound HTTP to trumba.com.\n";
    echo "Solution: Ask your host to whitelist outbound requests to trumba.com,\nor use a proxy script.\n";
} else {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_raw);
    if (!$xml) {
        echo "❌ XML parse failed\n";
        foreach (libxml_get_errors() as $e) echo "  - " . $e->message;
    } else {
        echo "✅ XML parsed OK\n";
        $namespaces = $xml->getNamespaces(true);
        echo "Namespaces found: " . implode(', ', array_keys($namespaces)) . "\n";

        $items = $xml->channel->item ?? [];
        $count = 0;
        foreach ($items as $i) { $count++; }
        echo "Total items in feed: $count\n\n";

        echo "=== FIRST ITEM — ALL FIELDS DUMP ===\n";
        foreach ($items as $item) {
            echo "Standard fields:\n";
            foreach ($item as $key => $val) {
                echo "  $key = " . substr((string)$val, 0, 120) . "\n";
            }
            echo "\nNamespace fields:\n";
            foreach ($namespaces as $prefix => $ns_uri) {
                $ns_item = $item->children($ns_uri);
                foreach ($ns_item as $key => $val) {
                    echo "  [$prefix] $key = " . substr((string)$val, 0, 120) . "\n";
                }
            }
            break; // only first item
        }

        echo "\n=== FIRST 5 ITEMS (title + all date-like fields) ===\n";
        $n = 0;
        foreach ($items as $item) {
            if ($n++ >= 5) break;
            echo "[$n] " . (string)$item->title . "\n";
            foreach ($namespaces as $prefix => $ns_uri) {
                $ns_item = $item->children($ns_uri);
                foreach ($ns_item as $key => $val) {
                    if (stripos($key,'date') !== false || stripos($key,'start') !== false || stripos($key,'time') !== false || stripos($key,'when') !== false) {
                        echo "    [$prefix:$key] = " . (string)$val . "\n";
                    }
                }
            }
        }

        // Test month filter
        $month_start = date('Y-m-01');
        $month_end   = date('Y-m-t');
        echo "=== EVENTS IN CURRENT MONTH (" . date('F Y') . ") ===\n";
        $this_month = 0;
        foreach ($items as $item) {
            $ev_date = null;
            foreach ($namespaces as $prefix => $ns_uri) {
                $ns_item = $item->children($ns_uri);
                if (isset($ns_item->startdate)) { $ev_date = strtotime((string)$ns_item->startdate); break; }
            }
            if (!$ev_date) $ev_date = strtotime((string)$item->pubDate);
            if (!$ev_date) continue;
            if ($ev_date >= strtotime($month_start) && $ev_date <= strtotime($month_end . ' 23:59:59')) {
                echo "✅ " . date('M j', $ev_date) . " — " . html_entity_decode(strip_tags((string)$item->title)) . "\n";
                $this_month++;
            }
        }
        if ($this_month === 0) {
            echo "⚠️  No events found for current month.\n";
            echo "Feed may contain events further in the future — check dates above.\n";
        }
    }
}

echo '</pre>';
?>