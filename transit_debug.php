<?php
session_start();
define('ADMIN_PASSWORD', 'Concac1979$');
if (isset($_POST['admin_login']) && $_POST['password'] === ADMIN_PASSWORD) $_SESSION['wynston_admin'] = true;
if (empty($_SESSION['wynston_admin'])) { ?>
<form method="POST"><input type="password" name="password" placeholder="password"><button name="admin_login">Login</button></form>
<?php exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4","u990588858_Multiplex","Concac1979$",[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "<pre>";
echo "step=" . ($_POST['step'] ?? 'none') . "\n";
echo "FILES: "; print_r($_FILES);

if (($_POST['step'] ?? '') === 'upload' && isset($_FILES['stops_file'])) {
    $f = $_FILES['stops_file'];
    echo "error=" . $f['error'] . " size=" . $f['size'] . "\n";
    
    if ($f['error'] === 0) {
        $raw = file_get_contents($f['tmp_name']);
        echo "raw length=" . strlen($raw) . "\n";
        echo "first 300 chars: " . substr($raw, 0, 300) . "\n---\n";
        
        $content = str_replace("\r\n", "\n", $raw);
        $lines = explode("\n", $content);
        echo "total lines=" . count($lines) . "\n";
        
        $header_line = array_shift($lines);
        echo "header_line=" . $header_line . "\n";
        
        $header = str_getcsv($header_line);
        echo "header fields=" . count($header) . ": ";
        print_r($header);
        
        $col = array_flip(array_map('trim', $header));
        echo "col map: "; print_r($col);
        
        // Test first data line
        $first = trim($lines[0]);
        echo "first data line: " . $first . "\n";
        $row = str_getcsv($first);
        echo "row fields=" . count($row) . ": "; print_r($row);
        
        if (isset($col['stop_lat'], $col['stop_lon'])) {
            $lat = (float)trim($row[$col['stop_lat']] ?? 0);
            $lng = (float)trim($row[$col['stop_lon']] ?? 0);
            echo "lat=$lat lng=$lng\n";
            echo "in bbox: " . ($lat>=49.00 && $lat<=49.50 && $lng>=-123.30 && $lng<=-122.20 ? 'YES' : 'NO') . "\n";
            
            $stop_code = trim($row[$col['stop_code']] ?? '');
            echo "stop_code=" . var_export($stop_code, true) . " is_numeric=" . var_export(is_numeric($stop_code), true) . "\n";
            $location_type = (int)trim($row[$col['location_type']] ?? 0);
            echo "location_type=$location_type\n";
        }
        
        // Try actual insert on first valid row
        $pdo->exec("TRUNCATE TABLE transit_stops");
        $stmt = $pdo->prepare("INSERT IGNORE INTO transit_stops (stop_id,stop_name,stop_lat,stop_lng,stop_type,zone_id,is_ftn) VALUES (?,?,?,?,?,?,?)");
        
        $inserted = 0; $skipped = 0;
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $row = str_getcsv($line);
            if (count($row) < 6) { $skipped++; continue; }
            
            $lat = (float)trim($row[$col['stop_lat']] ?? 0);
            $lng = (float)trim($row[$col['stop_lon']] ?? 0);
            if ($lat==0 || $lng==0) { $skipped++; continue; }
            if ($lat<49.00||$lat>49.50||$lng<-123.30||$lng>-122.20) { $skipped++; continue; }
            
            $stop_id   = trim($row[$col['stop_id']] ?? '');
            $stop_name = trim($row[$col['stop_name']] ?? '');
            $zone_id   = trim($row[$col['zone_id']] ?? '');
            $loc_type  = (int)trim($row[$col['location_type']] ?? 0);
            if ($loc_type === 2) { $skipped++; continue; }
            
            $stop_type = ($loc_type===1||stripos($stop_name,'Station')!==false) ? 'skytrain' : 'ftn_bus';
            
            try { $stmt->execute([$stop_id,$stop_name,$lat,$lng,$stop_type,$zone_id,1]); $inserted++; }
            catch(Exception $e) { $skipped++; }
            
            if ($i < 5) echo "row $i: inserted stop_id=$stop_id name=$stop_name lat=$lat\n";
        }
        echo "\nFINAL: inserted=$inserted skipped=$skipped\n";
    }
}
echo "</pre>";
?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="step" value="upload">
<input type="file" name="stops_file">
<button type="submit">Upload stops.txt</button>
</form>
