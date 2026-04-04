<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';

ob_start();
include "$base_dir/navbar.php";
$navlink_content = ob_get_clean();
$page  = 'nav';
$fpage = 'foot';

// ── Get property ID ───────────────────────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: active-listings.php'); exit; }

require_once "$base_dir/db.php";

// ── Load from ddf_listings (no long cache — data changes every 30 min) ────────
try {
    $stmt = $pdo->prepare("SELECT * FROM ddf_listings WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='container py-5 text-center'><h3>Database error.</h3></div>");
}

if (!$p) {
    die("<div class='container py-5 text-center'><h3>Listing not found.</h3><a href='active-listings.php'>← Back to listings</a></div>");
}

// ── Collect photos — use photos_json if available (all photos), else fallback to img1-6 ──
$photos = [];
if (!empty($p['photos_json'])) {
    $photos = json_decode($p['photos_json'], true) ?: [];
}
// Fallback to img columns if photos_json not populated yet
if (empty($photos)) {
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($p['img'.$i])) $photos[] = $p['img'.$i];
    }
}
$has_photos  = count($photos) > 0;
$total_photos = count($photos);

// ── Virtual tour URL — saved directly from DDF Media array ──────────────────
$virtual_tour_url = !empty($p['video_url']) ? $p['video_url'] : null;
$tour_type        = null;
if ($virtual_tour_url) {
    if (stripos($virtual_tour_url, 'matterport') !== false) {
        $tour_type = 'matterport';
    } elseif (stripos($virtual_tour_url, 'vimeo') !== false) {
        $tour_type = 'vimeo';
    } elseif (stripos($virtual_tour_url, 'youtube') !== false || stripos($virtual_tour_url, 'youtu.be') !== false) {
        $tour_type = 'youtube';
    } else {
        $tour_type = 'iframe';
    }
}

// ── Format price ──────────────────────────────────────────────────────────────
$price_display = !empty($p['price']) ? '$' . number_format($p['price']) : 'T.B.A.';

// ── Parse raw_json for extra fields not in dedicated columns ──────────────────
$raw = !empty($p['raw_json']) ? (json_decode($p['raw_json'], true) ?: []) : [];

// Building type (from StructureType array e.g. ["House"], ["Apartment"] etc.)
$building_type = '';
if (!empty($raw['StructureType']) && is_array($raw['StructureType'])) {
    $building_type = implode(', ', array_filter($raw['StructureType']));
}

// Open house (parse from description — DDF doesn't have a dedicated field)
// Look for open house info in raw data
$open_house_dates = [];
if (!empty($raw['OpenHouse']) && is_array($raw['OpenHouse'])) {
    foreach ($raw['OpenHouse'] as $oh) {
        if (!empty($oh['OpenHouseStartTime'])) {
            $start = strtotime($oh['OpenHouseStartTime']);
            $end   = !empty($oh['OpenHouseEndTime']) ? strtotime($oh['OpenHouseEndTime']) : null;
            if ($start && $start > time()) {
                $open_house_dates[] = [
                    'date'  => date('M j', $start),
                    'day'   => date('l', $start),
                    'start' => date('g:i A', $start),
                    'end'   => $end ? date('g:i A', $end) : '',
                ];
            }
        }
    }
}
// Also try to detect from description text
$open_house_from_desc = '';
if (empty($open_house_dates) && !empty($p['description'])) {
    if (preg_match('/open house[:\s]+([A-Za-z]+ \d+[^.()]+)/i', $p['description'], $m)) {
        $open_house_from_desc = trim($m[1]);
    }
}

// Maintenance / strata fee
$maintenance_fee = '';
if (!empty($raw['AssociationFee'])) {
    $freq = !empty($raw['AssociationFeeFrequency']) ? ' / ' . $raw['AssociationFeeFrequency'] : ' Monthly';
    $maintenance_fee = '$' . number_format((float)$raw['AssociationFee'], 2) . $freq;
}

// Parking type
$parking_type = '';
if (!empty($raw['ParkingFeatures']) && is_array($raw['ParkingFeatures'])) {
    $parking_type = implode(', ', array_filter($raw['ParkingFeatures']));
}

// Appliances
$appliances = '';
if (!empty($raw['Appliances']) && is_array($raw['Appliances'])) {
    $appliances = implode(', ', array_filter($raw['Appliances']));
}

// Community features / amenities
$community_features = '';
if (!empty($raw['CommunityFeatures']) && is_array($raw['CommunityFeatures'])) {
    $community_features = implode(', ', array_filter($raw['CommunityFeatures']));
}

// Lot features
$lot_features = '';
if (!empty($raw['LotFeatures']) && is_array($raw['LotFeatures'])) {
    $lot_features = implode(', ', array_filter($raw['LotFeatures']));
}

// Common interest (Freehold / Condominium / Strata)
$common_interest = $raw['CommonInterest'] ?? '';

// Lot size
$lot_size = '';
if (!empty($raw['LotSizeArea'])) {
    $unit     = $raw['LotSizeUnits'] ?? 'sqft';
    $lot_size = number_format($raw['LotSizeArea']) . ' ' . $unit;
}

// Fireplace
$fireplace = !empty($raw['FireplaceYN']) && $raw['FireplaceYN'] ? 'Yes (' . ($raw['FireplacesTotal'] ?? 1) . ')' : '';

// Basement
$basement = '';
if (!empty($raw['Basement']) && is_array($raw['Basement'])) {
    $b = array_filter($raw['Basement'], fn($v) => !empty($v) && strtolower($v) !== 'unknown');
    if ($b) $basement = implode(', ', $b);
}

// Sale history — not in DDF API but we can note listing history from ModificationTimestamp
// We'll show a placeholder section that explains this
$listing_date = !empty($raw['OriginalEntryTimestamp'])
    ? date('M j, Y', strtotime($raw['OriginalEntryTimestamp'])) : '';
$days_on_market = '';
if (!empty($raw['OriginalEntryTimestamp'])) {
    $days_on_market = (int)round((time() - strtotime($raw['OriginalEntryTimestamp'])) / 86400);
}

// ── School catchment — postal code based (reliable across all DDF listings) ──
$catchment_city  = strtolower(trim($p['city'] ?? ''));
$postal_code     = strtoupper(trim($p['postal_code'] ?? ''));
$postal_fsa      = substr(str_replace(' ', '', $postal_code), 0, 3); // e.g. "V6K" from "V6K 2H4"

