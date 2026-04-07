<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

// ── Route ─────────────────────────────────────────────────────────────────────
$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) { header('Location: neighbourhoods.php'); exit; }

$n = $pdo->prepare("SELECT * FROM neighbourhoods WHERE slug = ? AND is_active = 1 LIMIT 1");
$n->execute([$slug]);
$nb = $n->fetch(PDO::FETCH_ASSOC);
if (!$nb) { header('Location: neighbourhoods.php'); exit; }

// ── Demographics (Stats Can JSON columns) ─────────────────────────────────────
$demo_gender    = !empty($nb['demo_gender'])    ? json_decode($nb['demo_gender'],    true) : null;
$demo_languages = !empty($nb['demo_languages']) ? json_decode($nb['demo_languages'], true) : null;
$demo_household = !empty($nb['demo_household']) ? json_decode($nb['demo_household'], true) : null;
$demo_age       = !empty($nb['demo_age'])       ? json_decode($nb['demo_age'],       true) : null;
$has_demo       = $demo_gender || $demo_languages || $demo_household || $demo_age;

// ── Coming Soon (multi_2025) ──────────────────────────────────────────────────
$cs_listings = [];
if (!empty($nb['db_neighborhood'])) {
    $cs = $pdo->prepare("
        SELECT id, address, neighborhood, property_type, est_completion,
               latitude, longitude, img1, is_paid, description
        FROM multi_2025 WHERE neighborhood = ?
        ORDER BY est_completion ASC, id DESC LIMIT 6
    ");
    $cs->execute([$nb['db_neighborhood']]);
    $cs_listings = $cs->fetchAll(PDO::FETCH_ASSOC);
}

// ── Active MLS (geo bounding box) ─────────────────────────────────────────────
$active_listings = [];
if (!empty($nb['lat_min']) && !empty($nb['lat_max'])) {
    $al = $pdo->prepare("
        SELECT id, address, city, property_type, building_type,
               bedrooms, bathrooms, sqft, price_formatted, mls_number,
               latitude, longitude, img1
        FROM ddf_listings
        WHERE status = 'Active'
          AND latitude  BETWEEN ? AND ?
          AND longitude BETWEEN ? AND ?
        ORDER BY price ASC LIMIT 6
    ");
    $al->execute([$nb['lat_min'], $nb['lat_max'], $nb['lng_min'], $nb['lng_max']]);
    $active_listings = $al->fetchAll(PDO::FETCH_ASSOC);
}

// ── HPI history ───────────────────────────────────────────────────────────────
$history = $pdo->prepare("
    SELECT month_year, avg_price, price_detached, price_condo, price_townhouse, price_duplex
    FROM neighbourhood_hpi_history
    WHERE neighbourhood_id = ?
    ORDER BY month_year ASC LIMIT 12
");
$history->execute([$nb['id']]);
$hpi_history = $history->fetchAll(PDO::FETCH_ASSOC);

// ── Community Events ──────────────────────────────────────────────────────────
// Map neighbourhood area to city name used in community_events table
$area_to_city = [
    'Vancouver East'    => 'Vancouver',
    'Vancouver West'    => 'Vancouver',
    'North Vancouver'   => 'North Vancouver',
    'Burnaby'           => 'Burnaby',
    'Richmond'          => 'Richmond',
    'West Vancouver'    => 'West Vancouver',
    'New Westminster'   => 'New Westminster',
    'Port Moody'        => 'Port Moody',
    'Coquitlam'         => 'Coquitlam',
    'Port Coquitlam'    => 'Port Coquitlam',
];
$nb_city = $area_to_city[$nb['area']] ?? 'Vancouver';

// City-level events link
$city_event_links = [
    'Vancouver'       => ['url'=>'https://vancouver.ca/news-calendar/calendar-of-events.aspx',       'label'=>'View all Vancouver events'],
    'North Vancouver' => ['url'=>'https://www.cnv.org/parks-recreation/events',                      'label'=>'View all North Vancouver events'],
    'Burnaby'         => ['url'=>'https://www.burnaby.ca/recreation-and-arts/events',                'label'=>'View all Burnaby events'],
    'Richmond'        => ['url'=>'https://www.richmond.ca/culture/calendar/search/Default.aspx',     'label'=>'View all Richmond events'],
    'West Vancouver'  => ['url'=>'https://westvancouver.ca/news-events/events',                      'label'=>'View all West Vancouver events'],
    'New Westminster' => ['url'=>'https://www.newwestcity.ca/culture-and-recreation/events',         'label'=>'View all New Westminster events'],
    'Port Moody'      => ['url'=>'https://www.portmoody.ca/en/recreation-and-culture/events.aspx',  'label'=>'View all Port Moody events'],
    'Coquitlam'       => ['url'=>'https://www.coquitlam.ca/475/Events',                             'label'=>'View all Coquitlam events'],
    'Port Coquitlam'  => ['url'=>'https://www.portcoquitlam.ca/culture-recreation/events',          'label'=>'View all Port Coquitlam events'],
];

$community_events = [];
$month_start = date('Y-m-01');
$month_end   = date('Y-m-t');

// 1. Manual events: neighbourhood-specific + city-wide, current month
try {
    $evq = $pdo->prepare("
        SELECT * FROM community_events
        WHERE city = ?
          AND is_active = 1
          AND event_date BETWEEN ? AND ?
          AND (neighbourhood_id IS NULL OR neighbourhood_id = ?)
        ORDER BY neighbourhood_id IS NULL ASC, event_date ASC
    ");
    $evq->execute([$nb_city, $month_start, $month_end, $nb['id']]);
    $community_events = $evq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $community_events = []; }

// 2. RSS feed for Vancouver + North Vancouver (cache 6 hours in rss_cache table)
$rss_events = [];
$rss_feeds  = [
    'Vancouver'       => 'https://www.trumba.com/calendars/city-of-vancouver-events.rss',
    'North Vancouver' => 'https://www.trumba.com/calendars/city-of-north-vancouver-community-events.rss',
];
if (isset($rss_feeds[$nb_city])) {
    $cache_key = 'rss_' . strtolower(str_replace(' ', '_', $nb_city));
    $cached    = null;
    try {
        $cq = $pdo->prepare("SELECT content, fetched_at FROM rss_cache WHERE cache_key=? LIMIT 1");
        $cq->execute([$cache_key]);
        $cached = $cq->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    $needs_refresh = !$cached || (time() - strtotime($cached['fetched_at'])) > 21600; // 6 hours

    if ($needs_refresh) {
        $xml_raw = false;

        // Try cURL first (more reliable on shared hosting)
        if (function_exists('curl_init')) {
            $ch = curl_init($rss_feeds[$nb_city]);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Wynston/1.0)',
            ]);
            $xml_raw = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code !== 200) $xml_raw = false;
        }

        // Fallback to file_get_contents
        if (!$xml_raw) {
            $xml_raw = @file_get_contents($rss_feeds[$nb_city]);
        }

        if ($xml_raw) {
            try {
                $pdo->prepare("INSERT INTO rss_cache (cache_key, content, fetched_at) VALUES (?,?,NOW())
                               ON DUPLICATE KEY UPDATE content=VALUES(content), fetched_at=NOW()")
                    ->execute([$cache_key, $xml_raw]);
                $cached = ['content' => $xml_raw];
            } catch (Exception $e) {}
        }
    }

    if (!empty($cached['content'])) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($cached['content']);
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                // Trumba stores date in <category> as "2026/03/05 (Thu)"
                $category_raw = (string)$item->category;
                $event_date   = null;

                // Parse "YYYY/MM/DD" from category field
                if (preg_match('/(\d{4})\/(\d{2})\/(\d{2})/', $category_raw, $m)) {
                    $event_date = strtotime("{$m[1]}-{$m[2]}-{$m[3]}");
                }
                // Fallback to pubDate
                if (!$event_date) {
                    $event_date = strtotime((string)$item->pubDate);
                }
                if (!$event_date) continue;

                // Only show events in current month
                if ($event_date < strtotime($month_start) || $event_date > strtotime($month_end . ' 23:59:59')) continue;

                // Extract location from description if present
                $desc_raw = (string)$item->description;
                $location = '';
                if (preg_match('/Location:\s*([^\n<]+)/i', $desc_raw, $m)) {
                    $location = trim($m[1]);
                }

                $rss_events[] = [
                    'title'        => html_entity_decode(strip_tags((string)$item->title), ENT_QUOTES, 'UTF-8'),
                    'event_date'   => date('Y-m-d', $event_date),
                    'event_time'   => date('g:ia', $event_date) !== '12:00am' ? date('g:ia', $event_date) : '',
                    'description'  => mb_strimwidth(strip_tags($desc_raw), 0, 200, '…'),
                    'url'          => (string)$item->link,
                    'location_name'=> $location,
                    'category'     => 'community',
                    'source'       => 'rss',
                    'neighbourhood_id' => null,
                ];
            }
        }
    }
}

// Merge: manual events first, then RSS, deduplicate by title+date
$all_events = $community_events;
foreach ($rss_events as $re) {
    $dup = false;
    foreach ($all_events as $ae) {
        if (strtolower($ae['title']) === strtolower($re['title']) && $ae['event_date'] === $re['event_date']) { $dup = true; break; }
    }
    if (!$dup) $all_events[] = $re;
}
usort($all_events, fn($a, $b) => strcmp($a['event_date'], $b['event_date']));

$category_icons = [
    'festival'   => 'fas fa-star',
    'market'     => 'fas fa-shopping-bag',
    'community'  => 'fas fa-users',
    'recreation' => 'fas fa-futbol',
    'arts'       => 'fas fa-palette',
    'sports'     => 'fas fa-running',
    'family'     => 'fas fa-child',
    'other'      => 'fas fa-calendar-day',
];
$has_prices = ((int)($nb['avg_price']        ?? 0) > 0)
           || ((int)($nb['hpi_benchmark']    ?? 0) > 0)
           || ((int)($nb['price_detached']   ?? 0) > 0)
           || ((int)($nb['price_condo']      ?? 0) > 0)
           || ((int)($nb['price_townhouse']  ?? 0) > 0)
           || ((int)($nb['price_duplex']     ?? 0) > 0);
$has_history = count($hpi_history) >= 1;

$chart_labels = $chart_avg = $chart_det = $chart_condo = $chart_town = $chart_dup = [];
foreach ($hpi_history as $h) {
    $chart_labels[] = date('M Y', strtotime($h['month_year']));
    $chart_avg[]    = (int)$h['avg_price'];
    $chart_det[]    = (int)$h['price_detached'];
    $chart_condo[]  = (int)$h['price_condo'];
    $chart_town[]   = (int)$h['price_townhouse'];
    $chart_dup[]    = (int)$h['price_duplex'];
}

function fmt_price($n) { return ($n && (int)$n > 0) ? '$'.number_format((int)$n) : 'N/A'; }
function fmt_chg($v) {
    if ($v === null || $v === '') return null;
    $v = (float)$v;
    return ['val'=>($v>=0?'+':'').number_format($v,1).'%', 'cls'=>$v>=0?'pos':'neg'];
}

