<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';

ob_start();
include "$base_dir/navbar.php";
$navlink_content = ob_get_clean();
$page  = 'nav';
$fpage = 'foot';

// ── Get property ID from URL ──────────────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: half-map.php'); exit; }

// ── Load from database with simple file cache ─────────────────────────────────
require_once "$base_dir/db.php";

$cache_dir  = __DIR__ . '/cache/';
$cache_file = $cache_dir . 'property_' . $id . '.json';
$cache_ttl  = 300; // cache for 5 minutes (300 seconds)

$p = null;

// Use cache if it exists and is fresh
if (is_dir($cache_dir) && file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $p = json_decode(file_get_contents($cache_file), true);
}

// Otherwise query DB and save cache
if (!$p) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM multi_2025 WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        // Save to cache if folder is writable
        if ($p && is_dir($cache_dir) && is_writable($cache_dir)) {
            file_put_contents($cache_file, json_encode($p));
        }
    } catch (PDOException $e) {
        die("<div class='container py-5 text-center'><h3>Database error: " . htmlspecialchars($e->getMessage()) . "</h3></div>");
    }
}

if (!$p) {
    die("<div class='container py-5 text-center'><h3>Property #$id not found.</h3><a href='half-map.php'>← Back to listings</a></div>");
}

ob_start();
?>

<!-- ============================ Photo Gallery Banner ========================= -->
<?php
// Collect all available photos
$photos = [];
for ($i = 1; $i <= 6; $i++) {
    if (!empty($p['img'.$i])) $photos[] = $p['img'.$i];
}
$has_photos = count($photos) > 0;
?>
<div class="featured_slick_gallery gray">
    <div class="featured_slick_gallery-slide">
        <?php if ($has_photos): ?>
            <?php foreach ($photos as $photo): ?>
            <div class="featured_slick_padd">
                <a href="<?= htmlspecialchars($photo) ?>" class="mfp-gallery">
                    <img src="<?= htmlspecialchars($photo) ?>" class="img-fluid mx-auto"
                         style="width:100%;height:400px;object-fit:cover;" 
                         alt="<?= htmlspecialchars($p['address']) ?>"
                         loading="lazy">
                </a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="featured_slick_padd">
                <div class="generic-hero-placeholder">
                    <i class="fas fa-building"></i>
                    <span><?= htmlspecialchars($p['property_type']) ?></span>
                    <small>Photos Available Once Listed</small>
                </div>
            </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
    <a href="JavaScript:Void(0);" class="btn-view-pic">
        <?= $has_photos ? 'View ' . count($photos) . ' Photos' : 'View photos' ?>
    </a>
</div>
<!-- ============================ Photo Gallery End ============================ -->


