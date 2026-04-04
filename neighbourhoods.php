<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

$cities = [
    'vancouver'      => ['label'=>'Vancouver',       'slug'=>'neighbourhoods-vancouver.php',     'area_match'=>['Vancouver East','Vancouver West'], 'photo'=>'https://images.unsplash.com/photo-1559511260-66a654ae982a?w=800&q=80',  'desc'=>'38 neighbourhoods across East & West Vancouver'],
    'burnaby'        => ['label'=>'Burnaby',          'slug'=>'neighbourhoods-burnaby.php',       'area_match'=>['Burnaby'],                         'photo'=>'https://images.unsplash.com/photo-1609825488888-3a766db05542?w=800&q=80', 'desc'=>'30 neighbourhoods across North, South & East Burnaby'],
    'richmond'       => ['label'=>'Richmond',         'slug'=>'neighbourhoods-richmond.php',      'area_match'=>['Richmond'],                        'photo'=>'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=800&q=80', 'desc'=>'22 neighbourhoods from Steveston to Brighouse'],
    'northvan'       => ['label'=>'North Vancouver',  'slug'=>'neighbourhoods-northvan.php',      'area_match'=>['City of North Vancouver','District of North Vancouver'], 'photo'=>'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=800&q=80', 'desc'=>'25 neighbourhoods on the North Shore'],
    'westvancouver'  => ['label'=>'West Vancouver',   'slug'=>'neighbourhoods-westvancouver.php', 'area_match'=>['West Vancouver'],                  'photo'=>'https://images.unsplash.com/photo-1567521464027-f127ff144326?w=800&q=80', 'desc'=>'38 neighbourhoods from Ambleside to Horseshoe Bay'],
    'newwestminster' => ['label'=>'New Westminster',  'slug'=>'neighbourhoods-newwestminster.php','area_match'=>['New Westminster'],                 'photo'=>'https://images.unsplash.com/photo-1580983559367-0dc2f8934365?w=800&q=80', 'desc'=>'14 neighbourhoods from Queens Park to Queensborough'],
    'portmoody'      => ['label'=>'Port Moody',       'slug'=>'neighbourhoods-portmoody.php',     'area_match'=>['Port Moody'],                      'photo'=>'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=800&q=80', 'desc'=>'11 neighbourhoods from Port Moody Centre to Belcarra'],
    'coquitlam'      => ['label'=>'Coquitlam',        'slug'=>'neighbourhoods-coquitlam.php',     'area_match'=>['Coquitlam'],                       'photo'=>'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=800&q=80', 'desc'=>'24 neighbourhoods from Maillardville to Burke Mountain'],
    'portcoquitlam'  => ['label'=>'Port Coquitlam',   'slug'=>'neighbourhoods-portcoquitlam.php', 'area_match'=>['Port Coquitlam'],                  'photo'=>'https://images.unsplash.com/photo-1501426026826-31c667bdf23d?w=800&q=80', 'desc'=>'10 neighbourhoods from Citadel to Riverwood'],
];

$city_counts = [];
foreach ($cities as $key => $c) {
    $ph = implode(',', array_fill(0, count($c['area_match']), '?'));
    try {
        $q = $pdo->prepare("SELECT COUNT(*) FROM neighbourhoods WHERE area IN ($ph) AND is_active=1");
        $q->execute($c['area_match']);
        $city_counts[$key] = (int)$q->fetchColumn();
    } catch(Exception $e){ $city_counts[$key] = 0; }
}

$use_fallback = false;

ob_start(); include "$base_dir/navbar.php"; $navlink_content=ob_get_clean();
$page='nav2'; $fpage='foot'; ob_start();
?>

<!-- ── HERO ── -->
<div class="nb-hub-hero">
    <div class="nb-hub-hero-overlay"></div>
    <div class="container h-100 position-relative" style="z-index:2;">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="nb-hub-eyebrow">Metro Vancouver Real Estate</div>
                <h1 class="nb-hub-title">Find Your <em>Perfect</em><br>Neighbourhood</h1>
                <p class="nb-hub-subtitle">From tree-lined Dunbar to waterfront Steveston — explore 137 neighbourhoods across Metro Vancouver with real market data, school catchments, and local insights curated by Wynston.</p>
                <div class="nb-hub-cities-quick">
                    <?php foreach($cities as $key=>$c): ?>
                    <a href="<?= $c['slug'] ?>" class="nb-hub-city-pill"><?= $c['label'] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── INTRO + MAP — split layout, map bleeds to right edge ── -->