// ── Schools — read from DB (neighbourhood_schools table) ─────────────────────
$schools = [];
try {
    $sq = $pdo->prepare("
        SELECT name, type, grades, rating, phone, url
        FROM neighbourhood_schools
        WHERE neighbourhood_id = ?
        ORDER BY sort_order ASC, type DESC, name ASC
    ");
    $sq->execute([$nb['id']]);
    $schools = $sq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $schools = []; }

// ── Nav — matches blog.php exactly ───────────────────────────────────────────
ob_start();
include "$base_dir/navbar.php";
$navlink_content = ob_get_clean();
$page  = 'nav';
$fpage = 'foot';
ob_start();
?>


<!-- ① BIG HERO (same style as neighbourhoods.php hub) -->
<!-- ① BIG HERO -->
<?php
// ── Hero image priority ───────────────────────────────────────────────────────
// 1. ?hero= param passed from card or map click (same image as card)
// 2. DB hero_image column set manually in admin
// 3. Unsplash keyword fallback

$hero_img_url = null;

// Priority 1 — photo passed from card/map click
if (!empty($_GET['hero'])) {
    $candidate = $_GET['hero'];
    if (preg_match('#^/assets/#', $candidate) || preg_match('#^https?://#', $candidate)) {
        $hero_img_url = htmlspecialchars($candidate);
    }
}

// Priority 2 — DB custom image
if (!$hero_img_url && !empty($nb['hero_image'])) {
    $hero_img_url = htmlspecialchars($nb['hero_image']);
}

// Priority 3 — Unsplash fallback
if (!$hero_img_url) {
    $city_word  = explode(' ', $nb['area'])[0];
    $nb_keyword = preg_replace('/\s+(VE|VW|RI|NV|WV|BN|BE|BS|PM|PoC)$/i', '', $nb['name']);
    $hero_img_url = 'https://source.unsplash.com/1600x520/?' . urlencode($nb_keyword . ' ' . $city_word . ' neighbourhood');
}
?>
<div class="nb-detail-hero" style="background-image:url('<?= $hero_img_url ?>');">
    <div class="nb-detail-hero-overlay"></div>
    <div class="container h-100 position-relative" style="z-index:2;">
        <div class="row h-100 align-items-end pb-5">
            <div class="col-lg-8">
                <?php
                $city_links = [
                    'Vancouver East' => 'neighbourhoods-vancouver.php',
                    'Vancouver West' => 'neighbourhoods-vancouver.php',
                    'Burnaby'        => 'neighbourhoods-burnaby.php',
                    'North Vancouver'=> 'neighbourhoods-northvan.php',
                    'Richmond'       => 'neighbourhoods-richmond.php',
                    'West Vancouver' => 'neighbourhoods-westvancouver.php',
                    'New Westminster'=> 'neighbourhoods-newwestminster.php',
                    'Port Moody'     => 'neighbourhoods-portmoody.php',
                    'Coquitlam'      => 'neighbourhoods-coquitlam.php',
                    'Port Coquitlam' => 'neighbourhoods-portcoquitlam.php',
                ];
                $city_page = $city_links[$nb['area']] ?? 'neighbourhoods.php';
                $city_display = str_replace(['Vancouver East','Vancouver West'],['East Vancouver','West Vancouver'], $nb['area']);
                ?>
                <a href="<?= $city_page ?>" class="nb-detail-back"><i class="fas fa-arrow-left me-2"></i><?= htmlspecialchars($city_display) ?></a>
                <h1 class="nb-detail-title"><?= htmlspecialchars($nb['name']) ?></h1>
                <p class="nb-detail-subtitle"><?= htmlspecialchars($nb['area']) ?> &nbsp;&middot;&nbsp; Metro Vancouver &nbsp;&middot;&nbsp; Real Estate &amp; New Construction</p>
            </div>
        </div>
    </div>
</div>

<!-- ② DESCRIPTION + AGENT -->
<section style="background:#f4f6fb;padding:48px 0 40px;">
    <div class="container">
        <div class="nb-desc-agent-wrap">
            <!-- About card -->
            <div class="nb-about-card">
                <h2 class="nb-about-title">About <?= htmlspecialchars($nb['name']) ?></h2>
                <?php if (!empty($nb['description'])): ?>
                <p class="nb-about-body"><?= nl2br(htmlspecialchars($nb['description'])) ?></p>
                <?php else: ?>
                <p class="nb-about-body" style="color:#aaa;">Detailed neighbourhood information for <?= htmlspecialchars($nb['name']) ?> is coming soon. <a href="contact.php" style="color:#0065ff;">Contact Tam</a> for current insights.</p>
                <?php endif; ?>
            </div>
            <!-- Agent card -->
            <div class="nb-agent-card">
                <div class="nb-agent-inner">
                    <img src="/assets/img/user-6.jpg" alt="Tam Nguyen" class="nb-agent-img">
                    <div class="nb-agent-info">
                        <h4>Tam Nguyen</h4>
                        <p>Pre-Sale &amp; New Construction Specialist</p>
                        <a href="tel:6047824689" class="nb-agent-phone"><i class="fas fa-phone me-1"></i>(604) 782-4689</a>
                    </div>
                </div>
                <a href="half-map.php?neighborhood=<?= urlencode($nb['db_neighborhood']) ?>" class="nb-agent-cta">
                    Browse <?= htmlspecialchars($nb['name']) ?> Listings
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ③ LIVABILITY + STATS — circular gauges matching mockup -->
<?php if (!empty($nb['walkscore'])||!empty($nb['transitscore'])||!empty($nb['bikescore'])||!empty($nb['population'])||!empty($nb['median_income'])): ?>
<section style="background:#fff;padding:48px 0 40px;">
    <div class="container">
        <div class="nb-liv-wrap">
            <!-- Circular score gauges -->
            <?php if (!empty($nb['walkscore'])||!empty($nb['transitscore'])||!empty($nb['bikescore'])): ?>
            <div class="nb-liv-card">
                <h3 class="nb-section-title">Livability</h3>
                <div class="nb-gauges-row">
                    <?php if (!empty($nb['walkscore'])): ?>
                    <div class="nb-gauge">
                        <svg viewBox="0 0 80 80" class="nb-gauge-svg">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="7"/>
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#16a34a" stroke-width="7"
                                stroke-dasharray="<?= round(($nb['walkscore']/100)*213.6, 1) ?> 213.6"
                                stroke-dashoffset="53.4" stroke-linecap="round"/>
                        </svg>
                        <div class="nb-gauge-inner">
                            <span class="nb-gauge-val" style="color:#16a34a;"><?= $nb['walkscore'] ?></span>
                        </div>
                        <div class="nb-gauge-label">Walk</div>
                        <i class="fas fa-walking nb-gauge-icon" style="color:#16a34a;"></i>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($nb['transitscore'])): ?>
                    <div class="nb-gauge">
                        <svg viewBox="0 0 80 80" class="nb-gauge-svg">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="7"/>
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#f59e0b" stroke-width="7"
                                stroke-dasharray="<?= round(($nb['transitscore']/100)*213.6, 1) ?> 213.6"
                                stroke-dashoffset="53.4" stroke-linecap="round"/>
                        </svg>
                        <div class="nb-gauge-inner">
                            <span class="nb-gauge-val" style="color:#f59e0b;"><?= $nb['transitscore'] ?></span>
                        </div>
                        <div class="nb-gauge-label">Transit</div>
                        <i class="fas fa-bus nb-gauge-icon" style="color:#f59e0b;"></i>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($nb['bikescore'])): ?>
                    <div class="nb-gauge">
                        <svg viewBox="0 0 80 80" class="nb-gauge-svg">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="7"/>
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#0065ff" stroke-width="7"
                                stroke-dasharray="<?= round(($nb['bikescore']/100)*213.6, 1) ?> 213.6"
                                stroke-dashoffset="53.4" stroke-linecap="round"/>
                        </svg>
                        <div class="nb-gauge-inner">
                            <span class="nb-gauge-val" style="color:#0065ff;"><?= $nb['bikescore'] ?></span>
                        </div>
                        <div class="nb-gauge-label">Bike</div>
                        <i class="fas fa-bicycle nb-gauge-icon" style="color:#0065ff;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Stat pills row below gauges -->
                <?php if (!empty($nb['population'])||!empty($nb['median_income'])||!empty($nb['area_sqkm'])): ?>
                <div class="nb-stat-pills-row">
                    <?php if (!empty($nb['population'])): ?><div class="nb-stat-item"><strong><?= number_format($nb['population']) ?></strong><span>Population</span></div><?php endif; ?>
                    <?php if (!empty($nb['median_income'])): ?><div class="nb-stat-item"><strong>$<?= number_format($nb['median_income']) ?></strong><span>Median Income</span></div><?php endif; ?>
                    <?php if (!empty($nb['area_sqkm'])): ?><div class="nb-stat-item"><strong><?= $nb['area_sqkm'] ?> km²</strong><span>Area</span></div><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- ── Stats Can Demographics ─────────────────────────────── -->
                <?php if ($has_demo): ?>
                <div class="nb-demo-grid">

                    <?php if ($demo_gender): ?>
                    <div class="nb-demo-card">
                        <div class="nb-demo-title">Male vs. Female</div>
                        <?php $f = (float)($demo_gender['female'] ?? 52); $m = round(100-$f, 1); ?>
                        <div class="nb-gender-bar-wrap">
                            <span class="nb-gender-lbl">FEMALE</span>
                            <div class="nb-gender-bar">
                                <div style="width:<?= $f ?>%;background:#ec4899;height:100%;border-radius:4px 0 0 4px;"></div>
                                <div style="width:<?= $m ?>%;background:#0065ff;height:100%;border-radius:0 4px 4px 0;"></div>
                            </div>
                            <span class="nb-gender-lbl">MALE</span>
                        </div>
                        <div class="nb-gender-pct"><?= $f ?>% – <?= $m ?>%</div>
                    </div>
                    <?php endif; ?>

                    <?php if ($demo_languages): ?>
                    <div class="nb-demo-card">
                        <div class="nb-demo-title">Top 3 Languages</div>
                        <?php
                        $lang_flags = ['English'=>'🇨🇦','French'=>'🇨🇦','Cantonese'=>'🇭🇰','Mandarin'=>'🇨🇳','Chinese n.o.s'=>'🇨🇳','Punjabi'=>'🇮🇳','Tagalog'=>'🇵🇭','Korean'=>'🇰🇷','Spanish'=>'🇪🇸','Vietnamese'=>'🇻🇳','Hindi'=>'🇮🇳','Persian'=>'🇮🇷','Arabic'=>'🇸🇦','Japanese'=>'🇯🇵'];
                        foreach (array_slice($demo_languages, 0, 3) as $lang => $pct): ?>
                        <div class="nb-lang-row">
                            <span class="nb-lang-name"><?= htmlspecialchars($lang) ?></span>
                            <span class="nb-lang-flag"><?= $lang_flags[$lang] ?? '🌐' ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($demo_household): ?>
                    <div class="nb-demo-card">
                        <div class="nb-demo-title">Majority of Neighbours Are</div>
                        <?php
                        $hh_colors = ['#ec4899','#06b6d4','#f59e0b','#16a34a','#8b5cf6'];
                        $hh_vals   = array_values($demo_household);
                        $hh_keys   = array_keys($demo_household);
                        $hh_total  = array_sum($hh_vals) ?: 100;
                        $hh_dash   = 213.6;
                        $hh_offset = 53.4;
                        ?>
                        <svg viewBox="0 0 80 80" style="width:72px;height:72px;display:block;margin:8px auto;">
                            <?php foreach ($hh_vals as $i => $v):
                                $arc = round(($v/$hh_total)*$hh_dash, 1);
                                $gap = $hh_dash - $arc;
                            ?>
                            <circle cx="40" cy="40" r="34" fill="none"
                                stroke="<?= $hh_colors[$i % count($hh_colors)] ?>" stroke-width="10"
                                stroke-dasharray="<?= $arc ?> <?= $gap ?>"
                                stroke-dashoffset="<?= $hh_offset ?>"
                                stroke-linecap="butt"/>
                            <?php $hh_offset -= $arc; endforeach; ?>
                        </svg>
                        <?php foreach ($hh_keys as $i => $k): ?>
                        <div class="nb-demo-row">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $hh_colors[$i % count($hh_colors)] ?>;margin-right:6px;flex-shrink:0;"></span>
                            <span class="nb-demo-row-lbl"><?= htmlspecialchars($k) ?></span>
                            <span class="nb-demo-row-val"><?= $hh_vals[$i] ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($demo_age): ?>
                    <div class="nb-demo-card">
                        <div class="nb-demo-title">Population by Age</div>
                        <?php
                        $age_colors = ['#06b6d4','#ec4899','#f59e0b','#16a34a','#8b5cf6','#0065ff','#dc2626'];
                        $age_vals   = array_values($demo_age);
                        $age_keys   = array_keys($demo_age);
                        $age_total  = array_sum($age_vals) ?: 100;
                        $age_dash   = 213.6;
                        $age_offset = 53.4;
                        ?>
                        <svg viewBox="0 0 80 80" style="width:72px;height:72px;display:block;margin:8px auto;">
                            <?php foreach ($age_vals as $i => $v):
                                $arc = round(($v/$age_total)*$age_dash, 1);
                                $gap = $age_dash - $arc;
                            ?>
                            <circle cx="40" cy="40" r="34" fill="none"
                                stroke="<?= $age_colors[$i % count($age_colors)] ?>" stroke-width="10"
                                stroke-dasharray="<?= $arc ?> <?= $gap ?>"
                                stroke-dashoffset="<?= $age_offset ?>"
                                stroke-linecap="butt"/>
                            <?php $age_offset -= $arc; endforeach; ?>
                        </svg>
                        <?php foreach ($age_keys as $i => $k): ?>
                        <div class="nb-demo-row">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $age_colors[$i % count($age_colors)] ?>;margin-right:6px;flex-shrink:0;"></span>
                            <span class="nb-demo-row-lbl"><?= htmlspecialchars($k) ?></span>
                            <span class="nb-demo-row-val"><?= $age_vals[$i] ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Market Snapshot panel -->
            <?php if ($has_prices): ?>
            <div class="nb-market-card">
                <div class="nb-market-hd">
                    <h3 class="nb-section-title">Market Snapshot</h3>
                    <div class="nb-market-tabs" id="nb-mkt-tabs">
                        <button class="nb-mkt-tab active" onclick="nbMktTab(this,'detached')">Detached</button>
                        <button class="nb-mkt-tab" onclick="nbMktTab(this,'condo')">Condo</button>
                        <button class="nb-mkt-tab" onclick="nbMktTab(this,'townhouse')">Townhouse</button>
                        <button class="nb-mkt-tab" onclick="nbMktTab(this,'duplex')">Duplex</button>
                    </div>
                </div>
                <!-- HPI Hero — updates on tab click -->
                <div class="nb-hpi-hero">
                    <div class="nb-hpi-label" id="nb-hpi-label">HPI BENCHMARK</div>
                    <div class="nb-hpi-val" id="nb-hpi-val"><?= fmt_price($nb['hpi_benchmark'] ?: $nb['avg_price'] ?: $nb['price_detached']) ?></div>
                    <div class="nb-hpi-changes">
                        <?php $yoy = fmt_chg($nb['hpi_change_yoy']); if ($yoy): ?>
                        <span class="nb-chg <?= $yoy['cls'] ?>" id="nb-hpi-yoy"><i class="fas fa-arrow-<?= $yoy['cls']==='pos'?'up':'down' ?>"></i> <?= $yoy['val'] ?> YoY</span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Chart -->
                <?php if ($has_history): ?>
                <div style="margin:16px 0;"><canvas id="nbChart" height="80"></canvas></div>
                <?php endif; ?>
                <!-- Price row -->
                <div class="nb-price-row">
                    <div class="nb-price-cell">
                        <div class="nb-price-cell-label">DETACHED</div>
                        <div class="nb-price-cell-val"><?= fmt_price($nb['price_detached']) ?></div>
                    </div>
                    <div class="nb-price-cell">
                        <div class="nb-price-cell-label">CONDO</div>
                        <div class="nb-price-cell-val"><?= fmt_price($nb['price_condo']) ?></div>
                    </div>
                    <div class="nb-price-cell">
                        <div class="nb-price-cell-label">TOWNHOUSE</div>
                        <div class="nb-price-cell-val"><?= fmt_price($nb['price_townhouse']) ?></div>
                    </div>
                    <div class="nb-price-cell">
                        <div class="nb-price-cell-label">DUPLEX / MULTIPLEX</div>
                        <div class="nb-price-cell-val"><?= fmt_price($nb['price_duplex']) ?></div>
                    </div>
                </div>
                <p style="font-size:10px;color:#aaa;margin-top:12px;margin-bottom:0;">* Price data sourced from REBGV HPI. Updated monthly. Not intended as investment advice.<?php if (!empty($nb['price_updated_date'])): ?> Updated <?= date('F Y', strtotime($nb['price_updated_date'])) ?>.<?php endif; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ⑥+⑦ NEARBY AMENITIES + COMMUNITY FACILITIES -->