// Vancouver FSA (first 3 of postal code) → school catchment
$postal_catchment = [
    'V5K' => ['elementary' => 'Hastings Elementary',           'secondary' => 'Britannia Secondary',             'board' => 'VSB'],
    'V5L' => ['elementary' => 'Grandview Elementary',          'secondary' => 'Britannia Secondary',             'board' => 'VSB'],
    'V5M' => ['elementary' => 'Renfrew Elementary',            'secondary' => 'Windermere Secondary',            'board' => 'VSB'],
    'V5N' => ['elementary' => 'Kensington Elementary',         'secondary' => 'Gladstone Secondary',             'board' => 'VSB'],
    'V5P' => ['elementary' => 'Carleton Elementary',           'secondary' => 'Killarney Secondary',             'board' => 'VSB'],
    'V5R' => ['elementary' => 'Renfrew Elementary',            'secondary' => 'Windermere Secondary',            'board' => 'VSB'],
    'V5S' => ['elementary' => 'Cunningham Elementary',         'secondary' => 'Killarney Secondary',             'board' => 'VSB'],
    'V5T' => ['elementary' => 'Mount Pleasant Elementary',     'secondary' => 'Britannia Secondary',             'board' => 'VSB'],
    'V5V' => ['elementary' => 'Livingstone Elementary',        'secondary' => 'Eric Hamber Secondary',           'board' => 'VSB'],
    'V5W' => ['elementary' => 'Sir Richard McBride Elementary','secondary' => 'John Oliver Secondary',           'board' => 'VSB'],
    'V5X' => ['elementary' => 'David Oppenheimer Elementary',  'secondary' => 'Sir Winston Churchill Secondary', 'board' => 'VSB'],
    'V5Y' => ['elementary' => 'Simon Fraser Elementary',       'secondary' => 'Eric Hamber Secondary',           'board' => 'VSB'],
    'V5Z' => ['elementary' => 'Simon Fraser Elementary',       'secondary' => 'Eric Hamber Secondary',           'board' => 'VSB'],
    'V6A' => ['elementary' => 'Strathcona Elementary',         'secondary' => 'Britannia Secondary',             'board' => 'VSB'],
    'V6B' => ['elementary' => 'Lord Roberts Elementary',       'secondary' => 'King Edward Secondary',           'board' => 'VSB'],
    'V6C' => ['elementary' => 'Lord Roberts Elementary',       'secondary' => 'King Edward Secondary',           'board' => 'VSB'],
    'V6E' => ['elementary' => 'Lord Roberts Elementary',       'secondary' => 'King Edward Secondary',           'board' => 'VSB'],
    'V6G' => ['elementary' => 'Lord Roberts Elementary',       'secondary' => 'King Edward Secondary',           'board' => 'VSB'],
    'V6H' => ['elementary' => 'Simon Fraser Elementary',       'secondary' => 'Eric Hamber Secondary',           'board' => 'VSB'],
    'V6J' => ['elementary' => 'Carnarvon Elementary',          'secondary' => 'Kitsilano Secondary',             'board' => 'VSB'],
    'V6K' => ['elementary' => 'Carnarvon Elementary',          'secondary' => 'Kitsilano Secondary',             'board' => 'VSB'],
    'V6L' => ['elementary' => 'Maple Grove Elementary',        'secondary' => 'Magee Secondary',                 'board' => 'VSB'],
    'V6M' => ['elementary' => 'Kerrisdale Elementary',         'secondary' => 'Magee Secondary',                 'board' => 'VSB'],
    'V6N' => ['elementary' => 'David Oppenheimer Elementary',  'secondary' => 'Sir Winston Churchill Secondary', 'board' => 'VSB'],
    'V6P' => ['elementary' => 'Emily Carr Elementary',         'secondary' => 'Eric Hamber Secondary',           'board' => 'VSB'],
    'V6R' => ['elementary' => 'Queen Mary Elementary',         'secondary' => 'Lord Byng Secondary',             'board' => 'VSB'],
    'V6S' => ['elementary' => 'Dunbar Elementary',             'secondary' => 'Lord Byng Secondary',             'board' => 'VSB'],
    'V6T' => ['elementary' => 'Queen Mary Elementary',         'secondary' => 'Lord Byng Secondary',             'board' => 'VSB'],
    'V6Z' => ['elementary' => 'Lord Roberts Elementary',       'secondary' => 'King Edward Secondary',           'board' => 'VSB'],
];

// City-level fallback for non-Vancouver cities
$city_catchment = [
    'burnaby'         => ['elementary' => 'SD41 School Locator',  'secondary' => 'SD41 School Locator',  'board' => 'Burnaby SD41',    'locator' => 'https://www.sd41.bc.ca/programs-services/registration/school-locator/'],
    'richmond'        => ['elementary' => 'SD38 School Locator',  'secondary' => 'SD38 School Locator',  'board' => 'Richmond SD38',   'locator' => 'https://www.sd38.bc.ca/schools/find-a-school'],
    'north vancouver' => ['elementary' => 'NVSD44 School Locator','secondary' => 'NVSD44 School Locator','board' => 'North Van SD44',  'locator' => 'https://www.nvsd44.bc.ca/Schools'],
    'west vancouver'  => ['elementary' => 'WVSD45 School Locator','secondary' => 'WVSD45 School Locator','board' => 'West Van SD45',   'locator' => 'https://westvancouverschools.ca/schools/'],
    'coquitlam'       => ['elementary' => 'SD43 School Locator',  'secondary' => 'SD43 School Locator',  'board' => 'Coquitlam SD43',  'locator' => 'https://www.sd43.bc.ca/SchoolLocator/Pages/default.aspx'],
    'port coquitlam'  => ['elementary' => 'SD43 School Locator',  'secondary' => 'SD43 School Locator',  'board' => 'Coquitlam SD43',  'locator' => 'https://www.sd43.bc.ca/SchoolLocator/Pages/default.aspx'],
];

$schools = null;
$school_locator_url = 'https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx';

// 1. Try postal code FSA first — works for all Vancouver listings
if (!empty($postal_fsa) && isset($postal_catchment[$postal_fsa])) {
    $schools = $postal_catchment[$postal_fsa];
}

// 2. Fall back to city-level locator for non-Vancouver cities
if (!$schools && isset($city_catchment[$catchment_city])) {
    $schools = $city_catchment[$catchment_city];
    $school_locator_url = $schools['locator'] ?? $school_locator_url;
}

ob_start();
?>

<!-- ============================ Photo Gallery Banner ========================= -->
<div class="featured_slick_gallery gray">
    <div class="featured_slick_gallery-slide">
        <?php if ($has_photos): ?>
            <?php foreach ($photos as $photo): ?>
            <div class="featured_slick_padd">
                <a href="<?= htmlspecialchars($photo) ?>" class="mfp-gallery">
                    <img src="<?= htmlspecialchars($photo) ?>" class="img-fluid mx-auto"
                         style="width:100%;height:700px;object-fit:cover;"
                         alt="<?= htmlspecialchars($p['address']) ?>" loading="lazy">
                </a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="featured_slick_padd">
                <div class="generic-hero-placeholder">
                    <i class="fas fa-building"></i>
                    <span><?= htmlspecialchars($p['property_type']) ?></span>
                    <small>MLS® <?= htmlspecialchars($p['mls_number']) ?></small>
                </div>
            </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
    <a href="JavaScript:Void(0);" class="btn-view-pic">
        <?= $has_photos ? 'View ' . count($photos) . ' Photos' : 'View photos' ?>
    </a>
</div>

