<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

// ── Neighbourhood card photos — /assets/img/port-moody/ ──────────────────────
$nb_photos = [
    'belcarra'              => '/assets/img/port-moody/Belcarra.webp',
    'ioco'                  => '/assets/img/port-moody/Ioco.webp',
    'anmore'                => '/assets/img/port-moody/Anmore.webp',
    'heritage-woods-pm'     => '/assets/img/port-moody/Heritage Woods.webp',
    'barber-street-pm'      => '/assets/img/port-moody/Barber Street.webp',
    'heritage-mountain-pm'  => '/assets/img/port-moody/Heritage Mountain.webp',
    'mountain-meadows-pm'   => '/assets/img/port-moody/Mountain Meadows.webp',
    'north-shore-pm'        => '/assets/img/port-moody/North Shore Port Moody.webp',
    'port-moody-centre'     => '/assets/img/port-moody/Port Moody Centre.webp',
    'glenayre-pm'           => '/assets/img/port-moody/Glenayre.webp',
    'college-park-pm'       => '/assets/img/port-moody/College Park.webp',
];

// ── GeoJSON NAME → DB slug(s) ────────────────────────────────────────────
$geoMap = [
    'Belcarra'               => ['belcarra'],
    'Ioco'                   => ['ioco'],
    'Anmore'                 => ['anmore'],
    'Heritage Woods'         => ['heritage-woods-pm'],
    'Barber Street'          => ['barber-street-pm'],
    'Heritage Mountain'      => ['heritage-mountain-pm'],
    'Mountain Meadows'       => ['mountain-meadows-pm'],
    'North Shore Port Moody' => ['north-shore-pm'],
    'Port Moody Centre'      => ['port-moody-centre'],
    'Glenayre'               => ['glenayre-pm'],
    'College Park'           => ['college-park-pm'],
];

