<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';

ob_start();
include "$base_dir/navbar.php"; 
$navlink_content = ob_get_clean(); 
$page= 'nav';
$fpage= 'foot';

ob_start();
?>

<!-- ============================================================ -->
<!-- HERO BANNER -->
<!-- ============================================================ -->
<div class="hero-banner vedio-banner">
	<div class="overlay"></div>	
	<video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
		<source src="<?php echo $static_url; ?>/img/banners.mp4" type="video/mp4">
	</video>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-9 col-md-11 col-sm-12">
				<div class="inner-banner-text text-center">
					<p class="lead-i text-light">BC's only intelligence platform built exclusively for multiplex developers.</p>
					<h2 class="text-light"><span class="font-normal">Wynston</span> Concierge.</h2>
					<p class="text-light mt-3 mb-0">Find the lot. Model the deal. Sell before completion.<br>Everything the largest firms have — built for builders like you.</p>
				</div>

				<div class="full-search-2 eclip-search italian-search hero-search-radius shadow-hard mt-5">
					<div class="hero-search-content">
						<div class="row align-items-center">
							<div class="col-xl-9 col-lg-8 col-md-7 col-sm-12">
								<div class="form-group mb-0">
									<h4 class="mb-0 text-dark">Access the Wynston Portal — Free.</h4>
									<p class="mb-0">Every registered developer gets full map access, live feasibility data, and pro forma tools at no cost.</p>
								</div>
							</div>
							<div class="col-xl-3 col-lg-4 col-md-5 col-sm-12">
								<div class="form-group mb-0">
									<a href="register.php" class="btn btn-primary full-width">Sign Up — $0</a>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>


<!-- ============================================================ -->
<!-- SECTION 1 — THE PLATFORM -->
<!-- ============================================================ -->
<section>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center mb-4">
					<h2 class="text-primary">This Isn't a Listing Platform. It's an Intelligence Platform.</h2>
					<p>Every developer in BC has access to a map. What they don't have is what's underneath it — zoning eligibility, FSR calculations, cost modelling, market velocity, heritage flags, and a pro forma that updates in real time. That's what Wynston was built to give you. For free.</p>
				</div>
			</div>
		</div>

		<div class="row align-items-center justify-content-center g-4">
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-map-location-dot text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>Live Feasibility Map</h4>
						<p>Every R1-1 zoned lot in Vancouver, scored and colour-coded for 3, 6, and 8-unit eligibility. See the opportunity before anyone else does.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-calculator text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>Instant Pro Forma</h4>
						<p>Click any lot and get a full cost breakdown — build cost, DCLs, permit fees, peat zone contingencies, and projected profit — calculated automatically.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-chart-line text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>Real Market Velocity</h4>
						<p>Not just sold prices — actual market movement. Month-over-month, 3-month, and 6-month trend data for duplexes and detached homes, by neighbourhood.</p>
					</div>
				</div>
			</div>
		</div>

		<div class="row align-items-center justify-content-center g-4 mt-1">
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-cube text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>3D Architectural Visualizer</h4>
						<p>See what you can actually build on each lot — styled in 8 architectural categories, scaled to real dimensions, with Vancouver setbacks already applied. Nothing like this exists anywhere else in Canada.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-triangle-exclamation text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>Constraint Intelligence</h4>
						<p>Heritage categories, peat zone boundaries, covenant flags, and transit proximity — surfaced automatically for every lot so nothing catches you off guard at due diligence.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-4">
				<div class="icon-mi-left mb-4">
					<i class="fa-solid fa-file-invoice-dollar text-primary fa-2x"></i>
					<div class="icon-mi-left-content">
						<h4>The Wynston Report</h4>
						<p>A branded, investor-grade PDF covering the full feasibility analysis for any lot — ready to share with partners and lenders. Available to all users for $19.99 per report.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 2 — TECH EDGE -->