<!-- ============================ Property Detail ============================== -->
<section class="gray-simple">
    <div class="container">
        <div class="row">

            <!-- ══ MAIN CONTENT ══ -->
            <div class="col-lg-8 col-md-12 col-sm-12">

                <!-- ── Title / Hero card ── -->
                <div class="property_block_wrap style-2 p-4">
                    <div class="prt-detail-title-desc">

                        <!-- Green Active badge + MLS number -->
                        <span class="label text-light bg-success">Active</span>
                        <span class="label text-light bg-secondary ms-2">MLS® <?= htmlspecialchars($p['mls_number']) ?></span>

                        <h3 class="mt-3"><?= htmlspecialchars($p['address']) ?></h3>
                        <span><i class="lni-map-marker"></i>
                            <?= htmlspecialchars($p['city']) ?>
                            <?php if (!empty($p['neighborhood']) && $p['neighborhood'] !== $p['city']): ?>
                                — <?= htmlspecialchars($p['neighborhood']) ?>
                            <?php endif; ?>,
                            <?= htmlspecialchars($p['province']) ?>
                            <?= htmlspecialchars($p['postal_code']) ?>
                        </span>

                        <h3 class="prt-price-fix text-primary mt-2"><?= $price_display ?></h3>

                        <!-- Title bar: specs on left, open house on right -->
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">

                            <div class="list-fx-features" style="flex:1;">
                                <!-- Property type + Building type -->
                                <div class="listing-card-info-icon">
                                    <div class="inc-fleat-icon me-1"><i class="fas fa-home"></i></div>
                                    <?= htmlspecialchars($p['property_type']) ?>
                                    <?php if (!empty($building_type) && strtolower($building_type) !== strtolower($p['property_type'])): ?>
                                        <span style="color:#888;margin-left:4px;">· <?= htmlspecialchars($building_type) ?></span>
                                    <?php endif; ?>
                                </div>
                                <!-- Beds -->
                                <?php if (!empty($p['bedrooms'])): ?>
                                <div class="listing-card-info-icon">
                                    <div class="inc-fleat-icon me-1"><i class="fas fa-bed"></i></div>
                                    <?= $p['bedrooms'] ?> Bed<?= $p['bedrooms'] > 1 ? 's' : '' ?>
                                </div>
                                <?php endif; ?>
                                <!-- Baths -->
                                <?php if (!empty($p['bathrooms'])): ?>
                                <div class="listing-card-info-icon">
                                    <div class="inc-fleat-icon me-1"><i class="fas fa-bath"></i></div>
                                    <?= $p['bathrooms'] ?> Bath<?= $p['bathrooms'] > 1 ? 's' : '' ?>
                                </div>
                                <?php endif; ?>
                                <!-- Sqft -->
                                <?php if (!empty($p['sqft'])): ?>
                                <div class="listing-card-info-icon">
                                    <div class="inc-fleat-icon me-1"><i class="fas fa-ruler-combined"></i></div>
                                    <?= number_format($p['sqft']) ?> sqft
                                </div>
                                <?php endif; ?>
                                <!-- Maintenance fee pill -->
                                <?php if (!empty($maintenance_fee)): ?>
                                <div class="listing-card-info-icon">
                                    <div class="inc-fleat-icon me-1"><i class="fas fa-building"></i></div>
                                    Strata <?= htmlspecialchars($maintenance_fee) ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Open House notice (right side) -->
                            <?php if (!empty($open_house_dates) || !empty($open_house_from_desc)): ?>
                            <div class="open-house-notice">
                                <div class="oh-icon"><i class="fas fa-door-open"></i></div>
                                <div class="oh-body">
                                    <div class="oh-label">Open House</div>
                                    <?php if (!empty($open_house_dates)): ?>
                                        <?php foreach ($open_house_dates as $oh): ?>
                                        <div class="oh-time"><?= htmlspecialchars($oh['day']) ?>, <?= htmlspecialchars($oh['date']) ?></div>
                                        <div class="oh-time"><?= htmlspecialchars($oh['start']) ?><?= $oh['end'] ? ' – ' . htmlspecialchars($oh['end']) : '' ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="oh-time"><?= htmlspecialchars($open_house_from_desc) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

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
                                <p>No description provided for this listing.</p>
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

                        <!-- Basic Info -->
                        <div class="detail-section-label">Basic Info</div>
                        <ul class="deatil_features">
                            <li><strong>MLS®</strong><?= htmlspecialchars($p['mls_number']) ?></li>
                            <li><strong>Status</strong><span style="color:#22c55e;font-weight:600;">● Active</span></li>
                            <li><strong>Property Type</strong><?= htmlspecialchars($p['property_type'] ?: '—') ?></li>
                            <?php if (!empty($building_type)): ?>
                            <li><strong>Building Type</strong><?= htmlspecialchars($building_type) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($common_interest)): ?>
                            <li><strong>Title / Ownership</strong><?= htmlspecialchars($common_interest) ?></li>
                            <?php endif; ?>
                            <li><strong>City</strong><?= htmlspecialchars($p['city']) ?>, <?= htmlspecialchars($p['province']) ?></li>
                            <?php if (!empty($p['neighborhood']) && $p['neighborhood'] !== $p['city']): ?>
                            <li><strong>Neighbourhood</strong><?= htmlspecialchars($p['neighborhood']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($p['postal_code'])): ?>
                            <li><strong>Postal Code</strong><?= htmlspecialchars($p['postal_code']) ?></li>
                            <?php endif; ?>
                            <?php if ($days_on_market !== ''): ?>
                            <li><strong>Days on Market</strong><?= $days_on_market ?> days</li>
                            <?php endif; ?>
                            <?php if (!empty($listing_date)): ?>
                            <li><strong>Listed On</strong><?= $listing_date ?></li>
                            <?php endif; ?>
                        </ul>

                        <!-- Interior -->
                        <div class="detail-section-label" style="margin-top:20px;">Interior</div>
                        <ul class="deatil_features">
                            <li><strong>Price</strong><?= $price_display ?></li>
                            <li><strong>Bedrooms</strong><?= !empty($p['bedrooms']) ? $p['bedrooms'] : '—' ?></li>
                            <li><strong>Bathrooms</strong><?= !empty($p['bathrooms']) ? $p['bathrooms'] : '—' ?></li>
                            <li><strong>Square Footage</strong><?= !empty($p['sqft']) ? number_format($p['sqft']) . ' sqft' : '—' ?></li>
                            <?php if (!empty($fireplace)): ?>
                            <li><strong>Fireplace</strong><?= htmlspecialchars($fireplace) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($basement)): ?>
                            <li><strong>Basement</strong><?= htmlspecialchars($basement) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($appliances)): ?>
                            <li><strong>Appliances</strong><?= htmlspecialchars($appliances) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($p['heating'])): ?>
                            <li><strong>Heating</strong><?= htmlspecialchars($p['heating']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($p['cooling'])): ?>
                            <li><strong>Cooling</strong><?= htmlspecialchars($p['cooling']) ?></li>
                            <?php endif; ?>
                        </ul>

                        <!-- Exterior & Building -->
                        <div class="detail-section-label" style="margin-top:20px;">Exterior & Building</div>
                        <ul class="deatil_features">
                            <?php if (!empty($p['year_built'])): ?>
                            <li><strong>Year Built</strong><?= $p['year_built'] ?></li>
                            <?php endif; ?>
                            <?php if (!empty($lot_size)): ?>
                            <li><strong>Lot Size</strong><?= htmlspecialchars($lot_size) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($p['parking'])): ?>
                            <li><strong>Parking Spaces</strong><?= $p['parking'] ?></li>
                            <?php endif; ?>
                            <?php if (!empty($parking_type)): ?>
                            <li><strong>Parking Type</strong><?= htmlspecialchars($parking_type) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($lot_features)): ?>
                            <li><strong>Lot Features</strong><?= htmlspecialchars($lot_features) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($p['zoning'])): ?>
                            <li><strong>Zoning</strong><?= htmlspecialchars($p['zoning']) ?></li>
                            <?php endif; ?>
                        </ul>

                        <!-- Strata / Condo Info (only show if applicable) -->
                        <?php if (!empty($maintenance_fee) || !empty($community_features)): ?>
                        <div class="detail-section-label" style="margin-top:20px;">Maintenance & Condo Info</div>
                        <ul class="deatil_features">
                            <?php if (!empty($maintenance_fee)): ?>
                            <li><strong>Maintenance Fees</strong><?= htmlspecialchars($maintenance_fee) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($community_features)): ?>
                            <li><strong>Building Amenities</strong><?= htmlspecialchars($community_features) ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>

                        <!-- Financial -->
                        <div class="detail-section-label" style="margin-top:20px;">Financial</div>
                        <ul class="deatil_features">
                            <?php if (!empty($p['tax_amount'])): ?>
                            <li><strong>Property Tax</strong>$<?= number_format($p['tax_amount']) ?>/yr<?= !empty($p['tax_year']) ? ' (' . $p['tax_year'] . ')' : '' ?></li>
                            <?php endif; ?>
                            <?php if (!empty($maintenance_fee)): ?>
                            <li><strong>Strata Fee</strong><?= htmlspecialchars($maintenance_fee) ?></li>
                            <?php endif; ?>
                        </ul>

                    </div>
                </div>

                <!-- ── Virtual Tour (Matterport or Video) ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-vr-cardboard me-2" style="color:#0065ff;"></i>Virtual Tour</h4>
                    </div>
                    <div class="block-body">
                        <?php if ($virtual_tour_url): ?>
                            <?php
                            // Build correct embed URL based on tour type
                            $embed_url = $virtual_tour_url;
                            if ($tour_type === 'vimeo') {
                                // Extract Vimeo ID and build embed URL
                                preg_match('/vimeo\.com\/(\d+)/', $virtual_tour_url, $m);
                                $embed_url = !empty($m[1]) ? 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=0&title=0&byline=0&portrait=0' : $virtual_tour_url;
                            } elseif ($tour_type === 'youtube') {
                                // Convert youtube.com/watch?v= to embed
                                preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $virtual_tour_url, $m);
                                $embed_url = !empty($m[1]) ? 'https://www.youtube.com/embed/' . $m[1] : $virtual_tour_url;
                            } elseif ($tour_type === 'matterport') {
                                $embed_url = $virtual_tour_url . '&play=1&qs=1';
                            }
                            ?>
                            <?php if ($tour_type === 'matterport'): ?>
                                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;">
                                    <iframe src="<?= htmlspecialchars($embed_url) ?>"
                                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                            allowfullscreen allow="xr-spatial-tracking" loading="lazy"></iframe>
                                </div>
                                <p style="font-size:12px;color:#888;margin-top:8px;"><i class="fas fa-cube me-1"></i>3D Virtual Tour powered by Matterport</p>
                            <?php elseif ($tour_type === 'vimeo'): ?>
                                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;">
                                    <iframe src="<?= htmlspecialchars($embed_url) ?>"
                                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                            allowfullscreen loading="lazy"></iframe>
                                </div>
                                <p style="font-size:12px;color:#888;margin-top:8px;"><i class="fab fa-vimeo-v me-1" style="color:#1ab7ea;"></i>Video Tour</p>
                            <?php elseif ($tour_type === 'youtube'): ?>
                                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;">
                                    <iframe src="<?= htmlspecialchars($embed_url) ?>"
                                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                            allowfullscreen loading="lazy"></iframe>
                                </div>
                                <p style="font-size:12px;color:#888;margin-top:8px;"><i class="fab fa-youtube me-1" style="color:#ff0000;"></i>Video Tour</p>
                            <?php else: ?>
                                <!-- Generic iframe for other tour platforms -->
                                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;">
                                    <iframe src="<?= htmlspecialchars($embed_url) ?>"
                                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                            allowfullscreen loading="lazy"></iframe>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="coming-soon-block video-placeholder">
                                <i class="fas fa-vr-cardboard"></i>
                                <p>No virtual tour available for this listing.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Gallery ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h4 class="property_block_title">Gallery</h4>
                            <?php if ($total_photos > 0): ?>
                            <span style="font-size:12px;color:#888;"><?= $total_photos ?> photo<?= $total_photos > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="block-body">
                        <?php if ($has_photos): ?>
                            <!-- First 6 shown in grid -->
                            <div class="gallery-placeholder-grid" id="gallery-grid">
                                <?php foreach (array_slice($photos, 0, 5) as $idx => $photo): ?>
                                <div class="gallery-real-tile" onclick="openModal(<?= $idx ?>)">
                                    <img src="<?= htmlspecialchars($photo) ?>" alt="Photo <?= $idx+1 ?>" loading="lazy">
                                    <div class="gallery-tile-hover"><i class="fas fa-expand-alt"></i></div>
                                </div>
                                <?php endforeach; ?>
                                <!-- Last tile is always "View All" overlay -->
                                <div class="gallery-expand-overlay" onclick="document.getElementById('gallery-modal').style.display='flex'">
                                    <i class="fas fa-expand-alt"></i>
                                    <span>View All <?= $total_photos ?> Photos</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="coming-soon-block">
                                <i class="fas fa-camera"></i>
                                <p>No photos available for this listing.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gallery modal — shows ALL photos -->
                <div id="gallery-modal" class="gallery-modal" style="display:none;" onclick="this.style.display='none'">
                    <div class="gallery-modal-inner" onclick="event.stopPropagation()">
                        <button class="gallery-modal-close" onclick="document.getElementById('gallery-modal').style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h5 style="color:#fff;margin:0;">
                                <i class="fas fa-building me-2"></i><?= htmlspecialchars($p['address']) ?>
                            </h5>
                            <span style="color:#888;font-size:13px;" id="modal-photo-counter"></span>
                        </div>
                        <!-- Main photo with prev/next arrows -->
                        <div style="position:relative;width:100%;margin-bottom:12px;">
                            <div style="border-radius:8px;overflow:hidden;background:#1e2836;min-height:300px;display:flex;align-items:center;justify-content:center;">
                                <img id="modal-main-img" src="<?= $has_photos ? htmlspecialchars($photos[0]) : '' ?>"
                                     style="width:100%;max-height:460px;object-fit:contain;" alt="Photo">
                            </div>
                            <button onclick="modalNav(-1)" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.6);border:none;color:#fff;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button onclick="modalNav(1)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.6);border:none;color:#fff;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <!-- Scrollable thumbnails — ALL photos -->
                        <?php if ($has_photos): ?>
                        <div id="modal-thumbs" style="display:flex;gap:6px;overflow-x:auto;padding-bottom:6px;">
                            <?php foreach ($photos as $idx => $photo): ?>
                            <img src="<?= htmlspecialchars($photo) ?>"
                                 data-idx="<?= $idx ?>"
                                 onclick="setModalPhoto(<?= $idx ?>)"
                                 style="width:72px;height:54px;object-fit:cover;border-radius:5px;cursor:pointer;flex-shrink:0;border:2px solid <?= $idx===0?'#22c55e':'transparent' ?>;transition:border 0.2s;"
                                 loading="lazy">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Sale & Listing History ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-history me-2" style="color:#0065ff;"></i>Listing History</h4>
                    </div>
                    <div class="block-body">
                        <table class="sale-history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Current listing -->
                                <tr class="sale-history-current">
                                    <td><?= !empty($listing_date) ? $listing_date : '—' ?></td>
                                    <td><span class="sh-badge active">Active</span></td>
                                    <td><?= $price_display ?></td>
                                    <td>—</td>
                                </tr>
                                <?php
                                // Check if price has been modified (price change)
                                $orig_ts = $raw['OriginalEntryTimestamp'] ?? null;
                                $mod_ts  = $raw['ModificationTimestamp'] ?? null;
                                $status_change_ts = $raw['StatusChangeTimestamp'] ?? null;
                                if (!empty($mod_ts) && $mod_ts !== $orig_ts):
                                ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($mod_ts)) ?></td>
                                    <td><span class="sh-badge updated">Updated</span></td>
                                    <td><?= $price_display ?></td>
                                    <td><span style="color:#888;font-size:12px;">Price/details updated</span></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <p style="font-size:11px;color:#bbb;margin-top:12px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Full sale history is available through your REALTOR®. <a href="agent-page.php" style="color:#0065ff;">Contact us</a> for a complete price history report.
                        </p>
                    </div>
                </div>

                <!-- ── School Catchment ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-graduation-cap me-2" style="color:#0065ff;"></i>School Catchment</h4>
                    </div>
                    <div class="block-body">
                        <p style="font-size:13px;color:#888;margin-bottom:16px;">
                            Schools serving this address based on local school board catchment boundaries.
                        </p>
                        <?php if ($schools): ?>
                        <?php
                        // Detect if this is a real school name or a city-level locator fallback
                        $is_real_school = !str_starts_with($schools['elementary'], 'Use ') && !str_starts_with($schools['elementary'], 'Based on');
                        ?>
                        <?php if ($is_real_school): ?>
                        <!-- Real school names found — show cards -->
                        <div class="school-catchment-grid">
                            <div class="school-card">
                                <div class="school-icon elementary"><i class="fas fa-school"></i></div>
                                <div class="school-info">
                                    <span class="school-type">Elementary School</span>
                                    <strong class="school-name"><?= htmlspecialchars($schools['elementary']) ?></strong>
                                    <span class="school-board"><?= htmlspecialchars($schools['board'] ?? 'School Board') ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($school_locator_url) ?>" target="_blank" class="school-link"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                            <div class="school-card">
                                <div class="school-icon secondary"><i class="fas fa-university"></i></div>
                                <div class="school-info">
                                    <span class="school-type">Secondary School</span>
                                    <strong class="school-name"><?= htmlspecialchars($schools['secondary']) ?></strong>
                                    <span class="school-board"><?= htmlspecialchars($schools['board'] ?? 'School Board') ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($school_locator_url) ?>" target="_blank" class="school-link"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- City-level fallback — show locator button -->
                        <a href="<?= htmlspecialchars($school_locator_url) ?>" target="_blank" rel="noopener"
                           style="display:flex;align-items:center;gap:14px;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;text-decoration:none;color:#002446;transition:box-shadow .2s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(0,101,255,0.15)'"
                           onmouseout="this.style.boxShadow='none'">
                            <div style="width:44px;height:44px;background:#0065ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-graduation-cap" style="color:#fff;font-size:18px;"></i>
                            </div>
                            <div>
                                <strong style="display:block;font-size:14px;margin-bottom:3px;"><?= htmlspecialchars($schools['board']) ?> School Locator</strong>
                                <span style="font-size:12px;color:#666;">Enter your address to find assigned schools →</span>
                            </div>
                            <i class="fas fa-external-link-alt ms-auto" style="color:#0065ff;opacity:.7;"></i>
                        </a>
                        <?php endif; ?>
                        <p style="font-size:11px;color:#bbb;margin-top:12px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Always verify with the <a href="<?= htmlspecialchars($school_locator_url) ?>" target="_blank" style="color:#0065ff;">official school locator</a>.
                        </p>
                        <?php else: ?>
                        <!-- No catchment found — show direct link to school district locator -->
                        <?php
                        $city_lower = strtolower(trim($p['city'] ?? ''));
                        $locator_links = [
                            'burnaby'       => ['name' => 'Burnaby SD41 School Locator',       'url' => 'https://www.sd41.bc.ca/programs-services/registration/school-locator/'],
                            'richmond'      => ['name' => 'Richmond SD38 School Locator',      'url' => 'https://www.sd38.bc.ca/schools/find-a-school'],
                            'north vancouver' => ['name' => 'North Van SD44 School Locator',   'url' => 'https://www.nvsd44.bc.ca/Schools'],
                            'west vancouver' => ['name' => 'West Van SD45 School Locator',     'url' => 'https://westvancouverschools.ca/schools/'],
                            'coquitlam'     => ['name' => 'SD43 School Locator',               'url' => 'https://www.sd43.bc.ca/SchoolLocator/Pages/default.aspx'],
                            'port coquitlam'=> ['name' => 'SD43 School Locator',               'url' => 'https://www.sd43.bc.ca/SchoolLocator/Pages/default.aspx'],
                        ];
                        $locator = $locator_links[$city_lower] ?? ['name' => 'VSB School Locator', 'url' => 'https://www.vsb.bc.ca/Student_Registration/Pages/School-Locator.aspx'];
                        ?>
                        <a href="<?= htmlspecialchars($locator['url']) ?>" target="_blank"
                           style="display:flex;align-items:center;gap:14px;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;text-decoration:none;color:#002446;transition:box-shadow .2s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(0,101,255,0.15)'"
                           onmouseout="this.style.boxShadow='none'">
                            <div style="width:44px;height:44px;background:#0065ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-graduation-cap" style="color:#fff;font-size:18px;"></i>
                            </div>
                            <div>
                                <strong style="display:block;font-size:14px;margin-bottom:2px;"><?= htmlspecialchars($locator['name']) ?></strong>
                                <span style="font-size:12px;color:#666;">Find schools for <?= htmlspecialchars($p['address']) ?></span>
                            </div>
                            <i class="fas fa-external-link-alt ms-auto" style="color:#0065ff;"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Walk Score ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-walking me-2" style="color:#0065ff;"></i>Walk Score</h4>
                    </div>
                    <div class="block-body">
                        <p style="font-size:13px;color:#888;margin-bottom:16px;">Walkability, transit, and bike scores for this address.</p>
                        <style>#ws-walkscore-tile { position:relative; text-align:left; min-height:60px; } #ws-walkscore-tile * { float:none; }</style>
                        <script type="text/javascript">
                            var ws_wsid    = '361dbc9c010ccf76ceea407fa304e222';
                            var ws_address = <?= json_encode(trim($p['address'] . ', ' . $p['city'] . ', BC, Canada')) ?>;
                            var ws_lat     = '<?= number_format((float)($p['latitude'] ?? 0), 7) ?>';
                            var ws_lon     = '<?= number_format((float)($p['longitude'] ?? 0), 7) ?>';
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
                            s.async = true;
                            s.src   = 'https://www.walkscore.com/tile/show-walkscore-tile.php';
                            s.onload = function() { var l=document.getElementById('ws-loading'); if(l) l.remove(); };
                            s.onerror = function() {
                                var t = document.getElementById('ws-walkscore-tile');
                                if (t) t.innerHTML = '<a href="https://www.walkscore.com/score/<?= urlencode($p['address'] . ' ' . $p['city']) ?>" target="_blank" style="display:inline-block;background:#f8f9fb;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;text-decoration:none;color:#002446;font-size:13px;"><i class="fas fa-external-link-alt me-2" style="color:#0065ff;"></i>View Walk Score for this address</a>';
                            };
                            document.body.appendChild(s);
                        })();
                        </script>
                        <p style="font-size:11px;color:#bbb;margin-top:12px;">Scores provided by <a href="https://www.walkscore.com" target="_blank" style="color:#0065ff;">Walk Score®</a></p>
                    </div>
                </div>

                <!-- ── Nearby Restaurants (Yelp + Google Maps) ── -->
                <div class="property_block_wrap">
                    <div class="property_block_wrap_header">
                        <h4 class="property_block_title"><i class="fas fa-utensils me-2" style="color:#d32323;"></i>Nearby Restaurants & Cafés</h4>
                    </div>
                    <div class="block-body">
                        <?php
                        $full_address = urlencode($p['address'] . ', ' . $p['city'] . ', BC');
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
                                    <span>Restaurants & cafés near <?= htmlspecialchars($p['city']) ?></span>
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

            </div>
            <!-- /main column -->

            <!-- ══ SIDEBAR ══ -->
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="property-sidebar side_stiky">


