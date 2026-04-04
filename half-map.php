<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';

// ── Database connection ──────────────────────────────────────────────────────
require_once "$base_dir/db.php"; // gives us $pdo

// ── Read filter values from GET params ──────────────────────────────────────
$neighborhood   = $_GET['neighborhood']   ?? '';
$ptype          = $_GET['ptype']          ?? '';
$est_completion = $_GET['est_completion'] ?? '';

// ── Build dynamic query against multi_2025 ───────────────────────────────────
$sql    = "SELECT * FROM multi_2025 WHERE 1=1";
$params = [];

if (!empty($neighborhood)) {
    $sql .= " AND neighborhood = :neighborhood";
    $params[':neighborhood'] = $neighborhood;
}
if (!empty($ptype)) {
    $sql .= " AND property_type = :ptype";
    $params[':ptype'] = $ptype;
}
if (!empty($est_completion)) {
    $sql .= " AND est_completion = :est_completion";
    $params[':est_completion'] = $est_completion;
}

$sql .= " ORDER BY id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Distinct filter options for dropdowns ────────────────────────────────────
$neighborhoods = $pdo->query("SELECT DISTINCT neighborhood FROM multi_2025 ORDER BY neighborhood ASC")->fetchAll(PDO::FETCH_COLUMN);
$years         = $pdo->query("SELECT DISTINCT est_completion FROM multi_2025 ORDER BY est_completion ASC")->fetchAll(PDO::FETCH_COLUMN);
$ptypes        = $pdo->query("SELECT DISTINCT property_type FROM multi_2025 ORDER BY property_type ASC")->fetchAll(PDO::FETCH_COLUMN);

// ── Build JSON for map markers ───────────────────────────────────────────────
$map_markers = [];
foreach ($properties as $p) {
    $lat = (float)($p['latitude']  ?? 0);
    $lng = (float)($p['longitude'] ?? 0);
    if ($lat != 0.0 && $lng != 0.0) {
        $map_markers[] = [
            'id'           => $p['id'],
            'address'      => htmlspecialchars($p['address']),
            'neighborhood' => htmlspecialchars($p['neighborhood']),
            'ptype'        => htmlspecialchars($p['property_type']),
            'year'         => $p['est_completion'],
            'lat'          => $lat,
            'lng'          => $lng,
            'img'          => !empty($p['img1']) ? $p['img1'] : '',
            'tier'         => $p['tier'] ?? ($p['is_paid'] ? 'concierge' : 'free'),
        ];
    }
}
$markers_json = json_encode($map_markers);

// ── Navbar ───────────────────────────────────────────────────────────────────
ob_start();
include "$base_dir/navbar.php";
$navlink_content = ob_get_clean();
$page  = 'nav';
$fpage = 'foot';

ob_start();
?>

