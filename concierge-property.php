<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';

ob_start();
include "$base_dir/navbar.php";
$navlink_content = ob_get_clean();
$page  = 'nav';
$fpage = 'foot';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: half-map.php'); exit; }

require_once "$base_dir/db.php";

$cache_dir  = __DIR__ . '/cache/';
$cache_file = $cache_dir . 'property_' . $id . '.json';
$cache_ttl  = 300;
$p = null;
if (is_dir($cache_dir) && file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $p = json_decode(file_get_contents($cache_file), true);
}
if (!$p) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM multi_2025 WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p && is_dir($cache_dir) && is_writable($cache_dir)) {
            file_put_contents($cache_file, json_encode($p));
        }
    } catch (PDOException $e) {
        die("<div style='padding:60px;text-align:center'><h3>Database error.</h3></div>");
    }
}
if (!$p) {
    die("<div style='padding:60px;text-align:center'><h3>Property not found.</h3><a href='half-map.php'>Back</a></div>");
}

$photos = [];
for ($i = 1; $i <= 6; $i++) {
    if (!empty($p['img'.$i])) $photos[] = $p['img'.$i];
}
$has_photos = count($photos) > 0;

$awards    = !empty($p['builder_awards']) ? array_filter(array_map('trim', explode("\n", $p['builder_awards']))) : [];
$features  = !empty($p['features'])  ? array_filter(array_map('trim', explode(',', $p['features'])))  : [];
$amenities = !empty($p['amenities']) ? array_filter(array_map('trim', explode(',', $p['amenities']))) : [];

$other_listings = [];
try {
    $os = $pdo->prepare("SELECT id, address, neighborhood, property_type, est_completion, img1, price FROM multi_2025 WHERE id != :id AND is_paid = 1 ORDER BY id DESC LIMIT 4");
    $os->execute([':id' => $id]);
    $other_listings = $os->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$neighbourhood = strtolower(trim($p['neighborhood'] ?? ''));
$catchment_map = [
    'sunset'               => ['elementary' => 'Sir Richard McBride Elementary',    'secondary' => 'John Oliver Secondary'],
    'kitsilano'            => ['elementary' => 'Carnarvon Elementary',              'secondary' => 'Kitsilano Secondary'],
    'south cambie'         => ['elementary' => 'Emily Carr Elementary',             'secondary' => 'Eric Hamber Secondary'],
    'hastings-sunrise'     => ['elementary' => 'Hastings Elementary',               'secondary' => 'Britannia Secondary'],
    'renfrew-collingwood'  => ['elementary' => 'Renfrew Elementary',                'secondary' => 'Windermere Secondary'],
    'collingwood'          => ['elementary' => 'Renfrew Elementary',                'secondary' => 'Windermere Secondary'],
    'kerrisdale'           => ['elementary' => 'Kerrisdale Elementary',             'secondary' => 'Magee Secondary'],
    'mount pleasant'       => ['elementary' => 'Mount Pleasant Elementary',         'secondary' => 'Britannia Secondary'],
    'fairview'             => ['elementary' => 'Simon Fraser Elementary',           'secondary' => 'Eric Hamber Secondary'],
    'west end'             => ['elementary' => 'Lord Roberts Elementary',           'secondary' => 'King Edward Secondary'],
    'downtown'             => ['elementary' => 'Lord Roberts Elementary',           'secondary' => 'King Edward Secondary'],
    'coal harbour'         => ['elementary' => 'Lord Roberts Elementary',           'secondary' => 'King Edward Secondary'],
    'marpole'              => ['elementary' => 'David Oppenheimer Elementary',      'secondary' => 'Sir Winston Churchill Secondary'],
    'oakridge'             => ['elementary' => 'Laurier Elementary',                'secondary' => 'Sir Winston Churchill Secondary'],
    'killarney'            => ['elementary' => 'Carleton Elementary',               'secondary' => 'Killarney Secondary'],
    'west point grey'      => ['elementary' => 'Queen Mary Elementary',             'secondary' => 'Lord Byng Secondary'],
    'riley park'           => ['elementary' => 'Sir Charles Tupper Elementary',     'secondary' => 'Sir Charles Tupper Secondary'],
    'little mountain'      => ['elementary' => 'Sir Charles Tupper Elementary',     'secondary' => 'Sir Charles Tupper Secondary'],
    'shaughnessy'          => ['elementary' => 'Shaughnessy Elementary',            'secondary' => 'Eric Hamber Secondary'],
    'arbutus'              => ['elementary' => 'Carnarvon Elementary',              'secondary' => 'Kitsilano Secondary'],
    'arbutus ridge'        => ['elementary' => 'Carnarvon Elementary',              'secondary' => 'Kitsilano Secondary'],
    'dunbar'               => ['elementary' => 'Dunbar Elementary',                 'secondary' => 'Lord Byng Secondary'],
    'strathcona'           => ['elementary' => 'Lord Strathcona Elementary',        'secondary' => 'Britannia Secondary'],
    'grandview'            => ['elementary' => 'Grandview Elementary',              'secondary' => 'Britannia Secondary'],
    'kensington'           => ['elementary' => 'Kensington Elementary',             'secondary' => 'John Oliver Secondary'],
    'cedar cottage'        => ['elementary' => 'Kensington Elementary',             'secondary' => 'John Oliver Secondary'],
    'victoria'             => ['elementary' => 'Selkirk Elementary',                'secondary' => 'John Oliver Secondary'],
    'fraserview'           => ['elementary' => 'David Oppenheimer Elementary',      'secondary' => 'Killarney Secondary'],
    'champlain heights'    => ['elementary' => 'Champlain Heights Elementary',      'secondary' => 'Killarney Secondary'],
    'east vancouver'       => ['elementary' => 'Grandview Elementary',              'secondary' => 'Britannia Secondary'],
    'south vancouver'      => ['elementary' => 'David Oppenheimer Elementary',      'secondary' => 'Sir Winston Churchill Secondary'],
    'north vancouver'      => ['elementary' => 'Dorothy Lynas Elementary',          'secondary' => 'Carson Graham Secondary'],
    'burnaby'              => ['elementary' => 'Moscrop Secondary',                 'secondary' => 'Moscrop Secondary'],
    'richmond'             => ['elementary' => 'Thompson Elementary',               'secondary' => 'Hugh Boyd Secondary'],
    'surrey'               => ['elementary' => 'Forsyth Road Elementary',           'secondary' => 'Fleetwood Park Secondary'],
    'coquitlam'            => ['elementary' => 'Hillcrest Middle',                  'secondary' => 'Centennial Secondary'],
    'maple ridge'          => ['elementary' => 'Yennadon Elementary',               'secondary' => 'Thomas Haney Secondary'],
    'yennadon'             => ['elementary' => 'Yennadon Elementary',               'secondary' => 'Thomas Haney Secondary'],
    'langley'              => ['elementary' => 'Nicomekl Elementary',               'secondary' => 'Langley Secondary'],
    'abbotsford'           => ['elementary' => 'Babich Elementary',                 'secondary' => 'Abbotsford Senior Secondary'],
    'port moody'           => ['elementary' => 'Seaview Elementary',                'secondary' => 'Port Moody Secondary'],
    'port coquitlam'       => ['elementary' => 'Riverside Elementary',              'secondary' => 'Riverside Secondary'],
    'new westminster'      => ['elementary' => 'Connaught Heights Elementary',      'secondary' => 'New Westminster Secondary'],
    'delta'                => ['elementary' => 'Gray Elementary',                   'secondary' => 'South Delta Secondary'],
    'white rock'           => ['elementary' => 'White Rock Elementary',             'secondary' => 'Earl Marriott Secondary'],
    'pitt meadows'         => ['elementary' => 'Pitt Meadows Elementary',           'secondary' => 'Pitt Meadows Secondary'],
];
$schools = null;
// Only match if the neighbourhood CONTAINS the key (not the other way round — prevents false matches)
foreach ($catchment_map as $key => $val) {
    if (str_contains($neighbourhood, $key)) { $schools = $val; break; }
}
// Fallback: try partial word match for compound names
if (!$schools && $neighbourhood) {
    foreach ($catchment_map as $key => $val) {
        $words = explode(' ', $key);
        foreach ($words as $word) {
            if (strlen($word) > 4 && str_contains($neighbourhood, $word)) {
                $schools = $val; break 2;
            }
        }
    }
}

ob_start();
?>

<!-- HERO -->
<script>var lvPhotos = <?= json_encode($photos) ?>; var lvPhotoIdx = 0;</script>
<div class="lv-hero">
    <div class="lv-hero-img" id="lv-hero-img">
        <?php if (!$has_photos): ?>
        <div class="lv-hero-ph"><i class="fas fa-building"></i><span>Photos Coming Soon</span></div>
        <?php endif; ?>
    </div>

    <?php if ($has_photos): ?>
    <!-- Left/right arrows for photo navigation -->
    <button class="lv-arrow lv-arrow-left" onclick="heroPrev()" aria-label="Previous photo">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="lv-arrow lv-arrow-right" onclick="heroNext()" aria-label="Next photo">
        <i class="fas fa-chevron-right"></i>
    </button>
    <!-- Photo counter -->
    <div class="lv-photo-counter" id="lv-photo-counter">1 / <?= count($photos) ?></div>
    <?php endif; ?>

    <!-- Floating pill bar — centered over photo, above thumbnails -->
    <div class="lv-hero-bar" id="lv-hero-bar">
        <button class="lv-hbar-btn on" id="lv-hbar-photos" onclick="heroShowPhotos()">
            <i class="fas fa-camera"></i> Photos <?= count($photos) ?>
        </button>
        <?php if (!empty($p['video_url'])): ?>
        <button class="lv-hbar-btn" id="lv-hbar-video" onclick="heroShowVideo()">
            <i class="fas fa-play"></i> Videos
        </button>
        <?php endif; ?>
        <button class="lv-hbar-btn" id="lv-hbar-map" onclick="heroShowMap()">
            <i class="fas fa-map-marked-alt"></i> Map
        </button>
        <?php if (!empty($p['virtual_tour_url'])): ?>
        <button class="lv-hbar-btn" id="lv-hbar-tour" onclick="heroShowTour()">
            <i class="fas fa-vr-cardboard"></i> Virtual tour
        </button>
        <?php endif; ?>
    </div>

    <!-- Inline video overlay -->
    <?php if (!empty($p['video_url'])): ?>
    <?php
    $embed = $p['video_url'];
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $embed, $m))  $embed = 'https://www.youtube.com/embed/'.$m[1].'?autoplay=1&rel=0';
    elseif (preg_match('/youtu\.be\/([^?]+)/', $embed, $m))           $embed = 'https://www.youtube.com/embed/'.$m[1].'?autoplay=1';
    elseif (preg_match('/vimeo\.com\/(\d+)/', $embed, $m))            $embed = 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1';
    ?>
    <div id="lv-overlay-video" class="lv-hero-overlay" style="display:none;">
        <iframe id="lv-video-iframe" src="" style="width:100%;height:100%;border:0;" allowfullscreen allow="autoplay"></iframe>
    </div>
    <span id="lv-video-src" style="display:none"><?= htmlspecialchars($embed) ?></span>
    <?php endif; ?>

    <!-- Inline virtual tour overlay -->
    <?php if (!empty($p['virtual_tour_url'])): ?>
    <?php
    $tour_embed = trim($p['virtual_tour_url']);
    // Convert watch URLs to embed URLs
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $tour_embed, $m))  $tour_embed = 'https://www.youtube.com/embed/'.$m[1].'?autoplay=1&rel=0';
    elseif (preg_match('/youtu\.be\/([^?]+)/', $tour_embed, $m))           $tour_embed = 'https://www.youtube.com/embed/'.$m[1].'?autoplay=1';
    elseif (preg_match('/vimeo\.com\/(\d+)/', $tour_embed, $m))            $tour_embed = 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1';
    // Matterport: show/?m=XXX → embed URL
    elseif (preg_match('/matterport\.com\/show\/\?m=([^&]+)/', $tour_embed, $m)) $tour_embed = 'https://my.matterport.com/show/?m='.$m[1].'&play=1';
    ?>
    <div id="lv-overlay-tour" class="lv-hero-overlay" style="display:none;">
        <iframe id="lv-tour-iframe" src="" style="width:100%;height:100%;border:0;" allowfullscreen allow="autoplay; xr-spatial-tracking" allowvr></iframe>
    </div>
    <span id="lv-tour-src" style="display:none"><?= htmlspecialchars($tour_embed) ?></span>
    <?php endif; ?>

    <!-- Inline map overlay -->
    <div id="lv-overlay-map" class="lv-hero-overlay" style="display:none;">
        <iframe
            src="https://www.google.com/maps?q=<?= urlencode(($p['address'] ?? '').',Vancouver,BC') ?>&output=embed"
            style="width:100%;height:100%;border:0;" loading="lazy" allowfullscreen>
        </iframe>
    </div>