<div class="details-sidebar">

                    <!-- Agent Detail (same style as property-sidebar1.php) -->
                    <div class="details-sidebar">
                        <div class="sides-widget">
                            <div class="sides-widget-header bg-primary">
                                <div class="agent-photo">
                                    <img src="<?= $static_url ?>/img/user-6.jpg" alt="Tam Nguyen"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIzMCIgY3k9IjMwIiByPSIzMCIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIwLjIiLz48Y2lyY2xlIGN4PSIzMCIgY3k9IjI0IiByPSIxMCIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIwLjYiLz48cGF0aCBkPSJNMTAgNTJjMC0xMSA5LTIwIDIwLTIwczIwIDkgMjAgMjAiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC42Ii8+PC9zdmc+'">
                                </div>
                                <div class="sides-widget-details">
                                    <h4><a href="https://tamwynn.ca" target="_blank" rel="noopener">Tam Nguyen</a></h4>
                                    </h4>
                                    <span><i class="lni-phone-handset"></i>(604) 782-4689</span>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="sides-widget-body simple-form">
                                <form id="contact-form" onsubmit="return submitContactForm(event)">
                                    <input type="hidden" name="property_address" value="<?= htmlspecialchars($p['address']) ?>">
                                    <input type="hidden" name="mls_number" value="<?= htmlspecialchars($p['mls_number']) ?>">

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone No.</label>
                                        <input type="tel" name="phone" class="form-control" placeholder="Your Phone">
                                    </div>
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea name="message" class="form-control">I'm interested in MLS® <?= htmlspecialchars($p['mls_number']) ?> at <?= htmlspecialchars($p['address']) ?>. Please contact me.</textarea>
                                    </div>

                                    <!-- Date picker -->
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt me-1" style="color:#0065ff;"></i>Preferred Showing Date</label>
                                        <div class="date-picker-wrap">
                                            <div class="date-picker-nav">
                                                <button type="button" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                                                <span id="cal-month-label"></span>
                                                <button type="button" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                                            </div>
                                            <div class="date-picker-grid" id="cal-grid"></div>
                                            <input type="hidden" name="showing_date" id="showing_date">
                                            <div id="selected-date-display" style="font-size:12px;color:#0065ff;margin-top:6px;font-weight:600;min-height:18px;"></div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-light-primary fw-medium rounded full-width">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                    <div id="form-success" style="display:none;margin-top:12px;background:#e8f8e8;border:1px solid #22c55e;border-radius:8px;padding:12px;font-size:13px;color:#166534;">
                                        <i class="fas fa-check-circle me-1"></i> Message sent! We'll be in touch shortly.
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ── Mortgage Calculator ── -->
                    <div class="property_block_wrap mb-4">
                        <div class="property_block_wrap_header">
                            <h4 class="property_block_title"><i class="fas fa-calculator me-2" style="color:#0065ff;"></i>Mortgage Calculator</h4>
                        </div>
                        <div class="block-body">
                            <div class="form-group mb-3">
                                <label style="font-size:12px;color:#888;">Home Price</label>
                                <input type="number" id="mc-price" class="form-control"
                                       value="<?= !empty($p['price']) ? (int)$p['price'] : 1000000 ?>"
                                       oninput="calcMortgage()">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label style="font-size:12px;color:#888;">Down Payment (%)</label>
                                        <input type="number" id="mc-down" class="form-control" value="20" min="5" max="100" oninput="calcMortgage()">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label style="font-size:12px;color:#888;">Interest Rate (%)</label>
                                        <input type="number" id="mc-rate" class="form-control" value="5.5" step="0.1" min="0.1" oninput="calcMortgage()">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label style="font-size:12px;color:#888;">Amortization (years)</label>
                                <select id="mc-years" class="form-control" onchange="calcMortgage()">
                                    <option value="25" selected>25 years</option>
                                    <option value="20">20 years</option>
                                    <option value="15">15 years</option>
                                    <option value="30">30 years</option>
                                </select>
                            </div>
                            <div class="mortgage-result">
                                <div class="mortgage-result-label">Est. Monthly Payment</div>
                                <div class="mortgage-result-amount" id="mc-result">$—</div>
                                <div class="mortgage-result-note" id="mc-breakdown"></div>
                            </div>
                            <p style="font-size:10px;color:#bbb;margin-top:10px;">
                                Estimate only. Contact a mortgage broker for accurate rates.
                            </p>
                        </div>
                    </div>

                    <!-- ── Bottom navigation ── -->
                    <div class="bottom-nav-buttons">
                        <a href="active-listings.php" class="btn-bottom-nav secondary">
                            <i class="fas fa-map me-2"></i>Back to Map Search
                        </a>
                        <a href="agent-page.php" class="btn-bottom-nav primary">
                            <i class="fas fa-user-tie me-2"></i>Contact Agent
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Call To Action -->
<section class="bg-primary call-to-act-wrap">
    <div class="container">
        <?php include "$base_dir/Components/Home/estate-agent.php"; ?>
    </div>
