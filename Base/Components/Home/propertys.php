<?php
// ── Pull paid listings with photos from database ──────────────────────────────
$propertys = [];
try {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/Base/db.php');
    $cols = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('is_paid', $cols) && in_array('img1', $cols)) {
        $stmt = $pdo->query(
            "SELECT id, address, neighborhood, property_type,
                    est_completion, description, img1, img2, img3,
                    bedrooms, bathrooms, sqft, price
             FROM multi_2025
             WHERE is_paid = 1
               AND img1 IS NOT NULL AND img1 != ''
             ORDER BY id DESC
             LIMIT 6"
        );
        $propertys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $propertys = [];
}
?>

<?php if (empty($propertys)): ?>
<!-- No paid listings yet — clean placeholder -->
<div class="single-items">
    <div class="property-listing card border rounded-3 text-center p-5"
         style="background:#f8f9fb;border:2px dashed #d0d8e8 !important;">
        <i class="fas fa-building fa-3x mb-3" style="color:#d0d8e8;"></i>
        <h5 style="color:#889;">Featured Listings Coming Soon</h5>
        <p style="font-size:13px;color:#aab;margin:0;">
            Properties will appear here once developers upgrade to a paid listing.
        </p>
    </div>
</div>

<?php else: ?>
<?php foreach ($propertys as $item): ?>
<!-- Single Property -->
<div class="single-items">
    <div class="property-listing card border rounded-3">

        <!-- Images -->
        <div class="listing-img-wrapper p-3">
            <div class="list-img-slide position-relative">

                <!-- Pre-Sale badge -->
                <div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
                    <div class="label bg-primary text-light d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-star me-1" style="font-size:11px;"></i> Pre-Sale
                    </div>
                    <?php if (!empty($item['est_completion'])): ?>
                    <div class="label bg-dark text-light d-inline-flex align-items-center justify-content-center ms-1">
                        Est. <?= htmlspecialchars($item['est_completion']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="click rounded-3 overflow-hidden mb-0">
                    <!-- Main photo -->
                    <div>
                        <a href="single-property-1.php?id=<?= $item['id'] ?>">
                            <img src="<?= htmlspecialchars($item['img1']) ?>"
                                 class="img-fluid"
                                 style="width:100%;height:220px;object-fit:cover;"
                                 alt="<?= htmlspecialchars($item['address']) ?>"
                                 loading="lazy">
                        </a>
                    </div>
                    <!-- Photo 2 (if exists) -->
                    <?php if (!empty($item['img2'])): ?>
                    <div>
                        <a href="single-property-1.php?id=<?= $item['id'] ?>">
                            <img src="<?= htmlspecialchars($item['img2']) ?>"
                                 class="img-fluid"
                                 style="width:100%;height:220px;object-fit:cover;"
                                 alt="<?= htmlspecialchars($item['address']) ?>"
                                 loading="lazy">
                        </a>
                    </div>
                    <?php endif; ?>
                    <!-- Photo 3 (if exists) -->
                    <?php if (!empty($item['img3'])): ?>
                    <div>
                        <a href="single-property-1.php?id=<?= $item['id'] ?>">
                            <img src="<?= htmlspecialchars($item['img3']) ?>"
                                 class="img-fluid"
                                 style="width:100%;height:220px;object-fit:cover;"
                                 alt="<?= htmlspecialchars($item['address']) ?>"
                                 loading="lazy">
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card body -->
        <div class="listing-caption-wrapper px-3">
            <div class="listing-detail-wrapper">
                <div class="listing-short-detail-wrap">
                    <div class="listing-short-detail">
                        <div class="d-flex align-items-center">
                            <span class="label bg-light-success text-success prt-type me-2">Pre-Sale</span>
                            <span class="label bg-light-purple text-purple property-cats">
                                <?= htmlspecialchars($item['property_type']) ?>
                            </span>
                        </div>
                        <h4 class="listing-name fw-semibold fs-5 mb-1 mt-3">
                            <a href="single-property-1.php?id=<?= $item['id'] ?>">
                                <?= htmlspecialchars(mb_strimwidth($item['address'], 0, 55, '...')) ?>
                            </a>
                        </h4>
                        <div class="prt-location text-muted-2">
                            <span class="svg-icon svg-icon-2hx">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
                                    <path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <?= htmlspecialchars($item['neighborhood']) ?>, Vancouver, BC
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features row -->
            <div class="price-features-wrapper">
                <div class="list-fx-features d-flex align-items-center justify-content-between">
                    <?php if (!empty($item['bedrooms'])): ?>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
                            <i class="fa-solid fa-bed fs-sm"></i>
                        </div>
                        <span class="text-muted-2"><?= $item['bedrooms'] ?> Beds</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['bathrooms'])): ?>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
                            <i class="fa-solid fa-bath fs-sm"></i>
                        </div>
                        <span class="text-muted-2"><?= $item['bathrooms'] ?> Baths</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['sqft'])): ?>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
                            <i class="fa-solid fa-clone fs-sm"></i>
                        </div>
                        <span class="text-muted-2"><?= number_format($item['sqft']) ?> sqft</span>
                    </div>
                    <?php endif; ?>
                    <?php if (empty($item['bedrooms']) && empty($item['bathrooms']) && empty($item['sqft'])): ?>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
                            <i class="fa-solid fa-calendar-alt fs-sm"></i>
                        </div>
                        <span class="text-muted-2">Est. <?= htmlspecialchars($item['est_completion']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer: price + view button -->
            <div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
                <div class="listing-short-detail-flex">
                    <h6 class="listing-card-info-price m-0">
                        <?= !empty($item['price']) ? htmlspecialchars($item['price']) : 'T.B.A.' ?>
                    </h6>
                </div>
                <div class="footer-flex">
                    <a href="single-property-1.php?id=<?= $item['id'] ?>" class="prt-view">
                        <span class="svg-icon text-primary svg-icon-2hx">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
                                <path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
