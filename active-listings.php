<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

// ── Database ──────────────────────────────────────────────────────────────────
require_once "$base_dir/db.php";

// ── Filter values from GET ────────────────────────────────────────────────────
$city      = $_GET['city']      ?? 'Vancouver';
$ptype     = $_GET['ptype']     ?? ''; // now maps to building_type
$beds_min  = $_GET['beds_min']  ?? '';
$price_max = $_GET['price_max'] ?? '';

// ── Map user-facing ptype values to building_type DB values ───────────────────
// DDF StructureType field contains values like "House", "Apartment", "Row-Townhouse" etc.
// We group them into friendly labels for the filter dropdown
$ptype_map = [
    'detached'  => ['House', 'Single Family', 'Detached'],
    'condo'     => ['Apartment', 'Apartment/Condo', 'Condominium'],
    'townhouse' => ['Row / Townhouse', 'Row-Townhouse', 'Townhouse', 'Row/Townhouse'],
    'duplex'    => ['Duplex', 'Half Duplex', 'Fourplex', 'Triplex'],
    'other'     => ['Mobile Home', 'Manufactured'],
];

// ── Query ddf_listings ────────────────────────────────────────────────────────
$sql    = "SELECT * FROM ddf_listings WHERE status = 'Active' AND latitude IS NOT NULL AND longitude IS NOT NULL";
$params = [];

if (!empty($city)) {
    $sql .= " AND city = :city";
    $params[':city'] = $city;
}

// Filter by building_type using the map above
if (!empty($ptype) && isset($ptype_map[$ptype])) {
    $vals         = $ptype_map[$ptype];
    $placeholders = [];
    foreach ($vals as $i => $v) {
        $key = ':bt' . $i;
        $placeholders[] = $key;
        $params[$key]   = $v;
    }
    $sql .= " AND building_type IN (" . implode(',', $placeholders) . ")";
}