<section style="background:#fff;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:stretch;min-height:540px;">

        <!-- Left: description anchored to container -->
        <div style="
            width:calc((100vw - min(100vw, 1320px)) / 2 + min(100vw, 1320px) * 0.38);
            flex-shrink:0;
            padding:60px calc((100vw - min(100vw, 1320px)) / 2 + 12px) 60px calc((100vw - min(100vw, 1320px)) / 2 + 12px);
            display:flex;flex-direction:column;justify-content:center;
            border-right:1px solid #eaeef5;
        ">
            <div class="nb-hub-intro-label">Your Neighbourhood Guide</div>
            <h2 class="nb-hub-intro-title">Every Neighbourhood.<br>Every Detail.</h2>
            <p class="nb-hub-intro-body">Wynston covers Metro Vancouver like no other platform — with monthly market data, school catchment maps, walkability scores, community events, and coming-soon listings for every neighbourhood we track.</p>
            <p class="nb-hub-intro-body">Whether you're comparing Kitsilano to Point Grey or deciding between Brentwood and Metrotown, our neighbourhood guides give you the data to make a confident decision.</p>
            <div class="nb-hub-stats-row">
                <div class="nb-hub-stat"><strong>182</strong><span>Neighbourhoods</span></div>
                <div class="nb-hub-stat"><strong>9</strong><span>Cities</span></div>
                <div class="nb-hub-stat"><strong>Monthly</strong><span>Market Data</span></div>
            </div>
            <div style="margin-top:auto;padding-top:24px;font-size:11px;color:#aaa;">
                <i class="fas fa-mouse-pointer me-1" style="color:#0065ff;"></i>Click a city on the map or a card below to explore
            </div>
        </div>

        <!-- Right: map bleeds to browser right edge -->
        <div style="flex:1;position:relative;min-height:540px;">
            <div id="nb-hub-map" style="position:absolute;inset:0;width:100%;height:100%;"></div>
        </div>

    </div>
</section>

<!-- ── CITY CARDS ── -->
<section style="background:#f8f9fc;padding:60px 0 90px;">
    <div class="container">
        <div class="row mb-5">
            <div class="col text-center">
                <div class="nb-hub-intro-label">Browse by City</div>
                <h2 class="nb-hub-section-title">Where Do You Want to Live?</h2>
                <p style="color:#888;font-size:15px;max-width:520px;margin:10px auto 0;">Select a city to explore its neighbourhoods, market trends, and listings.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php
            // City key → folder name for hero image path
            $city_img_folder = [
                'vancouver'      => 'vancouver',
                'burnaby'        => 'burnaby',
                'northvan'       => 'north-vancouver',
                'richmond'       => 'richmond',
                'westvancouver'  => 'west-vancouver',
                'newwestminster' => 'new-westminster',
                'portmoody'      => 'port-moody',
                'coquitlam'      => 'coquitlam',
                'portcoquitlam'  => 'port-coquitlam',
            ];
            foreach($cities as $key=>$c):
                $folder = $city_img_folder[$key] ?? $key;
                $hero_photo = '/assets/img/' . $folder . '/' . $folder . '-hero.webp';
            ?>
            <div class="col-lg-4 col-md-6">
                <a href="<?= $c['slug'] ?>" class="nb-city-card">
                    <div class="nb-city-card-img" style="background-image:url('<?= $hero_photo ?>')"></div>
                    <div class="nb-city-card-overlay"></div>
                    <div class="nb-city-card-body">
                        <div class="nb-city-card-count"><?= $city_counts[$key] ?: '' ?> Neighbourhoods</div>
                        <h3 class="nb-city-card-name"><?= $c['label'] ?></h3>
                        <p class="nb-city-card-desc"><?= $c['desc'] ?></p>
                        <div class="nb-city-card-cta">Explore <i class="fas fa-arrow-right ms-2"></i></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-primary call-to-act-wrap">
    <div class="container"><?php include "$base_dir/Components/Home/estate-agent.php"; ?></div>
</section>