<?php
$nb_lat = !empty($nb['lat_min']) && !empty($nb['lat_max']) ? round(($nb['lat_min']+$nb['lat_max'])/2, 5) : null;
$nb_lng = !empty($nb['lng_min']) && !empty($nb['lng_max']) ? round(($nb['lng_min']+$nb['lng_max'])/2, 5) : null;

// ── GeoJSON boundary lookup ───────────────────────────────────────────────────
// Map DB slug → GeoJSON NAME property
$geojson_name_map = [
    // ── Vancouver East & West → vancouver-boundaries.geojson ─────────────
    'arbutus'=>'Arbutus Ridge',       // note: file uses "Arbutus Ridge" not "Arbutus"
    'cambie'=>'Cambie',
    'champlain-heights'=>'Champlain Heights',
    'coal-harbour'=>'Coal Harbour',
    'collingwood'=>'Collingwood',
    'collingwood-ve'=>'Collingwood',
    'downtown-ve'=>'Downtown',
    'downtown-vw'=>'Downtown',
    'dunbar'=>'Dunbar',
    'fairview-vw'=>'Fairview',
    'false-creek'=>'False Creek',
    'fraser-ve'=>'Fraser',
    'fraserview-ve'=>'Fraserview',
    'grandview-woodland'=>'Grandview-Woodland',
    'hastings'=>'Hastings',
    'hastings-sunrise'=>'Hastings-Sunrise',
    'kerrisdale'=>'Kerrisdale',
    'killarney'=>'Killarney',
    'kitsilano'=>'Kitsilano',
    'knight'=>'Knight',
    'mackenzie-heights'=>'Mackenzie Heights',
    'main'=>'Main',
    'marpole'=>'Marpole',
    'mount-pleasant-ve'=>'Mount Pleasant',
    'mount-pleasant-vw'=>'Mount Pleasant',
    'oakridge'=>'Oakridge',
    'point-grey'=>'Point Grey',
    'quilchena'=>'Quilchena',
    'renfrew-east'=>'Renfrew',
    'renfrew-heights'=>'Renfrew Heights',
    'renfrew-ve'=>'Renfrew',
    'shaughnessy'=>'Shaughnessy',
    'south-cambie'=>'South Cambie',
    'south-granville'=>'South Granville',
    'south-marine'=>'South Marine',
    'south-vancouver'=>'South Vancouver',
    'southlands'=>'Southlands',
    'sw-marine'=>'SW Marine',
    'strathcona'=>'Strathcona',
    'university'=>'University',
    'victoria'=>'Victoria',
    'west-end'=>'West End',
    'yaletown'=>'Yaletown',
    // ── Richmond → richmond-boundaries.geojson ───────────────────────────
    'boyd-park'=>'Boyd Park',
    'bridgeport-ri'=>'Bridgeport',
    'brighouse'=>'Brighouse',
    'brighouse-south'=>'Brighouse South',
    'broadmoor'=>'Broadmoor',
    'east-cambie'=>'East Cambie',
    'east-richmond-ri'=>'East Richmond',
    'garden-city'=>'Garden City',
    'gilmore-ri'=>'Gilmore',
    'granville'=>'Granville',
    'hamilton-ri'=>'Hamilton',
    'ironwood'=>'Ironwood',
    'lackner'=>'Lackner',
    'mclennan'=>'McLennan',
    'mclennan-north'=>'McLennan North',
    'mcnair'=>'McNair',
    'quilchena-ri'=>'Quilchena',
    'riverdale-ri'=>'Riverdale',
    'saunders-ri'=>'Saunders',
    'sea-island-ri'=>'Sea Island',
    'seafair'=>'Seafair',
    'south-arm-ri'=>'South Arm',
    'steveston-north'=>'Steveston North',
    'steveston-south'=>'Steveston South',
    'steveston-village'=>'Steveston Village',
    'terra-nova'=>'Terra Nova',
    'west-cambie-ri'=>'West Cambie',
    'westwind-ri'=>'Westwind',
    'woodwards-ri'=>'Woodwards',
    // ── Burnaby → burnaby-boundaries.geojson ─────────────────────────────
    'big-bend'=>'Big Bend',
    'brentwood'=>'Brentwood Park',
    'brentwood-park-bn'=>'Brentwood Park',
    'buckingham-heights'=>'Buckingham Heights',
    'burnaby-hospital'=>'Burnaby Hospital',
    'burnaby-lake'=>'Burnaby Lake',
    'capitol-hill-bn'=>'Capitol Hill',
    'cariboo'=>'Cariboo',
    'central-bn'=>'Central Burnaby',
    'central-park-bn'=>'Central Park',
    'deer-lake'=>'Deer Lake',
    'east-burnaby'=>'East Burnaby',
    'edmonds-bn'=>'Edmonds',
    'forest-glen-bn'=>'Forest Hills',
    'forest-hills-bn'=>'Forest Hills',
    'forglen'=>'Forglen',
    'garden-village'=>'Garden Village',
    'government-road'=>'Government Road',
    'greentree-village'=>'Greentree Village',
    'highgate'=>'Highgate',
    'lake-city-industrial'=>'Lake City Industrial',
    'metrotown'=>'Metrotown',
    'montecito'=>'Montecito',
    'oakdale'=>'Oakdale',
    'oaklands'=>'Oaklands',
    'parkcrest'=>'Parkcrest',
    'simon-fraser-university'=>'Simon Fraser University',
    'south-slope'=>'South Slope',
    'sperling-duthie'=>'Sperling-Duthie',
    'sullivan-heights'=>'Sullivan Heights',
    'suncrest'=>'Suncrest',
    'the-crest'=>'The Crest',
    'upper-deer-lake'=>'Upper Deer Lake',
    'vancouver-heights'=>'Vancouver Heights',
    'westridge-bn'=>'Westridge',
    'willingdon-heights'=>'Willingdon Heights',
    // ── North Vancouver (City + District) → northvancouver-boundaries.geojson
    'blueridge-nv'=>'Blueridge',
    'boulevard'=>'Boulevard',
    'calverhall'=>'Calverhall',
    'canyon-heights-nv'=>'Canyon Heights',
    'capilano-highlands'=>'Capilano Highlands',
    'central-lonsdale'=>'Central Lonsdale',
    'deep-cove'=>'Deep Cove',
    'delbrook'=>'Delbrook',
    'dollarton'=>'Dollarton',
    'edgemont'=>'Edgemont',
    'forest-hills-nv'=>'Forest Hills',
    'grouse-woods'=>'Grouse Woods',
    'harbourside'=>'Harbourside',
    'indian-river'=>'Indian River',
    'lower-lonsdale'=>'Lower Lonsdale',
    'lynn-valley'=>'Lynn Valley',
    'lynnmour'=>'Lynnmour',
    'moodyville'=>'Norgate',
    'mosquito-creek'=>'Mosquito Creek',
    'norgate'=>'Norgate',
    'northlands'=>'Northlands',
    'pemberton'=>'Pemberton',
    'pemberton-heights'=>'Pemberton Heights',
    'princess-park'=>'Princess Park',
    'queensbury'=>'Queensbury',
    'roche-point'=>'Roche Point',
    'seymour'=>'Seymour',
    'tempe'=>'Tempe',
    'upper-delbrook'=>'Upper Delbrook',
    'upper-lonsdale'=>'Upper Lonsdale',
    'westlynn'=>'Westlynn',
    'westlynn-terrace'=>'Westlynn Terrace',
    'windsor-park'=>'Windsor Park',
    'woodlands-sunshine'=>'Woodlands-Sunshine',
    // ── West Vancouver → westvancouver-boundaries.geojson ────────────────
    'altamont'=>'Altamont',
    'ambleside'=>'Ambleside',
    'ambleside-wv'=>'Ambleside',
    'bayridge'=>'Bayridge',
    'british-properties'=>'British Properties',
    'canterbury'=>'Canterbury',
    'caulfeild'=>'Caulfeild',
    'cedardale'=>'Cedardale',
    'chartwell'=>'Chartwell',
    'chelsea-park'=>'Chelsea Park',
    'cypress'=>'Cypress',
    'cypress-park-estates'=>'Cypress Park Estates',
    'cypress-wv'=>'Cypress',
    'deer-ridge'=>'Deer Ridge',
    'dundarave'=>'Dundarave',
    'eagle-harbour'=>'Eagle Harbour',
    'eagle-ridge'=>'Eagle Ridge',
    'eagleridge'=>'Eagle Ridge',
    'furry-creek'=>'Furry Creeks',
    'gleneagles'=>'Gleneagles',
    'glenmore'=>'Glenmore',
    'horseshoe-bay'=>'Horseshoe Bay',
    'howe-sound'=>'Howe Sound',
    'lions-bay'=>'Lions Bay',
    'old-caulfeild'=>'Olde Caulfeild',
    'olde-caulfeild'=>'Olde Caulfeild',
    'panorama-village'=>'Panorama Village',
    'park-royal'=>'Park Royal',
    'passage-island'=>'Passage Island',
    'porteau'=>'Porteau',
    'queens'=>'Queens',
    'queens-wv'=>'Queens',
    'rockridge'=>'Rockridge',
    'sandy-cove'=>'Sandy Cove',
    'sentinel-hill'=>'Sentinel Hill',
    'upper-caulfeild'=>'Upper Caulfeild',
    'west-bay'=>'West Bay',
    'west-bay-wv'=>'West Bay',
    'westhill'=>'Westhill',
    'westmount'=>'Westmount',
    'westmount-wv'=>'Westmount',
    'whitby-estates'=>'Whitby Estates',
    'whytecliff'=>'Whytecliff',
    // ── New Westminster → newwestminster-boundaries.geojson ──────────────
    'brunette-nw'=>'Brunette',
    'connaught-heights'=>'Connaught Heights',
    'downtown-nw'=>'Downtown',
    'fraserview-nw'=>'Fraserview',
    'glenbrooke-north'=>'Glenbrooke North',
    'moody-park'=>'Moody Park',
    'north-arm-nw'=>'North Arm',
    'quay-nw'=>'Quay',
    'queens-park-nw'=>"Queen's Park",
    'queensborough-nw'=>'Queensborough',
    'sapperton'=>'Sapperton',
    'the-heights-nw'=>'The Heights',
    'uptown-nw'=>'Uptown',
    'west-end-nw'=>'West End',
    // ── Coquitlam → coquitlam-boundaries.geojson ─────────────────────────
    'burke-mountain'=>'Burke Mountain',
    'canyon-springs'=>'Canyon Springs',
    'cape-horn'=>'Cape Horn',
    'central-coquitlam'=>'Central Coquitlam',
    'chineside'=>'Chineside',
    'coquitlam-east'=>'Coquitlam East',
    'coquitlam-west'=>'Coquitlam West',
    'eagle-ridge-coq'=>'Eagle Ridge',
    'harbour-chines'=>'Habour Chines',
    'harbour-place'=>'Habour Place',
    'hockaday'=>'Hockaday',
    'maillardville'=>'Maillardville',
    'meadowbrook'=>'Meadow Brook',
    'mountain-meadows-coq'=>'Mountain Meadows',
    'new-horizons'=>'New Horizons',
    'north-coquitlam'=>'North Coquitlam',
    'park-ridge-estates'=>'Park Ridge Estates',
    'ranch-park'=>'Range Park',
    'river-springs'=>'River Springs',
    'scott-creek'=>'Scott Creek',
    'summit-view'=>'Summit View',
    'upper-eagle-ridge'=>'Upper Eagle Creek',
    'westwood-plateau'=>'Westwood Plateau',
    'westwood-summit'=>'Westwood Summit',
    // ── Port Coquitlam → port-coquitlam-boundaries.geojson ───────────────
    'birchland-manor'=>'Birchland Manor',
    'central-port-coquitlam'=>'Central Port Coquitlam',
    'citadel-poc'=>'Citadel',
    'glenwood-poc'=>'Glennwood',
    'lincoln-park-poc'=>'Lincoln Park',
    'lower-mary-hill'=>'Lower Mary Hill',
    'mary-hill'=>'Mary Hill',
    'oxford-heights'=>'Oxford Heights',
    'riverwood'=>'River Wood',
    'woodland-acres-poc'=>'Woodland Acres',
    // ── Port Moody → portmoody-boundaries.geojson ────────────────────────
    'anmore'=>'Anmore',
    'barber-street-pm'=>'Barber Street',
    'belcarra'=>'Belcarra',
    'college-park-pm'=>'College Park',
    'glenayre-pm'=>'Glenayre',
    'heritage-mountain-pm'=>'Heritage Mountain',
    'heritage-woods-pm'=>'Heritage Woods',
    'ioco'=>'Ioco',
    'mountain-meadows-pm'=>'Mountain Meadows',
    'north-shore-pm'=>'North Shore Port Moody',
    'port-moody-centre'=>'Port Moody Centre',
];