</section>

<script>
// ── Gallery modal with full navigation ───────────────────────────────────────
var photos = <?= json_encode($photos) ?>;
var currentModalIdx = 0;

function openModal(idx) {
    currentModalIdx = idx || 0;
    document.getElementById('gallery-modal').style.display = 'flex';
    setModalPhoto(currentModalIdx);
}

function setModalPhoto(idx) {
    if (idx < 0) idx = photos.length - 1;
    if (idx >= photos.length) idx = 0;
    currentModalIdx = idx;
    var img = document.getElementById('modal-main-img');
    if (img) img.src = photos[idx];
    // Update counter
    var counter = document.getElementById('modal-photo-counter');
    if (counter) counter.textContent = (idx + 1) + ' / ' + photos.length;
    // Update thumbnail borders
    document.querySelectorAll('#modal-thumbs img').forEach(function(th) {
        th.style.borderColor = parseInt(th.getAttribute('data-idx')) === idx ? '#22c55e' : 'transparent';
    });
    // Scroll active thumb into view
    var activeThumb = document.querySelector('#modal-thumbs img[data-idx="' + idx + '"]');
    if (activeThumb) activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
}

function modalNav(dir) {
    setModalPhoto(currentModalIdx + dir);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('gallery-modal').style.display === 'flex') {
        if (e.key === 'ArrowRight') modalNav(1);
        if (e.key === 'ArrowLeft')  modalNav(-1);
        if (e.key === 'Escape')     document.getElementById('gallery-modal').style.display = 'none';
    }
});