if (!empty($beds_min)) {
    $sql .= " AND bedrooms >= :beds_min";
    $params[':beds_min'] = (int)$beds_min;
}
if (!empty($price_max)) {
    $sql .= " AND price <= :price_max";
    $params[':price_max'] = (int)$price_max;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Distinct dropdown options ─────────────────────────────────────────────────
$cities = $pdo->query("SELECT DISTINCT city FROM ddf_listings WHERE city != '' ORDER BY city ASC")->fetchAll(PDO::FETCH_COLUMN);

// ── Map markers JSON ──────────────────────────────────────────────────────────
$map_markers = [];
foreach ($properties as $p) {
    $lat = (float)($p['latitude']  ?? 0);
    $lng = (float)($p['longitude'] ?? 0);
    if ($lat != 0.0 && $lng != 0.0) {
        $map_markers[] = [
            'id'      => $p['id'],
            'address' => htmlspecialchars($p['address']),
            'city'    => htmlspecialchars($p['city']),
            'ptype'   => htmlspecialchars($p['building_type'] ?: $p['property_type']),
            'price'   => htmlspecialchars($p['price_formatted']),
            'beds'    => (int)($p['bedrooms']  ?? 0),
            'baths'   => (int)($p['bathrooms'] ?? 0),
            'sqft'    => (int)($p['sqft']      ?? 0),
            'mls'     => htmlspecialchars($p['mls_number']),
            'lat'     => $lat,
            'lng'     => $lng,
            'img'     => !empty($p['img1']) ? $p['img1'] : '',
        ];
    }
}
$markers_json = json_encode($map_markers);

// ── Navbar ────────────────────────────────────────────────────────────────────
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
                                <h4>Active MLS® Listings</h4>
                                <div class="input-group">
                                    <select name="city" class="form-control">
                                        <?php foreach ($cities as $c): ?>
                                            <option value="<?= htmlspecialchars($c) ?>"
                                                <?= ($city === $c) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text btn-active-search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="_mp_filter_last">
                                <a class="map_filter" data-bs-toggle="collapse" href="#filtermap"
                                   role="button" aria-expanded="<?= !empty($ptype) || !empty($beds_min) || !empty($price_max) ? 'true' : 'false' ?>"
                                   aria-controls="filtermap">
                                    <i class="fa fa-sliders-h mr-2"></i>Filter
                                    <?php if (!empty($ptype) || !empty($beds_min) || !empty($price_max)): ?>
                                        <span class="filter-active-dot"></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Extended filters -->
                    <div class="col-lg-12 col-md-12 mt-2">
                        <div class="collapse <?= !empty($ptype) || !empty($beds_min) || !empty($price_max) ? 'show' : '' ?>" id="filtermap">
                            <div class="row">

                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Property Type</label>
                                        <select name="ptype" class="form-control">
                                            <option value="">All Types</option>
                                            <option value="detached"  <?= $ptype==='detached'  ? 'selected':'' ?>>Detached House</option>
                                            <option value="condo"     <?= $ptype==='condo'     ? 'selected':'' ?>>Condo / Apartment</option>
                                            <option value="townhouse" <?= $ptype==='townhouse' ? 'selected':'' ?>>Townhouse</option>
                                            <option value="duplex"    <?= $ptype==='duplex'    ? 'selected':'' ?>>Duplex / Multiplex</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Min. Bedrooms</label>
                                        <select name="beds_min" class="form-control">
                                            <option value="">Any</option>
                                            <?php foreach ([1,2,3,4,5] as $b): ?>
                                                <option value="<?= $b ?>" <?= ($beds_min == $b) ? 'selected' : '' ?>>
                                                    <?= $b ?>+ Beds
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Max Price</label>
                                        <select name="price_max" class="form-control">
                                            <option value="">Any Price</option>
                                            <?php
                                            $price_options = [500000,750000,1000000,1500000,2000000,3000000,5000000];
                                            foreach ($price_options as $po):
                                            ?>
                                                <option value="<?= $po ?>" <?= ($price_max == $po) ? 'selected' : '' ?>>
                                                    Under $<?= number_format($po) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-3 mt-2">
                                    <div class="elgio_filter">
                                        <div class="elgio_ft_first">
                                            <a href="active-listings.php" class="btn btn-dark">Reset</a>
                                        </div>
                                        <div class="elgio_ft_last">
                                            <button type="submit" class="btn btn-primary btn-active-search">Apply Filters</button>
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
                    Showing <strong><?= count($properties) ?></strong>
                    active listing<?= count($properties) === 1 ? '' : 's' ?>
                    <?= !empty($city) ? 'in <strong>' . htmlspecialchars($city) . '</strong>' : 'across Greater Vancouver' ?>
                    <?php if (!empty($ptype)): ?>
                        · <strong><?= htmlspecialchars(ucfirst($ptype)) ?></strong>
                    <?php endif; ?>
                    <span class="mls-data-note">· MLS® Data</span>
                </small>
            </div>

            <!-- ── Listings grid ── -->
            <div class="row justify-content-center list-layout">

                <?php if (empty($properties)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No active listings found. Try adjusting your filters.</p>
                        <a href="active-listings.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($properties as $p): ?>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                        <div class="listing-item-container" data-id="<?= $p['id'] ?>">

                            <!-- Card image -->
                            <a href="active-property.php?id=<?= $p['id'] ?>" class="listing-img-wrap">
                                <?php if (!empty($p['img1'])): ?>
                                    <img src="<?= htmlspecialchars($p['img1']) ?>"
                                         alt="<?= htmlspecialchars($p['address']) ?>"
                                         style="width:100%;height:175px;object-fit:cover;display:block;"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="generic-img-placeholder">
                                        <i class="fas fa-building"></i>
                                        <span><?= htmlspecialchars($p['building_type'] ?: $p['property_type'] ?: 'Residential') ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="listing-badges">
                                    <span class="badge-active">Active</span>
                                    <?php if (!empty($p['building_type'])): ?>
                                        <span class="badge-ptype"><?= htmlspecialchars($p['building_type']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge-mls">MLS® <?= htmlspecialchars($p['mls_number']) ?></span>
                                </div>
                            </a>

                            <!-- Card body -->
                            <div class="listing-content">
                                <a href="active-property.php?id=<?= $p['id'] ?>" class="listing-address-link">
                                    <div class="listing-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($p['address']) ?>
                                    </div>
                                </a>
                                <div class="listing-neighborhood">
                                    <i class="fas fa-map"></i>
                                    <?= htmlspecialchars($p['city']) ?>
                                    <?php if (!empty($p['neighborhood']) && $p['neighborhood'] !== $p['city']): ?>
                                        — <?= htmlspecialchars($p['neighborhood']) ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Specs row -->
                                <?php if (!empty($p['bedrooms']) || !empty($p['bathrooms']) || !empty($p['sqft'])): ?>
                                <div class="listing-specs">
                                    <?php if (!empty($p['bedrooms'])): ?>
                                    <span><i class="fas fa-bed"></i> <?= $p['bedrooms'] ?> bd</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['bathrooms'])): ?>
                                    <span><i class="fas fa-bath"></i> <?= $p['bathrooms'] ?> ba</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['sqft'])): ?>
                                    <span><i class="fas fa-ruler-combined"></i> <?= number_format($p['sqft']) ?> sf</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Price row -->
                                <div class="listing-price-row">
                                    <div class="listing-price-active">
                                        <span class="active-label">Price</span>
                                        <span class="active-price"><?= htmlspecialchars($p['price_formatted']) ?></span>
                                    </div>
                                    <a href="active-property.php?id=<?= $p['id'] ?>" class="btn-view-active">
                                        View <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <!-- Notify + Share -->
                                <div style="display:flex;gap:8px;margin-top:10px;">
                                    <button class="card-notify-btn" onclick="wynOpenNotify(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['address'])) ?>')">
                                        <i class="fas fa-bell"></i> Notify Me
                                    </button>
                                    <button class="card-share-btn" onclick="wynShare('<?= addslashes(htmlspecialchars($p['address'])) ?>', 'active-property.php?id=<?= $p['id'] ?>')">
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
var markerMap = {}, customPopup = null, popupIsClick = false, outsideClickFn = null;