$nb_boundary  = null;
$geojson_name = $geojson_name_map[$slug] ?? null;
if ($geojson_name) {
    $area = $nb['area'] ?? '';
    $city_file_map = [
        'Vancouver East'            => 'vancouver-boundaries.geojson',
        'Vancouver West'            => 'vancouver-boundaries.geojson',
        'Richmond'                  => 'richmond-boundaries.geojson',
        'Burnaby'                   => 'burnaby-boundaries.geojson',
        'City of North Vancouver'   => 'northvancouver-boundaries.geojson',
        'District of North Vancouver'=> 'northvancouver-boundaries.geojson',
        'West Vancouver'            => 'westvancouver-boundaries.geojson',
        'New Westminster'           => 'newwestminster-boundaries.geojson',
        'Coquitlam'                 => 'coquitlam-boundaries.geojson',
        'Port Coquitlam'            => 'port-coquitlam-boundaries.geojson',
        'Port Moody'                => 'portmoody-boundaries.geojson',
    ];
    $geojson_file = $city_file_map[$area] ?? null;

    if ($geojson_file) {
        $geojson_path = null;
        foreach ([__DIR__.'/'.$geojson_file, __DIR__.'/../'.$geojson_file, $_SERVER['DOCUMENT_ROOT'].'/'.$geojson_file] as $p) {
            if (file_exists($p)) { $geojson_path = $p; break; }
        }
        if ($geojson_path) {
            $gj = json_decode(file_get_contents($geojson_path), true);
            if ($gj) {
                foreach ($gj['features'] as $feat) {
                    if (($feat['properties']['NAME'] ?? '') === $geojson_name) {
                        $nb_boundary = $feat['geometry'];
                        // Calculate search radius from polygon size
                        $ring = $nb_boundary['type'] === 'Polygon'
                            ? $nb_boundary['coordinates'][0]
                            : $nb_boundary['coordinates'][0][0];
                        $lats = array_column($ring, 1);
                        $lngs = array_column($ring, 0);
                        $clat = (min($lats) + max($lats)) / 2;
                        $h = (max($lats) - min($lats)) * 111000;
                        $w = (max($lngs) - min($lngs)) * 111000 * cos(deg2rad($clat));
                        $nb_radius = (int) min(5000, max(1200, sqrt(($w/2)**2 + ($h/2)**2) * 1.1));
                        break;
                    }
                }
            }
        }
    }
}
$nb_radius = $nb_radius ?? 1500;

// ── Places cache — read from DB, serve instantly ─────────────────────────────
$places_cached = [];
if ($nb_lat && $nb_lng) {
    try {
        $cq = $pdo->prepare("SELECT category, name, address, lat, lng, osm_url
                              FROM places_cache WHERE neighbourhood_id = ?
                              ORDER BY category, name");
        $cq->execute([$nb['id']]);
        foreach ($cq->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $places_cached[$row['category']][] = $row;
        }
    } catch(Exception $e) {}
}
$has_places = !empty($places_cached);
?>
<section style="padding:56px 0 0;background:#fff;">
    <div style="max-width:1400px;margin:0 auto;padding:0 28px;">
        <h2 class="nb-section-title mb-1">Nearby Amenities and Community Facilities</h2>
        <p style="font-size:13px;color:#888;margin-bottom:20px;">Grocery stores, parks, transit, schools, recreation and more near <?= htmlspecialchars($nb['name']) ?>.</p>
    </div>
    <div style="width:100%;">
        <div class="nb-amenities-wrap">
            <!-- Categories sidebar -->
            <div class="nb-cat-sidebar">
                <div class="nb-cat-btn active" data-cat="all" onclick="nbCatFilter(this,'all')"><i class="fas fa-th me-2"></i>All</div>
                <div class="nb-cat-btn" data-cat="grocery" onclick="nbCatFilter(this,'grocery')"><i class="fas fa-shopping-cart me-2"></i>Grocery</div>
                <div class="nb-cat-btn" data-cat="park" onclick="nbCatFilter(this,'park')"><i class="fas fa-tree me-2"></i>Parks</div>
                <div class="nb-cat-btn" data-cat="transit" onclick="nbCatFilter(this,'transit')"><i class="fas fa-bus me-2"></i>Transit</div>
                <div class="nb-cat-btn" data-cat="recreation" onclick="nbCatFilter(this,'recreation')"><i class="fas fa-dumbbell me-2"></i>Recreation</div>
                <div class="nb-cat-btn" data-cat="school" onclick="nbCatFilter(this,'school')"><i class="fas fa-graduation-cap me-2"></i>Schools</div>
                <div class="nb-cat-btn" data-cat="health" onclick="nbCatFilter(this,'health')"><i class="fas fa-plus-circle me-2"></i>Health</div>
                <div class="nb-cat-btn" data-cat="restaurant" onclick="nbCatFilter(this,'restaurant')"><i class="fas fa-utensils me-2"></i>Dining</div>
            </div>

            <!-- Google Map -->
            <div class="nb-amenities-map-wrap">
                <?php if ($nb_lat && $nb_lng): ?>
                <div id="nb-amenities-map" style="width:100%;height:100%;min-height:480px;"></div>
                <!-- Custom tooltip — replaces Google InfoWindow -->
                <div id="nb-map-tooltip"></div>
                <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:100%;min-height:420px;background:#f8f9fc;color:#aaa;">
                    <div class="text-center"><i class="fas fa-map" style="font-size:36px;opacity:.3;display:block;margin-bottom:10px;"></i><p>Map unavailable — no coordinates set</p></div>
                </div>
                <?php endif; ?>
                <div class="nb-map-caption-bar"><i class="fas fa-map-marker-alt me-1" style="color:#0065ff;"></i>Powered by OpenStreetMap &amp; Overpass API</div>
            </div>

            <!-- Right panel — dynamically updated by JS -->
            <div class="nb-facilities-list" id="nb-right-panel">
                <div id="nb-panel-facilities">
                    <h4 class="nb-facilities-list-title" id="nb-panel-title">Nearby Places</h4>
                    <div id="nb-places-list">
                        <div style="text-align:center;padding:32px 16px;color:#aaa;">
                            <i class="fas fa-spinner fa-spin" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                            <p style="font-size:13px;margin:0;">Loading nearby places…</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($nb_lat && $nb_lng): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Amenities Map — instant from DB cache, Overpass refresh if stale ──────────
var NB_LAT      = <?= $nb_lat ?>;
var NB_LNG      = <?= $nb_lng ?>;
var NB_ID       = <?= (int)$nb['id'] ?>;
var NB_BOUNDARY = <?= $nb_boundary ? json_encode($nb_boundary) : 'null' ?>;
var NB_RADIUS   = <?= $nb_radius ?? 1500 ?>;
// Pre-loaded from DB — instant, zero API calls
var NB_CACHED = <?= json_encode($places_cached) ?>;
var NB_EMPTY  = <?= empty($places_cached) ? 'true' : 'false' ?>;

var nbMap, nbAllMarkers = {}, nbActiveCat = 'all';

var NB_CATS = {
    grocery:    { color:'#0065ff', icon:'fa-shopping-cart', label:'Grocery Stores',
                  tags:[['shop','supermarket'],['shop','convenience'],['shop','grocery'],['shop','food']] },
    park:       { color:'#16a34a', icon:'fa-tree',           label:'Parks',
                  tags:[['leisure','park'],['leisure','playground'],['leisure','garden'],['leisure','nature_reserve']] },
    transit:    { color:'#f59e0b', icon:'fa-bus',            label:'Transit',
                  tags:[['railway','station'],['highway','bus_stop'],['amenity','bus_station'],['railway','subway_entrance']] },
    recreation: { color:'#8b5cf6', icon:'fa-dumbbell',       label:'Recreation',
                  tags:[['leisure','fitness_centre'],['leisure','sports_centre'],['leisure','swimming_pool'],['leisure','ice_rink']] },
    school:     { color:'#7c3aed', icon:'fa-graduation-cap', label:'Schools',
                  tags:[['amenity','school'],['amenity','college'],['amenity','university'],['amenity','kindergarten']] },
    health:     { color:'#0891b2', icon:'fa-plus-circle',     label:'Health & Clinics',
                  tags:[['amenity','hospital'],['amenity','clinic'],['amenity','doctors'],['amenity','pharmacy']] },
    restaurant: { color:'#dc2626', icon:'fa-utensils',       label:'Dining',
                  tags:[['amenity','restaurant'],['amenity','cafe'],['amenity','fast_food'],['amenity','bar'],['amenity','pub'],['amenity','food_court']] },
};

// ── Shared tooltip ────────────────────────────────────────────────────────────
var nbSharedIw = null;
function nbShowTooltip(marker, content) {
    nbHideTooltip();
    var tip = document.getElementById('nb-map-tooltip');
    if (!tip) return;
    tip.innerHTML = content;
    tip.style.display = 'block';
    function positionTip() {
        var proj = nbMap.project(marker.getLatLng());
        var mapOffset = nbMap.project(nbMap.getBounds().getNorthWest());
        var px = proj.x - mapOffset.x;
        var py = proj.y - mapOffset.y;
        var tw = tip.offsetWidth, th = tip.offsetHeight;
        var mapDiv = document.getElementById('nb-amenities-map');
        var left = Math.max(8, Math.min(mapDiv.offsetWidth - tw - 8, px - tw / 2));
        var top  = Math.max(8, py - th - 18);
        tip.style.left = left + 'px';
        tip.style.top  = top  + 'px';
    }
    requestAnimationFrame(positionTip);
    nbSharedIw = nbMap.on('moveend', positionTip);
}
function nbHideTooltip() {
    var tip = document.getElementById('nb-map-tooltip');
    if (tip) tip.style.display = 'none';
    if (nbSharedIw) { nbMap.off('moveend', nbSharedIw); nbSharedIw = null; }
}

// ── Create a circle marker ────────────────────────────────────────────────────
function nbMakeMarker(lat, lng, cat) {
    var cfg = NB_CATS[cat];
    var html = '<div style="'
        + 'width:32px;height:32px;'
        + 'background:' + cfg.color + ';'
        + 'border:2.5px solid #fff;'
        + 'border-radius:50%;'
        + 'display:flex;align-items:center;justify-content:center;'
        + 'box-shadow:0 2px 6px rgba(0,0,0,0.35);'
        + 'cursor:pointer;'
        + '">'
        + '<i class="fas ' + cfg.icon + '" style="color:#fff;font-size:13px;"></i>'
        + '</div>';
    var icon = L.divIcon({
        html: html,
        className: '',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -18]
    });
    return L.marker([lat, lng], { icon: icon, bubblingMouseEvents: false });
}