<!-- ============================ Property Detail ============================== -->
<section class="gray-simple">
    <div class="container">
        <div class="row">

            <!-- ══════════════════════════════════════════
                 MAIN CONTENT  (left column)
            ══════════════════════════════════════════ -->
            <div class="col-lg-8 col-md-12 col-sm-12">

                <!-- ── Title / Hero card ── -->
                <div class="property_block_wrap style-2 p-4">
                    <div class="prt-detail-title-desc">
                        <span class="label text-light bg-primary">Pre-Sale</span>
                        <span class="label text-light bg-secondary ms-2">Est. <?= htmlspecialchars($p['est_completion']) ?></span>

                        <h3 class="mt-3"><?= htmlspecialchars($p['address']) ?></h3>
                        <span><i class="lni-map-marker"></i> <?= htmlspecialchars($p['neighborhood']) ?>, Vancouver, BC</span>

                        <h3 class="prt-price-fix text-primary mt-2">T.B.A.</h3>

                        <div class="list-fx-features">
                            <div class="listing-card-info-icon">
                                <div class="inc-fleat-icon me-1"><i class="fas fa-home"></i></div>
                                <?= htmlspecialchars($p['property_type']) ?>
                            </div>
                            <div class="listing-card-info-icon">
                                <div class="inc-fleat-icon me-1"><i class="fas fa-calendar-alt"></i></div>
                                Est. <?= htmlspecialchars($p['est_completion']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Description ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Description</h4>
                    </div>
                    <div class="block-body">
                        <?php if (!empty($p['description'])): ?>
                            <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                        <?php else: ?>
                            <div class="coming-soon-block">
                                <i class="fas fa-file-alt"></i>
                                <p>Detailed description will be provided by the developer.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Property Details ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Property Details</h4>
                    </div>
                    <div class="block-body">
                        <ul class="deatil_features">
                            <li><strong>Type:</strong> <?= htmlspecialchars($p['property_type']) ?></li>
                            <li><strong>Neighbourhood:</strong> <?= htmlspecialchars($p['neighborhood']) ?></li>
                            <li><strong>Est. Completion:</strong> <?= htmlspecialchars($p['est_completion']) ?></li>
                            <li><strong>Price:</strong> <?= !empty($p['price']) ? htmlspecialchars($p['price']) : '<span class="tba-detail">T.B.A.</span>' ?></li>
                            <li><strong>Status:</strong> Pre-Sale</li>
                            <li><strong>City:</strong> Vancouver, BC</li>
                            <li><strong>Bedrooms:</strong> <?= !empty($p['bedrooms']) ? htmlspecialchars($p['bedrooms']) : '<span class="tba-detail">T.B.A.</span>' ?></li>
                            <li><strong>Bathrooms:</strong> <?= !empty($p['bathrooms']) ? htmlspecialchars($p['bathrooms']) : '<span class="tba-detail">T.B.A.</span>' ?></li>
                            <li><strong>Size:</strong> <?= !empty($p['sqft']) ? number_format($p['sqft']) . ' sqft' : '<span class="tba-detail">T.B.A.</span>' ?></li>
                            <li><strong>Parking:</strong> <?= !empty($p['parking']) ? htmlspecialchars($p['parking']) : '<span class="tba-detail">T.B.A.</span>' ?></li>
                            <li><strong>Strata Fee:</strong> <?= !empty($p['strata_fee']) ? htmlspecialchars($p['strata_fee']) : '<span class="tba-detail">T.B.A.</span>' ?></li>
                        </ul>
                    </div>
                </div>

                <!-- ── Details & Features ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Details &amp; Features</h4>
                    </div>
                    <div class="block-body">
                        <?php if (!empty($p['features'])): ?>
                            <ul class="detail-features-list">
                                <?php foreach (array_filter(array_map('trim', explode(',', $p['features']))) as $feature): ?>
                                <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="coming-soon-block">
                                <i class="fas fa-list-ul"></i>
                                <p>Full details and features will be provided by the developer once available.</p>
                                <span class="coming-soon-tag">Coming Soon</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Amenities ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Amenities</h4>
                    </div>
                    <div class="block-body">
                        <?php if (!empty($p['amenities'])): ?>
                            <div class="amenities-grid">
                                <?php foreach (array_filter(array_map('trim', explode(',', $p['amenities']))) as $amenity): ?>
                                <div class="amenity-tag">
                                    <i class="fas fa-check"></i>
                                    <?= htmlspecialchars($amenity) ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="coming-soon-block">
                                <i class="fas fa-star"></i>
                                <p>Amenity information will be listed once provided by the developer.</p>
                                <span class="coming-soon-tag">Coming Soon</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Video ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Property Video</h4>
                    </div>
                    <div class="block-body">
                        <?php if (!empty($p['video_url'])): ?>
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;">
                                <iframe src="<?= htmlspecialchars($p['video_url']) ?>"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                        allowfullscreen loading="lazy"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="coming-soon-block video-placeholder">
                                <i class="fas fa-video"></i>
                                <p>Video walkthrough will be available once provided by the developer.</p>
                                <span class="coming-soon-tag">Coming Soon</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Floor Plan ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Floor Plan</h4>
                    </div>
                    <div class="block-body">
                        <?php if (!empty($p['floorplan'])): ?>
                            <?php $fp_ext = strtolower(pathinfo($p['floorplan'], PATHINFO_EXTENSION)); ?>
                            <?php if ($fp_ext === 'pdf'): ?>
                                <!-- PDF floor plan -->
                                <div class="floorplan-pdf-wrap">
                                    <i class="fas fa-file-pdf"></i>
                                    <div>
                                        <strong>Floor Plan Available</strong>
                                        <small>PDF Document</small>
                                    </div>
                                    <a href="<?= htmlspecialchars($p['floorplan']) ?>" target="_blank" class="btn-view-fp">
                                        <i class="fas fa-download me-1"></i>View / Download
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Image floor plan -->
                                <a href="<?= htmlspecialchars($p['floorplan']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($p['floorplan']) ?>"
                                         alt="Floor Plan" style="width:100%;border-radius:8px;cursor:zoom-in;">
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="coming-soon-block floorplan-placeholder">
                                <i class="fas fa-drafting-compass"></i>
                                <p>Floor plans will be uploaded once provided by the developer.</p>
                                <span class="coming-soon-tag">Coming Soon</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Gallery ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title">Gallery</h4>
                    </div>
                    <div class="block-body">
                        <?php if ($has_photos): ?>
                            <div class="gallery-placeholder-grid" id="gallery-grid">
                                <?php foreach ($photos as $idx => $photo): ?>
                                <div class="gallery-real-tile" onclick="openModal(<?= $idx ?>)">
                                    <img src="<?= htmlspecialchars($photo) ?>" alt="Photo <?= $idx+1 ?>" loading="lazy">
                                    <div class="gallery-tile-hover"><i class="fas fa-expand-alt"></i></div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($photos) < 6): ?>
                                    <?php for ($g = count($photos); $g < 6; $g++): ?>
                                    <div class="gallery-placeholder-tile">
                                        <i class="fas fa-camera"></i>
                                        <span class="gallery-tile-label">Coming Soon</span>
                                    </div>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                <div class="gallery-expand-overlay" onclick="document.getElementById('gallery-modal').style.display='flex'">
                                    <i class="fas fa-expand-alt"></i>
                                    <span>View All <?= count($photos) ?> Photos</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="gallery-placeholder-grid" id="gallery-grid">
                                <?php for ($g = 0; $g < 6; $g++): ?>
                                <div class="gallery-placeholder-tile">
                                    <i class="fas fa-camera"></i>
                                    <span class="gallery-tile-label">Coming Soon</span>
                                </div>
                                <?php endfor; ?>
                                <div class="gallery-expand-overlay" onclick="document.getElementById('gallery-modal').style.display='flex'">
                                    <i class="fas fa-expand-alt"></i>
                                    <span>View All Photos</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gallery modal -->
                <div id="gallery-modal" class="gallery-modal" style="display:none;" onclick="this.style.display='none'">
                    <div class="gallery-modal-inner" onclick="event.stopPropagation()">
                        <button class="gallery-modal-close" onclick="document.getElementById('gallery-modal').style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                        <h5 style="color:#fff;margin-bottom:20px;">
                            <i class="fas fa-building me-2"></i>
                            <?= htmlspecialchars($p['address']) ?>
                        </h5>
                        <!-- Active large photo -->
                        <div id="modal-main-photo" style="width:100%;margin-bottom:12px;border-radius:8px;overflow:hidden;background:#1e2836;min-height:300px;display:flex;align-items:center;justify-content:center;">
                            <?php if ($has_photos): ?>
                                <img id="modal-main-img" src="<?= htmlspecialchars($photos[0]) ?>"
                                     style="width:100%;max-height:420px;object-fit:contain;" alt="Photo">
                            <?php else: ?>
                                <div style="color:#445;text-align:center;padding:60px 20px;">
                                    <i class="fas fa-camera" style="font-size:48px;display:block;margin-bottom:12px;"></i>
                                    Photos coming soon
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Thumbnails -->
                        <?php if ($has_photos): ?>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <?php foreach ($photos as $idx => $photo): ?>
                            <img src="<?= htmlspecialchars($photo) ?>"
                                 onclick="document.getElementById('modal-main-img').src='<?= htmlspecialchars($photo) ?>'"
                                 style="width:80px;height:60px;object-fit:cover;border-radius:5px;cursor:pointer;border:2px solid <?= $idx===0?'#0065ff':'transparent' ?>;transition:border 0.2s;"
                                 onmouseover="this.style.borderColor='#0065ff'"
                                 onmouseout="this.style.borderColor='transparent'">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── School Catchment ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-graduation-cap me-2" style="color:#0065ff;"></i>School Catchment</h4>
                    </div>
                    <div class="block-body">
                        <p style="font-size:13px;color:#888;margin-bottom:16px;">
                            Schools serving this address based on Vancouver School Board catchment boundaries.
                        </p>
                        <?php
                        // Look up schools based on neighbourhood
                        $neighbourhood = strtolower(trim($p['neighborhood'] ?? ''));
                        $catchment_map = [
                            'sunset'               => ['elementary' => 'Sir Richard McBride Elementary', 'secondary' => 'John Oliver Secondary'],
                            'kitsilano'            => ['elementary' => 'Carnarvon Elementary',            'secondary' => 'Kitsilano Secondary'],
                            'south cambie'         => ['elementary' => 'Emily Carr Elementary',           'secondary' => 'Eric Hamber Secondary'],
                            'hastings-sunrise'     => ['elementary' => 'Hastings Elementary',             'secondary' => 'Britannia Secondary'],
                            'renfrew-collingwood'  => ['elementary' => 'Renfrew Elementary',              'secondary' => 'Windermere Secondary'],
                            'kerrisdale'           => ['elementary' => 'Kerrisdale Elementary',           'secondary' => 'Magee Secondary'],
                            'shaughnessy'          => ['elementary' => 'Shaughnessy Elementary',          'secondary' => 'Eric Hamber Secondary'],
                            'arbutus ridge'        => ['elementary' => 'Maple Grove Elementary',         'secondary' => 'Magee Secondary'],
                            'dunbar-southlands'    => ['elementary' => 'Dunbar Elementary',               'secondary' => 'Lord Byng Secondary'],
                            'marpole'              => ['elementary' => 'David Oppenheimer Elementary',   'secondary' => 'Sir Winston Churchill Secondary'],
                            'mount pleasant'       => ['elementary' => 'Mount Pleasant Elementary',      'secondary' => 'Britannia Secondary'],
                            'fairview'             => ['elementary' => 'Simon Fraser Elementary',        'secondary' => 'Eric Hamber Secondary'],
                            'west end'             => ['elementary' => 'Lord Roberts Elementary',        'secondary' => 'King Edward Secondary'],
                            'downtown'             => ['elementary' => 'Lord Roberts Elementary',        'secondary' => 'King Edward Secondary'],
                            'strathcona'           => ['elementary' => 'Strathcona Elementary',          'secondary' => 'Britannia Secondary'],
                            'grandview-woodland'   => ['elementary' => 'Grandview Elementary',           'secondary' => 'Britannia Secondary'],
                            'kensington-cedar cottage' => ['elementary' => 'Kensington Elementary',     'secondary' => 'Gladstone Secondary'],
                            'riley park'           => ['elementary' => 'Livingstone Elementary',         'secondary' => 'Eric Hamber Secondary'],
                            'oakridge'             => ['elementary' => 'Laurier Elementary',             'secondary' => 'Sir Winston Churchill Secondary'],
                            'victoria-fraserview'  => ['elementary' => 'Cunningham Elementary',         'secondary' => 'Killarney Secondary'],
                            'killarney'            => ['elementary' => 'Carleton Elementary',            'secondary' => 'Killarney Secondary'],
                            'south hill'           => ['elementary' => 'Sir Wilfred Laurier Elementary', 'secondary' => 'John Oliver Secondary'],
                            'west point grey'      => ['elementary' => 'Queen Mary Elementary',         'secondary' => 'Lord Byng Secondary'],
                        ];

                        // Find best match
                        $schools = null;
                        foreach ($catchment_map as $key => $val) {
                            if (str_contains($neighbourhood, $key) || str_contains($key, $neighbourhood)) {
                                $schools = $val;
                                break;
                            }
                        }
                        ?>

                        <?php if ($schools): ?>
                        <div class="school-catchment-grid">
                            <div class="school-card">
                                <div class="school-icon elementary">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="school-info">
                                    <span class="school-type">Elementary School</span>
                                    <strong class="school-name"><?= htmlspecialchars($schools['elementary']) ?></strong>
                                    <span class="school-board">Vancouver School Board</span>
                                </div>
                                <a href="https://www.vsb.bc.ca" target="_blank" class="school-link">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            <div class="school-card">
                                <div class="school-icon secondary">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="school-info">
                                    <span class="school-type">Secondary School</span>
                                    <strong class="school-name"><?= htmlspecialchars($schools['secondary']) ?></strong>
                                    <span class="school-board">Vancouver School Board</span>
                                </div>
                                <a href="https://www.vsb.bc.ca" target="_blank" class="school-link">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <p style="font-size:11px;color:#bbb;margin-top:12px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Catchment boundaries may change. Always verify with the 
                            <a href="https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx" target="_blank" style="color:#0065ff;">VSB School Locator</a>.
                        </p>
                        <?php else: ?>
                        <div class="coming-soon-block">
                            <i class="fas fa-graduation-cap"></i>
                            <p>School catchment info for <strong><?= htmlspecialchars($p['neighborhood']) ?></strong> will be added shortly.</p>
                            <a href="https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx" target="_blank" class="coming-soon-tag" style="text-decoration:none;">
                                Check VSB School Locator →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Walk Score ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-walking me-2" style="color:#0065ff;"></i>Walk Score</h4>
                    </div>
                    <div class="block-body">
                        <p style="font-size:13px;color:#888;margin-bottom:16px;">
                            Walkability, transit, and bike scores for this address.
                        </p>
                        <style>
                            #ws-walkscore-tile { position:relative; text-align:left; min-height:60px; }
                            #ws-walkscore-tile * { float:none; }
                        </style>
                        <script type="text/javascript">
                            var ws_wsid    = '361dbc9c010ccf76ceea407fa304e222';
                            var ws_address = <?= json_encode($p['address']) ?>;
                            var ws_lat     = '<?= (float)$p['latitude'] ?>';
                            var ws_lon     = '<?= (float)$p['longitude'] ?>';
                            var ws_format  = 'wide';
                            var ws_width   = '600';
                            var ws_height  = '200';
                            var ws_transit = '1';
                            var ws_bike    = '1';
                        </script>
                        <div id="ws-walkscore-tile">
                            <div id="ws-loading" style="color:#bbb;font-size:12px;padding:10px 0;">
                                <i class="fas fa-circle-notch fa-spin me-2"></i>Loading scores...
                            </div>
                        </div>
                        <script>
                        (function() {
                            var s = document.createElement('script');
                            s.type  = 'text/javascript';
                            s.async = true;
                            s.src   = 'https://www.walkscore.com/tile/show-walkscore-tile.php';
                            s.onload = function() {
                                var loader = document.getElementById('ws-loading');
                                if (loader) loader.remove();
                            };
                            document.body.appendChild(s);
                        })();
                        </script>
                        <p style="font-size:11px;color:#bbb;margin-top:12px;">
                            Scores provided by <a href="https://www.walkscore.com" target="_blank" style="color:#0065ff;">Walk Score</a>
                        </p>
                    </div>
                </div>

                <!-- ── Nearby Restaurants (Yelp + Google Maps) ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-utensils me-2" style="color:#d32323;"></i>Nearby Restaurants & Cafés</h4>
                    </div>
                    <div class="block-body">
                        <?php
                        $full_address = urlencode($p['address'] . ', Vancouver, BC');
                        $yelp_url  = 'https://www.yelp.ca/search?find_desc=Restaurants&find_loc=' . $full_address;
                        $lat = (float)($p['latitude']  ?? 49.25);
                        $lng = (float)($p['longitude'] ?? -123.12);
                        $gmaps_url = 'https://www.google.com/maps/search/restaurants/@' . $lat . ',' . $lng . ',15z';
                        ?>
                        <div class="nearby-links-row">
                            <a href="<?= $yelp_url ?>" target="_blank" rel="noopener" class="nearby-link-btn yelp">
                                <i class="fab fa-yelp"></i>
                                <div>
                                    <strong>Search Yelp</strong>
                                    <span>Restaurants & cafés near this address</span>
                                </div>
                                <i class="fas fa-external-link-alt" style="flex-shrink:0;opacity:.6;"></i>
                            </a>
                            <a href="<?= $gmaps_url ?>" target="_blank" rel="noopener" class="nearby-link-btn gmaps">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Google Maps Nearby</strong>
                                    <span>See restaurants on the map</span>
                                </div>
                                <i class="fas fa-external-link-alt" style="flex-shrink:0;opacity:.6;"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── Back to Map ── -->
                <div class="bottom-nav-buttons">
                    <a href="half-map.php" class="btn-bottom-nav secondary">
                        <i class="fas fa-map me-2"></i>Back to Map Search
                    </a>
                </div>

            </div>
            <!-- /main column -->

            <!-- ══════════════════════════════════════════
                 SIDEBAR  (right column)
            ══════════════════════════════════════════ -->
            <div class="col-lg-4 col-md-12 col-sm-12">
                <?php include "$base_dir/Components/Features/property-sidebar1.php"; ?>
            </div>

        </div>
    </div>
