<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/Base/db.php');

$places = [];
try {
    $query = $pdo->query("SELECT neighborhood, COUNT(*) as total 
                          FROM multi_2025 
                          GROUP BY neighborhood 
                          HAVING total > 0 
                          ORDER BY RAND() 
                          LIMIT 13");
    $db_data = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($db_data as $row) {
        if (!empty($row['neighborhood'])) {
            $slug = strtolower(trim($row['neighborhood']));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-') . '.webp';

            $places[] = [
                'img'   => '/img/bestplaces/' . $slug,
                'title' => $row['neighborhood'],
                'count' => number_format($row['total']) . ' Properties'
            ];
        }
    }
} catch (PDOException $e) {
    $places = [];
}
?>

<?php foreach ($places as $item): ?>
<div class="col">
    <div class="position-relative">
        <a href="half-map.php?neighborhood=<?= urlencode($item['title']) ?>"
           class="d-flex align-items-center justify-content-start border rounded-pill p-2"
           title="Browse <?= htmlspecialchars($item['title']) ?> pre-sale properties">
            <div class="explod-thumb flex-shrink-0">
                <img src="<?= $static_url . $item['img'] ?>" class="img-fluid circle" width="65" height="65" style="object-fit:cover;width:65px;height:65px;border-radius:50%;" alt="<?= htmlspecialchars($item['title']) ?>"
                     onerror="this.onerror=null;this.src='<?= $static_url ?>/img/bestplaces/default.webp';">
            </div>
            <div class="explod-caps ps-3">
                <h5 class="fs-6 fw-medium mb-0 text-dark"><?= htmlspecialchars($item['title']) ?></h5>
                <p class="text-muted-2 fs-sm m-0"><?= $item['count'] ?></p>
            </div>
        </a>
    </div>
</div>
<?php endforeach; ?>