// ── Build tooltip HTML for a place ───────────────────────────────────────────
function nbPlaceTooltip(name, addr, osmUrl, cat) {
    var cfg = NB_CATS[cat];
    return '<div style="display:flex;align-items:center;gap:7px;margin-bottom:6px;">'
        + '<span style="width:26px;height:26px;background:' + cfg.color + ';border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
        + '<i class="fas ' + cfg.icon + '" style="color:#fff;font-size:11px;"></i></span>'
        + '<span style="font-size:13px;font-weight:700;color:#002446;">' + name + '</span></div>'
        + (addr ? '<div style="font-size:11px;color:#888;margin-bottom:8px;padding-left:33px;">' + addr + '</div>' : '')
        + '<a href="' + osmUrl + '" target="_blank" rel="noopener" '
        + 'style="display:inline-flex;align-items:center;gap:4px;background:' + cfg.color + ';color:#fff;'
        + 'border-radius:5px;padding:5px 11px;font-size:11px;font-weight:700;text-decoration:none;">'
        + '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'
        + 'View on OpenStreetMap</a>';
}

// ── Add cached places to map and panels ──────────────────────────────────────
// Point-in-polygon test using ray casting
function nbPointInPolygon(lat, lng) {
    if (!NB_BOUNDARY) return true; // no boundary = allow all
    var coords = NB_BOUNDARY.type === 'Polygon'
        ? NB_BOUNDARY.coordinates
        : NB_BOUNDARY.coordinates[0];
    var poly = coords[0]; // outer ring [lng, lat] pairs
    var inside = false;
    for (var i = 0, j = poly.length - 1; i < poly.length; j = i++) {
        var xi = poly[i][1], yi = poly[i][0]; // lat, lng
        var xj = poly[j][1], yj = poly[j][0];
        var intersect = ((yi > lng) !== (yj > lng)) &&
            (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}

function nbLoadFromCache() {
    Object.keys(NB_CATS).forEach(function(cat) {
        nbAllMarkers[cat] = [];
        var places = NB_CACHED[cat] || [];
        places.forEach(function(p) {
            var lat = parseFloat(p.lat);
            var lng = parseFloat(p.lng);
            // Filter to actual polygon boundary
            if (!nbPointInPolygon(lat, lng)) return;
            var marker = nbMakeMarker(lat, lng, cat);
            var iw = nbPlaceTooltip(p.name, p.address, p.osm_url, cat);
            marker.on('click', function() {
                nbShowTooltip(marker, iw);
                nbHighlightPanel(p.name);
            });
            marker.addTo(nbMap);
            nbAllMarkers[cat].push({ marker: marker, name: p.name, addr: p.address, osmUrl: p.osm_url, lat: lat, lng: lng, iwContent: iw });
        });
    });
    nbRenderPanel('all');
}



// ── Panel rendering ───────────────────────────────────────────────────────────
function nbHighlightPanel(name) {
    document.querySelectorAll('#nb-places-list .nb-fac-item').forEach(function(el) {
        el.style.background = ''; el.style.borderRadius = ''; el.style.border = '';
        var n = el.querySelector('.nb-fac-name');
        if (n && n.textContent.trim() === name) {
            el.style.background = '#f0f4ff'; el.style.borderRadius = '8px'; el.style.border = '2px solid #0065ff';
            var panel = document.getElementById('nb-right-panel');
            if (panel) panel.scrollTop = el.offsetTop - panel.offsetTop - 10;
        }
    });
}

function nbRenderPanel(cat) {
    var list  = document.getElementById('nb-places-list');
    var title = document.getElementById('nb-panel-title');
    var items = [];
    if (cat === 'all') {
        if (title) title.textContent = 'Nearby Places';
        Object.keys(NB_CATS).forEach(function(c) { (nbAllMarkers[c]||[]).forEach(function(i){ items.push({i:i,cat:c}); }); });
    } else {
        var cfg = NB_CATS[cat];
        if (title) title.textContent = cfg ? cfg.label : 'Nearby Places';
        items = (nbAllMarkers[cat]||[]).map(function(i){ return {i:i,cat:cat}; });
    }
    if (!items.length) {
        list.innerHTML = '<div style="text-align:center;padding:24px;color:#aaa;font-size:13px;">No results found nearby</div>';
        return;
    }
    list.innerHTML = items.map(function(obj, idx) {
        var cfg = NB_CATS[obj.cat];
        return '<div class="nb-fac-item nb-panel-clickable" data-cat="' + obj.cat + '" data-idx="' + idx + '" style="cursor:pointer;">'
            + '<div class="nb-fac-img-placeholder"><i class="fas ' + cfg.icon + '" style="font-size:20px;color:' + cfg.color + ';"></i></div>'
            + '<div class="nb-fac-info"><div class="nb-fac-name">' + obj.i.name + '</div>'
            + '<div class="nb-fac-type"><i class="fas fa-map-marker-alt me-1" style="color:#aaa;font-size:10px;"></i>' + (obj.i.addr||'') + '</div></div>'
            + '<a href="' + obj.i.osmUrl + '" target="_blank" rel="noopener" class="nb-fac-btn" onclick="event.stopPropagation();"><i class="fas fa-external-link-alt" style="font-size:10px;"></i></a>'
            + '</div>';
    }).join('');
    // Store flat list for click lookup
    var flatItems = items;
    list.querySelectorAll('.nb-panel-clickable').forEach(function(el) {
        el.addEventListener('click', function() {
            var idx = parseInt(el.dataset.idx);
            var obj = flatItems[idx];
            if (!obj) return;
            nbMap.panTo([obj.i.lat, obj.i.lng]);
            nbShowTooltip(obj.i.marker, obj.i.iwContent);
            list.querySelectorAll('.nb-panel-clickable').forEach(function(r){ r.style.background=''; r.style.border=''; r.style.borderRadius=''; });
            el.style.background='#f0f4ff'; el.style.border='2px solid #0065ff'; el.style.borderRadius='8px';
        });
    });
}

window.nbCatFilter = function(btn, cat) {
    document.querySelectorAll('.nb-cat-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    nbActiveCat = cat;
    Object.keys(nbAllMarkers).forEach(function(k) {
        nbAllMarkers[k].forEach(function(item) {
            if (cat === 'all' || cat === k) { if (!nbMap.hasLayer(item.marker)) item.marker.addTo(nbMap); }
            else { if (nbMap.hasLayer(item.marker)) nbMap.removeLayer(item.marker); }
        });
    });
    nbRenderPanel(cat);
};

function initNbMap() {
    nbMap = L.map('nb-amenities-map', {
        center: [NB_LAT, NB_LNG], zoom: 15,
        scrollWheelZoom: false, attributionControl: false
    });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom:19 }).addTo(nbMap);
    nbMap.on('click', function() { nbHideTooltip(); });

    // Draw neighbourhood boundary
    if (NB_BOUNDARY) {
        var coords = NB_BOUNDARY.type === 'Polygon' ? NB_BOUNDARY.coordinates : NB_BOUNDARY.coordinates[0];
        L.polygon(coords.map(function(ring){ return ring.map(function(pt){ return [pt[1],pt[0]]; }); }), {
            color:'#0065ff', weight:2.5, opacity:0.8, fillColor:'#0065ff', fillOpacity:0.06, interactive:false
        }).addTo(nbMap);
    }

    // Render from cache — instant
    if (NB_EMPTY) {
        var list = document.getElementById('nb-places-list');
        var title = document.getElementById('nb-panel-title');
        if (title) title.textContent = 'Nearby Places';
        if (list) list.innerHTML = '<div style="text-align:center;padding:32px 16px;color:#aaa;">'
            + '<i class="fas fa-map-marker-alt" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3;"></i>'
            + '<p style="font-size:13px;margin:0;">Places data not yet loaded for this neighbourhood.</p>'
            + '</div>';
    } else {
        nbLoadFromCache();
    }
}

window.addEventListener('load', function() { initNbMap(); });
</script>
<?php else: ?>
<script>window.nbCatFilter = function(){};</script>
<?php endif; ?>

        <?php
        // Community facilities now served by Google Places API on the map — no hardcoded array needed
        ?>

<!-- ⑧ COMMUNITY EVENTS -->
<section style="padding:56px 0;background:#f4f6fb;">
    <div class="container">
        <h2 class="nb-section-title mb-1">Interactive Neighbourhood Events Calendar</h2>
        <p style="font-size:13px;color:#888;margin-bottom:28px;">Upcoming programs and events in <?= htmlspecialchars($nb['name']) ?> and <?= htmlspecialchars($nb_city) ?>.</p>

        <?php if (!empty($all_events)):
            // Group events by date for calendar dots
            $event_dates = [];
            foreach ($all_events as $ev) {
                $d = date('j', strtotime($ev['event_date']));
                $event_dates[$d] = true;
            }
            // Build dynamic category filter — only show categories that have events
            $event_cats_present = array_unique(array_column($all_events, 'category'));
            $all_cat_labels = [
                'festival'   => 'Festivals',
                'market'     => 'Markets',
                'community'  => 'Community',
                'recreation' => 'Recreation',
                'arts'       => 'Arts',
                'workshop'   => 'Workshops',
                'library'    => 'Library',
                'sports'     => 'Sports',
                'family'     => 'Family',
                'other'      => 'Other',
            ];
            $events_shown  = array_slice($all_events, 0, 3);
            $events_hidden = array_slice($all_events, 3);
            $has_more      = count($events_hidden) > 0;
        ?>
        <!-- Search + filter bar -->
        <div class="nb-cal-bar">
            <div class="nb-cal-search"><i class="fas fa-search"></i><input type="text" placeholder="Search events..." id="nb-evt-search" oninput="nbEvtFilter()"></div>
            <div class="nb-cal-filters">
                <button class="nb-cal-filter active" data-cat="all" onclick="nbCalFilter(this)">All</button>
                <?php foreach ($event_cats_present as $cat):
                    $lbl = $all_cat_labels[$cat] ?? ucfirst($cat);
                ?>
                <button class="nb-cal-filter" data-cat="<?= htmlspecialchars($cat) ?>" onclick="nbCalFilter(this)"><?= htmlspecialchars($lbl) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nb-events-layout">
            <!-- Calendar widget -->
            <div class="nb-calendar-wrap">
                <div class="nb-cal-nav">
                    <button class="nb-cal-arrow" onclick="nbCalNav(-1)">&#8249;</button>
                    <span class="nb-cal-month-label" id="nb-cal-month"><?= date('F Y') ?></span>
                    <button class="nb-cal-arrow" onclick="nbCalNav(1)">&#8250;</button>
                </div>
                <div class="nb-cal-grid">
                    <div class="nb-cal-dow">Sun</div><div class="nb-cal-dow">Mon</div><div class="nb-cal-dow">Tue</div>
                    <div class="nb-cal-dow">Wed</div><div class="nb-cal-dow">Thu</div><div class="nb-cal-dow">Fri</div><div class="nb-cal-dow">Sat</div>
                </div>
                <div class="nb-cal-days" id="nb-cal-days">
                    <?php
                    $today = (int)date('j');
                    $first_dow = (int)date('N', strtotime(date('Y-m-01')));
                    $first_dow = $first_dow % 7; // Sun=0
                    $days_in_month = (int)date('t');
                    for ($i=0; $i<$first_dow; $i++) echo '<div class="nb-cal-day empty"></div>';
                    for ($d=1; $d<=$days_in_month; $d++):
                        $is_today = ($d === $today);
                        $has_event = isset($event_dates[$d]);
                    ?>
                    <div class="nb-cal-day <?= $is_today?'today':'' ?>" data-day="<?= $d ?>">
                        <?= $d ?>
                        <?php if ($has_event): ?><span class="nb-cal-dot"></span><?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Event spotlight -->
            <div class="nb-spotlight-wrap">
                <h4 class="nb-spotlight-title">Event Spotlight</h4>
                <div id="nb-events-list">
                    <?php foreach ($events_shown as $ev):
                        $is_local = !empty($ev['neighbourhood_id']);
                    ?>
                    <div class="nb-spotlight-card" data-cat="<?= htmlspecialchars(strtolower($ev['category'] ?? 'other')) ?>" data-title="<?= htmlspecialchars(strtolower($ev['title'])) ?>">
                        <div class="nb-spot-date">
                            <span class="nb-spot-mon"><?= date('M', strtotime($ev['event_date'])) ?></span>
                            <span class="nb-spot-day"><?= date('j', strtotime($ev['event_date'])) ?></span>
                        </div>
                        <div class="nb-spot-body">
                            <div class="nb-spot-title"><?= htmlspecialchars($ev['title']) ?></div>
                            <?php if (!empty($ev['event_time'])): ?><div class="nb-spot-time"><?= htmlspecialchars($ev['event_time']) ?></div><?php endif; ?>
                            <?php if (!empty($ev['location_name'])): ?><div class="nb-spot-loc"><i class="fas fa-map-marker-alt me-1" style="color:#0065ff;"></i><?= htmlspecialchars($ev['location_name']) ?></div><?php endif; ?>
                            <?php if (!empty($ev['url'])): ?>
                            <div style="margin-top:8px;"><i class="fas fa-map-marker-alt me-1" style="color:#aaa;font-size:10px;"></i><a href="<?= htmlspecialchars($ev['url']) ?>" target="_blank" class="nb-spot-link">View Details</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($has_more): ?>
                    <div id="nb-events-more" style="display:none;">
                        <?php foreach ($events_hidden as $ev): ?>
                        <div class="nb-spotlight-card" data-cat="<?= htmlspecialchars(strtolower($ev['category'] ?? 'other')) ?>" data-title="<?= htmlspecialchars(strtolower($ev['title'])) ?>">
                            <div class="nb-spot-date">
                                <span class="nb-spot-mon"><?= date('M', strtotime($ev['event_date'])) ?></span>
                                <span class="nb-spot-day"><?= date('j', strtotime($ev['event_date'])) ?></span>
                            </div>
                            <div class="nb-spot-body">
                                <div class="nb-spot-title"><?= htmlspecialchars($ev['title']) ?></div>
                                <?php if (!empty($ev['event_time'])): ?><div class="nb-spot-time"><?= htmlspecialchars($ev['event_time']) ?></div><?php endif; ?>
                                <?php if (!empty($ev['location_name'])): ?><div class="nb-spot-loc"><i class="fas fa-map-marker-alt me-1" style="color:#0065ff;"></i><?= htmlspecialchars($ev['location_name']) ?></div><?php endif; ?>
                                <?php if (!empty($ev['url'])): ?><div style="margin-top:8px;"><a href="<?= htmlspecialchars($ev['url']) ?>" target="_blank" class="nb-spot-link">View Details</a></div><?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="nb-events-toggle" id="nb-events-btn" onclick="toggleEvents()">
                        <i class="fas fa-calendar-plus me-2"></i>Show all <?= count($all_events) ?> events
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($city_event_links[$nb_city])): ?>
        <p style="font-size:12px;color:#aaa;margin-top:20px;text-align:center;">
            <a href="<?= $city_event_links[$nb_city]['url'] ?>" target="_blank" rel="noopener" style="color:#0065ff;">
                <?= $city_event_links[$nb_city]['label'] ?> <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
            </a>
        </p>
        <?php endif; ?>

        <script>
        function toggleEvents(){
            var h=document.getElementById('nb-events-more');
            var b=document.getElementById('nb-events-btn');
            var showing=h&&h.style.display!=='none';
            if(h) h.style.display=showing?'none':'';
            if(b) b.innerHTML=showing
                ? '<i class="fas fa-calendar-plus me-2"></i>Show all <?= count($all_events) ?> events'
                : '<i class="fas fa-chevron-up me-2"></i>Show fewer events';
        }
        function nbCalFilter(btn){
            document.querySelectorAll('.nb-cal-filter').forEach(function(b){b.classList.remove('active');});
            btn.classList.add('active');
            var cat = btn.dataset.cat;
            document.querySelectorAll('.nb-spotlight-card').forEach(function(c){
                c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none';
            });
        }
        function nbEvtFilter(){
            var q=document.getElementById('nb-evt-search').value.toLowerCase();
            document.querySelectorAll('.nb-spotlight-card').forEach(function(c){
                c.style.display=(!q||c.dataset.title.includes(q))?'':'none';
            });
        }
        var nbCalBase=new Date();
        function nbCalNav(dir){
            nbCalBase=new Date(nbCalBase.getFullYear(),nbCalBase.getMonth()+dir,1);
            var months=['January','February','March','April','May','June','July','August','September','October','November','December'];
            document.getElementById('nb-cal-month').textContent=months[nbCalBase.getMonth()]+' '+nbCalBase.getFullYear();
            var first=(nbCalBase.getDay());
            var days=new Date(nbCalBase.getFullYear(),nbCalBase.getMonth()+1,0).getDate();
            var html='';
            for(var i=0;i<first;i++) html+='<div class="nb-cal-day empty"></div>';
            for(var d=1;d<=days;d++) html+='<div class="nb-cal-day">'+d+'</div>';
            document.getElementById('nb-cal-days').innerHTML=html;
        }
        </script>

        <?php else: ?>
        <div style="background:#fff;border:2px dashed #dde2ec;border-radius:12px;padding:48px;text-align:center;color:#aaa;">
            <i class="fas fa-calendar-alt" style="font-size:36px;display:block;margin-bottom:12px;opacity:.25;"></i>
            <p style="font-size:14px;margin:0;">No events listed for <?= date('F Y') ?> yet.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ⑨ COMING SOON DEVELOPMENTS -->