</section>
<!-- ============================ Property Detail End ========================== -->


<!-- ============================ Call To Action ================================ -->
<section class="bg-primary call-to-act-wrap">
    <div class="container">
        <?php include "$base_dir/Components/Home/estate-agent.php"; ?>
    </div>
</section>






<style>
/* ── Section block padding (matches title card p-4 spacing) ──────────── */
.property_block_wrap {
    background: #fff;
    border-radius: 8px;
    margin-bottom: 24px;
    overflow: hidden;
}
.property_block_wrap_header {
    padding: 18px 24px 14px;
    border-bottom: 1px solid #f0f0f0;
}
.property_block_title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #002446;
}
.block-body {
    padding: 20px 24px;
}

/* ── Hero photo placeholder ───────────────────────────────────────── */
.generic-hero-placeholder {
    width: 100%;
    height: 280px;
    background: linear-gradient(135deg, #002446 0%, #004080 60%, #0065ff 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.85);
    gap: 10px;
}
.generic-hero-placeholder i     { font-size: 56px; opacity: 0.6; }
.generic-hero-placeholder span  { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
.generic-hero-placeholder small { font-size: 12px; opacity: 0.65; letter-spacing: 0.5px; }

/* ── Coming soon placeholder block ──────────────────────────────────── */
.coming-soon-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 36px 20px;
    background: #f8f9fb;
    border: 2px dashed #d0d8e8;
    border-radius: 8px;
    text-align: center;
    color: #aab;
    gap: 10px;
}
.coming-soon-block i  { font-size: 36px; opacity: 0.4; }
.coming-soon-block p  { margin: 0; font-size: 13px; color: #889; line-height: 1.6; }
.coming-soon-tag {
    display: inline-block;
    background: #e8f0ff;
    color: #0065ff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    padding: 3px 12px;
    border-radius: 20px;
}

/* Taller placeholder for video */
.video-placeholder   { min-height: 180px; }
.floorplan-placeholder { min-height: 160px; }

/* ── T.B.A. detail items ─────────────────────────────────────────────── */
.tba-detail {
    color: #aaa;
    font-style: italic;
    font-size: 12px;
}

/* ── Amenities grid ───────────────────────────────────────────────────── */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}
.amenity-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0f4ff;
    border: 1px solid #dce8ff;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 500;
    color: #002446;
}
.amenity-tag i { color: #0065ff; font-size: 11px; flex-shrink: 0; }

/* ── Features list ────────────────────────────────────────────────────── */
.detail-features-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
}
.detail-features-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #444;
    padding: 6px 0;
    border-bottom: 1px solid #f5f5f5;
}
.detail-features-list li i { color: #0065ff; font-size: 12px; flex-shrink: 0; }

/* ── Real photo gallery tiles ─────────────────────────────────────────── */
.gallery-real-tile {
    aspect-ratio: 4/3;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.gallery-real-tile img { width:100%; height:100%; object-fit:cover; display:block; transition: transform 0.3s; }
.gallery-real-tile:hover img { transform: scale(1.05); }
.gallery-tile-hover {
    position: absolute; inset: 0;
    background: rgba(0,36,70,0.4);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.2s;
    color: #fff; font-size: 22px;
}
.gallery-real-tile:hover .gallery-tile-hover { opacity: 1; }

/* ── Floor plan PDF display ───────────────────────────────────────────── */
.floorplan-pdf-wrap {
    display: flex; align-items: center; gap: 16px;
    background: #f8f9fb; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 20px;
}
.floorplan-pdf-wrap i { font-size: 36px; color: #dc2626; }
.floorplan-pdf-wrap div { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.floorplan-pdf-wrap strong { font-size: 14px; color: #002446; }
.floorplan-pdf-wrap small  { font-size: 12px; color: #888; }
.btn-view-fp {
    background: #002446; color: #fff; padding: 10px 18px;
    border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; white-space: nowrap; transition: background 0.2s;
}
.btn-view-fp:hover { background: #0065ff; color: #fff; }
.gallery-placeholder-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    position: relative;
}
.gallery-placeholder-tile {
    aspect-ratio: 4/3;
    background: #f0f3f8;
    border: 2px dashed #d0d8e8;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ccd;
    gap: 6px;
}
.gallery-placeholder-tile i { font-size: 22px; }
.gallery-tile-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #bbc; }

/* Expand overlay — sits over the bottom-right tile */
.gallery-expand-overlay {
    position: absolute;
    bottom: 0;
    right: 0;
    width: calc(33.33% - 5px);
    aspect-ratio: 4/3;
    background: rgba(0, 36, 70, 0.82);
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    gap: 8px;
    transition: background 0.2s;
}
.gallery-expand-overlay:hover { background: rgba(0, 101, 255, 0.88); }
.gallery-expand-overlay i    { font-size: 22px; }
.gallery-expand-overlay span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }

/* ── Gallery fullscreen modal ─────────────────────────────────────────── */
.gallery-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.92);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.gallery-modal-inner {
    background: #111820;
    border-radius: 12px;
    padding: 28px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}
.gallery-modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.gallery-modal-close:hover { background: #0065ff; }
.gallery-modal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.gallery-modal-tile {
    aspect-ratio: 4/3;
    background: #1e2836;
    border: 2px dashed #2d3d52;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #445;
    gap: 8px;
}
.gallery-modal-tile i    { font-size: 26px; }
.gallery-modal-tile span { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; }

/* ── School catchment ─────────────────────────────────────────────────── */
.school-catchment-grid { display: flex; flex-direction: column; gap: 12px; }
.school-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8f9fb;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    transition: box-shadow 0.2s;
}
.school-card:hover { box-shadow: 0 4px 16px rgba(0,36,70,0.08); }
.school-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.school-icon.elementary { background: #e8f4ff; color: #0065ff; }
.school-icon.secondary  { background: #fff3e8; color: #f07600; }
.school-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.school-type  { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #aaa; font-weight: 600; }
.school-name  { font-size: 14px; font-weight: 700; color: #002446; }
.school-board { font-size: 11px; color: #888; }
.school-link {
    color: #ccd;
    font-size: 13px;
    text-decoration: none;
    padding: 8px;
    border-radius: 6px;
    transition: color 0.2s, background 0.2s;
}
/* ── Bottom nav buttons (matches active-property) ───────────────────── */
.bottom-nav-buttons { display:flex; flex-direction:column; gap:10px; margin-bottom:16px; }
.btn-bottom-nav { display:flex; align-items:center; justify-content:center; padding:13px 20px; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; transition:all 0.2s; }
.btn-bottom-nav.secondary { background:#f1f5f9; color:#002446; border:1px solid #e2e8f0; }
.btn-bottom-nav.secondary:hover { background:#e2e8f0; color:#002446; }
.btn-bottom-nav.primary { background:#0065ff; color:#fff; border:1px solid #0065ff; }
.btn-bottom-nav.primary:hover { background:#0052d4; color:#fff; }
.nearby-links-row { display:flex; flex-direction:column; gap:10px; }
.nearby-link-btn { display:flex; align-items:center; gap:14px; padding:14px 16px; border-radius:10px; text-decoration:none; transition:box-shadow .2s, transform .15s; }
.nearby-link-btn:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); transform:translateY(-1px); }
.nearby-link-btn > i:first-child { font-size:20px; width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.nearby-link-btn div { flex:1; }
.nearby-link-btn div strong { display:block; font-size:14px; margin-bottom:2px; }
.nearby-link-btn div span { font-size:12px; }
.nearby-link-btn.yelp { background:#fff5f5; border:1px solid #fecaca; color:#991b1b; }
.nearby-link-btn.yelp > i:first-child { background:#d32323; color:#fff; }
.nearby-link-btn.gmaps { background:#f0f9ff; border:1px solid #bae6fd; color:#0369a1; }
.nearby-link-btn.gmaps > i:first-child { background:#0284c7; color:#fff; }
</style>


<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>