<div class="home-map-banner half-map">

    <!-- ════════ LEFT – Google Map ════════ -->
    <div class="fs-left-map-box">
        <div class="home-map fl-wrap">
            <div class="hm-map-container fw-map">
                <div id="map" style="height:100%;width:100%;"></div>
            </div>
        </div>
    </div>

    <!-- ════════ RIGHT – Filters + Listings ════════ -->
    <div class="fs-inner-container">
        <div class="fs-content">

            <!-- Search / Filter form -->
            <form method="GET" action="" id="filter-form">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="_mp_filter mb-3">
                            <div class="_mp_filter_first">
                                <h4>Browse Coming Soon Properties</h4>
                                <div class="input-group">
                                    <select name="neighborhood" class="form-control">
                                        <option value="">All Neighborhoods</option>
                                        <?php foreach ($neighborhoods as $n): ?>
                                            <option value="<?= htmlspecialchars($n) ?>"
                                                <?= ($neighborhood === $n) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($n) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text btn-multiplex-search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="_mp_filter_last">
                                <a class="map_filter" data-bs-toggle="collapse" href="#filtermap"
                                   role="button" aria-expanded="false" aria-controls="filtermap">
                                    <i class="fa fa-sliders-h mr-2"></i>Filter
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Extended filters -->
                    <div class="col-lg-12 col-md-12 mt-2">
                        <div class="collapse" id="filtermap">
                            <div class="row">

                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Property Type</label>
                                        <select name="ptype" class="form-control">
                                            <option value="">All Types</option>
                                            <?php foreach ($ptypes as $pt): ?>
                                                <option value="<?= htmlspecialchars($pt) ?>"
                                                    <?= ($ptype === $pt) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($pt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Est. Completion Year</label>
                                        <select name="est_completion" class="form-control">
                                            <option value="">All Years</option>
                                            <?php foreach ($years as $yr): ?>
                                                <option value="<?= htmlspecialchars($yr) ?>"
                                                    <?= ($est_completion == $yr) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($yr) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-3 mt-2">
                                    <div class="elgio_filter">
                                        <div class="elgio_ft_first">
                                            <a href="?" class="btn btn-dark">Reset</a>
                                        </div>
                                        <div class="elgio_ft_last">
                                            <button type="submit" class="btn btn-primary btn-multiplex-search">Apply Filters</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Results count -->
            <div class="results-count mb-3">
                <small class="text-muted">
                    Showing <strong><?= count($properties) ?></strong> propert<?= count($properties) === 1 ? 'y' : 'ies' ?>
                </small>
            </div>

            <!-- ── Listings grid ─────────────────────────────────── -->
            <div class="row justify-content-center list-layout" id="listings-grid">

                <?php if (empty($properties)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No properties found. Try adjusting your filters.</p>
                    </div>
                <?php else: ?>

                <?php
                // ── Ad injection: pick 2 random positions among the listing slots ──
                $total_cards = count($properties);
                $ad_positions = [];
                if ($total_cards >= 2) {
                    // Insert first ad after card 2, second ad somewhere in the second half
                    $pos1 = 2;
                    $pos2 = max(4, intval($total_cards * 0.6));
                    $ad_positions = [$pos1, $pos2];
                } elseif ($total_cards >= 1) {
                    $ad_positions = [1];
                }
                // Pick which 2 ads to show (shuffle so they're different each filter)
                $ad_pool = [1, 2, 3, 4];
                shuffle($ad_pool);
                $ads_to_show = array_slice($ad_pool, 0, count($ad_positions));
                $ad_idx = 0;
                ?>

                    <?php foreach ($properties as $i => $p): ?>

                    <?php
                    // Inject ad card before this listing if position matches
                    if (in_array($i, $ad_positions) && $ad_idx < count($ads_to_show)):
                        $which_ad = $ads_to_show[$ad_idx++];
                    ?>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4 ad-card-slot">
                        <?php if ($which_ad === 1): ?>
                        <!-- AD 1: Concierge pitch — gold premium feel -->
                        <div class="promo-card promo-gold">
                            <div class="promo-stamp">⭐ Wynston Concierge</div>
                            <div class="promo-headline">Your Project.<br>Our Expertise.</div>
                            <div class="promo-body">Join developers who trust Wynnston to showcase their pre-sale listings with a dedicated concierge microsite — professional photography, floor plans & exclusive buyer reach.</div>
                            <div class="promo-features">
                                <span>📐 Custom floor plans</span>
                                <span>📸 Professional renders</span>
                                <span>🎯 Targeted buyer reach</span>
                            </div>
                            <a href="create-account.php" class="promo-btn">List With Us →</a>
                            <div class="promo-sub">Serving Metro Vancouver developers since 2024</div>
                        </div>

                        <?php elseif ($which_ad === 2): ?>
                        <!-- AD 2: Urgency / scarcity angle — dark navy -->
                        <div class="promo-card promo-navy">
                            <div class="promo-eyebrow">LIMITED SPOTS AVAILABLE</div>
                            <div class="promo-headline">Stand Out<br>Before Ground Breaks.</div>
                            <div class="promo-body">Pre-sale buyers are searching now. Get your development in front of qualified Metro Vancouver buyers before your competition does.</div>
                            <div class="promo-stats">
                                <div class="promo-stat"><span class="stat-n">500+</span><span class="stat-l">Monthly Buyers</span></div>
                                <div class="promo-stat"><span class="stat-n">3×</span><span class="stat-l">More Inquiries</span></div>
                                <div class="promo-stat"><span class="stat-n">48h</span><span class="stat-l">Go Live</span></div>
                            </div>
                            <a href="create-account.php" class="promo-btn promo-btn-light">Claim Your Spot →</a>
                        </div>

                        <?php elseif ($which_ad === 3): ?>
                        <!-- AD 3: Creative package — warm amber -->
                        <div class="promo-card promo-amber">
                            <div class="promo-eyebrow">🎨 Creative Package</div>
                            <div class="promo-headline">No Renderings?<br>We've Got You.</div>
                            <div class="promo-body">Our in-house creative team produces stunning floor plans and renderings for your pre-sale project — so you can market with confidence from day one.</div>
                            <div class="promo-checklist">
                                <div>✓ 2D &amp; 3D floor plans</div>
                                <div>✓ Exterior &amp; interior renders</div>
                                <div>✓ Site plan &amp; amenity visuals</div>
                                <div>✓ Print &amp; digital ready</div>
                            </div>
                            <a href="create-account.php" class="promo-btn promo-btn-dark">Get Creative Package →</a>
                        </div>

                        <?php else: ?>
                        <!-- AD 4: Social proof / testimonial — clean white -->
                        <div class="promo-card promo-white">
                            <div class="promo-quote-mark">"</div>
                            <div class="promo-quote">Wynston's concierge listing brought us qualified buyers before our sales centre even opened. Worth every penny.</div>
                            <div class="promo-quotee">
                                <div class="promo-quotee-avatar">DV</div>
                                <div>
                                    <div class="promo-quotee-name">Metro Vancouver Developer</div>
                                    <div class="promo-quotee-role">2024 Concierge Client</div>
                                </div>
                            </div>
                            <div class="promo-divider"></div>
                            <div class="promo-cta-row">
                                <span>Ready to reach more buyers?</span>
                                <a href="create-account.php" class="promo-btn-inline">Get Started →</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php
                    // Determine tier & routing
                    $p_tier = $p['tier'] ?? ($p['is_paid'] ? 'concierge' : 'free');
                    $p_url  = ($p_tier === 'concierge' ? 'concierge-property.php' : 'single-property-2.php') . '?id=' . $p['id'];
                    ?>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                        <div class="listing-item-container" data-id="<?= $p['id'] ?>">

                            <!-- Card image -->
                            <a href="<?= $p_url ?>" class="listing-img-wrap">
                                <?php if (!empty($p['img1'])): ?>
                                    <img src="<?= htmlspecialchars($p['img1']) ?>"
                                         alt="<?= htmlspecialchars($p['address']) ?>"
                                         style="width:100%;height:175px;object-fit:cover;display:block;">
                                <?php else: ?>
                                    <div class="generic-img-placeholder">
                                        <i class="fas fa-building"></i>
                                        <span><?= htmlspecialchars($p['property_type']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="listing-badges">
                                    <?php if ($p_tier === 'concierge'): ?>
                                        <span class="badge-concierge-lbl">⭐ Concierge</span>
                                    <?php else: ?>
                                        <span class="badge-presale">Coming Soon</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['est_completion'])): ?>
                                        <span class="badge-year">Est. <?= htmlspecialchars($p['est_completion']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>

                            <!-- Card body -->
                            <div class="listing-content">
                                <a href="<?= $p_url ?>" class="listing-address-link">
                                    <div class="listing-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($p['address']) ?>
                                    </div>
                                </a>
                                <div class="listing-neighborhood">
                                    <i class="fas fa-map"></i>
                                    <?= htmlspecialchars($p['neighborhood']) ?>
                                </div>

                                <?php if (!empty($p['description'])): ?>
                                <div class="listing-desc">
                                    <?= htmlspecialchars(mb_strimwidth($p['description'], 0, 110, '...')) ?>
                                </div>
                                <?php endif; ?>

                                <div class="listing-price-row">
                                    <div class="listing-price-tba">
                                        <span class="tba-label">Price</span>
                                        <span class="tba-value"><?= !empty($p['price']) ? htmlspecialchars($p['price']) : 'T.B.A.' ?></span>
                                    </div>
                                    <div class="listing-completion">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= htmlspecialchars($p['est_completion']) ?>
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;margin-top:10px;">
                                    <button class="card-notify-btn" onclick="wynOpenNotify(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['address'])) ?>')">
                                        <i class="fas fa-bell"></i> Notify Me
                                    </button>
                                    <button class="card-share-btn" onclick="wynShare('<?= addslashes(htmlspecialchars($p['address'])) ?>', '<?= $p_url ?>')">
                                        <i class="fas fa-share-alt"></i> Share
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /list-layout -->

        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLLe2WCEVXzFadCQCEodFCp0EpYWe8i2M&callback=initMap" async defer></script>

<script>
var propertyMarkers = <?= $markers_json ?>;
var markerMap      = {};
var customPopup    = null;
var popupIsClick   = false;
var outsideClickFn = null;  // track so we can always remove it

function removeOutsideListener() {
    if (outsideClickFn) {
        document.removeEventListener('click', outsideClickFn);
        outsideClickFn = null;
    }
}

function hidePopup() {
    removeOutsideListener();
    if (customPopup) { customPopup.remove(); customPopup = null; }
    popupIsClick = false;
}

function showPopup(map, marker, prop, isClick) {
    if (!isClick && popupIsClick) return;
    // Always clean up any existing outside-click listener before creating new popup
    removeOutsideListener();
    hidePopup();
    popupIsClick = isClick;

    var w = isClick ? 250 : 215;
    var imgH = isClick ? 150 : 115;

    var photo = prop.img
        ? '<img src="'+prop.img+'" style="width:100%;height:'+imgH+'px;object-fit:cover;display:block;">'
        : '<div style="width:100%;height:'+imgH+'px;background:linear-gradient(135deg,#002446,#0065ff);display:flex;align-items:center;justify-content:center;font-size:30px;">🏢</div>';

    var extra = isClick
        ? '<div style="margin:6px 0 8px;font-size:12px;color:#555;">Price: <strong style="color:#002446;">T.B.A.</strong></div>'
          + '<a href="'+(prop.is_paid ? 'concierge-property.php' : 'single-property-2.php')+'?id='+prop.id+'" style="display:block;background:#0065ff;color:#fff;padding:9px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;text-align:center;">View Details →</a>'
        : '';

    var closeX = isClick
        ? '<div onclick="hidePopup()" style="position:absolute;top:7px;right:7px;width:22px;height:22px;background:rgba(0,0,0,0.55);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:13px;z-index:10;line-height:1;">✕</div>'
        : '';

    var div = document.createElement('div');
    div.id  = 'cmap-popup';
    div.style.cssText = 'position:absolute;z-index:9999;pointer-events:'+(isClick?'auto':'none')+';';
    div.innerHTML =
        '<div style="position:relative;width:'+w+'px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.28);">'
            + closeX + photo
            + '<div style="padding:10px 12px 12px;">'
                + '<div style="font-size:10px;text-transform:uppercase;color:#0065ff;font-weight:700;letter-spacing:.5px;margin-bottom:2px;">'+prop.ptype+'</div>'
                + '<div style="font-size:12px;font-weight:700;color:#002446;line-height:1.4;margin-bottom:3px;">'+prop.address+'</div>'
                + '<div style="font-size:11px;color:#888;">📍 '+prop.neighborhood+' · Est. '+prop.year+'</div>'
                + extra
            + '</div>'
        + '</div>';

    document.getElementById('map').appendChild(div);
    customPopup = div;

    // Position after render so we get real height
    requestAnimationFrame(function() {
        var mapEl = document.getElementById('map');
        var proj  = map.getProjection();
        var bounds= map.getBounds();
        if (!proj || !bounds) return;
        var nw = proj.fromLatLngToPoint(new google.maps.LatLng(bounds.getNorthEast().lat(), bounds.getSouthWest().lng()));
        var pt = proj.fromLatLngToPoint(marker.getPosition());
        var sc = Math.pow(2, map.getZoom());
        var x  = Math.floor((pt.x - nw.x) * sc);
        var y  = Math.floor((pt.y - nw.y) * sc);
        var h  = div.offsetHeight || 260;
        var left = Math.max(5, Math.min(x - w/2, mapEl.offsetWidth - w - 5));
        var top  = y - h - 16;
        if (top < 5) top = y + 20;
        div.style.left = left + 'px';
        div.style.top  = top  + 'px';
    });

    // Attach outside-click-to-close only for click popups
    if (isClick) {
        setTimeout(function() {
            outsideClickFn = function(e) {
                if (customPopup && !customPopup.contains(e.target)) {
                    hidePopup();
                }
            };
            document.addEventListener('click', outsideClickFn);
        }, 200);
    }
}

function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 49.2500, lng: -123.1200 },
        scrollwheel: true,
        zoomControl: false,
        fullscreenControl: false,
        mapTypeControl: false,
        streetViewControl: false,
        styles: [{ featureType:'poi', elementType:'labels', stylers:[{visibility:'off'}] }]
    });

    var iconBlue = { path: google.maps.SymbolPath.CIRCLE, scale:9,  fillColor:'#0065ff', fillOpacity:1, strokeColor:'#fff', strokeWeight:2 };
    var iconHot  = { path: google.maps.SymbolPath.CIRCLE, scale:13, fillColor:'#ff4500', fillOpacity:1, strokeColor:'#fff', strokeWeight:2.5 };
    var hoverTimer;

    propertyMarkers.forEach(function(prop) {
        var marker = new google.maps.Marker({
            position: { lat: prop.lat, lng: prop.lng },
            map: map, title: prop.address, icon: iconBlue
        });
        markerMap[prop.id] = { marker:marker, prop:prop };

        marker.addListener('mouseover', function() {
            clearTimeout(hoverTimer);
            marker.setIcon(iconHot);
            showPopup(map, marker, prop, false);
        });
        marker.addListener('mouseout', function() {
            marker.setIcon(iconBlue);
            hoverTimer = setTimeout(function() { if (!popupIsClick) hidePopup(); }, 300);
        });
        marker.addListener('click', function() {
            // reset all markers
            Object.values(markerMap).forEach(function(m) { m.marker.setIcon(iconBlue); });
            marker.setIcon(iconHot);
            showPopup(map, marker, prop, true);
        });
    });

    // Auto fit
    if (propertyMarkers.length > 0) {
        var b = new google.maps.LatLngBounds();
        propertyMarkers.forEach(function(p) { b.extend({lat:p.lat,lng:p.lng}); });
        map.fitBounds(b);
        if (propertyMarkers.length === 1) map.setZoom(15);
    }

    // Card hover → highlight marker
    document.querySelectorAll('.listing-item-container[data-id]').forEach(function(card) {
        var id = parseInt(card.getAttribute('data-id'));
        card.addEventListener('mouseenter', function() {
            if (markerMap[id]) {
                markerMap[id].marker.setIcon(iconHot);
                markerMap[id].marker.setZIndex(999);
                showPopup(map, markerMap[id].marker, markerMap[id].prop, false);
            }
        });
        card.addEventListener('mouseleave', function() {
            if (markerMap[id]) {
                markerMap[id].marker.setIcon(iconBlue);
                markerMap[id].marker.setZIndex(1);
                if (!popupIsClick) hidePopup();
            }
        });
    });
}
</script>