<section style="padding:60px 0;background:#fff;">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-8"><div class="sec-heading"><h2>Coming Soon Developments</h2><p>Pre-sale and new construction projects tracked in <?= htmlspecialchars($nb['name']) ?>.</p></div></div>
            <div class="col-lg-4 text-lg-end"><a href="half-map.php?neighborhood=<?= urlencode($nb['db_neighborhood']) ?>" class="btn btn-outline-primary btn-sm">View All <i class="fas fa-arrow-right ms-1"></i></a></div>
        </div>
        <?php if (!empty($cs_listings)): ?>
        <div class="row g-4">
            <?php foreach ($cs_listings as $p): $link = (!empty($p['is_paid'])?'concierge-property.php':'single-property-2.php').'?id='.$p['id']; ?>
            <div class="col-lg-4 col-md-6">
                <div class="nb-listing-card">
                    <a href="<?= $link ?>" class="nb-card-img-wrap">
                        <?php if (!empty($p['img1'])): ?><img src="<?= htmlspecialchars($p['img1']) ?>" alt="<?= htmlspecialchars($p['address']) ?>" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy">
                        <?php else: ?><div class="nb-img-ph"><i class="fas fa-building"></i><span><?= htmlspecialchars($p['property_type']) ?></span></div><?php endif; ?>
                        <div class="nb-card-badges"><span class="nb-b-presale">Pre-Sale</span><span class="nb-b-year">Est. <?= htmlspecialchars($p['est_completion']) ?></span></div>
                    </a>
                    <div class="nb-card-body">
                        <a href="<?= $link ?>" style="text-decoration:none;"><div class="nb-card-addr"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['address']) ?></div></a>
                        <div class="nb-card-sub"><i class="fas fa-map"></i> <?= htmlspecialchars($p['neighborhood']) ?></div>
                        <?php if (!empty($p['description'])): ?><div class="nb-card-desc"><?= htmlspecialchars(mb_strimwidth($p['description'],0,100,'...')) ?></div><?php endif; ?>
                        <div class="nb-card-foot">
                            <div><span class="nb-tba-l">Price</span><span class="nb-tba-v">T.B.A.</span></div>
                            <span class="nb-est"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($p['est_completion']) ?></span>
                        </div>
                        <div class="nb-card-acts">
                            <button class="nb-btn-save" onclick="wynSaveProp(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['address'])) ?>', '<?= $link ?>')"><i class="fas fa-heart"></i> Save</button>
                            <button class="nb-btn-share" onclick="wynShare('<?= addslashes(htmlspecialchars($p['address'])) ?>', '<?= $link ?>')"><i class="fas fa-share-alt"></i> Share</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center" style="padding:48px 16px;color:#aaa;">
            <i class="fas fa-hard-hat" style="font-size:40px;display:block;margin-bottom:14px;opacity:.25;"></i>
            <p>No coming soon developments currently tracked in <?= htmlspecialchars($nb['name']) ?>.</p>
            <a href="concierge.php" style="color:#0065ff;font-weight:600;font-size:13px;">Are you a developer? List your project →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ⑩ ACTIVE MLS LISTINGS -->