function removeOutsideListener() { if (outsideClickFn) { document.removeEventListener('click', outsideClickFn); outsideClickFn = null; } }
function hidePopup() { removeOutsideListener(); if (customPopup) { customPopup.remove(); customPopup = null; } popupIsClick = false; }

function showPopup(map, marker, prop, isClick) {
    if (!isClick && popupIsClick) return;
    removeOutsideListener(); hidePopup(); popupIsClick = isClick;
    var w = isClick ? 250 : 215, imgH = isClick ? 150 : 115;
    var photo = prop.img
        ? '<img src="'+prop.img+'" style="width:100%;height:'+imgH+'px;object-fit:cover;display:block;">'
        : '<div style="width:100%;height:'+imgH+'px;background:linear-gradient(135deg,#1a7a4a,#22c55e);display:flex;align-items:center;justify-content:center;font-size:30px;">🏡</div>';
    var specs = [];
    if (prop.beds)  specs.push(prop.beds + ' bd');
    if (prop.baths) specs.push(prop.baths + ' ba');
    if (prop.sqft)  specs.push(prop.sqft.toLocaleString() + ' sf');
    var specsHtml = specs.length ? '<div style="font-size:11px;color:#666;margin-bottom:4px;">'+specs.join(' · ')+'</div>' : '';
    var extra = isClick
        ? specsHtml + '<div style="margin:4px 0 8px;font-size:13px;font-weight:800;color:#002446;">'+prop.price+'</div>'
          + '<a href="active-property.php?id='+prop.id+'" style="display:block;background:#22c55e;color:#fff;padding:9px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;text-align:center;">View Details →</a>'
        : '<div style="font-size:13px;font-weight:800;color:#002446;margin-top:4px;">'+prop.price+'</div>';
    var closeX = isClick ? '<div onclick="hidePopup()" style="position:absolute;top:7px;right:7px;width:22px;height:22px;background:rgba(0,0,0,0.55);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:13px;z-index:10;line-height:1;">✕</div>' : '';
    var div = document.createElement('div');
    div.id = 'cmap-popup';
    div.style.cssText = 'position:absolute;z-index:9999;pointer-events:'+(isClick?'auto':'none')+';';
    div.innerHTML = '<div style="position:relative;width:'+w+'px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.28);">'
        + closeX + photo
        + '<div style="padding:10px 12px 12px;">'
            + '<div style="font-size:10px;text-transform:uppercase;color:#22c55e;font-weight:700;letter-spacing:.5px;margin-bottom:2px;">'+prop.ptype+'</div>'
            + '<div style="font-size:12px;font-weight:700;color:#002446;line-height:1.4;margin-bottom:3px;">'+prop.address+'</div>'
            + '<div style="font-size:11px;color:#888;margin-bottom:4px;">📍 '+prop.city+'</div>'
            + extra
        + '</div></div>';
    document.getElementById('map').appendChild(div);
    customPopup = div;
    requestAnimationFrame(function() {
        var mapEl = document.getElementById('map');
        var proj = map.getProjection(), bounds = map.getBounds();
        if (!proj || !bounds) return;
        var nw = proj.fromLatLngToPoint(new google.maps.LatLng(bounds.getNorthEast().lat(), bounds.getSouthWest().lng()));
        var pt = proj.fromLatLngToPoint(marker.getPosition());
        var sc = Math.pow(2, map.getZoom());
        var x = Math.floor((pt.x - nw.x) * sc), y = Math.floor((pt.y - nw.y) * sc);
        var h = div.offsetHeight || 260;
        var left = Math.max(5, Math.min(x - w/2, mapEl.offsetWidth - w - 5));
        var top = y - h - 16; if (top < 5) top = y + 20;
        div.style.left = left+'px'; div.style.top = top+'px';
    });
    if (isClick) {
        setTimeout(function() {
            outsideClickFn = function(e) { if (customPopup && !customPopup.contains(e.target)) hidePopup(); };
            document.addEventListener('click', outsideClickFn);
        }, 200);
    }
}