</div><!-- /lv-hero -->

<!-- TITLE (below hero, on white) -->
<div class="lv-title-block">
    <div class="lv-wrap">
        <div class="lv-title-row">
            <div>
                <img src="/assets/img/Wynnston Concierge.png" class="lv-blogo" alt="Wynnston Concierge">
                <h1 class="lv-h1"><?= htmlspecialchars($p['address']) ?></h1>
                <p class="lv-by">
                    <?php
                    $dev_name = !empty($p['developer_name']) ? $p['developer_name'] : (!empty($p['builder_website']) ? 'Developer' : null);
                    if ($dev_name): ?>
                    By <?php if (!empty($p['builder_website'])): ?><a href="<?= htmlspecialchars($p['builder_website']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($dev_name) ?></a><?php else: ?><strong><?= htmlspecialchars($dev_name) ?></strong><?php endif; ?>
                    <?php else: ?>Pre-Sale Listing<?php endif; ?>
                </p>
                <nav class="lv-crumb">
                    <a href="half-map.php">Listings</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($p['neighborhood']) ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars($p['address']) ?></span>
                </nav>
            </div>
            <div class="lv-title-actions">
                <button class="lv-btn-ghost" onclick="wynOpenNotify(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['address'])) ?>')">
                    <i class="fas fa-bell me-1"></i>Get Updates
                </button>
                <button class="wyn-share-btn" onclick="wynShare('<?= addslashes(htmlspecialchars($p['address'])) ?>', 'concierge-property.php?id=<?= $p['id'] ?>')">
                    <i class="fas fa-share-alt me-1"></i>Share
                </button>
                <a href="#lv-contact" class="lv-btn-dark">Request Info</a>
            </div>
        </div>
    </div>
</div>

<!-- STATUS BADGES -->
<div class="lv-badges-row">
    <div class="lv-wrap lv-badges-inner">
        <span class="lv-badge yellow">Pre-Sale</span>
        <span class="lv-badge blue">Est. <?= htmlspecialchars($p['est_completion']) ?></span>
        <?php if (!empty($p['property_type'])): ?><span class="lv-badge gray"><?= htmlspecialchars($p['property_type']) ?></span><?php endif; ?>
    </div>
</div>

