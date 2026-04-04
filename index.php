<?php
include 'Base/data_loader.php';
// echo count($all_listings); // Uncomment this to see if it counts the rows!		
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';

// Include the common navlink content
ob_start();
include "$base_dir/navbar5.php"; // This file contains the shared navlink content
$navlink_content = ob_get_clean(); // Capture the navlink content
$page= 'nav2';
$fpage= 'foot';

// Optionally define the Hero block content
ob_start();
?>
			
			
<!-- ============================ Hero Banner  Start================================== -->
<div class="hero-banner" style="background:#002446 url(<?php echo $static_url; ?>/img/new-banner.jpg) no-repeat center center / cover;" data-overlay="7">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="inner-banner-text text-center">
                    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:16px;">Metro Vancouver New Construction — Tracked in Real Time</p>
                    <h2 class="lead-i" style="font-size: 48px !important; font-weight: 900; text-transform: none; letter-spacing: -1px; color: #fff; opacity: 1; line-height: 1.1; margin-bottom: 20px;">The Market Moves Before MLS Does.<br>Wynston Lets You See It First.</h2>
                    <p style="font-size:17px;color:rgba(255,255,255,.8);max-width:620px;margin:16px auto 0;line-height:1.7;">Upcoming developments. Active listings. Market intelligence. Everything happening in Vancouver real estate — in one place, before anyone else has it.</p>
                </div>

                <form action="search-router.php" method="GET">
                    <div class="full-search-2 eclip-search italian-search hero-search-radius shadow-hard mt-5">
                        <div class="hero-search-content">
                            <div class="row align-items-center">
                                
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 b-r">
                                    <div class="form-group mb-0">
                                        <div class="choose-propert-type">
                                            <ul>
                                                <li>
                                                    <input class="form-check-input" type="radio" name="st" id="st_presale" value="presale" onchange="togglePropertyTypes()">
                                                    <label class="form-check-label" for="st_presale">Coming Soon</label>
                                                </li>
                                                <li>
                                                    <input class="form-check-input" type="radio" name="st" id="st_active" value="active" checked onchange="togglePropertyTypes()">
                                                    <label class="form-check-label" for="st_active">Active</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 b-r">
                                    <div class="form-group mb-0">
                                        <div class="position-relative ps-5">
                                            <select name="city" class="form-control border-0">
                                                <option value="">Select City</option>
                                                <option value="Vancouver">Vancouver</option>
                                                <option value="Burnaby">Burnaby</option>
                                                <option value="Richmond">Richmond</option>
                                                <option value="West Vancouver">West Vancouver</option>
                                                <option value="North Vancouver">North Vancouver</option>
                                                <option value="Coquitlam">Coquitlam</option>
                                                <option value="Port Coquitlam">Port Coquitlam</option>
                                            </select>
                                            <div class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                                <i class="fa-solid fa-city text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 b-r">
                                    <div class="form-group mb-0">
                                        <div class="position-relative ps-5">
                                            <select id="dynamic_ptypes" name="ptype" class="form-control border-0"></select>
                                            <div class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                                <i class="fa-solid fa-house text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
                                    <div class="form-group mb-0">
                                        <button type="button" onclick="wynSearchClick()" class="btn btn-multiplex-search full-width">Search Properties</button>
                                    </div>
                                </div>
                                    
                            </div>
                        </div>
                    </div>
                </form>