function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12, center: { lat: 49.2500, lng: -123.1200 },
        scrollwheel: true, zoomControl: false, fullscreenControl: false,
        mapTypeControl: false, streetViewControl: false,
        styles: [{ featureType:'poi', elementType:'labels', stylers:[{visibility:'off'}] }]
    });
    var iconGreen = { path: google.maps.SymbolPath.CIRCLE, scale:9,  fillColor:'#22c55e', fillOpacity:1, strokeColor:'#fff', strokeWeight:2 };
    var iconHot   = { path: google.maps.SymbolPath.CIRCLE, scale:13, fillColor:'#16a34a', fillOpacity:1, strokeColor:'#fff', strokeWeight:2.5 };
    var hoverTimer;
    propertyMarkers.forEach(function(prop) {
        var marker = new google.maps.Marker({ position:{lat:prop.lat,lng:prop.lng}, map:map, title:prop.address, icon:iconGreen });
        markerMap[prop.id] = { marker:marker, prop:prop };
        marker.addListener('mouseover', function() { clearTimeout(hoverTimer); marker.setIcon(iconHot); showPopup(map, marker, prop, false); });
        marker.addListener('mouseout',  function() { marker.setIcon(iconGreen); hoverTimer = setTimeout(function() { if (!popupIsClick) hidePopup(); }, 300); });
        marker.addListener('click',     function() { Object.values(markerMap).forEach(function(m) { m.marker.setIcon(iconGreen); }); marker.setIcon(iconHot); showPopup(map, marker, prop, true); });
    });
    if (propertyMarkers.length > 0) {
        var b = new google.maps.LatLngBounds();
        propertyMarkers.forEach(function(p) { b.extend({lat:p.lat,lng:p.lng}); });
        map.fitBounds(b);
        if (propertyMarkers.length === 1) map.setZoom(15);
    }
    document.querySelectorAll('.listing-item-container[data-id]').forEach(function(card) {
        var id = parseInt(card.getAttribute('data-id'));
        card.addEventListener('mouseenter', function() {
            if (markerMap[id]) { markerMap[id].marker.setIcon(iconHot); markerMap[id].marker.setZIndex(999); showPopup(map, markerMap[id].marker, markerMap[id].prop, false); }
        });
        card.addEventListener('mouseleave', function() {
            if (markerMap[id]) { markerMap[id].marker.setIcon(iconGreen); markerMap[id].marker.setZIndex(1); if (!popupIsClick) hidePopup(); }
        });
    });
}
</script>