// ── Calendar date picker ──────────────────────────────────────────────────────
var calDate = new Date();
calDate.setDate(1);
var selectedDate = null;
var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
var days   = ['Su','Mo','Tu','We','Th','Fr','Sa'];

function changeMonth(dir) {
    calDate.setMonth(calDate.getMonth() + dir);
    renderCal();
}

function renderCal() {
    var label = document.getElementById('cal-month-label');
    var grid  = document.getElementById('cal-grid');
    if (!label || !grid) return;

    label.textContent = months[calDate.getMonth()] + ' ' + calDate.getFullYear();
    grid.innerHTML = '';

    // Day headers
    days.forEach(function(d) {
        var h = document.createElement('div');
        h.className = 'cal-day-header';
        h.textContent = d;
        grid.appendChild(h);
    });

    var firstDay = new Date(calDate.getFullYear(), calDate.getMonth(), 1).getDay();
    var daysInMonth = new Date(calDate.getFullYear(), calDate.getMonth() + 1, 0).getDate();
    var today = new Date(); today.setHours(0,0,0,0);

    // Empty cells before first day
    for (var i = 0; i < firstDay; i++) {
        var empty = document.createElement('div');
        empty.className = 'cal-day empty';
        grid.appendChild(empty);
    }

    // Day cells
    for (var d = 1; d <= daysInMonth; d++) {
        var cell = document.createElement('div');
        var cellDate = new Date(calDate.getFullYear(), calDate.getMonth(), d);
        cell.className = 'cal-day' + (cellDate < today ? ' past' : '');
        cell.textContent = d;

        if (cellDate >= today) {
            (function(date, el) {
                el.addEventListener('click', function() {
                    selectedDate = date;
                    document.getElementById('showing_date').value =
                        date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
                    document.getElementById('selected-date-display').textContent =
                        '✓ ' + months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
                    document.querySelectorAll('.cal-day.selected').forEach(function(e) { e.classList.remove('selected'); });
                    el.classList.add('selected');
                });
            })(cellDate, cell);
        }
        grid.appendChild(cell);
    }
}