<section class="gray-simple">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-8"><div class="sec-heading"><h2>Active MLS® Listings</h2><p>Currently listed properties in <?= htmlspecialchars($nb['name']) ?> via MLS®.</p></div></div>
            <div class="col-lg-4 text-lg-end"><a href="active-listings.php" class="btn btn-outline-primary btn-sm">View All <i class="fas fa-arrow-right ms-1"></i></a></div>
        </div>
        <?php if (!empty($active_listings)): ?>
        <div class="row g-4">
            <?php foreach ($active_listings as $p): ?>
            <div class="col-lg-4 col-md-6">
                <div class="nb-listing-card">
                    <a href="active-property.php?id=<?= $p['id'] ?>" class="nb-card-img-wrap">
                        <?php if (!empty($p['img1'])): ?><img src="<?= htmlspecialchars($p['img1']) ?>" alt="<?= htmlspecialchars($p['address']) ?>" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy">
                        <?php else: ?><div class="nb-img-ph"><i class="fas fa-building"></i><span><?= htmlspecialchars($p['building_type']?:$p['property_type']?:'Residential') ?></span></div><?php endif; ?>
                        <div class="nb-card-badges">
                            <span class="nb-b-active">Active</span>
                            <?php if (!empty($p['building_type'])): ?><span class="nb-b-type"><?= htmlspecialchars($p['building_type']) ?></span><?php endif; ?>
                            <span class="nb-b-mls">MLS® <?= htmlspecialchars($p['mls_number']) ?></span>
                        </div>
                    </a>
                    <div class="nb-card-body">
                        <a href="active-property.php?id=<?= $p['id'] ?>" style="text-decoration:none;"><div class="nb-card-addr"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['address']) ?></div></a>
                        <div class="nb-card-sub"><i class="fas fa-map"></i> <?= htmlspecialchars($p['city']) ?></div>
                        <?php if (!empty($p['bedrooms'])||!empty($p['bathrooms'])||!empty($p['sqft'])): ?>
                        <div class="nb-card-specs">
                            <?php if (!empty($p['bedrooms'])): ?><span><i class="fas fa-bed"></i> <?= $p['bedrooms'] ?> bd</span><?php endif; ?>
                            <?php if (!empty($p['bathrooms'])): ?><span><i class="fas fa-bath"></i> <?= $p['bathrooms'] ?> ba</span><?php endif; ?>
                            <?php if (!empty($p['sqft'])): ?><span><i class="fas fa-ruler-combined"></i> <?= number_format($p['sqft']) ?> sf</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="nb-card-foot">
                            <div><span class="nb-tba-l">Price</span><span class="nb-act-price"><?= htmlspecialchars($p['price_formatted']) ?></span></div>
                            <a href="active-property.php?id=<?= $p['id'] ?>" class="nb-btn-view">View <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="font-size:10px;color:#aaa;margin-top:20px;border-top:1px solid #e5e7eb;padding-top:14px;line-height:1.6;">The data relating to real estate on this website comes in part from the MLS® Reciprocity program of the Real Estate Board of Greater Vancouver. Real estate listings held by participating real estate firms are marked with the MLS® logo.</p>
        <?php else: ?>
        <div class="text-center" style="padding:48px 16px;color:#aaa;">
            <i class="fas fa-key" style="font-size:40px;display:block;margin-bottom:14px;opacity:.25;"></i>
            <p>No active MLS® listings currently found in <?= htmlspecialchars($nb['name']) ?>.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="bg-primary call-to-act-wrap">
    <div class="container">
        <?php include "$base_dir/Components/Home/estate-agent.php"; ?>
    </div>
</section>