<style>
html, body { overflow-x: hidden; }
.home-map-banner.half-map { display:flex !important; align-items:flex-start !important; overflow:visible !important; }
.fs-left-map-box { flex:0 0 50% !important; width:50% !important; height:calc(100vh - 70px) !important; position:sticky !important; top:70px !important; overflow:hidden !important; }
.fs-inner-container { flex:0 0 50% !important; width:50% !important; min-height:calc(100vh - 70px) !important; overflow-y:visible !important; overflow-x:hidden !important; }
.home-map, .hm-map-container, .fw-map, #map { height:100% !important; width:100% !important; }
.btn-active-search, .btn-primary { background-color:#1a7a4a !important; color:#fff !important; border:none !important; transition:all .3s ease !important; }
.btn-active-search:hover, .btn-primary:hover { background-color:#22c55e !important; box-shadow:0 4px 12px rgba(34,197,94,.4) !important; }
.btn-dark { background:#002446 !important; color:#fff !important; border:none !important; }
.btn-dark:hover { background:#003a6e !important; }
.listing-item-container { border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; background:#fff; transition:box-shadow .25s, transform .25s; cursor:pointer; }
.listing-item-container:hover { box-shadow:0 6px 24px rgba(0,36,70,.15); transform:translateY(-2px); border-color:#22c55e; }
.listing-img-wrap { position:relative; display:block; text-decoration:none; }
.listing-img-wrap img { width:100%; height:175px; object-fit:cover; display:block; }
.generic-img-placeholder { width:100%; height:175px; background:linear-gradient(135deg,#1a7a4a 0%,#15803d 60%,#22c55e 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; color:rgba(255,255,255,.8); gap:8px; }
.generic-img-placeholder i { font-size:42px; opacity:.65; }
.generic-img-placeholder span { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.listing-badges { position:absolute; top:10px; left:10px; display:flex; gap:6px; z-index:2; flex-wrap:wrap; }
.badge-active { background:#22c55e; color:#fff; font-size:10px; font-weight:700; padding:3px 9px; border-radius:4px; text-transform:uppercase; }
.badge-ptype  { background:rgba(0,36,70,.75); color:#fff; font-size:10px; font-weight:600; padding:3px 9px; border-radius:4px; }
.badge-mls    { background:rgba(0,0,0,.5); color:#fff; font-size:10px; font-weight:600; padding:3px 9px; border-radius:4px; }
.listing-content { padding:14px 16px; }
.listing-address { font-size:13px; font-weight:600; color:#002446; margin-bottom:4px; }
.listing-address i, .listing-neighborhood i { color:#22c55e; margin-right:5px; font-size:11px; }
.listing-neighborhood { font-size:12px; color:#667; margin-bottom:8px; }
.listing-address-link { text-decoration:none; }
.listing-address-link:hover .listing-address { color:#22c55e; }
.listing-specs { display:flex; gap:12px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #f0f0f0; }
.listing-specs span { font-size:12px; color:#555; display:flex; align-items:center; gap:4px; }
.listing-specs i { color:#22c55e; font-size:11px; }
.listing-price-row { display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f0f0f0; padding-top:10px; margin-top:4px; }
.listing-price-active { display:flex; flex-direction:column; }
.active-label { font-size:10px; text-transform:uppercase; color:#aaa; letter-spacing:.5px; }
.active-price { font-size:20px; font-weight:800; color:#002446; }
.btn-view-active { background:#22c55e; color:#fff; padding:7px 14px; border-radius:6px; font-size:12px; font-weight:700; text-decoration:none; transition:background .2s; white-space:nowrap; }
.btn-view-active:hover { background:#16a34a; color:#fff; }
.mls-data-note { background:#e8f5ee; color:#1a7a4a; font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px; margin-left:6px; text-transform:uppercase; letter-spacing:.5px; }
.filter-active-dot { display:inline-block; width:7px; height:7px; background:#22c55e; border-radius:50%; margin-left:5px; vertical-align:middle; }

/* ── Hide theme compare/saved widget ─────────────────────────────── */
.compare-property-wrap, .compare-property-slide, .cp-slide-wrap,
.slide-property-wrap, .compare-wrap, #compare-sidebar,
.compare-list-wrap, .property-compare-wrap { display:none !important; }

/* ── Notify / Share buttons ──────────────────────────────────────── */
.card-notify-btn { display:flex;align-items:center;gap:6px;background:#002446;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:700;padding:6px 12px;cursor:pointer;transition:background .2s;text-transform:uppercase;letter-spacing:.5px; }
.card-notify-btn:hover { background:#0065ff; }
.card-share-btn { display:flex;align-items:center;gap:5px;background:#f4f6fb;color:#555;border:none;border-radius:6px;font-size:11px;font-weight:600;padding:6px 10px;cursor:pointer;transition:background .2s; }
.card-share-btn:hover { background:#e8ecf5; }
#wyn-notify-modal { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:99999;align-items:center;justify-content:center;padding:16px; }
#wyn-notify-modal.open { display:flex; }
.wyn-notify-box { background:#fff;border-radius:14px;max-width:420px;width:100%;padding:36px 32px;box-shadow:0 24px 64px rgba(0,0,0,.2); }
.wyn-notify-box h4 { font-size:18px;font-weight:800;color:#002446;margin-bottom:8px; }
.wyn-notify-box p  { font-size:13px;color:#666;margin-bottom:20px;line-height:1.6; }
.wyn-notify-input { width:100%;padding:11px 14px;border:1.5px solid #dde;border-radius:8px;font-size:14px;margin-bottom:10px;outline:none; }
.wyn-notify-input:focus { border-color:#002446; }
.wyn-notify-submit { width:100%;padding:13px;background:#002446;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;transition:background .2s; }
.wyn-notify-submit:hover { background:#0065ff; }
.wyn-notify-close { background:none;border:none;font-size:13px;color:#aaa;cursor:pointer;margin-top:12px;width:100%;text-align:center; }
.wyn-notify-success { text-align:center;padding:10px 0; }
.wyn-notify-success i { font-size:40px;color:#16a34a;margin-bottom:12px;display:block; }
@media (max-width:768px) {
    .home-map-banner.half-map { flex-direction:column !important; height:auto !important; }
    .fs-left-map-box  { flex:none !important; width:100% !important; height:50vh !important; }
    .fs-inner-container { flex:none !important; width:100% !important; height:auto !important; }
}
</style>


<!-- Notify Me Modal -->
<div id="wyn-notify-modal">
    <div class="wyn-notify-box">
        <div id="wyn-notify-form-wrap">
            <h4><i class="fas fa-bell me-2" style="color:#c9a84c;"></i>Get Notified About This Property</h4>
            <p id="wyn-notify-desc">Enter your email and we will notify you of any updates — price changes, status updates, or when similar properties become available.</p>
            <input type="email" id="wyn-notify-email" class="wyn-notify-input" placeholder="your@email.com">
            <input type="text" id="wyn-notify-name" class="wyn-notify-input" placeholder="Your name (optional)">
            <input type="hidden" id="wyn-notify-pid" value="">
            <button class="wyn-notify-submit" onclick="wynSubmitNotify()"><i class="fas fa-bell me-2"></i>Notify Me</button>
            <button class="wyn-notify-close" onclick="wynCloseNotify()">No thanks</button>
        </div>
        <div id="wyn-notify-success-wrap" class="wyn-notify-success" style="display:none;">
            <i class="fas fa-check-circle"></i>
            <h4 style="color:#002446;">You are on the list!</h4>
            <p style="color:#666;font-size:13px;">We will email you when there are updates to this property.</p>
            <button class="wyn-notify-close" onclick="wynCloseNotify()" style="color:#002446;font-weight:700;">Close</button>
        </div>
    </div>
</div>
<script>
function wynOpenNotify(pid, address) {
    document.getElementById('wyn-notify-pid').value = pid;
    document.getElementById('wyn-notify-desc').textContent = 'Get updates on ' + address + ' — price changes, status updates, or when similar listings become available.';
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
    if (!email || !email.includes('@')) { document.getElementById('wyn-notify-email').style.borderColor = '#e00'; return; }
    document.getElementById('wyn-notify-email').style.borderColor = '#dde';
    fetch('notify-subscribe.php', {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'email='+encodeURIComponent(email)+'&name='+encodeURIComponent(name)+'&property_id='+encodeURIComponent(pid)+'&source=active-listings'
    }).then(function(){
        document.getElementById('wyn-notify-form-wrap').style.display='none';
        document.getElementById('wyn-notify-success-wrap').style.display='block';
    }).catch(function(){
        document.getElementById('wyn-notify-form-wrap').style.display='none';
        document.getElementById('wyn-notify-success-wrap').style.display='block';
    });
}
function wynShare(address, url) {
    var fullUrl = window.location.origin + '/' + url;
    if (navigator.share) { navigator.share({title: address + ' — Wynston', url: fullUrl}); }
    else { navigator.clipboard.writeText(fullUrl).then(function(){ alert('Link copied!'); }); }
}
document.getElementById('wyn-notify-modal').addEventListener('click', function(e){ if(e.target===this) wynCloseNotify(); });
</script>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>