// ── Contact form submit ───────────────────────────────────────────────────────
function submitContactForm(e) {
    e.preventDefault();
    var form = document.getElementById('contact-form');
    var data = new FormData(form);
    // In production wire this to your contact handler
    document.getElementById('form-success').style.display = 'block';
    form.querySelector('button[type=submit]').disabled = true;
    return false;
}

// ── Mortgage calculator ───────────────────────────────────────────────────────
function calcMortgage() {
    var price = parseFloat(document.getElementById('mc-price').value) || 0;
    var down  = parseFloat(document.getElementById('mc-down').value)  || 20;
    var rate  = parseFloat(document.getElementById('mc-rate').value)  || 5.5;
    var years = parseInt(document.getElementById('mc-years').value)   || 25;

    var principal = price * (1 - down / 100);
    var monthlyRate = rate / 100 / 12;
    var n = years * 12;
    var payment = principal * (monthlyRate * Math.pow(1 + monthlyRate, n)) / (Math.pow(1 + monthlyRate, n) - 1);

    var resultEl    = document.getElementById('mc-result');
    var breakdownEl = document.getElementById('mc-breakdown');

    if (isNaN(payment) || payment <= 0) {
        resultEl.textContent    = '$—';
        breakdownEl.textContent = '';
        return;
    }

    resultEl.textContent    = '$' + Math.round(payment).toLocaleString() + '/mo';
    breakdownEl.textContent = 'Loan: $' + Math.round(principal).toLocaleString() + ' · ' + rate + '% · ' + years + ' yrs';
}


// ── Share property ────────────────────────────────────────────────────────────
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: '<?= htmlspecialchars($p['address']) ?>',
            text: 'Check out this listing: MLS® <?= htmlspecialchars($p['mls_number']) ?> — <?= $price_display ?>',
            url: window.location.href
        }).catch(function() {});
    } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            var btn = document.querySelector('.btn-share-save.share');
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.style.background = '#22c55e';
            btn.style.color = '#fff';
            setTimeout(function() { btn.innerHTML = orig; btn.style.background = ''; btn.style.color = ''; }, 2000);
        });
    }
}

// ── Save property (placeholder — wire to user account later) ─────────────────
function saveProperty() {
    var btn   = document.getElementById('save-btn');
    var label = document.getElementById('save-label');
    var saved = btn.classList.toggle('saved');
    label.textContent = saved ? 'Saved!' : 'Save';
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    renderCal();
    calcMortgage();
});
</script>