<style>
/* ── Hero ── */
.nb-detail-hero{position:relative;height:520px;display:flex;align-items:center;background-size:cover;background-position:center 40%;}
.nb-detail-hero-overlay{position:absolute;inset:0;background:linear-gradient(160deg,rgba(0,15,40,.90) 0%,rgba(0,40,90,.70) 100%);}
.nb-detail-back{display:inline-flex;align-items:center;font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-decoration:none;margin-bottom:16px;letter-spacing:.3px;transition:color .2s;}
.nb-detail-back:hover{color:#fff;}
.nb-detail-title{font-size:clamp(40px,7vw,78px);font-weight:900;color:#fff;line-height:1.0;margin:0 0 12px;letter-spacing:-2px;}
.nb-detail-subtitle{font-size:13px;color:rgba(255,255,255,.55);font-weight:600;letter-spacing:.8px;text-transform:uppercase;margin:0;}

/* ── Section title ── */
.nb-section-title{font-size:22px;font-weight:800;color:#002446;margin:0 0 4px;letter-spacing:-.3px;}

/* ── Description + Agent ── */
.nb-desc-agent-wrap{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;}
@media(max-width:900px){.nb-desc-agent-wrap{grid-template-columns:1fr;}}
.nb-about-card{background:#fff;border-radius:14px;padding:28px 32px;box-shadow:0 2px 12px rgba(0,36,70,.06);}
.nb-about-title{font-size:20px;font-weight:800;color:#002446;margin:0 0 14px;}
.nb-about-body{font-size:15px;color:#555;line-height:1.9;margin:0;}
.nb-agent-card{background:#002446;border-radius:14px;padding:22px 24px;}
.nb-agent-inner{display:flex;align-items:center;gap:14px;margin-bottom:16px;}
.nb-agent-img{width:62px;height:62px;border-radius:50%;object-fit:cover;border:3px solid #0065ff;flex-shrink:0;}
.nb-agent-info h4{color:#fff;font-size:16px;font-weight:800;margin:0 0 3px;}
.nb-agent-info p{color:rgba(255,255,255,.6);font-size:12px;margin:0 0 8px;}
.nb-agent-phone{display:block;color:#fff;font-size:13px;font-weight:700;text-decoration:none;}
.nb-agent-cta{display:block;background:#0065ff;color:#fff;border-radius:8px;padding:12px;font-size:13px;font-weight:700;text-decoration:none;text-align:center;margin-top:0;}
.nb-agent-cta:hover{background:#0052cc;color:#fff;}

/* ── Livability + Market side-by-side ── */
.nb-liv-wrap{display:grid;grid-template-columns:1fr 1.6fr;gap:24px;align-items:start;}
@media(max-width:900px){.nb-liv-wrap{grid-template-columns:1fr;}}
.nb-liv-card{background:#fff;border-radius:14px;padding:28px 28px 24px;box-shadow:0 2px 12px rgba(0,36,70,.06);}
.nb-gauges-row{display:flex;gap:20px;justify-content:space-around;margin:20px 0 16px;}
.nb-gauge{display:flex;flex-direction:column;align-items:center;gap:6px;position:relative;}
.nb-gauge-svg{width:88px;height:88px;}
.nb-gauge-inner{position:absolute;top:12px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;justify-content:center;width:88px;height:64px;}
.nb-gauge-val{font-size:22px;font-weight:900;line-height:1;}
.nb-gauge-label{font-size:13px;font-weight:700;color:#002446;}
.nb-gauge-icon{font-size:14px;}
.nb-stat-pills-row{display:flex;gap:12px;flex-wrap:wrap;border-top:1px solid #f0f4ff;padding-top:16px;}
.nb-stat-item{flex:1;min-width:80px;text-align:center;}
.nb-stat-item strong{display:block;font-size:16px;font-weight:800;color:#002446;}
.nb-stat-item span{display:block;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;}

/* ── Market snapshot card ── */
.nb-market-card{background:#fff;border-radius:14px;padding:24px 28px;box-shadow:0 2px 12px rgba(0,36,70,.06);}
.nb-market-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;}
.nb-market-tabs{display:flex;gap:4px;background:#f4f6fb;border-radius:8px;padding:4px;}
.nb-mkt-tab{background:transparent;border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;color:#555;cursor:pointer;transition:all .2s;}
.nb-mkt-tab.active{background:#0065ff;color:#fff;}
.nb-hpi-hero{margin-bottom:12px;}
.nb-hpi-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#aaa;margin-bottom:4px;}
.nb-hpi-val{font-size:28px;font-weight:900;color:#002446;letter-spacing:-1px;line-height:1.1;}
.nb-hpi-changes{display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;}
.nb-chg{font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;}
.nb-chg.pos{background:rgba(22,163,74,.12);color:#16a34a;}
.nb-chg.neg{background:rgba(220,38,38,.10);color:#dc2626;}
.nb-price-row{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:12px;}
@media(max-width:700px){.nb-price-row{grid-template-columns:repeat(2,1fr);}}
.nb-price-cell{background:#f4f6fb;border-radius:8px;padding:10px 12px;border:1px solid #eaeef5;}
.nb-price-cell-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa;margin-bottom:4px;}
.nb-price-cell-val{font-size:14px;font-weight:800;color:#002446;}

/* ── Schools ── */
.nb-school-card2{background:#fff;border:1px solid #eaeef5;border-radius:12px;padding:20px;height:100%;}
.nb-school2-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
.nb-sch-badge{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:3px 10px;border-radius:20px;}
.nb-sch-badge.elementary{background:#dbeafe;color:#1d4ed8;}
.nb-sch-badge.secondary{background:#dcfce7;color:#15803d;}
.nb-sch-badge.french\ immersion{background:#fce7f3;color:#9d174d;}
.nb-sch-name{font-size:14px;font-weight:700;color:#002446;margin:0 0 12px;line-height:1.4;}
.nb-sch-rating{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
.nb-r-track{flex:1;background:#e5e7eb;border-radius:4px;height:6px;overflow:hidden;}
.nb-r-fill{height:100%;border-radius:4px;}
.nb-sch-links{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #eaeef5;padding-top:10px;}

/* ── Amenities layout ── */
.nb-amenities-wrap{display:grid;grid-template-columns:180px 1fr 260px;gap:0;border-top:1px solid #eaeef5;border-bottom:1px solid #eaeef5;background:#fff;box-shadow:0 2px 12px rgba(0,36,70,.06);width:100%;}
@media(max-width:900px){.nb-amenities-wrap{grid-template-columns:1fr;}}
.nb-cat-sidebar{background:#f8f9fc;border-right:1px solid #eaeef5;padding:20px 12px;display:flex;flex-direction:column;gap:4px;}
.nb-cat-btn{display:flex;align-items:center;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:#555;cursor:pointer;transition:all .2s;user-select:none;}
.nb-cat-btn:hover{background:#e8f0ff;color:#0065ff;}
.nb-cat-btn.active{background:#e8f0ff;color:#0065ff;font-weight:700;}
.nb-cat-btn i{width:16px;text-align:center;}
.nb-amenities-map-wrap{position:relative;min-height:480px;border-right:1px solid #eaeef5;}
/* Custom map tooltip */
#nb-map-tooltip{
    display:none;
    position:absolute;
    z-index:999;
    background:#fff;
    border-radius:10px;
    padding:12px 14px;
    box-shadow:0 4px 20px rgba(0,0,0,.18);
    min-width:200px;
    max-width:240px;
    pointer-events:auto;
    font-family:system-ui,sans-serif;
    border:1px solid #eaeef5;
}
#nb-map-tooltip::after{
    content:'';
    position:absolute;
    bottom:-8px;
    left:50%;
    transform:translateX(-50%);
    border-left:8px solid transparent;
    border-right:8px solid transparent;
    border-top:8px solid #fff;
    filter:drop-shadow(0 2px 2px rgba(0,0,0,.08));
}
.nb-map-caption-bar{position:absolute;bottom:0;left:0;right:0;background:rgba(255,255,255,.92);padding:8px 14px;font-size:11px;color:#888;border-top:1px solid #eaeef5;}
.nb-facilities-list{padding:20px 16px;overflow-y:auto;max-height:480px;}
.nb-facilities-list-title{font-size:14px;font-weight:800;color:#002446;margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid #f0f4ff;}
.nb-fac-item{display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid #f4f6fb;}
.nb-fac-item:last-child{border-bottom:none;}
.nb-fac-img-placeholder{width:64px;height:52px;background:#f0f4ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.nb-fac-info{flex:1;min-width:0;}
.nb-fac-name{font-size:13px;font-weight:700;color:#002446;margin-bottom:3px;line-height:1.3;}
.nb-fac-type{font-size:11px;color:#aaa;margin-bottom:4px;}
.nb-fac-open{font-size:10px;font-weight:700;color:#16a34a;background:#dcfce7;border-radius:10px;padding:2px 8px;}
.nb-fac-btn{flex-shrink:0;font-size:11px;font-weight:700;color:#002446;border:1px solid #dde2ec;background:#fff;border-radius:6px;padding:5px 10px;text-decoration:none;white-space:nowrap;align-self:flex-end;}
.nb-fac-btn:hover{background:#f0f4ff;color:#0065ff;}

/* ── Events calendar ── */
.nb-cal-bar{display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.nb-cal-search{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #dde2ec;border-radius:8px;padding:0 14px;flex:1;min-width:200px;}
.nb-cal-search i{color:#aaa;font-size:13px;}
.nb-cal-search input{border:none;outline:none;font-size:14px;color:#555;background:transparent;padding:10px 0;width:100%;}
.nb-cal-filters{display:flex;gap:8px;flex-wrap:wrap;}
.nb-cal-filter{background:#fff;border:1px solid #dde2ec;border-radius:20px;padding:7px 16px;font-size:12px;font-weight:600;color:#555;cursor:pointer;transition:all .2s;}
.nb-cal-filter.active{background:#0065ff;border-color:#0065ff;color:#fff;}
.nb-events-layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;}
@media(max-width:800px){.nb-events-layout{grid-template-columns:1fr;}}
.nb-calendar-wrap{background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 12px rgba(0,36,70,.06);}
.nb-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.nb-cal-arrow{background:none;border:1px solid #dde2ec;border-radius:6px;width:30px;height:30px;font-size:18px;cursor:pointer;color:#555;display:flex;align-items:center;justify-content:center;}
.nb-cal-month-label{font-size:15px;font-weight:800;color:#002446;}
.nb-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:8px;}
.nb-cal-dow{text-align:center;font-size:11px;font-weight:700;color:#aaa;padding:4px 0;}
.nb-cal-days{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
.nb-cal-day{text-align:center;padding:8px 4px;font-size:13px;color:#555;border-radius:6px;cursor:pointer;position:relative;transition:background .15s;}
.nb-cal-day:hover{background:#f0f4ff;color:#0065ff;}
.nb-cal-day.today{background:#002446;color:#fff;font-weight:800;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;margin:0 auto;padding:0;}
.nb-cal-day.empty{cursor:default;}
.nb-cal-dot{display:block;width:5px;height:5px;background:#0065ff;border-radius:50%;margin:2px auto 0;}
.nb-spotlight-wrap{display:flex;flex-direction:column;gap:12px;}
.nb-spotlight-title{font-size:16px;font-weight:800;color:#002446;margin:0 0 4px;}
.nb-spotlight-card{background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 2px 10px rgba(0,36,70,.07);display:flex;gap:14px;align-items:flex-start;}
.nb-spot-date{background:#0065ff;border-radius:10px;padding:8px 12px;text-align:center;flex-shrink:0;min-width:52px;}
.nb-spot-mon{display:block;font-size:10px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.5px;}
.nb-spot-day{display:block;font-size:22px;font-weight:900;color:#fff;line-height:1.1;}
.nb-spot-body{flex:1;}
.nb-spot-title{font-size:14px;font-weight:700;color:#002446;margin-bottom:4px;line-height:1.4;}
.nb-spot-time{font-size:12px;color:#888;margin-bottom:3px;}
.nb-spot-loc{font-size:12px;color:#888;}
.nb-spot-link{display:inline-flex;align-items:center;gap:6px;background:#0065ff;color:#fff;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:700;text-decoration:none;}
.nb-spot-link:hover{background:#0052cc;color:#fff;}
.nb-events-toggle{background:transparent;border:2px solid #002446;color:#002446;border-radius:30px;padding:10px 28px;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;margin-top:8px;width:100%;}
.nb-events-toggle:hover{background:#002446;color:#fff;}

/* ── Stats Can Demographics ── */
.nb-demo-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:20px;padding-top:18px;border-top:1px solid #f0f4ff;}
.nb-demo-card{background:#f8f9fc;border-radius:10px;padding:14px 14px 10px;border:1px solid #eaeef5;}
.nb-demo-title{font-size:11px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;}
.nb-gender-bar-wrap{display:flex;align-items:center;gap:8px;margin:8px 0 4px;}
.nb-gender-bar{flex:1;height:10px;border-radius:4px;display:flex;overflow:hidden;}
.nb-gender-lbl{font-size:9px;font-weight:700;color:#aaa;letter-spacing:.3px;white-space:nowrap;}
.nb-gender-pct{font-size:14px;font-weight:800;color:#002446;text-align:center;}
.nb-lang-row{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f0f2f5;}
.nb-lang-row:last-child{border-bottom:none;}
.nb-lang-name{font-size:13px;font-weight:600;color:#002446;}
.nb-lang-flag{font-size:16px;}
.nb-demo-row{display:flex;align-items:center;padding:3px 0;}
.nb-demo-row-lbl{font-size:11px;color:#555;flex:1;}
.nb-demo-row-val{font-size:11px;font-weight:700;color:#002446;margin-left:6px;}
.nb-listing-card{background:#fff;border:1px solid #eaeef5;border-radius:10px;overflow:hidden;transition:box-shadow .2s;height:100%;}
.nb-listing-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.09);}
.nb-card-img-wrap{display:block;position:relative;overflow:hidden;}
.nb-img-ph{width:100%;height:200px;background:linear-gradient(135deg,#002446,#003a6e);display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,.7);gap:8px;}
.nb-img-ph i{font-size:38px;opacity:.5;}
.nb-card-badges{position:absolute;top:10px;left:10px;display:flex;gap:5px;flex-wrap:wrap;z-index:2;}
.nb-b-presale{background:#0065ff;color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:4px;text-transform:uppercase;}
.nb-b-year{background:rgba(0,0,0,.5);color:#fff;font-size:10px;padding:3px 8px;border-radius:4px;}
.nb-b-active{background:#16a34a;color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:4px;}
.nb-b-type{background:rgba(0,0,0,.45);color:#fff;font-size:10px;padding:3px 7px;border-radius:4px;}
.nb-b-mls{background:rgba(0,0,0,.35);color:#fff;font-size:9px;padding:3px 7px;border-radius:4px;}
.nb-card-body{padding:14px 16px;}
.nb-card-addr{font-size:13px;font-weight:700;color:#002446;margin-bottom:4px;}
.nb-card-addr i,.nb-card-sub i{color:#0065ff;margin-right:4px;font-size:11px;}
.nb-card-sub{font-size:12px;color:#888;margin-bottom:8px;}
.nb-card-desc{font-size:12px;color:#888;line-height:1.5;margin-bottom:10px;border-top:1px solid #f0f2f5;padding-top:8px;}
.nb-card-specs{display:flex;gap:10px;font-size:12px;color:#555;margin-bottom:10px;flex-wrap:wrap;}
.nb-card-specs i{color:#0065ff;margin-right:3px;}
.nb-card-foot{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f0f2f5;padding-top:10px;margin-top:4px;}
.nb-tba-l{display:block;font-size:10px;text-transform:uppercase;color:#aaa;letter-spacing:.5px;}
.nb-tba-v{display:block;font-size:20px;font-weight:800;color:#002446;letter-spacing:1px;}
.nb-act-price{display:block;font-size:17px;font-weight:800;color:#002446;}
.nb-est{font-size:12px;color:#555;background:#f0f4ff;padding:5px 10px;border-radius:6px;}
.nb-est i{color:#0065ff;margin-right:4px;}
.nb-btn-view{display:inline-flex;align-items:center;background:#f0f4ff;color:#0065ff;border-radius:6px;padding:7px 12px;font-size:12px;font-weight:700;text-decoration:none;}
.nb-btn-view:hover{background:#0065ff;color:#fff;}
.nb-card-acts{display:flex;gap:8px;margin-top:10px;}
.nb-btn-save{display:inline-flex;align-items:center;gap:5px;background:#002446;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:700;padding:7px 12px;cursor:pointer;transition:background .2s;}
.nb-btn-save:hover{background:#dc2626;}
.nb-btn-share{display:inline-flex;align-items:center;gap:5px;background:#f4f6fb;color:#555;border:none;border-radius:6px;font-size:11px;font-weight:600;padding:7px 10px;cursor:pointer;}
.nb-btn-share:hover{background:#e8ecf5;}
</style>

<?php if ($has_history): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    var chartData = {
        labels:    <?= json_encode($chart_labels) ?>,
        hpi:       <?= json_encode($chart_avg) ?>,
        detached:  <?= json_encode($chart_det) ?>,
        condo:     <?= json_encode($chart_condo) ?>,
        townhouse: <?= json_encode($chart_town) ?>,
        duplex:    <?= json_encode($chart_dup) ?>
    };

    var currentPrices = {
        detached:  <?= (int)($nb['price_detached']  ?? 0) ?>,
        condo:     <?= (int)($nb['price_condo']      ?? 0) ?>,
        townhouse: <?= (int)($nb['price_townhouse']  ?? 0) ?>,
        duplex:    <?= (int)($nb['price_duplex']     ?? 0) ?>
    };

    var typeLabels = {
        detached:'DETACHED PRICE', condo:'CONDO PRICE',
        townhouse:'TOWNHOUSE PRICE', duplex:'DUPLEX / MULTIPLEX PRICE'
    };

    var typeColors = {
        detached:'#0065ff', condo:'#16a34a', townhouse:'#dc2626', duplex:'#f59e0b'
    };

    var ctx = document.getElementById('nbChart');
    if (!ctx) return;

    var nbChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'HPI Benchmark',
                    data: chartData.hpi,
                    borderColor: '#002446',
                    backgroundColor: 'rgba(0,36,70,.07)',
                    tension: .4, pointRadius: 3, borderWidth: 2.5, fill: true
                },
                {
                    label: 'Detached',
                    data: chartData.detached,
                    borderColor: '#0065ff',
                    backgroundColor: 'transparent',
                    tension: .4, pointRadius: 3, borderWidth: 2, fill: false
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                tooltip: { callbacks: { label: function(c) { return ' ' + c.dataset.label + ': $' + c.parsed.y.toLocaleString(); } } }
            },
            scales: {
                y: { ticks: { callback: function(v) { return '$' + (v/1000).toFixed(0) + 'k'; }, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.04)' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });

    window.nbMktTab = function(btn, type) {
        document.querySelectorAll('.nb-mkt-tab').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        nbChart.data.datasets[1].data        = chartData[type];
        nbChart.data.datasets[1].borderColor = typeColors[type];
        nbChart.data.datasets[1].label       = btn.textContent.trim();
        nbChart.update();
        var price = currentPrices[type];
        var valEl = document.getElementById('nb-hpi-val');
        var lblEl = document.getElementById('nb-hpi-label');
        if (valEl) valEl.textContent = price > 0 ? '$' + price.toLocaleString() : 'N/A';
        if (lblEl) lblEl.textContent = typeLabels[type];
    };
});
</script>
<?php else: ?>
<script>
window.nbMktTab = function(btn, type) {
    document.querySelectorAll('.nb-mkt-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
};
</script>
<?php endif; ?>

<script>
function wynShare(address, url) {
    var full = window.location.origin + '/' + url;
    if (navigator.share) { navigator.share({ title: address + ' — Wynston', url: full }); }
    else { navigator.clipboard.writeText(full).then(function() { alert('Link copied!'); }); }
}
</script>
<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>