<!-- ===== DISCLAIMER MODAL — NEW CONSTRUCTION ===== -->
<div id="wyn-modal-cs" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.72);z-index:999999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:14px;max-width:600px;width:100%;padding:40px;box-shadow:0 24px 64px rgba(0,0,0,.35);max-height:90vh;overflow-y:auto;text-align:center;">
        <img src="<?php echo $static_url; ?>/img/favicon.png" alt="Wynston" style="height:64px;margin:0 auto 20px;display:block;">
        <h3 style="font-size:19px;font-weight:800;color:#002446;margin:0 0 14px;line-height:1.3;">Important Notice — New Construction Information Only</h3>
        <p style="font-size:14px;color:#444;line-height:1.75;margin-bottom:12px;">The developments in this section are <strong>new construction projects currently under development</strong> across Metro Vancouver, published for <strong>research and awareness purposes only.</strong></p>
        <div style="background:#f9f6f0;border-radius:8px;padding:16px 18px;font-size:12px;color:#555;line-height:1.9;margin:16px 0 24px;border-left:3px solid #0065ff;text-align:left;">
            <strong style="color:#002446;">NOT AN OFFER FOR SALE.</strong> Nothing on the following page constitutes an offer for sale, a solicitation to purchase, or a contract of any kind. These properties are not currently available for sale or lease through this platform.<br><br>
            <strong style="color:#002446;">FOR INFORMATION PURPOSES ONLY.</strong> All project details including addresses, descriptions, renderings, floorplans, and estimated timelines are provided for general research purposes only and are subject to change without notice.<br><br>
            <strong style="color:#002446;">NO PURCHASE CAN BE MADE THROUGH THIS PLATFORM.</strong> Wynston Real Estate does not accept deposits, purchase agreements, or reservations for any property in this section.<br><br>
            <strong style="color:#002446;">REGULATORY NOTICE.</strong> Under BC's Real Estate Development Marketing Act, certain multi-unit developments may not be marketed for sale until specific legislative requirements have been met. No representation is made as to the current eligibility for sale of any listed project.<br><br>
            <!-- Legal language to be reviewed by counsel before going live -->
            <strong style="color:#002446;">By clicking "I Understand" you confirm</strong> you are accessing this information for research purposes only.
        </div>
        <button onclick="wynProceed('cs')" style="background:#002446;color:#fff;border:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;width:100%;" onmouseover="this.style.background='#0065ff'" onmouseout="this.style.background='#002446'">
            <i class="fa-solid fa-check me-2"></i>I Understand — Continue for Research Purposes Only
        </button>
        <p style="font-size:11px;color:#aaa;text-align:center;margin-top:14px;">Wynston Real Estate &nbsp;·&nbsp; Tam Nguyen, Realtor® &nbsp;·&nbsp; Royal Pacific Realty</p>
    </div>
</div>

<!-- ===== DISCLAIMER MODAL — ACTIVE LISTINGS ===== -->
<div id="wyn-modal-al" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.72);z-index:999999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:14px;max-width:600px;width:100%;padding:40px;box-shadow:0 24px 64px rgba(0,0,0,.35);max-height:90vh;overflow-y:auto;text-align:center;">
        <img src="<?php echo $static_url; ?>/img/favicon.png" alt="Wynston" style="height:64px;margin:0 auto 20px;display:block;">
        <h3 style="font-size:19px;font-weight:800;color:#002446;margin:0 0 14px;line-height:1.3;">MLS® Listing Data — Important Notice</h3>
        <p style="font-size:14px;color:#444;line-height:1.75;margin-bottom:12px;">The active listings displayed are sourced from the <strong>MLS® System of the Real Estate Board of Greater Vancouver</strong> and provided under license to Tam Nguyen, Realtor® with Royal Pacific Realty.</p>
        <div style="background:#f9f6f0;border-radius:8px;padding:16px 18px;font-size:12px;color:#555;line-height:1.9;margin:16px 0 24px;border-left:3px solid #0065ff;text-align:left;">
            <strong style="color:#002446;">MLS® DATA DISCLAIMER.</strong> The trademarks MLS®, Multiple Listing Service® and associated logos are owned by The Canadian Real Estate Association (CREA) and identify the quality of services provided by real estate professionals who are members of CREA.<br><br>
            <strong style="color:#002446;">DATA ACCURACY.</strong> Listing data is deemed reliable but not guaranteed accurate by the Real Estate Board of Greater Vancouver or its member brokerages. All measurements and property details should be independently verified. E.&amp;O.E.<br><br>
            <strong style="color:#002446;">NOT INTENDED TO SOLICIT.</strong> This website is not intended to solicit buyers or sellers currently under contract with another brokerage.<br><br>
            <!-- Insert REBGV member number and additional board-required disclosures before going live -->
            <strong style="color:#002446;">By clicking "I Understand" you agree</strong> to use this listing data for personal, non-commercial research purposes only.
        </div>
        <button onclick="wynProceed('al')" style="background:#002446;color:#fff;border:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;width:100%;" onmouseover="this.style.background='#0065ff'" onmouseout="this.style.background='#002446'">
            <i class="fa-solid fa-check me-2"></i>I Understand — View Active Listings
        </button>
        <p style="font-size:11px;color:#aaa;text-align:center;margin-top:14px;">Tam Nguyen, Realtor® &nbsp;·&nbsp; Royal Pacific Realty &nbsp;·&nbsp; Wynston Real Estate</p>
    </div>