<style>
/* ── Half-map layout ──────────────────────────────────────────────── */
.home-map-banner.half-map {
    display: flex !important;
    align-items: flex-start !important;
    overflow: visible !important;
}
/* Map panel sticks in view while listings + footer scroll */
.fs-left-map-box {
    flex: 0 0 50% !important;
    width: 50% !important;
    height: calc(100vh - 70px) !important;
    position: sticky !important;
    top: 70px !important;
    overflow: hidden !important;
}
/* Listings panel scrolls naturally — footer appears below it */
.fs-inner-container {
    flex: 0 0 50% !important;
    width: 50% !important;
    min-height: calc(100vh - 70px) !important;
    overflow-y: visible !important;
    overflow-x: hidden !important;
}
.home-map, .hm-map-container, .fw-map, #map {
    height: 100% !important;
    width:  100% !important;
}

/* ── Buttons ──────────────────────────────────────────────────────── */
.btn-multiplex-search, .btn-primary, .btn-dark {
    background-color: #002446 !important; color:#fff !important;
    border:none !important; transition:all .3s ease !important;
}
.btn-multiplex-search:hover,.btn-primary:hover,.btn-dark:hover {
    background-color:#0065ff !important; box-shadow:0 4px 12px rgba(0,101,255,.4);
}