<!-- ============================================================ -->
<section class="bg-light">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 col-md-6">
				<img src="<?php echo $static_url; ?>/img/vec-2.png" class="img-fluid" alt="Wynston Portal Intelligence" />
			</div>
			<div class="col-lg-6 col-md-6">
				<div class="story-wrap">
					<span class="text-primary fw-bold">Built Different. By Design.</span>
					<h2 class="mt-2 text-primary">Years Ahead of Anything Else on the Market.</h2>
					<p>Other platforms show you a grey block on a map. Wynston shows you a permit-ready, architecturally styled 3D model — sized to the actual lot, built to Vancouver's setback rules, based on BC's Provincial Standardized Housing Designs. The same designs that save builders up to $35,000 in architect fees and cut permit timelines by four months.</p>
					<p>Our market data isn't just sold prices pulled from a public feed. It's pre-registry velocity data — the movement between conditional and final sale — mapped by neighbourhood, tracked month over month, and benchmarked against CMHC figures. This level of market intelligence doesn't exist on any other platform in Canada.</p>
					<p>And it's only getting sharper. AI-generated narratives, automated lot scoring, and likely-to-sell signals are already on the roadmap — because the goal was never just a better map. It was a smarter way to build.</p>
					<a href="portal.php" class="btn btn-outline-primary mt-3">Explore the Portal</a>
				</div>
			</div>
		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 3 — TWO TIERS -->
<!-- ============================================================ -->
<section>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center mb-5">
					<h2 class="text-primary">Two Ways to Work With Wynston.</h2>
					<p>Start free with the portal. Or partner with Concierge and take your project from feasibility to sold — with everything handled.</p>
				</div>
			</div>
		</div>

		<div class="row g-4 justify-content-center">

			<div class="col-lg-5 col-md-6">
				<div class="card h-100 shadow-sm border-0 p-4">
					<div class="card-body">
						<span class="badge bg-secondary mb-3">Portal Access — Free</span>
						<h3 class="text-dark">The Smartest Free Tool a Developer Can Have.</h3>
						<p class="text-muted">No credit card. No contract. Just register and go.</p>
						<hr>
						<ul class="list-unstyled mt-3">
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Full feasibility map — all R1-1 lots in Vancouver</li>
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Live pro forma calculator per lot</li>
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Constraint flags — heritage, peat, covenants</li>
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>3D architectural visualizer</li>
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Market velocity data by neighbourhood</li>
							<li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Wynston Report — $19.99 per report</li>
						</ul>
						<a href="register.php" class="btn btn-outline-primary mt-4 full-width">Create Free Account</a>
					</div>
				</div>
			</div>

			<div class="col-lg-5 col-md-6">
				<div class="card h-100 shadow p-4" style="border: 2px solid var(--bs-primary);">
					<div class="card-body">
						<span class="badge bg-primary mb-3">Concierge Partnership</span>
						<h3 class="text-primary">From Feasibility to Sold. Done For You.</h3>
						<p class="text-muted">Everything in the free portal — plus the full marketing machine that large developers pay millions to run.</p>
						<hr>
						<ul class="list-unstyled mt-3">
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>Full portal access — everything included</li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i><strong>Unlimited Wynston Reports — free, with your branding</strong></li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>18-month marketing head start from land acquisition</li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>Dedicated project website + pre-completion campaign</li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>Exclusive pre-market launch to 5,000+ agent network</li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>Full negotiation, reporting &amp; closing support</li>
							<li class="mb-2"><i class="fa-solid fa-star text-primary me-2"></i>No additional cost beyond standard commission</li>
						</ul>
						<a href="contact.php?ref=concierge" class="btn btn-primary mt-4 full-width">Book a Private Presentation</a>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 4 — NUMBERS -->
<!-- ============================================================ -->
<section class="bg-light">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center mb-5">
					<h2 class="text-primary">What Concierge Partners Experience.</h2>
					<p>The details behind these numbers are something we share exclusively in a private presentation. But here's what our developers consistently see.</p>
				</div>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-lg-3 col-md-6">
				<div class="text-center p-4 bg-white rounded shadow-sm h-100">
					<h2 class="text-primary display-6 fw-bold">18 mo.</h2>
					<p class="fw-semibold">Marketing Head Start</p>
					<p class="text-muted small">Buyer awareness and agent relationships built before your occupancy permit is issued.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="text-center p-4 bg-white rounded shadow-sm h-100">
					<h2 class="text-primary display-6 fw-bold">5,000+</h2>
					<p class="fw-semibold">Agent Pre-Market Network</p>
					<p class="text-muted small">Your project reaches qualified realtors and their buyers before it ever appears on MLS.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="text-center p-4 bg-white rounded shadow-sm h-100">
					<h2 class="text-primary display-6 fw-bold">30–40%</h2>
					<p class="fw-semibold">Faster Time to Sold</p>
					<p class="text-muted small">Pre-built demand means stronger offers, less negotiation, and faster closes on every unit.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="text-center p-4 bg-white rounded shadow-sm h-100">
					<h2 class="text-primary display-6 fw-bold">$0</h2>
					<p class="fw-semibold">Added to Your Cost</p>
					<p class="text-muted small">The full Concierge marketing program is included within a standard real estate commission. Nothing more.</p>
				</div>
			</div>
		</div>

		<div class="row justify-content-center mt-5">
			<div class="col-lg-8 text-center">
				<p class="text-muted">How we achieve these results — and why no other agent or platform in BC can replicate them — is a conversation we have in person. If you're building in Vancouver, this is worth an hour of your time.</p>
				<a href="contact.php?ref=concierge" class="btn btn-primary btn-lg mt-3">Request Your Private Presentation</a>
			</div>
		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 5 — VISION -->