</div>

<script>
function wynSearchClick() {
    var isPresale = document.getElementById('st_presale').checked;
    var modal = document.getElementById(isPresale ? 'wyn-modal-cs' : 'wyn-modal-al');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function wynProceed(type) {
    document.getElementById('wyn-modal-' + type).style.display = 'none';
    document.body.style.overflow = '';
    document.querySelector('form[action="search-router.php"]').submit();
}
</script>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Search Button Styling */
.btn-multiplex-search {
    background-color: #002446 !important; /* Deep Navy */
    color: #ffffff !important;
    border: none;
    height: 62px; /* Adjusted to align with search bar height */
    font-weight: 700;
    transition: all 0.3s ease;
    border-radius: 0 10px 10px 0; /* Rounds the right side of the search bar */
}

.btn-multiplex-search:hover {
    background-color: #0065ff !important; /* Vibrant Blue Hover */
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(0, 101, 255, 0.3);
}

/* Ensure icons match your primary blue */
.text-primary {
    color: #0065ff !important;
}
</style>

<script>
function togglePropertyTypes() {
    const isPresale = document.getElementById('st_presale').checked;
    const select = document.getElementById('dynamic_ptypes');
    select.innerHTML = '';
    
    if (isPresale) {
        const presaleOptions = [
            {val: '', text: 'Any Property Type'},
            {val: 'multiplex', text: 'Multiplex'},
            {val: 'duplex', text: 'Duplex'},
            {val: 'townhouse', text: 'Townhouse'},
            {val: 'condo', text: 'Condo/Apartment'}
            
        ];
        presaleOptions.forEach(opt => {
            select.options[select.options.length] = new Option(opt.text, opt.val);
        });
    } else {
        const activeOptions = [
            {val: '', text: 'Any Property Type'},
            {val: 'detached', text: 'Detached House'},
            {val: 'condo', text: 'Condo / Apartment'},
            {val: 'townhouse', text: 'Townhouse'},
            {val: 'duplex', text: 'Duplex / Multiplex'}
        ];
        activeOptions.forEach(opt => {
            select.options[select.options.length] = new Option(opt.text, opt.val);
        });
    }
}
window.onload = togglePropertyTypes;
</script>
<!-- ==================================== Hero Banner End ======================================== -->


<section>
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Discover What's Coming to Metro Vancouver.</h2>
					<p>Real projects. Real timelines. Real developers. Browse upcoming developments months before they appear anywhere else.</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 g-4">
		
			<!-- best-places code  -->
			<?php
				include "$base_dir/Components/Home/best-places.php";
			?>
			
		</div>
		
	</div>	
</section>
<hr class="opacity-25">


<!-- ============================ Latest Property For Sale Start ================================== -->
<section>
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>The Wynston Concierge Collection</h2>
					<p>Where Vision Meets Reality. Direct-from-developer access to the city’s most anticipated projects.</p>
				</div>
			</div>
		</div>
		
		<?php
		// Pull concierge listings from DB
		$concierge_listings = [];
		try {
			$__stmt = $pdo->query("SELECT * FROM multi_2025 WHERE tier = 'concierge' ORDER BY id DESC LIMIT 6");
			$concierge_listings = $__stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $__e) {
			// tier column may not exist yet — fall back to is_paid
			try {
				$__stmt = $pdo->query("SELECT * FROM multi_2025 WHERE is_paid = 1 ORDER BY id DESC LIMIT 6");
				$concierge_listings = $__stmt->fetchAll(PDO::FETCH_ASSOC);
			} catch (Exception $__e2) {}
		}
		?>

		<?php if (!empty($concierge_listings)): ?>
		<div class="row g-4">
			<?php foreach ($concierge_listings as $cp): ?>
			<?php
				$cp_img   = !empty($cp['img1']) ? $cp['img1'] : '';
				$cp_img2  = !empty($cp['img2']) ? $cp['img2'] : '';
				$cp_addr  = htmlspecialchars($cp['address'] ?? '');
				$cp_hood  = htmlspecialchars($cp['neighborhood'] ?? '');
				$cp_type  = htmlspecialchars($cp['property_type'] ?? 'Pre-Sale');
				$cp_price = !empty($cp['price']) ? htmlspecialchars($cp['price']) : 'T.B.A.';
				$cp_year  = !empty($cp['est_completion']) ? htmlspecialchars($cp['est_completion']) : '';
				$cp_beds  = !empty($cp['bedrooms']) ? $cp['bedrooms'] . ' Bed' : '';
				$cp_baths = !empty($cp['bathrooms']) ? $cp['bathrooms'] . ' Bath' : '';
				$cp_sqft  = !empty($cp['sqft']) ? number_format($cp['sqft']) . ' sqft' : '';
				$cp_dev   = htmlspecialchars($cp['developer_name'] ?? '');
				$cp_logo  = !empty($cp['builder_logo']) ? $cp['builder_logo'] : '';
				$cp_url   = 'concierge-property.php?id=' . (int)$cp['id'];
			?>
			<div class="col-lg-4 col-md-6 col-sm-12">
				<div class="concierge-card">

					<!-- Image -->
					<a href="<?= $cp_url ?>" class="concierge-img-wrap">
						<?php if ($cp_img): ?>
							<img src="<?= htmlspecialchars($cp_img) ?>" alt="<?= $cp_addr ?>">
						<?php else: ?>
							<div class="concierge-img-placeholder">
								<i class="fa-solid fa-building"></i>
								<span><?= $cp_type ?></span>
							</div>
						<?php endif; ?>
						<!-- Badges -->
						<div class="concierge-badges">
							<span class="badge-concierge-tag">⭐ Concierge</span>
							<?php if ($cp_year): ?>
								<span class="badge-year-tag">Est. <?= $cp_year ?></span>
							<?php endif; ?>
						</div>
					</a>

					<!-- Body -->
					<div class="concierge-body">
						<!-- Developer logo/name -->
						<?php if ($cp_logo || $cp_dev): ?>
						<div class="concierge-dev">
							<?php if ($cp_logo): ?>
								<img src="<?= htmlspecialchars($cp_logo) ?>" alt="<?= htmlspecialchars($cp_dev) ?>" class="concierge-dev-logo">
							<?php endif; ?>
							<?php if ($cp_dev): ?>
								<span class="concierge-dev-name"><?= $cp_dev ?></span>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<h4 class="concierge-title"><a href="<?= $cp_url ?>"><?= $cp_addr ?></a></h4>

						<?php if ($cp_hood): ?>
						<div class="concierge-location">
							<i class="fa-solid fa-location-dot"></i> <?= $cp_hood ?>
						</div>
						<?php endif; ?>

						<?php if ($cp_beds || $cp_baths || $cp_sqft): ?>
						<div class="concierge-specs">
							<?php if ($cp_beds): ?><span><i class="fa-solid fa-bed"></i> <?= $cp_beds ?></span><?php endif; ?>
							<?php if ($cp_baths): ?><span><i class="fa-solid fa-shower"></i> <?= $cp_baths ?></span><?php endif; ?>
							<?php if ($cp_sqft): ?><span><i class="fa-solid fa-vector-square"></i> <?= $cp_sqft ?></span><?php endif; ?>
						</div>
						<?php endif; ?>

						<div class="concierge-footer">
							<div class="concierge-price">
								<span class="price-label">FROM</span>
								<span class="price-value"><?= $cp_price ?></span>
							</div>
							<a href="<?= $cp_url ?>" class="concierge-btn">View Details →</a>
						</div>
					</div>

				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<div class="row mt-5">
			<div class="col-12 text-center">
				<a href="half-map.php" class="btn btn-outline-primary px-5 rounded">View All Concierge Listings</a>
			</div>
		</div>

		<?php else: ?>
		<div class="row">
			<div class="col-12 text-center py-5">
				<i class="fa-solid fa-building" style="font-size:48px;color:#e0e0e0;margin-bottom:16px;display:block;"></i>
				<p class="text-muted">Concierge collection coming soon. Check back shortly.</p>
			</div>
		</div>
		<?php endif; ?>

	</div>
</section>

<style>
/* ── Concierge Collection Cards ───────────────────────────────────── */
.concierge-card {
	border-radius: 14px;
	overflow: hidden;
	background: #fff;
	box-shadow: 0 2px 16px rgba(0,36,70,.08);
	border: 1px solid #e8e4dd;
	transition: box-shadow .25s, transform .25s;
	height: 100%;
	display: flex;
	flex-direction: column;
}
.concierge-card:hover {
	box-shadow: 0 12px 40px rgba(0,36,70,.18);
	transform: translateY(-4px);
}
.concierge-img-wrap {
	display: block;
	position: relative;
	overflow: hidden;
	height: 220px;
	background: #001830;
}
.concierge-img-wrap img {
	width: 100%; height: 100%; object-fit: cover;
	transition: transform .4s ease;
}
.concierge-card:hover .concierge-img-wrap img { transform: scale(1.04); }
.concierge-img-placeholder {
	width:100%; height:100%;
	background: #0065ff;
	display:flex; flex-direction:column; align-items:center; justify-content:center;
	color:rgba(255,255,255,.6); gap:10px;
}
.concierge-img-placeholder i { font-size:40px; }
.concierge-img-placeholder span { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.concierge-badges {
	position: absolute; top:12px; left:12px;
	display: flex; gap:6px; flex-wrap:wrap;
}
.badge-concierge-tag {
	background: #0065ff;
	color: #fff; font-size:10px; font-weight:800;
	padding:4px 10px; border-radius:20px; letter-spacing:.5px;
}
.badge-year-tag {
	background: rgba(0,0,0,.55); color:#fff;
	font-size:10px; font-weight:600;
	padding:4px 10px; border-radius:20px;
}
.concierge-body {
	padding: 20px; flex: 1; display:flex; flex-direction:column; gap:10px;
}
.concierge-dev {
	display:flex; align-items:center; gap:8px;
}
.concierge-dev-logo {
	height:28px; width:auto; max-width:80px; object-fit:contain;
	border-radius:4px;
}
.concierge-dev-name {
	font-size:11px; font-weight:700; color:#888;
	text-transform:uppercase; letter-spacing:.5px;
}
.concierge-title {
	font-size:16px; font-weight:700; color:#002446; margin:0; line-height:1.4;
}
.concierge-title a { color:inherit; text-decoration:none; }
.concierge-title a:hover { color:#0065ff; }
.concierge-location {
	font-size:12px; color:#888;
}
.concierge-location i { color:#0065ff; margin-right:4px; }
.concierge-specs {
	display:flex; gap:12px; flex-wrap:wrap;
}
.concierge-specs span {
	font-size:12px; color:#666;
	background:#f5f7ff; padding:4px 10px; border-radius:20px;
}
.concierge-specs i { color:#0065ff; margin-right:4px; }
.concierge-footer {
	display:flex; align-items:center; justify-content:space-between;
	margin-top:auto; padding-top:14px;
	border-top:1px solid #f0ece6;
}
.concierge-price {
	display:flex; flex-direction:column;
}
.price-label { font-size:10px; color:#aaa; text-transform:uppercase; letter-spacing:.5px; }
.price-value { font-size:20px; font-weight:800; color:#002446; }
.concierge-btn {
	background: #0065ff;
	color:#fff; font-size:12px; font-weight:700;
	padding:8px 16px; border-radius:20px; text-decoration:none;
	transition: opacity .2s;
	white-space:nowrap;
}
.concierge-btn:hover { opacity:.85; color:#fff; }
</style>
<!-- ============================ Latest Property For Sale End ================================== -->

<!-- ============================ New Listings (under 10 days) ================================== -->
<section class="bg-light">
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Featured Active Listings</h2>
					<p>Fresh MLS® listings across Greater Vancouver — updated daily alongside our coming-soon development pipeline.</p>
				</div>
			</div>
		</div>

		<?php
		// ── Pull newest ddf_listings (listed within last 2 days) ────────────────
		try {
			require_once "$base_dir/db.php";
			$new_listings = $pdo->query("
				SELECT *, DATEDIFF(NOW(), listed_date) AS days_on_market
				FROM ddf_listings
				WHERE status = 'Active'
				  AND listed_date IS NOT NULL
				  AND DATEDIFF(NOW(), listed_date) <= 10
				ORDER BY listed_date DESC
				LIMIT 6
			")->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			$new_listings = [];
		}
		?>

		<div class="row list-layout">

			<?php if (empty($new_listings)): ?>
			<div class="col-12 text-center py-4">
				<p class="text-muted">No listings added in the last 2 days — check back soon!</p>
			</div>
			<?php else: ?>
			<?php foreach ($new_listings as $nl):
				$price_fmt   = !empty($nl['price']) ? '$' . number_format($nl['price']) : 'Contact for Price';
				$btype       = $nl['building_type'] ?: $nl['property_type'] ?: 'Residential';
				$img         = !empty($nl['img1']) ? $nl['img1'] : '';
				// Days on market from OriginalEntryTimestamp
				$dom = $nl['days_on_market'] ?? '—';
				if ($dom === 0) $dom = 'Today';
				elseif (is_numeric($dom)) $dom = (int)$dom;
			?>
			<div class="col-lg-4 col-md-6 col-sm-12">
				<div class="property-listing card border rounded-3 h-100 new-listing-card">

					<!-- Image -->
					<div class="listing-img-wrapper p-3">
						<div class="position-relative rounded-3 overflow-hidden" style="height:200px;">
							<?php if ($img): ?>
								<img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($nl['address']) ?>"
								     style="width:100%;height:100%;object-fit:cover;display:block;">
							<?php else: ?>
								<div style="width:100%;height:100%;background:linear-gradient(135deg,#002446,#0065ff);display:flex;align-items:center;justify-content:center;">
									<i class="fas fa-building" style="font-size:48px;color:rgba(255,255,255,0.4);"></i>
								</div>
							<?php endif; ?>
							<!-- Badges -->
							<div style="position:absolute;top:10px;left:10px;display:flex;gap:6px;flex-wrap:wrap;">
								<span class="new-badge-hot">New</span>
								<span class="new-badge-dom"><?= $dom === 'Today' ? 'Today' : $dom . ' day' . ($dom != 1 ? 's' : '') . ' ago' ?></span>
							</div>
						</div>
					</div>

					<!-- Body -->
					<div class="listing-caption-wrapper px-3 pb-3">
						<div class="listing-detail-wrapper">
							<div class="listing-short-detail-wrap">
								<div class="listing-short-detail">
									<div class="d-flex align-items-center gap-2 mb-2">
										<span class="label bg-light-success text-success prt-type"><?= htmlspecialchars($btype) ?></span>
										<span class="label bg-light-primary text-primary prt-type">Active</span>
									</div>
									<h4 class="listing-name fw-semibold fs-6 mb-1">
										<a href="active-property.php?id=<?= $nl['id'] ?>" style="color:#002446;text-decoration:none;">
											<?= htmlspecialchars($nl['address']) ?>
										</a>
									</h4>
									<div class="prt-location text-muted-2 mb-2">
										<i class="fas fa-map-marker-alt me-1" style="color:#0065ff;"></i>
										<?= htmlspecialchars($nl['city']) ?>
										<?php if (!empty($nl['neighborhood']) && $nl['neighborhood'] !== $nl['city']): ?>
											— <?= htmlspecialchars($nl['neighborhood']) ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Specs -->
						<?php if (!empty($nl['bedrooms']) || !empty($nl['bathrooms']) || !empty($nl['sqft'])): ?>
						<div class="price-features-wrapper mb-3">
							<div class="list-fx-features d-flex align-items-center gap-3">
								<?php if (!empty($nl['bedrooms'])): ?>
								<div class="listing-card d-flex align-items-center">
									<div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
										<i class="fa-solid fa-bed fs-sm"></i>
									</div>
									<span class="text-muted-2"><?= $nl['bedrooms'] ?> Bed</span>
								</div>
								<?php endif; ?>
								<?php if (!empty($nl['bathrooms'])): ?>
								<div class="listing-card d-flex align-items-center">
									<div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
										<i class="fa-solid fa-bath fs-sm"></i>
									</div>
									<span class="text-muted-2"><?= $nl['bathrooms'] ?> Bath</span>
								</div>
								<?php endif; ?>
								<?php if (!empty($nl['sqft'])): ?>
								<div class="listing-card d-flex align-items-center">
									<div class="square--30 text-muted-2 fs-sm circle gray-simple me-2">
										<i class="fa-solid fa-clone fs-sm"></i>
									</div>
									<span class="text-muted-2"><?= number_format($nl['sqft']) ?> sqft</span>
								</div>
								<?php endif; ?>
							</div>
						</div>
						<?php endif; ?>

						<!-- Footer -->
						<div class="listing-detail-footer d-flex align-items-center justify-content-between py-3" style="border-top:1px solid #f0f0f0;">
							<h6 class="listing-card-info-price m-0" style="font-size:20px;font-weight:800;color:#002446;">
								<?= $price_fmt ?>
							</h6>
							<a href="active-property.php?id=<?= $nl['id'] ?>" class="btn btn-sm btn-primary rounded px-3">
								View <i class="fas fa-arrow-right ms-1"></i>
							</a>
						</div>
					</div>

				</div>
			</div>
			<?php endforeach; ?>
			<?php endif; ?>

		</div>

		<!-- Browse All -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 text-center mt-4">
				<a href="active-listings.php" class="btn btn-primary px-lg-5 rounded">Browse All Active Listings</a>
			</div>
		</div>

	</div>
</section>

<style>
.new-listing-card { transition: box-shadow .25s, transform .25s; }
.new-listing-card:hover { box-shadow: 0 8px 32px rgba(0,36,70,.15); transform: translateY(-3px); }
.new-badge-hot { background:#ef4444; color:#fff; font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; }
.new-badge-dom { background:rgba(0,0,0,.55); color:#fff; font-size:10px; font-weight:600; padding:3px 10px; border-radius:20px; }
</style>
<!-- ============================ Just Listed End ================================== -->

<!-- ============================ For Buyers ================================== -->
<section style="padding:80px 0;background:#fff;">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <div class="sec-heading center">
                    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#0065ff;margin-bottom:10px;">For Buyers</p>
                    <h2 style="font-size:32px;font-weight:600;color:#002446;">Stop Discovering Homes Like Everyone Else.</h2>
                    <p style="font-size:16px;color:#666;max-width:560px;margin:0 auto;">Wynston integrates the city’s newest developments with active MLS listings, giving you a singula view of the entire market.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">

            <div class="col-lg-4 col-md-6">
                <div style="background:#f9f6f0;border-radius:16px;padding:36px 32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#0065ff;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-magnifying-glass" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#0065ff;margin-bottom:8px;">Step 1</div>
                    <h4 style="font-size:18px;font-weight:800;color:#002446;margin-bottom:12px;">Search Every Listing in One Place</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Browse upcoming Coming Soon developments and active MLS listings across Metro Vancouver — all on one platform. No bouncing between sites. No missing out on what's available right now.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#0065ff;border-radius:16px;padding:36px 32px;height:100%;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.25)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-building" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#fff;margin-bottom:8px;">Step 2</div>
                    <h4 style="font-size:18px;font-weight:800;color:#fff;margin-bottom:12px;">Get the Inside Track on What's Coming</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.75);line-height:1.7;margin:0;">Wynston independently tracks upcoming developments — real projects, real timelines, real developers. Discover homes months before they ever appear on MLS. Not rumours. Not speculation. Real pipeline.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#f9f6f0;border-radius:16px;padding:36px 32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#0065ff;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-handshake" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#0065ff;margin-bottom:8px;">Step 3</div>
                    <h4 style="font-size:18px;font-weight:800;color:#002446;margin-bottom:12px;">Buy With the Right Team Behind You</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Experience a seamless transition into your next home. Backed by over a decade of proven results, Tam Nguyen offers comprehensive buyer representation that covers every detail from offer to keys, ensuring your interests are protected without any extra expense.</p>
                </div>
            </div>

        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="half-map.php" class="btn btn-primary px-lg-5 rounded me-3">Browse Coming Soon</a>
                <a href="active-listings.php" class="btn btn-outline-primary px-lg-5 rounded">View Active Listings</a>
            </div>
        </div>

    </div>
</section>
<div class="clearfix"></div>
<!-- ============================ For Buyers End ================================== -->



<!-- ============================ For Sellers ================================== -->
<section style="padding:80px 0;background:#002446;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">For Sellers</p>
                <h2 style="font-size:32px;font-weight:600;color:#fff;margin-bottom:16px;">Where Demand Meets Opportunity</h2>
                <p style="font-size:16px;color:rgba(255,255,255,.75);max-width:620px;margin:0 auto;line-height:1.8;">Developers are paying a premium for the right lots in Metro Vancouver, but the window for these deals is highly localized. Wynston identifies the specific pockets where "Missing Middle" projects are most viable. Get a clear-eyed assessment of your property’s value in today’s evolving development market—no guesswork, just data</p>
            </div>
        </div>

        <div class="row g-4 mt-5">
            <div class="col-lg-4 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:32px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-chart-bar" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:10px;">See Your Neighbourhood's Real Data</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin:0;">Recent sales, price per square foot, days on market, and month-over-month movement in your area — actual transaction data, updated monthly. Not an algorithm estimate.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:32px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-building-circle-check" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:10px;">See Developer Activity Around You</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin:0;">Active building permits, projects under construction, and developer acquisition patterns in your neighbourhood. If builders are active on your street, you'll know — before you list anywhere.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:rgba(201,168,76,.12);border:1px solid rgba(201,168,76,.35);border-radius:16px;padding:32px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:#c9a84c;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-arrows-left-right-to-line" style="color:#002446;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:10px;">Get Matched to Active Developers</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.75);line-height:1.7;margin:0;">If your property meets developer acquisition criteria, our team connects you directly with builders in our network who have flagged interest in your area. Two sides of the market. One conversation.</p>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="portal.php?tab=seller" class="btn btn-light btn-lg me-3" style="color:#002446;font-weight:700;">See What Your Property Is Worth</a>
                <a href="contact.php?ref=seller" class="btn btn-outline-light btn-lg" style="font-weight:700;">Talk to Tam's Team</a>
            </div>
        </div>
    </div>
</section>
<div class="clearfix"></div>
<!-- ============================ For Sellers End ================================== -->


<!-- ============================ For Developers ================================== -->
<section class="gray-bg">
	<div class="container">
		
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
                    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#0065ff;margin-bottom:10px;">For Developers</p>
                    <h2 style="font-size:32px;font-weight:600;color:#002446;">Build Momentum Before You Break Ground.</h2>
                    <p style="font-size:16px;color:#666;max-width:600px;margin:0 auto;">Rennies and Magnum start selling 18 months before completion. Your project shouldn't have to wait until the last nail is in. Wynston gives every Metro Vancouver developer a platform to build buyer awareness from the day their permit is approved.</p>
                </div>
			</div>
		</div>
		
		<div class="row justify-content-center g-4">
			
			<!-- services code  -->
			<?php
				include "$base_dir/Components/Home/services.php";
			?>

		</div>
		<div class="row mt-5">
            <div class="col-12 text-center">
                <a href="log-in.php" class="btn btn-primary px-lg-5 rounded me-3">Post Your Project — Free</a>
                <a href="concierge.php" class="btn btn-outline-primary px-lg-5 rounded">See How Concierge Works</a>
            </div>
        </div>
		
	</div>
</section>
<div class="clearfix"></div>
<!-- ============================ For Developers ====================== -->


<!-- ================================= Blog Grid ================================== -->
<section>
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Latest Articles about Vancouve Real Estate</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center g-4">
			
			<!-- blog code  -->
			<?php
				include "$base_dir/Components/Home/blog.php";
			?>
			
		</div>
		
	</div>		
</section>
<!-- ============================== Blog Grid End =============================== -->


<section class="bg-light">
	<div class="container">
		<div class="row align-items-center">
			
			<!-- download-apps code  -->
			<?php
				include "$base_dir/Components/Home/download-apps.php";
			?>	
		
		</div>
	</div>
</section>
<!-- ========================== Download App Section =============================== -->


<!-- ============================ Call To Action ================================== -->
<section class="bg-primary call-to-act-wrap">
	<div class="container">
		
		<!-- estate-agent code  -->
		<?php
			include "$base_dir/Components/Home/estate-agent.php";
		?>

	</div>
</section>
<!-- ============================ Call To Action End ================================== -->


			

<?php
$hero_content = ob_get_clean(); // Capture the hero content

// Include the base template
include "$base_dir/style/base.php";
?>