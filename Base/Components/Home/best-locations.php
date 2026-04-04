<?php
// 1. Connect to your database
include_once($_SERVER['DOCUMENT_ROOT'] . '/Base/db.php');

$locations = [];
try {
    // 2. Fetch the top 8 neighborhoods from your data
    $query = $pdo->query("SELECT neighborhood, COUNT(*) as total 
                          FROM multi_2025 
                          GROUP BY neighborhood 
                          ORDER BY total DESC 
                          LIMIT 8");
    $db_data = $query->fetchAll(PDO::FETCH_ASSOC);

    $i = 1;
    foreach ($db_data as $row) {
        if (!empty($row['neighborhood'])) {
            // Check if it's Burnaby or Vancouver to assign a generic image if needed
            $locations[] = [
                'img'   => '/img/c-' . $i . '.png', // Matches your original c-1.png, c-2.png
                'title' => $row['neighborhood'],
                'name'  => number_format($row['total']) . ' Properties'
            ];
            $i++;
            if ($i > 8) $i = 1; 
        }
    }
} catch (PDOException $e) {
    $locations = [];
}
?>

<?php foreach ($locations as $item): ?>
<div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
    <div class="location-property-wrap rounded-4 p-2">
        <div class="location-property-thumb rounded-4">
            <a href="grid-layout-with-sidebar.php?neighborhood=<?php echo urlencode($item['title']); ?>">
                <img src="<?php echo $static_url . $item['img']; ?>" class="img-fluid" alt="" />
            </a>
        </div>
        <div class="location-property-content">
            <div class="lp-content-flex">
                <h4 class="lp-content-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                <span class="text-muted-2"><?php echo $item['name']; ?></span>
            </div>
            <div class="lp-content-right">
                <a href="grid-layout-with-sidebar.php?neighborhood=<?php echo urlencode($item['title']); ?>" class="text-primary">
                    <span class="svg-icon svg-icon-2hx">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="5" fill="currentColor"/>
                            <path d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5391 10.2997 16.6115 10.6921 16.2878L14.6921 12.2878C15.0845 11.9641 15.1569 11.426 14.8232 11.0073C14.5702 10.6488 14.0816 10.607 13.7713 10.9173L11.9343 12.5657Z" fill="currentColor"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>