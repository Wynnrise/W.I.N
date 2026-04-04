<?php
/**
 * HIGH-PERFORMANCE DATA LOADER
 */

function get_market_data($filename, $limit = 1000) {
    $results = [];
    if (!file_exists($filename)) return [];

    if (($handle = fopen($filename, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ";"); 
        
        // Use a temporary array to store only the last $limit rows
        // This prevents the "spinning" by not reading the whole 52MB
        $temp_data = [];
        while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if(count($headers) == count($row)) {
                $temp_data[] = array_combine($headers, $row);
                // Keep only the most recent rows to save memory
                if (count($temp_data) > $limit) {
                    array_shift($temp_data);
                }
            }
        }
        fclose($handle);
        $results = array_reverse($temp_data);
    }
    return $results;
}

// Absolute path to your renamed CSV
$csv_path = $_SERVER['DOCUMENT_ROOT'] . '/issued-building-permits-categorized.csv';
$all_listings = get_market_data($csv_path);

// --- CATEGORY FILTERS (Now lightning fast) ---
$multiplex_list = array_filter($all_listings, function($item) {
    return isset($item['Category']) && $item['Category'] === 'Multiplex';
});

// --- NEIGHBORHOOD STATS ---
$neighborhood_stats = array_count_values(array_column($all_listings, 'GeoLocalArea'));
?>