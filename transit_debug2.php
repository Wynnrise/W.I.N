<?php
session_start();
define('ADMIN_PASSWORD', 'Concac1979$');
if (isset($_POST['admin_login']) && $_POST['password'] === ADMIN_PASSWORD) $_SESSION['wynston_admin'] = true;
if (empty($_SESSION['wynston_admin'])) { ?>
<form method="POST"><input type="password" name="password" placeholder="password"><button name="admin_login">Login</button></form>
<?php exit; }

$pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4","u990588858_Multiplex","Concac1979$",[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "<pre>";

// Test TRUNCATE
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("TRUNCATE TABLE transit_stops");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "TRUNCATE: OK\n";
} catch(Exception $e) { echo "TRUNCATE ERROR: " . $e->getMessage() . "\n"; }

// Check count after truncate
$cnt = $pdo->query("SELECT COUNT(*) FROM transit_stops")->fetchColumn();
echo "Rows after truncate: $cnt\n";

if (($_POST['step'] ?? '') === 'upload' && isset($_FILES['stops_file']) && $_FILES['stops_file']['error']===0) {
    $content = str_replace("\r\n", "\n", file_get_contents($_FILES['stops_file']['tmp_name']));
    $lines = explode("\n", $content);
    $header = str_getcsv(array_shift($lines));
    $col = array_flip(array_map('trim', $header));

    // Use INSERT not INSERT IGNORE so we can count properly
    $stmt = $pdo->prepare("INSERT INTO transit_stops (stop_id,stop_name,stop_lat,stop_lng,stop_type,zone_id,is_ftn) VALUES (?,?,?,?,?,?,?)");

    $inserted=0; $skipped=0; $errors=[];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $row = str_getcsv($line);
        if (count($row) < 6) { $skipped++; continue; }

        $lat = (float)trim($row[$col['stop_lat']] ?? 0);
        $lng = (float)trim($row[$col['stop_lon']] ?? 0);
        if ($lat==0||$lng==0) { $skipped++; continue; }
        if ($lat<49.00||$lat>49.50||$lng<-123.30||$lng>-122.20) { $skipped++; continue; }

        $loc = (int)trim($row[$col['location_type']] ?? 0);
        if ($loc===2) { $skipped++; continue; }

        $stop_id   = trim($row[$col['stop_id']] ?? '');
        $stop_name = trim($row[$col['stop_name']] ?? '');
        $zone_id   = trim($row[$col['zone_id']] ?? '');
        $stop_type = ($loc===1||stripos($stop_name,'Station')!==false) ? 'skytrain' : 'ftn_bus';

        try {
            $stmt->execute([$stop_id,$stop_name,$lat,$lng,$stop_type,$zone_id,1]);
            $inserted++;
        } catch(Exception $e) {
            if (count($errors) < 3) $errors[] = "stop_id=$stop_id: " . $e->getMessage();
            $skipped++;
        }
    }

    echo "inserted=$inserted skipped=$skipped\n";
    if ($errors) { echo "Sample errors:\n"; foreach($errors as $e) echo "  $e\n"; }
    
    $cnt2 = $pdo->query("SELECT COUNT(*) FROM transit_stops")->fetchColumn();
    echo "Rows in table now: $cnt2\n";
}
echo "</pre>";
?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="step" value="upload">
<input type="file" name="stops_file">
<button type="submit">Test Insert</button>
</form>