<style>
/* ── Section blocks ───────────────────────────────────────────────────── */
.property_block_wrap {
    background: #fff; border-radius: 8px; margin-bottom: 24px; overflow: hidden;
}
.property_block_wrap_header {
    padding: 18px 24px 14px; border-bottom: 1px solid #f0f0f0;
}
.property_block_title { margin: 0; font-size: 18px; font-weight: 700; color: #002446; }
.block-body { padding: 20px 24px; }

/* ── Hero placeholder ─────────────────────────────────────────────────── */
.generic-hero-placeholder {
    width: 100%; height: 280px;
    background: linear-gradient(135deg, #1a7a4a 0%, #15803d 60%, #22c55e 100%);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.85); gap: 10px;
}
.generic-hero-placeholder i     { font-size: 56px; opacity: 0.6; }
.generic-hero-placeholder span  { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
.generic-hero-placeholder small { font-size: 12px; opacity: 0.65; }

/* ── Open house notice ────────────────────────────────────────────────── */
.open-house-notice { display:flex; align-items:center; gap:12px; background:linear-gradient(135deg,#fff7ed,#fef3c7); border:1.5px solid #fbbf24; border-radius:10px; padding:12px 16px; min-width:160px; flex-shrink:0; }
.oh-icon { width:38px; height:38px; background:#f59e0b; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px; flex-shrink:0; }
.oh-body { display:flex; flex-direction:column; gap:2px; }
.oh-label { font-size:10px; text-transform:uppercase; letter-spacing:.8px; font-weight:700; color:#92400e; }
.oh-time  { font-size:13px; font-weight:700; color:#78350f; line-height:1.3; }

/* ── Detail section label ─────────────────────────────────────────────── */
.detail-section-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:700; color:#0065ff; margin-bottom:10px; padding-bottom:6px; border-bottom:2px solid #e8f0ff; }

/* ── Sale history table ───────────────────────────────────────────────── */
.sale-history-table { width:100%; border-collapse:collapse; font-size:13px; }
.sale-history-table th { text-align:left; padding:8px 12px; font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:#888; border-bottom:2px solid #f0f0f0; }
.sale-history-table td { padding:12px; border-bottom:1px solid #f5f5f5; color:#444; }
.sale-history-current td { background:#f0fff4; font-weight:600; }
.sh-badge { font-size:10px; font-weight:700; padding:3px 8px; border-radius:4px; text-transform:uppercase; }
.sh-badge.active  { background:#dcfce7; color:#166534; }
.sh-badge.updated { background:#dbeafe; color:#1e40af; }
.sh-badge.sold    { background:#fee2e2; color:#991b1b; }

/* ── Nearby links ─────────────────────────────────────────────────────── */
.nearby-links-row { display:flex; flex-direction:column; gap:10px; }
.nearby-link-btn { display:flex; align-items:center; gap:14px; padding:14px 16px; border-radius:10px; text-decoration:none; transition:box-shadow .2s,transform .15s; }
.nearby-link-btn:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); transform:translateY(-1px); }
.nearby-link-btn > i:first-child { font-size:22px; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.nearby-link-btn div { flex:1; }
.nearby-link-btn div strong { display:block; font-size:14px; margin-bottom:2px; }
.nearby-link-btn div span { font-size:12px; }
.nearby-link-btn.yelp { background:#fff5f5; border:1px solid #fecaca; color:#991b1b; }
.nearby-link-btn.yelp > i:first-child { background:#d32323; color:#fff; }
.nearby-link-btn.gmaps { background:#f0f9ff; border:1px solid #bae6fd; color:#0369a1; }
.nearby-link-btn.gmaps > i:first-child { background:#0284c7; color:#fff; }

/* ── Coming soon ──────────────────────────────────────────────────────── */
.coming-soon-block {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 36px 20px; background: #f8f9fb; border: 2px dashed #d0d8e8;
    border-radius: 8px; text-align: center; color: #aab; gap: 10px;
}
.coming-soon-block i { font-size: 36px; opacity: 0.4; }
.coming-soon-block p { margin: 0; font-size: 13px; color: #889; line-height: 1.6; }
.video-placeholder { min-height: 180px; }

/* ── Gallery ──────────────────────────────────────────────────────────── */
.gallery-placeholder-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; position: relative; }
.gallery-real-tile { aspect-ratio: 4/3; border-radius: 6px; overflow: hidden; position: relative; cursor: pointer; }
.gallery-real-tile img { width:100%; height:100%; object-fit:cover; display:block; transition: transform 0.3s; }
.gallery-real-tile:hover img { transform: scale(1.05); }
.gallery-tile-hover { position: absolute; inset: 0; background: rgba(0,36,70,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; color: #fff; font-size: 22px; }
.gallery-real-tile:hover .gallery-tile-hover { opacity: 1; }
.gallery-placeholder-tile { aspect-ratio: 4/3; background: #f0f3f8; border: 2px dashed #d0d8e8; border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #ccd; gap: 6px; }
.gallery-placeholder-tile i { font-size: 22px; }
.gallery-tile-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #bbc; }
.gallery-expand-overlay { position: absolute; bottom: 0; right: 0; width: calc(33.33% - 5px); aspect-ratio: 4/3; background: rgba(0,36,70,0.82); border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; cursor: pointer; gap: 8px; transition: background 0.2s; }
.gallery-expand-overlay:hover { background: rgba(34,197,94,0.88); }
.gallery-expand-overlay i { font-size: 22px; }
.gallery-expand-overlay span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }
.gallery-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; }
.gallery-modal-inner { background: #111820; border-radius: 12px; padding: 28px; width: 100%; max-width: 860px; max-height: 90vh; overflow-y: auto; position: relative; }
.gallery-modal-close { position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.1); border: none; color: #fff; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.gallery-modal-close:hover { background: #22c55e; }

/* ── Yelp block ───────────────────────────────────────────────────────── */
.yelp-placeholder { display: flex; align-items: center; gap: 16px; background: #fff5f5; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; }
.yelp-icon-wrap { width: 48px; height: 48px; background: #d32323; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; flex-shrink: 0; }
.yelp-content { flex: 1; }
.yelp-content strong { font-size: 14px; color: #002446; display: block; margin-bottom: 2px; }
.yelp-content p { font-size: 12px; color: #888; margin: 0; }
.btn-yelp { background: #d32323; color: #fff; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background 0.2s; }
.btn-yelp:hover { background: #b91c1c; color: #fff; }

/* ── School catchment ─────────────────────────────────────────────────── */
.school-catchment-grid { display: flex; flex-direction: column; gap: 12px; }
.school-card { display: flex; align-items: center; gap: 16px; background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; transition: box-shadow 0.2s; }
.school-card:hover { box-shadow: 0 4px 16px rgba(0,36,70,0.08); }
.school-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.school-icon.elementary { background: #e8f4ff; color: #0065ff; }
.school-icon.secondary  { background: #fff3e8; color: #f07600; }
.school-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.school-type  { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #aaa; font-weight: 600; }
.school-name  { font-size: 14px; font-weight: 700; color: #002446; }
.school-board { font-size: 11px; color: #888; }
.school-link  { color: #ccd; font-size: 13px; text-decoration: none; padding: 8px; border-radius: 6px; transition: color 0.2s, background 0.2s; }
.school-link:hover { color: #0065ff; background: #e8f0ff; }

/* ── Property details list ────────────────────────────────────────────── */
.deatil_features { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px 24px; }
.deatil_features li { font-size: 13px; color: #444; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
.deatil_features li strong { color: #002446; font-weight: 600; display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }

/* ── Mortgage result ──────────────────────────────────────────────────── */
.mortgage-result { background: #f0f7ff; border: 1px solid #dce8ff; border-radius: 10px; padding: 16px; text-align: center; }
.mortgage-result-label  { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #888; margin-bottom: 4px; }
.mortgage-result-amount { font-size: 28px; font-weight: 800; color: #0065ff; }
.mortgage-result-note   { font-size: 11px; color: #888; margin-top: 4px; }

/* ── Date picker calendar ─────────────────────────────────────────────── */
.date-picker-wrap { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
.date-picker-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.date-picker-nav button { background: none; border: 1px solid #e2e8f0; border-radius: 6px; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #666; transition: all 0.2s; }
.date-picker-nav button:hover { background: #0065ff; color: #fff; border-color: #0065ff; }
.date-picker-nav span { font-size: 13px; font-weight: 700; color: #002446; }
.date-picker-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
.cal-day-header { text-align: center; font-size: 10px; font-weight: 700; color: #aaa; padding: 4px 0; text-transform: uppercase; }
.cal-day { text-align: center; font-size: 12px; padding: 6px 2px; border-radius: 6px; cursor: pointer; color: #444; transition: all 0.15s; }
.cal-day:not(.empty):not(.past):hover { background: #e8f0ff; color: #0065ff; }
.cal-day.selected { background: #0065ff; color: #fff; font-weight: 700; }
.cal-day.past { color: #ccc; cursor: default; }
.cal-day.empty { cursor: default; }

/* ── Share & Save buttons ─────────────────────────────────────────────── */
.share-save-wrap { display: flex; gap: 12px; }
.btn-share-save {
    flex: 1; padding: 12px; border-radius: 10px; font-size: 14px;
    font-weight: 600; cursor: pointer; border: 2px solid; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-share-save.share { background: #f0fdf4; border-color: #22c55e; color: #16a34a; }
.btn-share-save.share:hover { background: #22c55e; color: #fff; }
.btn-share-save.save  { background: #fff7ed; border-color: #f97316; color: #ea580c; }
.btn-share-save.save:hover  { background: #f97316; color: #fff; }
.btn-share-save.save.saved  { background: #f97316; color: #fff; border-color: #f97316; }

/* ── Agent card ───────────────────────────────────────────────────────── */
.agent-card-wrap {
    display: flex; align-items: center; gap: 16px;
    background: #0065ff; border-radius: 12px; padding: 16px 20px;
}
.agent-avatar-circle {
    width: 60px; height: 60px; border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.agent-avatar-circle i { font-size: 26px; color: #fff; }
.agent-info { display: flex; flex-direction: column; gap: 3px; }
.agent-info strong { color: #fff; font-size: 16px; font-weight: 700; }
.agent-info span   { color: rgba(255,255,255,0.85); font-size: 14px; }


/* ── Bottom nav buttons ───────────────────────────────────────────────── */
.bottom-nav-buttons {
    display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;
}
.btn-bottom-nav {
    display: flex; align-items: center; justify-content: center;
    padding: 13px 20px; border-radius: 10px; font-size: 14px;
    font-weight: 600; text-decoration: none; transition: all 0.2s;
}
.btn-bottom-nav.secondary {
    background: #f1f5f9; color: #002446; border: 1px solid #e2e8f0;
}
.btn-bottom-nav.secondary:hover { background: #e2e8f0; color: #002446; }
.btn-bottom-nav.primary {
    background: #0065ff; color: #fff; border: 1px solid #0065ff;
}
.btn-bottom-nav.primary:hover { background: #0052d4; color: #fff; }

</style>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>