<!-- ICON SPECS ROW -->
<div class="lv-specs-bar">
    <div class="lv-wrap">
        <div class="lv-specs-row">
            <div class="lv-spec"><i class="fas fa-tag"></i><strong><?= !empty($p['price']) ? htmlspecialchars($p['price']) : 'Pricing coming soon' ?></strong><span>Price CAD</span></div>
            <div class="lv-spec-div"></div>
            <div class="lv-spec"><i class="fas fa-map-marker-alt"></i><strong><?= htmlspecialchars($p['neighborhood']) ?></strong><span>Neighbourhood</span></div>
            <div class="lv-spec-div"></div>
            <div class="lv-spec"><i class="fas fa-home"></i><strong><?= htmlspecialchars($p['property_type']) ?></strong><span>Type</span></div>
            <?php if (!empty($p['bedrooms'])): ?>
            <div class="lv-spec-div"></div>
            <div class="lv-spec"><i class="fas fa-bed"></i><strong><?= $p['bedrooms'] ?></strong><span>Bedrooms</span></div>
            <?php endif; ?>
            <?php if (!empty($p['sqft'])): ?>
            <div class="lv-spec-div"></div>
            <div class="lv-spec"><i class="fas fa-ruler-combined"></i><strong><?= number_format($p['sqft']) ?></strong><span>SqFt</span></div>
            <?php endif; ?>
            <?php if (!empty($p['bathrooms'])): ?>
            <div class="lv-spec-div"></div>
            <div class="lv-spec"><i class="fas fa-bath"></i><strong><?= $p['bathrooms'] ?></strong><span>Bathrooms</span></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- OVERVIEW + DETAILS TABLE -->
<section class="lv-overview-section" id="lv-overview">
    <div class="lv-wrap">
        <div class="lv-overview-grid">
            <div class="lv-ov-left">
                <h2 class="lv-serif-h">Overview</h2>
                <?php if (!empty($p['description'])): ?>
                <div class="lv-desc"><?= nl2br(htmlspecialchars($p['description'])) ?></div>
                <?php else: ?>
                <p class="lv-desc" style="color:#aaa;font-style:italic;">Description coming soon from the developer.</p>
                <?php endif; ?>
            </div>
            <div class="lv-ov-right" id="lv-details">
                <h3 class="lv-details-h"><?= htmlspecialchars($p['address']) ?> details</h3>
                <table class="lv-dtable">
                    <?php if (!empty($p['neighborhood'])): ?><tr><td>Neighbourhood</td><td><?= htmlspecialchars($p['neighborhood']) ?></td></tr><?php endif; ?>
                    <tr><td>Listing status</td><td><span class="lv-dtbadge">Pre-Sale</span></td></tr>
                    <tr><td>Est. Completion</td><td><?= htmlspecialchars($p['est_completion']) ?></td></tr>
                    <?php if (!empty($p['property_type'])): ?><tr><td>Building type</td><td><?= htmlspecialchars($p['property_type']) ?></td></tr><?php endif; ?>
                    <?php if (!empty($p['price'])): ?><tr><td>Price</td><td><?= htmlspecialchars($p['price']) ?></td></tr><?php endif; ?>
                    <?php if (!empty($p['bedrooms'])): ?><tr><td>Bedrooms</td><td><?= $p['bedrooms'] ?></td></tr><?php endif; ?>
                    <?php if (!empty($p['bathrooms'])): ?><tr><td>Bathrooms</td><td><?= $p['bathrooms'] ?></td></tr><?php endif; ?>
                    <?php if (!empty($p['sqft'])): ?><tr><td>Size</td><td><?= number_format($p['sqft']) ?> sqft</td></tr><?php endif; ?>
                    <?php if (!empty($p['parking'])): ?><tr><td>Parking</td><td><?= htmlspecialchars($p['parking']) ?></td></tr><?php endif; ?>
                    <?php if (!empty($p['strata_fee'])): ?><tr><td>Strata Fee</td><td><?= htmlspecialchars($p['strata_fee']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT BAND -->
<section class="lv-cta-band" id="lv-contact">
    <div class="lv-wrap">
        <div class="lv-cta-grid">
            <!-- Agent photo + info -->
            <div class="lv-cta-agent">
                <div class="lv-agent-photo">
                    <img src="<?= $static_url ?>/img/user-6.jpg" alt="Tam Nguyen">
                </div>
                <div class="lv-agent-info">
                    <h2 class="lv-serif-h" style="margin-bottom:6px;">
                        <a href="https://tamwynn.ca" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;">Contact Tam Nguyen</a>
                    </h2>
                    <p style="font-size:14px;color:rgba(0,36,70,.65);margin-bottom:8px;">Request additional information including price lists and floor plans.</p>
                    <div class="lv-agent-contact-row">
                        <a href="tel:6047824689" class="lv-agent-contact-btn"><i class="fas fa-phone me-2"></i>(604) 782-4689</a>
                        <a href="https://tamwynn.ca" target="_blank" rel="noopener" class="lv-agent-contact-btn" style="margin-left:8px;"><i class="fas fa-globe me-2"></i>tamwynn.ca</a>
                    </div>
                </div>
            </div>
            <!-- Blended contact form -->
            <div class="lv-cta-form">
                <div class="lv-form-row">
                    <div class="lv-form-group">
                        <label>Your Email</label>
                        <input type="email" class="lv-form-input" placeholder="email@example.com">
                    </div>
                    <div class="lv-form-group">
                        <label>Phone Number</label>
                        <input type="tel" class="lv-form-input" placeholder="(604) 000-0000">
                    </div>
                </div>
                <div class="lv-form-group">
                    <label>Message</label>
                    <textarea class="lv-form-input lv-form-textarea" rows="3">I'm interested in <?= htmlspecialchars($p['address']) ?>. Please send me more information.</textarea>
                </div>
                <button class="lv-form-btn">
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                </button>
            </div>
        </div>
    </div>
</section>


<!-- GALLERY -->
<?php if ($has_photos): ?>
<section class="lv-plain-section" id="lv-gallery-section">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Photos</h2>
        <div class="lv-gallery-grid">
            <?php foreach ($photos as $idx => $photo): ?>
            <div class="lv-gallery-tile" onclick="openGalleryAt(<?= $idx ?>)">
                <img src="<?= htmlspecialchars($photo) ?>" alt="Photo <?= $idx+1 ?>" loading="<?= $idx===0?'eager':'lazy' ?>">
                <div class="lv-gallery-overlay"><i class="fas fa-expand-alt"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- VIDEO -->
<?php if (!empty($p['video_url'])): ?>
<section class="lv-plain-section">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Video</h2>
        <div class="lv-video-wrap" id="lv-video-wrap">
            <iframe src="<?= htmlspecialchars($embed ?? $p['video_url']) ?>" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- VIRTUAL TOUR -->
<?php if (!empty($p['virtual_tour_url'])): ?>
<?php
$vt_display = trim($p['virtual_tour_url']);
if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $vt_display, $m))   $vt_display = 'https://www.youtube.com/embed/'.$m[1];
elseif (preg_match('/youtu\.be\/([^?]+)/', $vt_display, $m))            $vt_display = 'https://www.youtube.com/embed/'.$m[1];
elseif (preg_match('/vimeo\.com\/(\d+)/', $vt_display, $m))             $vt_display = 'https://player.vimeo.com/video/'.$m[1];
elseif (preg_match('/matterport\.com\/show\/\?m=([^&]+)/', $vt_display, $m)) $vt_display = 'https://my.matterport.com/show/?m='.$m[1].'&play=1';
?>
<section class="lv-cream-section">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Virtual Tour</h2>
        <div class="lv-video-wrap">
            <iframe src="<?= htmlspecialchars($vt_display) ?>" allowfullscreen loading="lazy" allow="xr-spatial-tracking" allowvr></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FLOOR PLANS -->