$nbs = [];
try {
    $q = $pdo->prepare("
        SELECT nb.*,
            (SELECT COUNT(*) FROM multi_2025 WHERE neighborhood=nb.db_neighborhood) as cs_count,
            (SELECT COUNT(*) FROM ddf_listings WHERE status='Active'
               AND latitude  BETWEEN nb.lat_min AND nb.lat_max
               AND longitude BETWEEN nb.lng_min AND nb.lng_max) as active_count
        FROM neighbourhoods nb
        WHERE nb.area='Port Moody' AND nb.is_active=1
        ORDER BY nb.sort_order ASC, nb.name ASC
    ");
    $q->execute();
    $nbs = $q->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {}

$visible = array_slice($nbs, 0, 4);
$hidden  = array_slice($nbs, 4);
$total   = count($nbs);


ob_start(); include "$base_dir/navbar2.php"; $navlink_content=ob_get_clean();
$page='nav2'; $fpage='foot'; ob_start();
?>

<!-- HERO -->
<div class="nbcity-hero" style="background-image:url('/assets/img/port-moody/port-moody-hero.webp');">
    <div class="nbcity-hero-overlay"></div>
    <div class="container h-100 position-relative" style="z-index:2;">
        <div class="row h-100 align-items-center">
            <div class="col-lg-7">
                <a href="neighbourhoods.php" class="nbcity-back"><i class="fas fa-arrow-left me-2"></i>All Cities</a>
                <h1 class="nbcity-title">Port Moody</h1>
                <p class="nbcity-subtitle"><?= $total ?> Neighbourhoods &nbsp;&middot;&nbsp; Metro Vancouver</p>
            </div>
        </div>
    </div>
</div>

<!-- MAP + DESCRIPTION — split layout -->
<section style="background:#fff;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:stretch;min-height:480px;">
        <div style="
            width:calc((100vw - min(100vw, 1320px)) / 2 + min(100vw, 1320px) * 0.4);
            flex-shrink:0;
            padding: 60px calc((100vw - min(100vw, 1320px)) / 2 + 12px) 60px calc((100vw - min(100vw, 1320px)) / 2 + 12px);
            display:flex;flex-direction:column;justify-content:center;
            border-right:1px solid #eaeef5;
        ">
            <div class="nbcity-label">About Port Moody</div>
            <p class="nbcity-intro-body">Port Moody is Metro Vancouver’s most scenic urban escape — a compact city where forested mountains meet tidewater at the head of Burrard Inlet. From the heritage character of Port Moody Centre to the family enclaves of Heritage Mountain and Heritage Woods, it offers rare outdoor access with SkyTrain connectivity.</p>
            <p class="nbcity-intro-body">Wynston tracks all <?= $total ?> MLS sub-areas with real monthly market data, school catchments, and coming-soon development activity.</p>
            <a href="neighbourhoods.php" style="font-size:13px;color:#0065ff;font-weight:700;text-decoration:none;margin-top:8px;"><i class="fas fa-th-large me-1"></i>Browse all cities</a>
            <div style="margin-top:auto;padding-top:24px;font-size:11px;color:#aaa;"><i class="fas fa-mouse-pointer me-1" style="color:#0065ff;"></i>Click map to enable scroll zoom &nbsp;&middot;&nbsp; Click a neighbourhood or another city to explore</div>
        </div>
        <div style="flex:1;position:relative;min-height:480px;">
            <div id="nbcity-map" style="position:absolute;inset:0;width:100%;height:100%;"></div>
        </div>
    </div>
</section>

<!-- NEIGHBOURHOOD CARDS -->
<section style="background:#f8f9fc;padding:60px 0 90px;">
    <div class="container">
        <div class="row mb-3 align-items-center">
            <div class="col"><div class="nbcity-area-label">Port Moody Neighbourhoods</div></div>
            <div class="col-auto"><span style="font-size:12px;color:#aaa;font-weight:600;"><?= $total ?> neighbourhoods</span></div>
        </div>

        <?php if (!empty($nbs)): ?>
        <div class="row g-3 mb-2" id="pm-visible">
            <?php foreach ($visible as $nb):
                $slug  = $nb['slug'];
                $photo = $nb_photos[$slug] ?? '';
                $cnt   = (int)$nb['cs_count'] + (int)$nb['active_count'];
                $link  = 'neighbourhood.php?slug=' . urlencode($slug) . '&hero=' . urlencode($photo);
            ?>
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                <a href="<?= $link ?>" class="nbcity-nb-card">
                    <div class="nbcity-nb-img" style="background-image:url('<?= str_replace(' ', '%20', htmlspecialchars($photo)) ?>')"></div>
                    <div class="nbcity-nb-overlay"></div>
                    <div class="nbcity-nb-body">
                        <?php if ($cnt > 0): ?><div class="nbcity-nb-count"><?= $cnt ?> <?= $cnt===1?'Listing':'Listings' ?></div><?php endif; ?>
                        <h3 class="nbcity-nb-name"><?= htmlspecialchars($nb['name']) ?></h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($hidden)): ?>
        <div class="row g-3 mb-2" id="pm-hidden" style="display:none;">
            <?php foreach ($hidden as $nb):
                $slug  = $nb['slug'];
                $photo = $nb_photos[$slug] ?? '';
                $cnt   = (int)$nb['cs_count'] + (int)$nb['active_count'];
                $link  = 'neighbourhood.php?slug=' . urlencode($slug) . '&hero=' . urlencode($photo);
            ?>
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                <a href="<?= $link ?>" class="nbcity-nb-card">
                    <div class="nbcity-nb-img" style="background-image:url('<?= str_replace(' ', '%20', htmlspecialchars($photo)) ?>')"></div>
                    <div class="nbcity-nb-overlay"></div>
                    <div class="nbcity-nb-body">
                        <?php if ($cnt > 0): ?><div class="nbcity-nb-count"><?= $cnt ?> <?= $cnt===1?'Listing':'Listings' ?></div><?php endif; ?>
                        <h3 class="nbcity-nb-name"><?= htmlspecialchars($nb['name']) ?></h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-2">
            <button class="nbcity-expand-btn" onclick="toggleSection('pm-hidden','pm-btn',<?= $total ?>)" id="pm-btn">
                <i class="fas fa-th me-2"></i>Show all <?= $total ?> neighbourhoods
            </button>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="text-center py-5" style="color:#aaa;">
            <i class="fas fa-map-marker-alt" style="font-size:40px;display:block;margin-bottom:16px;opacity:.25;"></i>
            <p>Neighbourhood data coming soon.</p>
            <a href="neighbourhoods.php" class="btn btn-primary mt-2">Browse Other Cities</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="bg-primary call-to-act-wrap">
    <div class="container"><?php include "$base_dir/Components/Home/estate-agent.php"; ?></div>
</section>

<style>
.nbcity-hero{position:relative;height:580px;display:flex;align-items:center;background-size:cover;background-position:center 40%;}
.nbcity-hero-overlay{position:absolute;inset:0;background:linear-gradient(160deg,rgba(0,15,40,.00) 0%,rgba(0,40,90,.05) 100%);}
.nbcity-back{display:inline-flex;align-items:center;font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-decoration:none;margin-bottom:16px;letter-spacing:.3px;transition:color .2s;}
.nbcity-back:hover{color:#fff;}
.nbcity-title{font-size:clamp(42px,7vw,80px);font-weight:900;color:#fff;line-height:1.0;margin:0 0 12px;letter-spacing:-2px;}
.nbcity-subtitle{font-size:14px;color:rgba(255,255,255,.55);font-weight:600;letter-spacing:.8px;text-transform:uppercase;margin:0 0 18px;}
.nbcity-label{font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#0065ff;margin-bottom:14px;}
.nbcity-intro-body{font-size:14px;color:#555;line-height:1.85;margin-bottom:12px;}
.nbcity-area-label{font-size:11px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:#002446;padding-bottom:8px;border-bottom:3px solid #002446;display:inline-block;}
.nbcity-nb-card{display:block;position:relative;border-radius:12px;overflow:hidden;text-decoration:none;height:190px;transition:transform .3s,box-shadow .3s;}
.nbcity-nb-card:hover{transform:translateY(-5px);box-shadow:0 20px 50px rgba(0,0,0,.22);}
.nbcity-nb-img{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .5s;background-color:#1a2f4a;}
.nbcity-nb-card:hover .nbcity-nb-img{transform:scale(1.08);}
.nbcity-nb-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,12,30,.88) 0%,rgba(0,12,30,.20) 55%,transparent 100%);}
.nbcity-nb-body{position:absolute;inset:0;padding:14px;display:flex;flex-direction:column;justify-content:flex-end;z-index:2;}
.nbcity-nb-count{display:inline-block;background:rgba(0,101,255,.85);color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:14px;margin-bottom:6px;width:fit-content;letter-spacing:.3px;}
.nbcity-nb-name{font-size:15px;font-weight:800;color:#fff;margin:0;line-height:1.25;}
.nbcity-expand-btn{background:transparent;border:2px solid #002446;color:#002446;border-radius:30px;padding:10px 28px;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;}
.nbcity-expand-btn:hover{background:#002446;color:#fff;}
.nb-label-tip{background:transparent!important;border:none!important;box-shadow:none!important;padding:0!important;font-family:system-ui,sans-serif!important;font-size:10px!important;font-weight:700!important;color:#fff!important;white-space:nowrap!important;text-shadow:0 1px 3px rgba(0,0,0,.8),0 0 6px rgba(0,0,0,.5)!important;pointer-events:none!important;text-align:center!important;line-height:1.2!important;}
.nb-label-tip::before{display:none!important;}
.nb-geo-popup .leaflet-popup-content-wrapper{background:#fff!important;border-radius:12px!important;box-shadow:0 16px 40px rgba(0,0,0,.2)!important;padding:0!important;}
.nb-geo-popup .leaflet-popup-content{margin:20px 22px!important;}
.nb-geo-popup .leaflet-popup-tip{background:#fff!important;}
.nb-city-tip{background:transparent!important;border:none!important;box-shadow:none!important;padding:0!important;font-family:system-ui,sans-serif!important;font-size:11px!important;font-weight:800!important;color:#002446!important;white-space:nowrap!important;text-shadow:0 1px 2px rgba(255,255,255,.9),0 0 6px rgba(255,255,255,.7)!important;pointer-events:none!important;text-align:center!important;line-height:1.2!important;}
.nb-city-tip::before{display:none!important;}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@3.0.2/Control.FullScreen.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@3.0.2/Control.FullScreen.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('nbcity-map', {
        center:[49.2831, -122.8515],
        zoom:11,
        scrollWheelZoom:false,
        attributionControl:false
    });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {maxZoom:19}).addTo(map);
    setTimeout(function(){ map.invalidateSize(); }, 100);

    map.on('click', function() { map.scrollWheelZoom.enable(); });
    map.on('mouseout', function() { map.scrollWheelZoom.disable(); });
    L.control.fullscreen({ position: 'topright', title: 'Expand map', titleCancel: 'Exit fullscreen' }).addTo(map);

    // ── Styles ────────────────────────────────────────────────────────────────────────────
    var defStyle  = {fillColor:'#0065ff', fillOpacity:.35, color:'#fff', weight:2};
    var hovStyle  = {fillColor:'#0052cc', fillOpacity:.65, color:'#fff', weight:2.5};
    var actStyle  = {fillColor:'#002446', fillOpacity:.85, color:'#fff', weight:2.5};
    var cityDef   = {fillColor:'#c8d8ee', fillOpacity:.45, color:'#fff', weight:1.5};
    var cityHov   = {fillColor:'#7baad8', fillOpacity:.65, color:'#fff', weight:2};
    var cityAct   = {fillColor:'#3a7fc1', fillOpacity:.80, color:'#fff', weight:2.5};
    var active = null, activeCity = null;

    // ── Other city config ────────────────────────────────────────────────────────
    var otherCities = {
        'vancouver':      { label:'Vancouver',       slug:'neighbourhoods-vancouver.php' },
        'burnaby':        { label:'Burnaby',          slug:'neighbourhoods-burnaby.php' },
        'northvan':       { label:'North Vancouver',  slug:'neighbourhoods-northvan.php' },
        'richmond':       { label:'Richmond',         slug:'neighbourhoods-richmond.php' },
        'westvancouver':  { label:'West Vancouver',   slug:'neighbourhoods-westvancouver.php' },
        'newwestminster': { label:'New Westminster',  slug:'neighbourhoods-newwestminster.php' },
        'coquitlam':      { label:'Coquitlam',        slug:'neighbourhoods-coquitlam.php' },
        'portcoquitlam':  { label:'Port Coquitlam',   slug:'neighbourhoods-portcoquitlam.php' },
    };

    function makeCityPopup(cityKey) {
        var cfg = otherCities[cityKey];
        return '<div style="font-family:system-ui;min-width:200px;">'
            + '<div style="font-size:10px;color:#0065ff;font-weight:700;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Metro Vancouver</div>'
            + '<div style="font-size:17px;font-weight:900;color:#002446;margin-bottom:12px;letter-spacing:-.3px;">' + cfg.label + '</div>'
            + '<a href="' + cfg.slug + '" style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-top:1px solid #f0f4ff;text-decoration:none;gap:8px;">'
            + '<span style="font-size:13px;font-weight:700;color:#002446;">Explore ' + cfg.label + '</span>'
            + '<span style="font-size:11px;color:#aaa;">→</span>'
            + '</a></div>';
    }

    // ── Port Moody neighbourhood data ────────────────────────────────────────────
    var bySlug = {};
    <?php foreach ($nbs as $nb):
        $photo = $nb_photos[$nb['slug']] ?? ''; ?>
    bySlug[<?= json_encode($nb['slug']) ?>] = {name:<?= json_encode($nb['name']) ?>, slug:<?= json_encode($nb['slug']) ?>, photo:<?= json_encode($photo) ?>, total:<?= (int)$nb['cs_count'] + (int)$nb['active_count'] ?>};
    <?php endforeach; ?>

    var geoSlugMap = <?= json_encode($geoMap) ?>;

    function makePopup(geoName, slugs) {
        var items = [], seen = {};
        slugs.forEach(function(s) { if (seen[s] || !bySlug[s]) return; seen[s]=true; items.push(bySlug[s]); });
        var rows = items.map(function(n) {
            var link = 'neighbourhood.php?slug=' + encodeURIComponent(n.slug) + '&hero=' + encodeURIComponent(n.photo);
            return '<a href="'+link+'" style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f0f4ff;text-decoration:none;gap:8px;">'
                +'<span style="font-size:13px;font-weight:700;color:#002446;">'+n.name+'</span>'
                +(n.total>0?'<span style="flex-shrink:0;font-size:10px;background:#0065ff;color:#fff;border-radius:10px;padding:2px 9px;font-weight:700;">'+n.total+'</span>':'<span style="font-size:11px;color:#aaa;">→</span>')
                +'</a>';
        }).join('');
        return '<div style="font-family:system-ui;min-width:210px;">'
            +'<div style="font-size:10px;color:#0065ff;font-weight:700;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Port Moody</div>'
            +'<div style="font-size:17px;font-weight:900;color:#002446;margin-bottom:12px;letter-spacing:-.3px;">'+geoName+'</div>'
            +(rows||'<p style="font-size:12px;color:#aaa;padding:8px 0;margin:0;">Coming soon</p>')
            +'</div>';
    }

    // ── Step 1: Load metro-vancouver.geojson for other cities (bottom layer) ──
    fetch('metro-vancouver.geojson')
        .then(function(r){ return r.json(); })
        .then(function(data) {
            var byCity = {};
            data.features.forEach(function(f) {
                var city = f.properties.CITY;
                if (!city || city === 'portmoody' || !otherCities[city]) return;
                if (!byCity[city]) byCity[city] = [];
                byCity[city].push(f);
            });
            Object.keys(byCity).forEach(function(cityKey) {
                var layer = L.geoJSON(
                    { type:'FeatureCollection', features: byCity[cityKey] },
                    { style: function() { return Object.assign({}, cityDef); } }
                );
                var bounds = layer.getBounds();
                if (bounds.isValid()) {
                    L.tooltip({ permanent:true, direction:'center', className:'nb-city-tip', interactive:false, opacity:1 })
                     .setContent(otherCities[cityKey].label).setLatLng(bounds.getCenter()).addTo(map);
                }
                layer.on('mouseover', function() { if (layer !== activeCity) layer.setStyle(cityHov); });
                layer.on('mouseout',  function() { if (layer !== activeCity) layer.setStyle(cityDef); });
                layer.on('click', function(e) {
                    if (activeCity && activeCity !== layer) activeCity.setStyle(cityDef);
                    layer.setStyle(cityAct); activeCity = layer;
                    L.popup({maxWidth:260, minWidth:220, closeButton:true, className:'nb-geo-popup'})
                     .setLatLng(e.latlng).setContent(makeCityPopup(cityKey)).openOn(map);
                });
                layer.addTo(map);
            });

            // ── Step 2: Load Port Moody neighbourhoods on top ────────────────────────────
            return fetch('portmoody-boundaries.geojson');
        })
        .then(function(r){ return r.json(); })
        .then(function(geojsonData) {
            L.geoJSON(geojsonData, {
                style: function() { return Object.assign({}, defStyle); },
                onEachFeature: function(feature, layer) {
                    var props   = feature.properties || {};
                    var geoName = props.NAME || props.Name || props.NEIGHBOURHOOD || props.Neighbourhood || props.LABEL || '';
                    var slugs   = geoSlugMap[geoName] || [];
                    layer.bindTooltip(geoName, {permanent:true, direction:'center', className:'nb-label-tip', interactive:false});
                    layer.on({
                        mouseover: function() { if (layer !== active) layer.setStyle(hovStyle); layer.bringToFront(); },
                        mouseout:  function() { if (layer !== active) layer.setStyle(defStyle); },
                        click: function(e) {
                            if (activeCity) { activeCity.setStyle(cityDef); activeCity = null; }
                            if (active && active !== layer) active.setStyle(defStyle);
                            layer.setStyle(actStyle); active = layer;
                            L.popup({maxWidth:270, minWidth:230, closeButton:true, className:'nb-geo-popup'})
                             .setLatLng(e.latlng).setContent(makePopup(geoName, slugs)).openOn(map);
                        }
                    });
                }
            }).addTo(map);
        });
});

function toggleSection(hiddenId, btnId, total) {
    var h = document.getElementById(hiddenId);
    var b = document.getElementById(btnId);
    var showing = h.style.display !== 'none';
    h.style.display = showing ? 'none' : '';
    b.innerHTML = showing
        ? '<i class="fas fa-th me-2"></i>Show all '+total+' neighbourhoods'
        : '<i class="fas fa-chevron-up me-2"></i>Show fewer';
}
</script>
<?php $hero_content=ob_get_clean(); include "$base_dir/style/base.php"; ?>