<!-- ============================================================ -->
<section>
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 col-md-6">
				<div class="story-wrap">
					<span class="text-primary fw-bold">Why We Built This.</span>
					<h2 class="mt-2 text-primary">The Playing Field Has Never Been Fair. We're Changing That.</h2>
					<p>Large developers in BC have always had two things small builders didn't: the data to make confident acquisition decisions, and the marketing reach to sell before a project is complete. Those two advantages compound every year — widening the gap between firms that scale and firms that grind.</p>
					<p>Wynston was built to close that gap. The portal gives every developer the same intelligence layer that institutional buyers have always had. The Concierge program gives your project the same sales momentum that multi-million dollar pre-sale campaigns create.</p>
					<p>We're starting in Vancouver. Expanding to Burnaby, Surrey, and beyond. The goal is the same everywhere: make the data actionable, make the marketing accessible, and level the field — permanently.</p>
					<a href="about-us.php" class="btn btn-outline-primary mt-3">Meet the Team Behind Wynston</a>
				</div>
			</div>
			<div class="col-lg-6 col-md-6">
				<img src="<?php echo $static_url; ?>/img/vec-2.png" class="img-fluid" alt="Wynston Vision" />
			</div>
		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 6 — HOW IT WORKS -->
<!-- ============================================================ -->
<section class="bg-light">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-10 text-center">
				<div class="sec-heading center mb-5">
					<h2 class="text-primary">From First Search to Final Sale.</h2>
					<p>Wynston works across your entire development lifecycle — not just the listing.</p>
				</div>
			</div>
		</div>

		<div class="row g-4 justify-content-center">
			<div class="col-lg-3 col-md-6 text-center">
				<div class="p-3">
					<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;font-size:1.3rem;font-weight:700;">1</div>
					<h5>Find the Right Lot</h5>
					<p class="text-muted small">Use the portal to identify R1-1 lots that match your build type, budget, and risk tolerance — before you make an offer.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 text-center">
				<div class="p-3">
					<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;font-size:1.3rem;font-weight:700;">2</div>
					<h5>Model the Deal</h5>
					<p class="text-muted small">Run your full pro forma instantly. Generate a branded Wynston Report to share with partners and lenders. Make decisions on data, not gut feel.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 text-center">
				<div class="p-3">
					<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;font-size:1.3rem;font-weight:700;">3</div>
					<h5>Build Buyer Demand Early</h5>
					<p class="text-muted small">Concierge partners start marketing from land acquisition — not completion. By the time your project is finished, buyers are already waiting.</p>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 text-center">
				<div class="p-3">
					<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;font-size:1.3rem;font-weight:700;">4</div>
					<h5>Sell at Full Price</h5>
					<p class="text-muted small">With a pre-warmed buyer pool, professional marketing, and expert negotiation, you close every unit faster — and stronger.</p>
				</div>
			</div>
		</div>
	</div>
</section>


<!-- ============================================================ -->
<!-- SECTION 7 — FINAL CTA -->
<!-- ============================================================ -->
<section class="bg-primary call-to-act-wrap">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-8 text-center">
				<h2 class="text-white">The smartest developers in BC already have access.</h2>
				<p class="text-white mt-3 mb-4">Join them for free — or find out what a full Concierge partnership looks like for your next project.</p>
				<div class="d-flex flex-wrap gap-3 justify-content-center">
					<a href="register.php" class="btn btn-light btn-lg">Access the Portal — Free</a>
					<a href="contact.php?ref=concierge" class="btn btn-outline-light btn-lg">Book a Private Presentation</a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$hero_content = ob_get_clean(); 
include "$base_dir/style/base.php";
?>