/* ── Listing card ─────────────────────────────────────────────────── */
.listing-item-container {
    border:1px solid #e2e8f0; border-radius:10px;
    overflow:hidden; background:#fff;
    transition:box-shadow .25s, transform .25s;
    cursor:pointer;
}
.listing-item-container:hover {
    box-shadow:0 6px 24px rgba(0,36,70,.15);
    transform:translateY(-2px);
    border-color:#0065ff;
}
.listing-item-container.map-active {
    box-shadow:0 0 0 2px #0065ff;
    border-color:#0065ff;
}

/* ── Image / placeholder ──────────────────────────────────────────── */
.listing-img-wrap { position:relative; display:block; text-decoration:none; }
.listing-img-wrap img { width:100%; height:175px; object-fit:cover; display:block; }
.generic-img-placeholder {
    width:100%; height:175px;
    background:linear-gradient(135deg,#002446 0%,#004080 60%,#0065ff 100%);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    color:rgba(255,255,255,.8); gap:8px;
}
.generic-img-placeholder i    { font-size:42px; opacity:.65; }
.generic-img-placeholder span { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }

/* ── Badges ───────────────────────────────────────────────────────── */
.listing-badges { position:absolute; top:10px; left:10px; display:flex; gap:6px; z-index:2; }
.badge-presale  { background:#0065ff; color:#fff; font-size:10px; font-weight:700; padding:3px 9px; border-radius:4px; text-transform:uppercase; }
.badge-year     { background:rgba(0,0,0,.5); color:#fff; font-size:10px; font-weight:600; padding:3px 9px; border-radius:4px; }

/* ── Card body ────────────────────────────────────────────────────── */
.listing-content { padding:14px 16px; }
.listing-address { font-size:13px; font-weight:600; color:#002446; margin-bottom:4px; }
.listing-address i, .listing-neighborhood i { color:#0065ff; margin-right:5px; font-size:11px; }
.listing-neighborhood { font-size:12px; color:#667; margin-bottom:8px; }
.listing-desc { font-size:12px; color:#888; line-height:1.5; margin-bottom:10px; border-top:1px solid #f0f0f0; padding-top:8px; }
.listing-address-link { text-decoration:none; }
.listing-address-link:hover .listing-address { color:#0065ff; }

/* ── Price row ────────────────────────────────────────────────────── */
.listing-price-row { display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f0f0f0; padding-top:10px; margin-top:4px; }
.listing-price-tba { display:flex; flex-direction:column; }
.tba-label { font-size:10px; text-transform:uppercase; color:#aaa; letter-spacing:.5px; }
.tba-value { font-size:22px; font-weight:800; color:#002446; letter-spacing:1.5px; }
.listing-completion { font-size:12px; color:#555; background:#f0f4ff; padding:5px 10px; border-radius:6px; }
.listing-completion i { color:#0065ff; margin-right:4px; }


/* ── Hide theme compare/saved widget ─────────────────────────────── */
.compare-property-wrap,
.compare-property-slide,
.cp-slide-wrap,
.slide-property-wrap,
.compare-wrap,
#compare-sidebar,
.compare-list-wrap,
.property-compare-wrap { display:none !important; }

/* ── Notify Me button on card ────────────────────────────────────── */
.card-notify-btn {
    display:flex; align-items:center; gap:6px;
    background:#002446; color:#fff;
    border:none; border-radius:6px;
    font-size:11px; font-weight:700;
    padding:6px 12px; cursor:pointer;
    transition:background .2s;
    text-transform:uppercase; letter-spacing:.5px;
}
.card-notify-btn:hover { background:#0065ff; }
.card-share-btn {
    display:flex; align-items:center; gap:5px;
    background:#f4f6fb; color:#555;
    border:none; border-radius:6px;
    font-size:11px; font-weight:600;
    padding:6px 10px; cursor:pointer;
    transition:background .2s;
}
.card-share-btn:hover { background:#e8ecf5; }

/* ── Notify modal ─────────────────────────────────────────────────── */
#wyn-notify-modal {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,.6); z-index:99999;
    align-items:center; justify-content:center; padding:16px;
}
#wyn-notify-modal.open { display:flex; }
.wyn-notify-box {
    background:#fff; border-radius:14px;
    max-width:420px; width:100%; padding:36px 32px;
    box-shadow:0 24px 64px rgba(0,0,0,.2);
}
.wyn-notify-box h4 { font-size:18px; font-weight:800; color:#002446; margin-bottom:8px; }
.wyn-notify-box p  { font-size:13px; color:#666; margin-bottom:20px; line-height:1.6; }
.wyn-notify-input {
    width:100%; padding:11px 14px; border:1.5px solid #dde;
    border-radius:8px; font-size:14px; margin-bottom:10px; outline:none;
}
.wyn-notify-input:focus { border-color:#002446; }
.wyn-notify-submit {
    width:100%; padding:13px; background:#002446; color:#fff;
    border:none; border-radius:8px; font-size:14px;
    font-weight:700; cursor:pointer; transition:background .2s;
}
.wyn-notify-submit:hover { background:#0065ff; }
.wyn-notify-close {
    background:none; border:none; font-size:13px;
    color:#aaa; cursor:pointer; margin-top:12px;
    width:100%; text-align:center;
}
.wyn-notify-success { text-align:center; padding:10px 0; }
.wyn-notify-success i { font-size:40px; color:#16a34a; margin-bottom:12px; display:block; }
/* ── Tier badges ──────────────────────────────────────────────────── */
.badge-concierge-lbl {
    background: linear-gradient(135deg,#c9a84c,#e8d84b);
    color: #002446; font-size:10px; font-weight:800;
    padding:3px 9px; border-radius:4px; letter-spacing:.4px;
}

/* ── Promo / Ad cards ─────────────────────────────────────────────── */
.promo-card {
    border-radius:12px; padding:24px; height:100%;
    min-height:300px; display:flex; flex-direction:column;
    justify-content:space-between; gap:14px;
    position:relative; overflow:hidden;
}
.promo-btn {
    display:inline-block; padding:10px 20px; border-radius:24px;
    font-size:12px; font-weight:800; text-decoration:none;
    text-transform:uppercase; letter-spacing:.5px;
    transition:opacity .2s, transform .2s; margin-top:auto;
    align-self:flex-start;
}
.promo-btn:hover { opacity:.85; transform:translateY(-1px); }
.promo-btn-light { background:#fff; color:#002446; }
.promo-btn-dark  { background:#002446; color:#fff; }

/* AD 1 — Gold */
.promo-gold {
    background: linear-gradient(145deg,#002446 0%,#003a70 100%);
    color:#fff;
    border:1px solid rgba(201,168,76,.3);
    box-shadow: 0 8px 32px rgba(0,36,70,.25), inset 0 1px 0 rgba(201,168,76,.2);
}
.promo-gold .promo-stamp {
    display:inline-block; background:linear-gradient(135deg,#c9a84c,#e8d84b);
    color:#002446; font-size:10px; font-weight:800;
    padding:4px 12px; border-radius:20px; letter-spacing:.5px;
    align-self:flex-start;
}
.promo-gold .promo-headline {
    font-size:22px; font-weight:800; line-height:1.2; color:#fff;
}
.promo-gold .promo-body { font-size:12px; color:rgba(255,255,255,.75); line-height:1.6; }
.promo-features { display:flex; flex-direction:column; gap:5px; }
.promo-features span { font-size:11px; color:rgba(255,255,255,.8); }
.promo-gold .promo-btn { background:linear-gradient(135deg,#c9a84c,#e8d84b); color:#002446; }
.promo-sub { font-size:10px; color:rgba(255,255,255,.4); margin-top:4px; }

/* AD 2 — Navy urgency */
.promo-navy {
    background: #001830;
    color:#fff;
    border:1px solid rgba(255,255,255,.08);
    box-shadow: 0 8px 32px rgba(0,0,0,.3);
}
.promo-eyebrow {
    font-size:10px; font-weight:800; letter-spacing:1.5px;
    text-transform:uppercase; color:#0065ff;
}
.promo-navy .promo-headline { font-size:22px; font-weight:800; line-height:1.2; color:#fff; }
.promo-navy .promo-body { font-size:12px; color:rgba(255,255,255,.65); line-height:1.6; }
.promo-stats { display:flex; gap:12px; }
.promo-stat { display:flex; flex-direction:column; align-items:center;
    background:rgba(255,255,255,.06); border-radius:8px; padding:10px 12px; flex:1; }
.stat-n { font-size:20px; font-weight:800; color:#0065ff; }
.stat-l { font-size:10px; color:rgba(255,255,255,.5); text-align:center; margin-top:2px; }
.promo-navy .promo-btn-light { background:#0065ff; color:#fff; }

/* AD 3 — Amber creative */
.promo-amber {
    background: #e8d84b;
    color:#002446;
    box-shadow: 0 8px 32px rgba(255,210,94,.3);
}
.promo-amber .promo-eyebrow { color:#7c3a00; }
.promo-amber .promo-headline { font-size:22px; font-weight:800; line-height:1.2; color:#002446; }
.promo-amber .promo-body { font-size:12px; color:#444; line-height:1.6; }
.promo-checklist div { font-size:11px; color:#333; }
.promo-amber .promo-btn-dark { background:#002446; color:#ffd25e; }

/* AD 4 — White testimonial */
.promo-white {
    background:#fff;
    border:1.5px solid #e8e4dd;
    box-shadow: 0 4px 24px rgba(0,36,70,.08);
}
.promo-quote-mark { font-size:60px; color:#e8d84b; line-height:.8; font-family:Georgia,serif; }
.promo-quote { font-size:14px; color:#002446; font-style:italic; line-height:1.7; font-weight:600; }
.promo-quotee { display:flex; align-items:center; gap:12px; margin-top:4px; }
.promo-quotee-avatar {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,#002446,#0065ff);
    color:#fff; font-size:12px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.promo-quotee-name { font-size:12px; font-weight:700; color:#002446; }
.promo-quotee-role { font-size:11px; color:#aaa; }
.promo-divider { height:1px; background:#f0ece6; }
.promo-cta-row {
    display:flex; align-items:center; justify-content:space-between;
    gap:8px; flex-wrap:wrap;
}
.promo-cta-row span { font-size:12px; color:#666; }
.promo-btn-inline {
    background:#002446; color:#fff;
    padding:8px 16px; border-radius:20px;
    font-size:11px; font-weight:800; text-decoration:none;
    text-transform:uppercase; letter-spacing:.5px;
    transition:background .2s; white-space:nowrap;
}
.promo-btn-inline:hover { background:#0065ff; color:#fff; }

/* ── Mobile ───────────────────────────────────────────────────────── */
@media (max-width:768px) {
    .home-map-banner.half-map { flex-direction:column !important; }
    .fs-left-map-box  { flex:none !important; width:100% !important; height:50vh !important; position:relative !important; top:0 !important; }
    .fs-inner-container { flex:none !important; width:100% !important; min-height:auto !important; }
    .home-map, .hm-map-container, .fw-map, #map { height:50vh !important; }
    .promo-card { min-height:auto; }
}
</style>


<!-- ── Notify Me Modal ───────────────────────────────────────────────────── -->
<div id="wyn-notify-modal">
    <div class="wyn-notify-box">
        <div id="wyn-notify-form-wrap">
            <h4><i class="fas fa-bell me-2" style="color:#c9a84c;"></i>Get Notified About This Project</h4>
            <p id="wyn-notify-desc">Enter your email and we'll let you know when this development has updates — new photos, completion dates, or when units become available.</p>
            <input type="email" id="wyn-notify-email" class="wyn-notify-input" placeholder="your@email.com">
            <input type="text" id="wyn-notify-name" class="wyn-notify-input" placeholder="Your name (optional)">
            <input type="hidden" id="wyn-notify-pid" value="">
            <button class="wyn-notify-submit" onclick="wynSubmitNotify()">
                <i class="fas fa-bell me-2"></i>Notify Me When There Are Updates
            </button>
            <button class="wyn-notify-close" onclick="wynCloseNotify()">No thanks</button>
        </div>
        <div id="wyn-notify-success-wrap" class="wyn-notify-success" style="display:none;">
            <i class="fas fa-check-circle"></i>
            <h4 style="color:#002446;">You're on the list!</h4>
            <p style="color:#666;font-size:13px;">We'll email you when there are updates to this development.</p>
            <button class="wyn-notify-close" onclick="wynCloseNotify()" style="color:#002446;font-weight:700;">Close</button>
        </div>
    </div>
</div>

<script>
function wynOpenNotify(pid, address) {
    document.getElementById('wyn-notify-pid').value = pid;
    document.getElementById('wyn-notify-desc').textContent = 'Get updates on ' + address + ' — new photos, completion dates, or when units become available.';
    document.getElementById('wyn-notify-form-wrap').style.display = 'block';
    document.getElementById('wyn-notify-success-wrap').style.display = 'none';
    document.getElementById('wyn-notify-email').value = '';
    document.getElementById('wyn-notify-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function wynCloseNotify() {
    document.getElementById('wyn-notify-modal').classList.remove('open');
    document.body.style.overflow = '';
}
function wynSubmitNotify() {
    var email = document.getElementById('wyn-notify-email').value.trim();
    var name  = document.getElementById('wyn-notify-name').value.trim();
    var pid   = document.getElementById('wyn-notify-pid').value;
    if (!email || !email.includes('@')) {
        document.getElementById('wyn-notify-email').style.borderColor = '#e00';
        return;
    }
    document.getElementById('wyn-notify-email').style.borderColor = '#dde';
    // POST to notify-subscribe.php
    fetch('notify-subscribe.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email) + '&name=' + encodeURIComponent(name) + '&property_id=' + encodeURIComponent(pid) + '&source=half-map'
    }).then(function() {
        document.getElementById('wyn-notify-form-wrap').style.display = 'none';
        document.getElementById('wyn-notify-success-wrap').style.display = 'block';
    }).catch(function() {
        document.getElementById('wyn-notify-form-wrap').style.display = 'none';
        document.getElementById('wyn-notify-success-wrap').style.display = 'block';
    });
}
function wynShare(address, url) {
    var fullUrl = window.location.origin + '/' + url;
    if (navigator.share) {
        navigator.share({ title: address + ' — Wynston', url: fullUrl });
    } else {
        navigator.clipboard.writeText(fullUrl).then(function() {
            alert('Link copied to clipboard!');
        });
    }
}
// Close modal on backdrop click
document.getElementById('wyn-notify-modal').addEventListener('click', function(e) {
    if (e.target === this) wynCloseNotify();
});
</script>


<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>