<?php if (!empty($p['floorplan'])): ?>
<section class="lv-cream-section" id="lv-plans">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Plans &amp; homes</h2>
        <div class="lv-plans-grid">
            <?php $fp_ext = strtolower(pathinfo($p['floorplan'], PATHINFO_EXTENSION)); ?>
            <div class="lv-plan-card">
                <?php if (in_array($fp_ext, ['jpg','jpeg','png','webp'])): ?>
                <div class="lv-plan-img" style="background-image:url('<?= htmlspecialchars($p['floorplan']) ?>')" onclick="window.open('<?= htmlspecialchars($p['floorplan']) ?>','_blank')">
                    <span class="lv-plan-chip">Floor Plan</span>
                </div>
                <?php else: ?>
                <div class="lv-plan-img lv-plan-pdf" onclick="window.open('<?= htmlspecialchars($p['floorplan']) ?>','_blank')">
                    <i class="fas fa-file-pdf"></i>
                    <span class="lv-plan-chip">PDF</span>
                </div>
                <?php endif; ?>
                <div class="lv-plan-body">
                    <strong><?= htmlspecialchars($p['address']) ?></strong>
                    <span class="lv-plan-price"><?= !empty($p['price']) ? htmlspecialchars($p['price']) : 'Pricing coming soon' ?></span>
                    <span class="lv-plan-type"><?= htmlspecialchars($p['property_type']) ?></span>
                    <a href="<?= htmlspecialchars($p['floorplan']) ?>" target="_blank" class="lv-plan-dl"><i class="fas fa-download me-1"></i>View / Download</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$community_features = !empty($p['community_features']) ? array_filter(array_map('trim', explode(',', $p['community_features']))) : [];
?>
<!-- FEATURES & FINISHES -->
<?php if (!empty($features) || !empty($amenities) || !empty($community_features)): ?>
<section class="lv-plain-section" id="lv-amenities">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Features &amp; finishes</h2>
        <div class="lv-ff-grid">
            <?php if (!empty($features)): ?>
            <div>
                <h4 class="lv-label-h">Interior Features</h4>
                <ul class="lv-ff-list">
                    <?php foreach ($features as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php if (!empty($amenities)): ?>
            <div>
                <h4 class="lv-label-h">Building Amenities</h4>
                <ul class="lv-ff-list">
                    <?php foreach ($amenities as $a): ?><li><?= htmlspecialchars($a) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php if (!empty($community_features)): ?>
            <div>
                <h4 class="lv-label-h">Lot / Community Features</h4>
                <ul class="lv-ff-list">
                    <?php foreach ($community_features as $cf): ?><li><?= htmlspecialchars($cf) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- LOCATION: Schools | Walkability | Nearby — all 3 in one row -->
<section class="lv-cream-section" id="lv-map-section">
    <div class="lv-wrap">
        <h2 class="lv-serif-h">Location &amp; schools</h2>
        <div class="lv-loc3-grid">

            <!-- COL 1: School catchment -->
            <div class="lv-loc3-col">
                <h4 class="lv-label-h">School Catchment</h4>
                <?php if ($schools): ?>
                    <div class="lv-school-row">
                        <div class="lv-school-icon-lg elem"><i class="fas fa-school"></i></div>
                        <div class="lv-school-detail">
                            <span class="lv-school-t">Elementary</span>
                            <strong><?= htmlspecialchars($schools['elementary']) ?></strong>
                        </div>
                    </div>
                    <div class="lv-school-row">
                        <div class="lv-school-icon-lg sec"><i class="fas fa-university"></i></div>
                        <div class="lv-school-detail">
                            <span class="lv-school-t">Secondary</span>
                            <strong><?= htmlspecialchars($schools['secondary']) ?></strong>
                        </div>
                    </div>
                    <p style="font-size:11px;color:#bbb;margin-top:10px;">Verify with <a href="https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx" target="_blank" style="color:var(--blue);">VSB School Locator</a></p>
                <?php else: ?>
                    <div class="lv-school-row">
                        <div class="lv-school-icon-lg elem"><i class="fas fa-school"></i></div>
                        <div class="lv-school-detail">
                            <span class="lv-school-t">Elementary</span>
                            <strong style="color:#aaa;">Check VSB for catchment</strong>
                        </div>
                    </div>
                    <div class="lv-school-row">
                        <div class="lv-school-icon-lg sec"><i class="fas fa-university"></i></div>
                        <div class="lv-school-detail">
                            <span class="lv-school-t">Secondary</span>
                            <strong style="color:#aaa;">Check VSB for catchment</strong>
                        </div>
                    </div>
                    <a href="https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx" target="_blank" style="font-size:12px;color:var(--blue);margin-top:10px;display:inline-block;">Check VSB School Locator →</a>
                <?php endif; ?>
            </div>

            <!-- COL 2: Walk Score -->
            <div class="lv-loc3-col">
                <h4 class="lv-label-h">Walkability</h4>
                <style>#ws-walkscore-tile{position:relative;text-align:left;min-height:60px;}#ws-walkscore-tile *{float:none;}</style>
                <script>var ws_wsid='361dbc9c010ccf76ceea407fa304e222';var ws_address=<?= json_encode($p['address']) ?>;var ws_lat='<?= (float)($p['latitude']??0) ?>';var ws_lon='<?= (float)($p['longitude']??0) ?>';var ws_format='wide';var ws_width='340';var ws_height='200';var ws_transit='1';var ws_bike='1';</script>
                <div id="ws-walkscore-tile"><div id="ws-load" style="color:#bbb;font-size:13px;padding:12px 0;"><i class="fas fa-circle-notch fa-spin me-2"></i>Loading scores...</div></div>
                <script>(function(){var s=document.createElement('script');s.async=true;s.src='https://www.walkscore.com/tile/show-walkscore-tile.php';s.onload=function(){var l=document.getElementById('ws-load');if(l)l.remove();};document.body.appendChild(s);})();</script>
            </div>

            <!-- COL 3: Nearby restaurants -->
            <div class="lv-loc3-col">
                <?php
                $full_address = urlencode(($p['address'] ?? '') . ', Vancouver, BC');
                $yelp_url  = 'https://www.yelp.ca/search?find_desc=Restaurants&find_loc=' . $full_address;
                $lat2 = (float)($p['latitude'] ?? 49.25);
                $lng2 = (float)($p['longitude'] ?? -123.12);
                $gmaps_url = 'https://www.google.com/maps/search/restaurants/@' . $lat2 . ',' . $lng2 . ',15z';
                ?>
                <h4 class="lv-label-h">Explore the neighbourhood</h4>
                <a href="<?= $yelp_url ?>" target="_blank" class="lv-nearby">
                    <i class="fab fa-yelp" style="color:#d32323;"></i>
                    <div><strong>Restaurants on Yelp</strong><span>Near <?= htmlspecialchars($p['address'] ?? '') ?></span></div>
                    <i class="fas fa-external-link-alt" style="color:#ccc;font-size:11px;"></i>
                </a>
                <a href="<?= $gmaps_url ?>" target="_blank" class="lv-nearby">
                    <i class="fas fa-map-marker-alt" style="color:#0284c7;"></i>
                    <div><strong>Google Maps</strong><span>See restaurants nearby</span></div>
                    <i class="fas fa-external-link-alt" style="color:#ccc;font-size:11px;"></i>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- BUILDER / AWARDS -->
<?php if (!empty($p['builder_logo']) || !empty($p['developer_name']) || !empty($p['developer_bio']) || !empty($awards) || !empty($p['builder_website'])): ?>
<section class="lv-plain-section lv-builder-band">
    <div class="lv-wrap">
        <div class="lv-builder-grid2">

            <!-- LEFT: logo + website button + awards -->
            <div class="lv-builder-left">
                <?php if (!empty($p['builder_logo'])): ?>
                <div class="lv-blogo-lg" style="margin-bottom:20px;"><img src="<?= htmlspecialchars($p['builder_logo']) ?>" alt="<?= htmlspecialchars($p['developer_name'] ?? 'Developer') ?>"></div>
                <?php endif; ?>
                <?php if (!empty($p['builder_website'])): ?>
                <a href="<?= htmlspecialchars($p['builder_website']) ?>" target="_blank" class="lv-btn-ghost" style="margin-bottom:24px;font-size:12px;padding:7px 16px;"><i class="fas fa-globe me-2"></i>Visit Website</a>
                <?php endif; ?>
                <?php if (!empty($awards)): ?>
                <div style="margin-top:20px;">
                    <h4 class="lv-label-h" style="margin-bottom:10px;">Awards &amp; Recognition</h4>
                    <ul class="lv-awards-compact">
                        <?php foreach ($awards as $award): ?>
                        <li><i class="fas fa-trophy"></i><?= htmlspecialchars($award) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT: name + bio -->
            <div class="lv-builder-right">
                <h2 class="lv-serif-h">About <?= !empty($p['developer_name']) ? htmlspecialchars($p['developer_name']) : 'the Developer' ?></h2>
                <?php if (!empty($p['developer_bio'])): ?>
                <div class="lv-developer-bio"><?= nl2br(htmlspecialchars($p['developer_bio'])) ?></div>
                <?php else: ?>
                <p class="lv-developer-bio" style="color:#aaa;font-style:italic;">Developer description coming soon.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>
<?php endif; ?>

<!-- FEATURED CONCIERGE LISTINGS -->
<?php if (!empty($other_listings)): ?>
<section class="lv-cream-section">
    <div class="lv-wrap">
        <div class="lv-fc-header">
            <h2 class="lv-serif-h" style="margin:0;">Featured Concierge Listings</h2>
            <a href="half-map.php" class="lv-view-all">view all <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="lv-fc-grid">
            <?php foreach ($other_listings as $ol): ?>
            <a href="concierge-property.php?id=<?= $ol['id'] ?>" class="lv-fc-card">
                <div class="lv-fc-img">
                    <?php if (!empty($ol['img1'])): ?>
                    <img src="<?= htmlspecialchars($ol['img1']) ?>" alt="">
                    <?php else: ?>
                    <div class="lv-fc-ph"><i class="fas fa-building"></i></div>
                    <?php endif; ?>
                    <span class="lv-fc-chip">FEATURED</span>
                </div>
                <strong class="lv-fc-name"><?= htmlspecialchars($ol['address']) ?></strong>
                <span class="lv-fc-price"><?= !empty($ol['price']) ? htmlspecialchars($ol['price']) : 'Pricing coming soon' ?></span>
                <span class="lv-fc-loc"><?= htmlspecialchars($ol['neighborhood']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- YELLOW UPDATES BAND -->
<section class="lv-updates-band">
    <div class="lv-wrap">
        <div class="lv-updates-grid">
            <div class="lv-updates-art">
                <svg width="90" height="90" viewBox="0 0 90 90" fill="none">
                    <rect x="8" y="16" width="60" height="58" rx="5" fill="rgba(0,36,70,.08)" stroke="rgba(0,36,70,.18)" stroke-width="2"/>
                    <rect x="16" y="8" width="56" height="48" rx="4" fill="white" stroke="rgba(0,36,70,.12)" stroke-width="1.5"/>
                    <line x1="26" y1="20" x2="62" y2="20" stroke="rgba(0,36,70,.18)" stroke-width="1.5"/>
                    <line x1="26" y1="28" x2="54" y2="28" stroke="rgba(0,36,70,.1)" stroke-width="1.5"/>
                    <line x1="26" y1="36" x2="56" y2="36" stroke="rgba(0,36,70,.1)" stroke-width="1.5"/>
                    <line x1="26" y1="44" x2="48" y2="44" stroke="rgba(0,36,70,.1)" stroke-width="1.5"/>
                    <path d="M60 58 L72 70" stroke="rgba(0,36,70,.25)" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="55" cy="52" r="14" fill="rgba(0,36,70,.06)" stroke="rgba(0,36,70,.18)" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h2 class="lv-updates-h">Interested? Receive the latest updates</h2>
                <p>Stay informed with updates on new community details and available inventory.</p>
                <a href="#lv-contact" class="lv-btn-outline">Get updates</a>
            </div>
        </div>
    </div>
</section>

<!-- LIGHTBOX MODAL -->
<div id="lv-gallery-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.96);z-index:9999;align-items:center;justify-content:center;" onclick="lbClose()">
    <!-- Close -->
    <button onclick="lbClose()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,.12);border:none;color:#fff;width:42px;height:42px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;z-index:10001;"><i class="fas fa-times"></i></button>
    <!-- Counter -->
    <div id="lv-lb-counter" style="position:absolute;top:20px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.7);font-size:13px;font-weight:600;font-family:var(--ss,sans-serif);z-index:10001;pointer-events:none;"></div>
    <!-- Prev -->
    <button onclick="event.stopPropagation();lbPrev()" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.12);border:none;color:#fff;width:48px;height:48px;border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;z-index:10001;transition:background .2s;"><i class="fas fa-chevron-left"></i></button>
    <!-- Next -->
    <button onclick="event.stopPropagation();lbNext()" style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.12);border:none;color:#fff;width:48px;height:48px;border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;z-index:10001;transition:background .2s;"><i class="fas fa-chevron-right"></i></button>
    <!-- Image -->
    <div onclick="event.stopPropagation()" style="max-width:calc(100vw - 120px);max-height:calc(100vh - 80px);display:flex;align-items:center;justify-content:center;">
        <img id="lv-lb-img" src="" alt="" style="max-width:100%;max-height:calc(100vh - 80px);border-radius:8px;object-fit:contain;display:block;">
    </div>