<style>
.nb-hub-hero{position:relative;height:640px;display:flex;align-items:center;background:url('/assets/img/bc-hero.webp') no-repeat center 60%/cover;}
.nb-hub-hero-overlay{position:absolute;inset:0;background:linear-gradient(160deg,rgba(0,15,40,.2) 0%,rgba(0,40,90,.2) 100%);}
.nb-hub-eyebrow{font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:18px;}
.nb-hub-title{font-size:clamp(38px,5.5vw,68px);font-weight:900;color:#fff;line-height:1.08;margin-bottom:22px;letter-spacing:-1.5px;}
.nb-hub-title em{font-style:italic;color:#5ab4ff;}
.nb-hub-subtitle{font-size:16px;color:rgba(255,255,255,.72);max-width:680px;margin:0 auto 34px;line-height:1.75;}
.nb-hub-cities-quick{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;}
.nb-hub-city-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);color:#fff;border-radius:30px;padding:9px 22px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;backdrop-filter:blur(6px);}
.nb-hub-city-pill:hover{background:#0065ff;border-color:#0065ff;color:#fff;}
.nb-hub-intro-label{font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#0065ff;margin-bottom:10px;}
.nb-hub-intro-title{font-size:clamp(24px,3vw,38px);font-weight:900;color:#002446;line-height:1.15;margin-bottom:16px;letter-spacing:-.5px;}
.nb-hub-intro-body{font-size:14px;color:#555;line-height:1.8;margin-bottom:14px;}
.nb-hub-stats-row{display:flex;gap:28px;margin-top:24px;padding-top:24px;border-top:2px solid #f0f4ff;}
.nb-hub-stat strong{display:block;font-size:26px;font-weight:900;color:#002446;letter-spacing:-1px;}
.nb-hub-stat span{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#aaa;margin-top:3px;}
.nb-hub-section-title{font-size:clamp(26px,3vw,40px);font-weight:900;color:#002446;letter-spacing:-.5px;margin-top:6px;}
.nb-city-card{display:block;position:relative;border-radius:16px;overflow:hidden;text-decoration:none;height:350px;transition:transform .35s,box-shadow .35s;}
.nb-city-card:hover{transform:translateY(-7px);box-shadow:0 28px 70px rgba(0,0,0,.25);}
.nb-city-card-img{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .55s;}
.nb-city-card:hover .nb-city-card-img{transform:scale(1.07);}
.nb-city-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,10,30,.90) 0%,rgba(0,10,30,.30) 55%,transparent 100%);}
.nb-city-card-body{position:absolute;inset:0;padding:24px;display:flex;flex-direction:column;justify-content:flex-end;z-index:2;}
.nb-city-card-count{display:inline-block;background:rgba(0,101,255,.8);color:#fff;font-size:10px;font-weight:700;padding:4px 12px;border-radius:20px;margin-bottom:10px;width:fit-content;letter-spacing:.3px;}
.nb-city-card-name{font-size:27px;font-weight:900;color:#fff;margin:0 0 7px;letter-spacing:-.3px;}
.nb-city-card-desc{font-size:12px;color:rgba(255,255,255,.62);margin:0 0 14px;line-height:1.55;}
.nb-city-card-cta{font-size:13px;font-weight:700;color:#5ab4ff;}
.nb-city-popup .leaflet-popup-content-wrapper{background:#fff!important;border-radius:12px!important;box-shadow:0 16px 40px rgba(0,0,0,.2)!important;padding:0!important;overflow:hidden!important;}
.nb-city-popup .leaflet-popup-content{margin:0!important;padding:20px 22px!important;min-width:220px;}
.nb-city-popup .leaflet-popup-tip{background:#fff!important;}
.nb-city-popup .leaflet-popup-close-button{color:#aaa!important;font-size:18px!important;font-weight:700!important;top:8px!important;right:10px!important;}
.nb-city-label{background:transparent!important;border:none!important;box-shadow:none!important;padding:0!important;font-family:system-ui,sans-serif!important;font-size:11px!important;font-weight:800!important;color:#fff!important;white-space:nowrap!important;text-shadow:0 1px 3px rgba(0,0,0,.9),0 0 8px rgba(0,0,0,.6)!important;pointer-events:none!important;text-align:center!important;}
.nb-city-label::before{display:none!important;}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@3.0.2/Control.FullScreen.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@3.0.2/Control.FullScreen.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    var map = L.map('nb-hub-map', {
        center: [49.22, -123.0],
        zoom: 10,
        scrollWheelZoom: false,
        attributionControl: false,
        zoomControl: true
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {maxZoom:19}).addTo(map);
    setTimeout(function(){ map.invalidateSize(); }, 100);

    // Enable scroll zoom on click, disable on mouseout — same as Richmond
    map.on('click', function() { map.scrollWheelZoom.enable(); });
    map.on('mouseout', function() { map.scrollWheelZoom.disable(); });

    // Fullscreen toggle
    L.control.fullscreen({ position: 'topright', title: 'Expand map', titleCancel: 'Exit fullscreen' }).addTo(map);

    // City config — label and link only, no individual colours
    var cityConfig = {
        'vancouver':      { label:'Vancouver',       slug:'neighbourhoods-vancouver.php' },
        'burnaby':        { label:'Burnaby',          slug:'neighbourhoods-burnaby.php' },
        'northvan':       { label:'North Vancouver',  slug:'neighbourhoods-northvan.php' },
        'richmond':       { label:'Richmond',         slug:'neighbourhoods-richmond.php' },
        'westvancouver':  { label:'West Vancouver',   slug:'neighbourhoods-westvancouver.php' },
        'newwestminster': { label:'New Westminster',  slug:'neighbourhoods-newwestminster.php' },
        'portmoody':      { label:'Port Moody',       slug:'neighbourhoods-portmoody.php' },
        'coquitlam':      { label:'Coquitlam',        slug:'neighbourhoods-coquitlam.php' },
        'portcoquitlam':  { label:'Port Coquitlam',   slug:'neighbourhoods-portcoquitlam.php' },
    };

    // Uniform colour scheme — light blue default, dark blue on hover/active
    var styleDefault = { fillColor:'#a8c8f0', fillOpacity:0.55, color:'#fff', weight:1.5 };
    var styleHover   = { fillColor:'#0065ff', fillOpacity:0.75, color:'#fff', weight:2 };
    var styleActive  = { fillColor:'#002446', fillOpacity:0.88, color:'#fff', weight:2.5 };

    var counts = <?= json_encode($city_counts) ?>;

    function makePopup(city) {
        var cfg = cityConfig[city];
        var count = counts[city] || 0;
        return '<div style="font-family:system-ui;">'
            + '<div style="font-size:10px;color:#0065ff;font-weight:700;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Metro Vancouver</div>'
            + '<div style="font-size:17px;font-weight:900;color:#002446;margin-bottom:12px;letter-spacing:-.3px;">' + cfg.label + '</div>'
            + '<a href="' + cfg.slug + '" style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-top:1px solid #f0f4ff;text-decoration:none;gap:8px;">'
            + '<span style="font-size:13px;font-weight:700;color:#002446;">Explore ' + cfg.label + '</span>'
            + '<span style="flex-shrink:0;font-size:10px;background:#0065ff;color:#fff;border-radius:10px;padding:2px 9px;font-weight:700;">' + count + ' areas</span>'
            + '</a>'
            + '</div>';
    }

    // Track active layer and label markers per city
    var activeLayer = null;
    var cityLayers  = {};
    var cityLabels  = {};
    var cityBounds  = {};

    fetch('metro-vancouver.geojson')
    .then(function(r){ return r.json(); })
    .then(function(data) {

        // Group features by city
        var byCity = {};
        data.features.forEach(function(f) {
            var city = f.properties.CITY;
            if (!city || !cityConfig[city]) return;
            if (!byCity[city]) byCity[city] = [];
            byCity[city].push(f);
        });

        Object.keys(byCity).forEach(function(city) {
            var cfg = cityConfig[city];
            var features = byCity[city];

            var layer = L.geoJSON({ type:'FeatureCollection', features: features }, {
                style: function() { return Object.assign({}, styleDefault); }
            });

            layer.on('mouseover', function() {
                if (layer !== activeLayer) layer.setStyle(styleHover);
            });
            layer.on('mouseout', function() {
                if (layer !== activeLayer) layer.setStyle(styleDefault);
            });
            layer.on('click', function(e) {
                if (activeLayer && activeLayer !== layer) activeLayer.setStyle(styleDefault);
                layer.setStyle(styleActive);
                activeLayer = layer;
                L.popup({ maxWidth: 240, minWidth: 220, closeButton: true, className: 'nb-city-popup' })
                 .setLatLng(e.latlng)
                 .setContent(makePopup(city))
                 .openOn(map);
            });

            layer.addTo(map);
            cityLayers[city] = layer;

            // Calculate centre of city for label
            var bounds = layer.getBounds();
            cityBounds[city] = bounds;
            var centre = bounds.getCenter();

            // Permanent label at city centre
            var label = L.tooltip({
                permanent: true,
                direction: 'center',
                className: 'nb-city-label',
                interactive: false,
                opacity: 1
            })
            .setContent(cfg.label)
            .setLatLng(centre);
            label.addTo(map);
            cityLabels[city] = label;
        });
    });
});
</script>
<?php $hero_content=ob_get_clean(); include "$base_dir/style/base.php"; ?>