</div>

<!-- ══════════════════════════════════ STYLES ══════════════════════════════════ -->
<style>
:root {
    --black: #1a1a1a;
    --dark:  #002446;
    --blue:  #0065ff;
    --yel:   #e8d84b;
    --sal:   #f2c4a8;
    --cream: #faf9f5;
    --bdr:   #e8e8e4;
    --sf:    'Segoe UI', Georgia, serif;
    --ss:    'Segoe UI', sans-serif;
}
* { box-sizing: border-box; }

/* Wrap */
.lv-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }

/* Hero */
.lv-hero { position:relative;background:#0d1929; }
.lv-hero-img { width:100%;height:620px;background-size:cover;background-position:center;transition:background-image .4s ease;display:block; }
.lv-hero-ph { height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#002446,#004080);color:rgba(255,255,255,.55);gap:14px; }
.lv-hero-ph i { font-size:64px; }
.lv-hero-ph span { font-family:var(--ss);font-size:15px; }
/* Arrow nav */
.lv-arrow { position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;background:rgba(0,0,0,.45);backdrop-filter:blur(6px);border:1.5px solid rgba(255,255,255,.2);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:15;transition:background .2s;line-height:1; }
.lv-arrow:hover { background:rgba(0,0,0,.7); }
.lv-arrow-left  { left:20px; }
.lv-arrow-right { right:20px; }
/* Photo counter */
.lv-photo-counter { position:absolute;bottom:70px;right:20px;background:rgba(0,0,0,.5);color:rgba(255,255,255,.85);font-family:var(--ss);font-size:12px;font-weight:600;padding:5px 12px;border-radius:20px;z-index:15;pointer-events:none; }
/* Hero overlays */
.lv-hero-overlay { position:absolute;top:0;left:0;right:0;bottom:0;background:#000;z-index:8; }
/* Floating pill bar */
.lv-hero-bar { position:absolute;bottom:14px;left:50%;transform:translateX(-50%);display:inline-flex;gap:2px;background:rgba(20,30,48,.82);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-radius:40px;padding:5px 6px;z-index:20;white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.45); }
.lv-hbar-btn { background:transparent;border:none;color:rgba(255,255,255,.72);font-family:var(--ss);font-size:13px;font-weight:600;padding:8px 18px;border-radius:32px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:background .15s,color .15s;white-space:nowrap; }
.lv-hbar-btn.on { background:rgba(255,255,255,.18);color:#fff; }
.lv-hbar-btn:hover { background:rgba(255,255,255,.12);color:#fff; }

/* Gallery grid */
.lv-gallery-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px; }
.lv-gallery-tile { aspect-ratio:4/3;border-radius:8px;overflow:hidden;position:relative;cursor:pointer; }
.lv-gallery-tile img { width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s; }
.lv-gallery-tile:hover img { transform:scale(1.05); }
.lv-gallery-overlay { position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;opacity:0;transition:opacity .2s; }
.lv-gallery-tile:hover .lv-gallery-overlay { opacity:1; }

/* Title block */
.lv-title-block { background:#fff;border-bottom:1px solid var(--bdr);padding:28px 0 20px; }
.lv-title-row { display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap; }
.lv-blogo { display:block;max-height:54px;max-width:180px;object-fit:contain;margin-bottom:12px; }
.lv-h1 { font-family:var(--sf);font-size:clamp(26px,4vw,46px);font-weight:700;color:var(--black);margin:0 0 6px;line-height:1.1; }
.lv-by { font-family:var(--ss);font-size:15px;color:#777;margin:0 0 10px; }
.lv-by a { color:var(--dark);font-weight:600;text-decoration:underline; }
.lv-crumb { display:flex;align-items:center;gap:6px;font-size:12px;color:#bbb;flex-wrap:wrap;font-family:var(--ss); }
.lv-crumb a { color:#999;text-decoration:none; }
.lv-crumb a:hover { color:var(--blue); }
.lv-crumb i { font-size:9px; }
.lv-title-actions { display:flex;gap:10px;align-items:center;padding-top:4px;flex-wrap:wrap; }

/* Badges row */
.lv-badges-row { background:#fff;padding:10px 0 18px;border-bottom:1px solid var(--bdr); }
.lv-badges-inner { display:flex;gap:8px;flex-wrap:wrap; }
.lv-badge { font-family:var(--ss);font-size:11px;font-weight:700;padding:4px 12px;border-radius:4px;text-transform:uppercase;letter-spacing:.5px; }
.lv-badge.yellow { background:var(--yel);color:var(--black); }
.lv-badge.blue   { background:#dbeafe;color:#1e40af; }
.lv-badge.gray   { background:#f3f4f6;color:#555; }

/* Specs bar */
.lv-specs-bar { background:#fff;padding:20px 0;border-bottom:1px solid var(--bdr); }
.lv-specs-row { display:flex;align-items:stretch;overflow-x:auto;border:1px solid var(--bdr);border-radius:8px;overflow:hidden; }
.lv-spec { display:flex;flex-direction:column;align-items:center;padding:18px 20px;text-align:center;gap:6px;flex:1;min-width:110px; }
.lv-spec i { font-size:20px;color:#bbb; }
.lv-spec strong { font-family:var(--ss);font-size:13px;font-weight:700;color:var(--black);line-height:1.3; }
.lv-spec span { font-family:var(--ss);font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:.5px; }
.lv-spec-div { width:1px;background:var(--bdr);flex-shrink:0;margin:12px 0; }

/* Overview */
.lv-overview-section { background:var(--cream);padding:60px 0;position:relative;overflow:hidden; }
.lv-overview-section::after { content:'';position:absolute;top:0;right:-200px;width:500px;height:100%;background:rgba(200,192,176,.08);clip-path:polygon(30% 0,100% 0,100% 100%,0 100%);pointer-events:none; }
.lv-overview-grid { display:grid;grid-template-columns:1fr 1fr;gap:64px; }
.lv-serif-h { font-family:var(--sf);font-size:clamp(22px,3vw,36px);font-weight:700;color:var(--black);margin:0 0 28px;line-height:1.2; }
.lv-desc { font-family:var(--ss);font-size:15px;line-height:1.85;color:#444; }
.lv-details-h { font-family:var(--sf);font-size:22px;font-weight:700;color:var(--black);margin:0 0 20px; }
.lv-dtable { width:100%;border-collapse:collapse;font-family:var(--ss); }
.lv-dtable tr { border-bottom:1px solid var(--bdr); }
.lv-dtable td { padding:12px 0;font-size:14px;vertical-align:middle; }
.lv-dtable td:first-child { color:#888;font-weight:500;width:46%; }
.lv-dtable td:last-child  { color:var(--black);font-weight:600; }
.lv-dtbadge { background:var(--yel);color:var(--black);font-size:11px;font-weight:700;padding:2px 10px;border-radius:4px;text-transform:uppercase; }

/* Contact band */
.lv-cta-band { background:#e8e05a;padding:60px 0; }
.lv-cta-grid { display:grid;grid-template-columns:auto 1fr;gap:52px;align-items:start; }
.lv-cta-agent { display:flex;flex-direction:column;align-items:center;gap:16px;min-width:180px; }
.lv-agent-photo { width:140px;height:140px;border-radius:50%;overflow:hidden;border:4px solid rgba(255,255,255,.6);box-shadow:0 6px 24px rgba(0,36,70,.15); }
.lv-agent-photo img { width:100%;height:100%;object-fit:cover; }
.lv-agent-info { text-align:center; }
.lv-agent-info .lv-serif-h { font-size:20px;margin-bottom:6px;color:var(--dark); }
.lv-agent-contact-btn { display:inline-flex;align-items:center;gap:6px;font-family:var(--ss);font-size:13px;font-weight:600;color:var(--dark);text-decoration:none;background:rgba(255,255,255,.5);padding:8px 18px;border-radius:6px;margin-top:4px;transition:background .2s; }
.lv-agent-contact-btn:hover { background:rgba(255,255,255,.75); }

/* Contact form — blended into salmon */
.lv-cta-form { background:rgba(255,255,255,.25);border-radius:12px;padding:28px;backdrop-filter:blur(4px); }
.lv-form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px; }
.lv-form-group { display:flex;flex-direction:column;gap:6px; }
.lv-form-group label { font-family:var(--ss);font-size:12px;font-weight:600;color:rgba(0,36,70,.75);text-transform:uppercase;letter-spacing:.5px; }
.lv-form-input { background:rgba(255,255,255,.65);border:1px solid rgba(0,36,70,.15);border-radius:7px;padding:11px 14px;font-family:var(--ss);font-size:14px;color:var(--dark);transition:background .2s,border-color .2s;width:100%;outline:none; }
.lv-form-input:focus { background:rgba(255,255,255,.9);border-color:rgba(0,36,70,.35); }
.lv-form-textarea { resize:vertical;min-height:88px;margin-bottom:16px; }
.lv-form-btn { background:var(--dark);color:#fff;font-family:var(--ss);font-size:14px;font-weight:700;padding:13px 32px;border-radius:8px;border:none;cursor:pointer;transition:background .2s;display:inline-flex;align-items:center; }
.lv-form-btn:hover { background:var(--blue); }

/* Sections */
.lv-plain-section { padding:60px 0;background:#fff; }
.lv-cream-section { padding:60px 0;background:var(--cream); }

/* Video */
.lv-video-wrap { position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:10px; }
.lv-video-wrap iframe { position:absolute;top:0;left:0;width:100%;height:100%;border:0; }

/* Plans */
.lv-plans-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px; }
.lv-plan-card { border:1px solid var(--bdr);border-radius:10px;overflow:hidden;background:#fff;transition:box-shadow .2s; }
.lv-plan-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.08); }
.lv-plan-img { aspect-ratio:4/3;background:#f0f3f8;background-size:cover;background-position:center;position:relative;cursor:pointer; }
.lv-plan-pdf { display:flex;align-items:center;justify-content:center;font-size:52px;color:#dc2626; }
.lv-plan-chip { position:absolute;bottom:10px;left:10px;background:var(--yel);color:var(--black);font-family:var(--ss);font-size:10px;font-weight:800;padding:3px 10px;border-radius:3px;text-transform:uppercase;letter-spacing:.5px; }
.lv-plan-body { padding:16px;display:flex;flex-direction:column;gap:4px;font-family:var(--ss); }
.lv-plan-body strong { font-size:14px;font-weight:700;color:var(--black); }
.lv-plan-price { font-size:13px;color:#666; }
.lv-plan-type { font-size:12px;color:#999; }
.lv-plan-dl { margin-top:10px;display:inline-flex;align-items:center;font-size:12px;font-weight:600;color:var(--dark);text-decoration:none;border:1px solid var(--bdr);border-radius:6px;padding:7px 14px;transition:background .15s; }
.lv-plan-dl:hover { background:#f0f4ff; }

/* Features & finishes */
.lv-ff-grid { display:grid;grid-template-columns:1fr 1fr 1fr;gap:52px;align-items:start; }
.lv-label-h { font-family:var(--ss);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--blue);margin:0 0 16px;padding-bottom:10px;border-bottom:2px solid var(--bdr); }
.lv-ff-list { list-style:none;padding:0;margin:0; }
.lv-ff-list li { font-family:var(--ss);font-size:14px;color:#444;padding:10px 0;border-bottom:1px solid var(--bdr);display:flex;align-items:flex-start;gap:10px; }
.lv-ff-list li::before { content:'—';color:#ccc;flex-shrink:0; }

/* Location — 3 equal columns in one row */
.lv-loc3-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:40px;align-items:start; }
.lv-loc3-col { min-width:0; }
.lv-nearby { display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:8px;text-decoration:none;border:1px solid var(--bdr);margin-bottom:10px;background:#fff;font-family:var(--ss);transition:box-shadow .2s,transform .15s; }
.lv-nearby:hover { box-shadow:0 4px 14px rgba(0,0,0,.07);transform:translateY(-1px); }
.lv-nearby > i:first-child { font-size:18px;flex-shrink:0; }
.lv-nearby div { flex:1; }
.lv-nearby div strong { display:block;font-size:13px;font-weight:700;color:var(--black); }
.lv-nearby div span { font-size:12px;color:#888; }

/* School rows */
.lv-school-row { display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--bdr); }
.lv-school-icon-lg { width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
.lv-school-icon-lg.elem { background:#e8f4ff;color:#2563eb; }
.lv-school-icon-lg.sec  { background:#fff3e8;color:#c47a20; }
.lv-school-detail { display:flex;flex-direction:column;gap:1px; }
.lv-school-t { font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#aaa;font-weight:700;font-family:var(--ss); }
.lv-school-detail strong { font-size:13px;font-weight:700;color:var(--dark);font-family:var(--ss);line-height:1.3; }

/* Builder section */
.lv-builder-grid2 { display:grid;grid-template-columns:240px 1fr;gap:60px;align-items:start; }
.lv-builder-left { display:flex;flex-direction:column;align-items:flex-start; }
.lv-builder-right { padding-top:4px; }
.lv-blogo-lg img { max-height:90px;max-width:200px;object-fit:contain; }
.lv-developer-bio { font-family:var(--ss);font-size:15px;line-height:1.85;color:#444;margin-top:0; }

/* Compact awards — width fits content, no full-width underline */
.lv-awards-compact { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0; }
.lv-awards-compact li { display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#555;padding:7px 0;border-bottom:1px solid var(--bdr);font-family:var(--ss);width:fit-content;padding-right:24px; }
.lv-awards-compact li i { color:#f59e0b;font-size:13px;flex-shrink:0; }

/* Updates band */
.lv-updates-band { background:#e8e05a;padding:64px 0; }
.lv-updates-grid { display:grid;grid-template-columns:auto 1fr;gap:40px;align-items:center; }
.lv-updates-art { opacity:.6; }
.lv-updates-h { font-family:var(--sf);font-size:clamp(20px,2.5vw,30px);font-weight:700;color:var(--black);margin:0 0 8px; }
.lv-updates-band p { font-family:var(--ss);font-size:14px;color:#333;margin-bottom:20px; }

/* Buttons */
.lv-btn-dark { background:var(--dark);color:#fff;font-family:var(--ss);font-size:13px;font-weight:600;padding:10px 22px;border-radius:6px;text-decoration:none;transition:background .2s;display:inline-flex;align-items:center; }
.lv-btn-dark:hover { background:var(--blue);color:#fff; }
.lv-btn-ghost { background:transparent;color:var(--dark);font-family:var(--ss);font-size:13px;font-weight:600;padding:9px 20px;border-radius:6px;text-decoration:none;border:1.5px solid var(--dark);transition:background .15s;display:inline-flex;align-items:center; }
.lv-btn-ghost:hover { background:#f0f4ff; }
.lv-btn-outline { background:transparent;color:var(--black);font-family:var(--ss);font-size:14px;font-weight:600;padding:11px 28px;border-radius:6px;text-decoration:none;border:2px solid var(--black);display:inline-flex;align-items:center;transition:background .15s; }
.lv-btn-outline:hover { background:rgba(0,0,0,.07); }

/* Featured Concierge Listings */
.lv-fc-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
.lv-view-all { font-family:var(--ss);font-size:13px;color:#888;text-decoration:none; }
.lv-view-all:hover { color:var(--dark); }
.lv-fc-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px; }
.lv-fc-card { text-decoration:none;color:inherit;display:block; }
.lv-fc-img { aspect-ratio:4/3;border-radius:8px;overflow:hidden;position:relative;background:#f0f3f8;margin-bottom:10px; }
.lv-fc-img img { width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s; }
.lv-fc-card:hover .lv-fc-img img { transform:scale(1.04); }
.lv-fc-ph { width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:30px; }
.lv-fc-chip { position:absolute;bottom:8px;right:8px;background:var(--yel);color:var(--black);font-size:9px;font-weight:800;padding:2px 8px;border-radius:3px;text-transform:uppercase;letter-spacing:.5px; }
.lv-fc-card strong,.lv-fc-name { display:block;font-family:var(--ss);font-size:14px;font-weight:700;color:var(--black);margin-bottom:2px; }
.lv-fc-price { display:block;font-family:var(--ss);font-size:13px;color:var(--black);font-weight:500; }
.lv-fc-loc   { display:block;font-family:var(--ss);font-size:12px;color:#888; }

/* Responsive */
@media (max-width:900px){
    .lv-hero-img { height:380px; }
    .lv-overview-grid,.lv-builder-grid2 { grid-template-columns:1fr;gap:36px; }
    .lv-loc3-grid,.lv-ff-grid { grid-template-columns:1fr;gap:32px; }
    .lv-cta-grid { grid-template-columns:1fr; }
    .lv-cta-agent { flex-direction:row;min-width:unset;align-items:flex-start; }
    .lv-agent-info { text-align:left; }
    .lv-updates-grid { grid-template-columns:1fr; }
    .lv-updates-art { display:none; }
    .lv-form-row { grid-template-columns:1fr; }
}
@media (max-width:600px){
    .lv-specs-row { flex-wrap:wrap; }
    .lv-spec { min-width:45%; border-bottom:1px solid var(--bdr); }
    .lv-spec-div { display:none; }
    .lv-fc-grid { grid-template-columns:1fr 1fr; }
}
</style>

<script>
var lvPhotos = lvPhotos || [];
var lvPhotoIdx = 0;
var lbIdx = 0;

// ── Set hero photo on page load and on arrow click ────────────────────
function heroSetPhoto(idx) {
    if (!lvPhotos.length) return;
    lvPhotoIdx = ((idx % lvPhotos.length) + lvPhotos.length) % lvPhotos.length;
    var el = document.getElementById('lv-hero-img');
    if (el) el.style.backgroundImage = 'url(' + lvPhotos[lvPhotoIdx] + ')';
    var counter = document.getElementById('lv-photo-counter');
    if (counter) counter.textContent = (lvPhotoIdx + 1) + ' / ' + lvPhotos.length;
}
function heroPrev() { heroSetPhoto(lvPhotoIdx - 1); }
function heroNext() { heroSetPhoto(lvPhotoIdx + 1); }
// Legacy alias
function setPhoto(idx) { heroSetPhoto(idx); }

// Load first photo on page ready
document.addEventListener('DOMContentLoaded', function() {
    if (lvPhotos.length) heroSetPhoto(0);
});

// ── Hero media helpers ────────────────────────────────────────────────
var _heroAllBtns = ['lv-hbar-photos','lv-hbar-video','lv-hbar-map','lv-hbar-tour'];
var _heroAllOvl  = ['lv-overlay-video','lv-overlay-map','lv-overlay-tour'];

function heroSetActive(btnId) {
    _heroAllBtns.forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.classList.toggle('on', el.id === btnId);
    });
}
function heroHideAll() {
    _heroAllOvl.forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    ['lv-video-iframe','lv-tour-iframe'].forEach(function(id){
        var f = document.getElementById(id); if (f) f.src = '';
    });
    document.querySelectorAll('.lv-arrow,.lv-photo-counter').forEach(function(el){ el.style.display = ''; });
}

function heroShowPhotos() {
    heroHideAll();
    heroSetActive('lv-hbar-photos');
    openGalleryAt(lvPhotoIdx);
}
function heroShowVideo() {
    heroHideAll();
    var ov = document.getElementById('lv-overlay-video');
    var src = document.getElementById('lv-video-src');
    var iframe = document.getElementById('lv-video-iframe');
    if (ov && src && iframe) { iframe.src = src.textContent.trim(); ov.style.display = 'block'; }
    document.querySelectorAll('.lv-arrow,.lv-photo-counter').forEach(function(el){ el.style.display = 'none'; });
    heroSetActive('lv-hbar-video');
}
function heroShowMap() {
    heroHideAll();
    var om = document.getElementById('lv-overlay-map');
    if (om) om.style.display = 'block';
    document.querySelectorAll('.lv-arrow,.lv-photo-counter').forEach(function(el){ el.style.display = 'none'; });
    heroSetActive('lv-hbar-map');
}
function heroShowTour() {
    heroHideAll();
    var ov = document.getElementById('lv-overlay-tour');
    var src = document.getElementById('lv-tour-src');
    var iframe = document.getElementById('lv-tour-iframe');
    if (ov && src && iframe) { iframe.src = src.textContent.trim(); ov.style.display = 'block'; }
    document.querySelectorAll('.lv-arrow,.lv-photo-counter').forEach(function(el){ el.style.display = 'none'; });
    heroSetActive('lv-hbar-tour');
}

// ── Lightbox ──────────────────────────────────────────────────────────
function lbShow(idx) {
    if (!lvPhotos.length) return;
    lbIdx = ((idx % lvPhotos.length) + lvPhotos.length) % lvPhotos.length;
    var modal = document.getElementById('lv-gallery-modal');
    var img   = document.getElementById('lv-lb-img');
    var ctr   = document.getElementById('lv-lb-counter');
    if (!modal || !img) return;
    img.src = lvPhotos[lbIdx];
    if (ctr) ctr.textContent = (lbIdx + 1) + ' / ' + lvPhotos.length;
    modal.style.display = 'flex';
}
function lbPrev() { lbShow(lbIdx - 1); }
function lbNext() { lbShow(lbIdx + 1); }
function lbClose() {
    var modal = document.getElementById('lv-gallery-modal');
    if (modal) modal.style.display = 'none';
}
function openGalleryAt(idx) { lbShow(idx); }

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('lv-gallery-modal');
    if (!modal || modal.style.display === 'none') return;
    if (e.key === 'ArrowLeft')  lbPrev();
    if (e.key === 'ArrowRight') lbNext();
    if (e.key === 'Escape')     lbClose();
});
</script>


<!-- ── Wynston Notify Me Modal ─────────────────────────────────────────── -->
<style>
.wyn-notify-btn  { display:inline-flex;align-items:center;gap:7px;background:#002446;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;padding:10px 18px;cursor:pointer;transition:background .2s;text-decoration:none; }
.wyn-notify-btn:hover  { background:#0065ff;color:#fff; }
.wyn-share-btn   { display:inline-flex;align-items:center;gap:7px;background:#f4f6fb;color:#444;border:1px solid #dde;border-radius:8px;font-size:13px;font-weight:600;padding:10px 14px;cursor:pointer;transition:background .2s;text-decoration:none; }
.wyn-share-btn:hover   { background:#e8ecf5;color:#444; }
#wyn-notify-modal { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.65);z-index:99999;align-items:center;justify-content:center;padding:16px; }
#wyn-notify-modal.open { display:flex; }
.wyn-notify-box  { background:#fff;border-radius:16px;max-width:440px;width:100%;padding:40px 36px;box-shadow:0 24px 64px rgba(0,0,0,.25); }
.wyn-notify-box h4 { font-size:20px;font-weight:800;color:#002446;margin:0 0 8px; }
.wyn-notify-box p  { font-size:14px;color:#666;line-height:1.7;margin:0 0 20px; }
.wyn-notify-input  { width:100%;padding:12px 14px;border:1.5px solid #dde;border-radius:8px;font-size:14px;margin-bottom:12px;outline:none;display:block; }
.wyn-notify-input:focus { border-color:#002446; }
.wyn-notify-submit { width:100%;padding:14px;background:#002446;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;transition:background .2s; }
.wyn-notify-submit:hover { background:#0065ff; }
.wyn-notify-cancel { background:none;border:none;font-size:13px;color:#aaa;cursor:pointer;margin-top:12px;width:100%;text-align:center;display:block; }
.wyn-success-wrap  { text-align:center;padding:16px 0; }
.wyn-success-wrap i { font-size:48px;color:#16a34a;margin-bottom:16px;display:block; }
</style>

<div id="wyn-notify-modal">
    <div class="wyn-notify-box">
        <div id="wyn-form-inner">
            <h4><i class="fas fa-bell me-2" style="color:#c9a84c;"></i>Stay Updated on This Project</h4>
            <p id="wyn-notify-desc">We will email you when there are updates — new photos, completion timeline, or when units become available.</p>
            <input type="email" id="wyn-email" class="wyn-notify-input" placeholder="your@email.com">
            <input type="text"  id="wyn-name"  class="wyn-notify-input" placeholder="Your name (optional)">
            <input type="hidden" id="wyn-pid" value="">
            <button class="wyn-notify-submit" onclick="wynSubmit()"><i class="fas fa-bell me-2"></i>Notify Me When There Are Updates</button>
            <button class="wyn-notify-cancel" onclick="wynCloseNotify()">No thanks</button>
        </div>
        <div id="wyn-success-inner" class="wyn-success-wrap" style="display:none;">
            <i class="fas fa-check-circle"></i>
            <h4 style="color:#002446;margin-bottom:8px;">You are on the list!</h4>
            <p style="color:#666;font-size:14px;">We will email you when there are updates to this project.</p>
            <button class="wyn-notify-cancel" onclick="wynCloseNotify()" style="color:#002446;font-weight:700;">Done</button>
        </div>
    </div>
</div>

<script>
function wynOpenNotify(pid, address) {
    document.getElementById('wyn-pid').value   = pid;
    document.getElementById('wyn-notify-desc').textContent = 'Get updates on ' + address + ' — new photos, completion dates, or when units become available.';
    document.getElementById('wyn-form-inner').style.display   = 'block';
    document.getElementById('wyn-success-inner').style.display = 'none';
    document.getElementById('wyn-email').value = '';
    document.getElementById('wyn-notify-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function wynCloseNotify() {
    document.getElementById('wyn-notify-modal').classList.remove('open');
    document.body.style.overflow = '';
}
function wynSubmit() {
    var email = document.getElementById('wyn-email').value.trim();
    var name  = document.getElementById('wyn-name').value.trim();
    var pid   = document.getElementById('wyn-pid').value;
    if (!email || !email.includes('@')) {
        document.getElementById('wyn-email').style.borderColor = '#e00';
        return;
    }
    document.getElementById('wyn-email').style.borderColor = '#dde';
    fetch('notify-subscribe.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email) + '&name=' + encodeURIComponent(name) + '&property_id=' + encodeURIComponent(pid) + '&source=single-property'
    }).finally(function() {
        document.getElementById('wyn-form-inner').style.display    = 'none';
        document.getElementById('wyn-success-inner').style.display = 'block';
    });
}
function wynShare(address, url) {
    var full = window.location.origin + '/' + url;
    if (navigator.share) {
        navigator.share({ title: address + ' — Wynston', url: full });
    } else {
        navigator.clipboard.writeText(full).then(function() {
            alert('Link copied to clipboard!');
        });
    }
}
document.getElementById('wyn-notify-modal').addEventListener('click', function(e) {
    if (e.target === this) wynCloseNotify();
});
</script>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>