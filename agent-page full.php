<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';

require_once "$base_dir/db.php";

ob_start();
include "$base_dir/navbar2.php";
$navlink_content = ob_get_clean();
$page  = 'nav2';
$fpage = 'foot';

ob_start();
?>

<!-- ============================ Hero ================================== -->
<section class="tam-hero">
    <div class="tam-hero-bg"></div>
    <div class="container">
        <div class="tam-hero-inner">

            <!-- Left: photo -->
            <div class="tam-hero-photo-wrap">
                <div class="tam-hero-photo-ring"></div>
                <img src="<?= $static_url ?>/img/tam-photo.jpg"
                     onerror="this.src='<?= $static_url ?>/img/user-6.jpg'"
                     alt="Tam Nguyen REALTOR®"
                     class="tam-hero-photo">
                <div class="tam-hero-badge">
                    <span>⭐ Concierge</span>
                </div>
            </div>

            <!-- Right: intro -->
            <div class="tam-hero-intro">
                <div class="tam-hero-eyebrow">Real Estate Marketing Powerhouse</div>
                <h1 class="tam-hero-name">Tam Nguyen</h1>
                <p class="tam-hero-title">REALTOR® &amp; Founder, Wynston.ca</p>
                <p class="tam-hero-creds">
                    Royal Pacific Realty Corporation &nbsp;·&nbsp;
                    Independently Owned &amp; Operated<br>
                    Real Estate Board of Greater Vancouver &nbsp;·&nbsp; BCFSA
                </p>

                <p class="tam-hero-bio">
                    With over 10 years of presale and multiplex sales experience across Metro Vancouver,
                    Tam built <strong>Wynston.ca</strong> to give small and mid-sized developers access
                    to the same elite marketing infrastructure previously reserved for large-scale firms.
                    From permit to prestige — she's with you every step.
                </p>

                <!-- Stats -->
                <div class="tam-stat-row">
                    <div class="tam-stat">
                        <div class="tam-stat-n">10+</div>
                        <div class="tam-stat-l">Years Experience</div>
                    </div>
                    <div class="tam-stat">
                        <div class="tam-stat-n">5,000+</div>
                        <div class="tam-stat-l">Agent Network</div>
                    </div>
                    <div class="tam-stat">
                        <div class="tam-stat-n">5</div>
                        <div class="tam-stat-l">Industry Awards</div>
                    </div>
                    <div class="tam-stat">
                        <div class="tam-stat-n">18mo</div>
                        <div class="tam-stat-l">Head Start</div>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="tam-cta-row">
                    <a href="https://tamwynn.ca" target="_blank" rel="noopener" class="tam-btn-primary">
                        Visit TamWynn.ca <i class="fas fa-arrow-up-right-from-square ms-2"></i>
                    </a>
                    <a href="contact.php" class="tam-btn-outline">
                        Get in Touch
                    </a>
                    <a href="tel:6047824689" class="tam-btn-phone">
                        <i class="fas fa-phone"></i> (604) 782-4689
                    </a>
                </div>

                <!-- Social -->
                <div class="tam-social-row">
                    <a href="https://tamwynn.ca" target="_blank" title="TamWynn.ca"><i class="fas fa-globe"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="mailto:sold@tamwynn.ca" title="Email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- ============================ Hero End ================================== -->


<!-- ============================ Awards ================================== -->
<section class="tam-awards-section">
    <div class="container">
        <div class="tam-section-label">Recognition</div>
        <h2 class="tam-section-title">Awards &amp; Recognitions</h2>
        <div class="tam-awards-row">
            <?php
            $awards = [
                ['year' => '2015', 'month' => 'Mar', 'title' => 'Executive Club Award'],
                ['year' => '2016', 'month' => 'Jun', 'title' => '100% Club Award'],
                ['year' => '2017', 'month' => 'Sep', 'title' => 'Executive Club Award'],
                ['year' => '2018', 'month' => 'May', 'title' => '100% Club Team Award'],
                ['year' => '2020', 'month' => 'May', 'title' => 'Top 10% in Western Canada'],
            ];
            foreach ($awards as $i => $a): ?>
            <div class="tam-award-card" style="animation-delay:<?= $i * 0.1 ?>s">
                <div class="tam-award-year"><?= $a['year'] ?></div>
                <div class="tam-award-icon"><i class="fas fa-trophy"></i></div>
                <div class="tam-award-title"><?= $a['title'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- ============================ Awards End ================================== -->


<!-- ============================ The Process ================================== -->
<section class="tam-process-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4">
                <div class="tam-section-label">How It Works</div>
                <h2 class="tam-section-title" style="color:#fff;">The Powerhouse<br><span style="color:#c9a84c;">5-Phase Process</span></h2>
                <p style="color:rgba(255,255,255,.55);font-size:15px;line-height:1.8;margin-top:16px;">
                    From the moment land is acquired to the final close — Tam's system runs parallel to your construction timeline, building buyer demand before the first foundation is poured.
                </p>
                <a href="https://tamwynn.ca" target="_blank" class="tam-btn-primary mt-4 d-inline-block">
                    Full Process at TamWynn.ca →
                </a>
            </div>
            <div class="col-lg-8">
                <div class="tam-phases">
                    <?php
                    $phases = [
                        ['n'=>'01','icon'=>'fa-seedling',     'title'=>'Land Acquisition & Early Engagement',   'desc'=>'Marketing starts the moment land acquisition begins — giving your project an 18-month head start building buyer anticipation and realtor awareness before construction.'],
                        ['n'=>'02','icon'=>'fa-rocket',       'title'=>'Completion & Market-Ready Launch',      'desc'=>'As occupancy approaches, we transition from "coming soon" to "move-in ready" with professional staging, photography, video, and a full Wynston platform launch.'],
                        ['n'=>'03','icon'=>'fa-fire',         'title'=>'Building the Buzz',                     'desc'=>'Exclusive pre-market launch to 5,000+ agents, custom OpenHouse Magazine, wine-and-cheese broker events — creating urgency and qualified buyer traffic from day one.'],
                        ['n'=>'04','icon'=>'fa-bullseye',     'title'=>'Digital Domination',                    'desc'=>'Precision-targeted Instagram, Facebook, and Google campaigns. A dedicated 24/7 digital showroom. Drip email campaigns to thousands of qualified buyers and agents.'],
                        ['n'=>'05','icon'=>'fa-handshake',    'title'=>'Negotiation & Closing Support',         'desc'=>'Aggressive negotiation backed by real-time Wynston pipeline data. Full support from accepted offer through completion — coordinating lawyers, due diligence, and closing logistics.'],
                    ];
                    foreach ($phases as $i => $ph): ?>
                    <div class="tam-phase-card">
                        <div class="tam-phase-num"><?= $ph['n'] ?></div>
                        <div class="tam-phase-icon"><i class="fas <?= $ph['icon'] ?>"></i></div>
                        <div class="tam-phase-body">
                            <div class="tam-phase-title"><?= $ph['title'] ?></div>
                            <div class="tam-phase-desc"><?= $ph['desc'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ============================ Process End ================================== -->


<!-- ============================ Past Sales ================================== -->
<section class="tam-sales-section">
    <div class="container">
        <div class="tam-section-label">Track Record</div>
        <h2 class="tam-section-title">Successful Sales</h2>
        <p class="tam-section-sub">A selection of completed projects across Metro Vancouver.</p>

        <div class="row g-3 mt-2">
            <?php
            $sales = [
                ['title'=>'Vimy Cres, Vancouver',          'type'=>'Detached',  'img'=>'m1'],
                ['title'=>'Elliott St, Vancouver',          'type'=>'Detached',  'img'=>'m2'],
                ['title'=>'Ivy On The Park, Vancouver West','type'=>'Condos',    'img'=>'m11'],
                ['title'=>'Lilloet St, Vancouver',          'type'=>'Detached',  'img'=>'m5'],
                ['title'=>'Soho District, Brentwood',       'type'=>'Condos',    'img'=>'m7'],
                ['title'=>'Grant St, Vancouver',            'type'=>'Detached',  'img'=>'m6'],
                ['title'=>'Malta Pl, Vancouver',            'type'=>'Detached',  'img'=>'m3'],
                ['title'=>'Central Park, Vancouver',        'type'=>'Condos',    'img'=>'m8'],
                ['title'=>'Falaise, Vancouver',             'type'=>'Detached',  'img'=>'m9'],
                ['title'=>'Cedar Creek, Burnaby',           'type'=>'Condos',    'img'=>'m10'],
            ];
            foreach ($sales as $s): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="tam-sale-card">
                    <div class="tam-sale-img">
                        <!-- Using navy placeholder since tamwynn.ca images are external -->
                        <div class="tam-sale-placeholder">
                            <i class="fas fa-<?= $s['type']==='Condos'?'building':'home' ?>"></i>
                        </div>
                        <div class="tam-sale-overlay"></div>
                        <span class="tam-sale-type"><?= $s['type'] ?></span>
                        <span class="tam-sale-badge"><i class="fas fa-check-circle"></i> Sold</span>
                    </div>
                    <div class="tam-sale-body">
                        <div class="tam-sale-title"><?= $s['title'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="https://tamwynn.ca" target="_blank" rel="noopener" class="btn btn-outline-primary rounded px-5">
                See All Projects at TamWynn.ca <i class="fas fa-arrow-up-right-from-square ms-2"></i>
            </a>
        </div>
    </div>
</section>
<!-- ============================ Past Sales End ================================== -->


<!-- ============================ Partners ================================== -->
<section class="tam-partners-section">
    <div class="container">
        <div class="tam-section-label">The Network</div>
        <h2 class="tam-section-title">Industry Partners</h2>
        <p class="tam-section-sub">Tam works with Vancouver's top specialists — coordinated on your behalf at cost, not premium.</p>

        <div class="row g-4 mt-2 justify-content-center">
            <?php
            $partners = [
                ['name'=>'RenderArt',  'role'=>'Architectural Visualisation', 'url'=>'https://www.renderart.ca',       'icon'=>'fa-cube'],
                ['name'=>'HomeStaged', 'role'=>'Professional Staging',         'url'=>'https://www.homestaged.ca',      'icon'=>'fa-couch'],
                ['name'=>'Duplex.House','role'=>'Real Estate Web Design',      'url'=>'https://www.realwebdesign.ca',   'icon'=>'fa-laptop-code'],
            ];
            foreach ($partners as $p): ?>
            <div class="col-lg-4 col-md-6">
                <a href="<?= $p['url'] ?>" target="_blank" rel="noopener" class="tam-partner-card">
                    <div class="tam-partner-icon"><i class="fas <?= $p['icon'] ?>"></i></div>
                    <div class="tam-partner-name"><?= $p['name'] ?></div>
                    <div class="tam-partner-role"><?= $p['role'] ?></div>
                    <div class="tam-partner-link">Visit site <i class="fas fa-arrow-right ms-1"></i></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- ============================ Partners End ================================== -->


<!-- ============================ CTA ================================== -->
<section class="tam-cta-section">
    <div class="container">
        <div class="tam-cta-inner">
            <div class="tam-cta-content">
                <div class="tam-section-label" style="color:rgba(201,168,76,.7);">Ready to Start?</div>
                <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;color:#fff;margin:10px 0 16px;line-height:1.2;">
                    Your Project Deserves<br><span style="color:#c9a84c;">A Head Start.</span>
                </h2>
                <p style="color:rgba(255,255,255,.6);font-size:15px;line-height:1.8;max-width:520px;">
                    Every day your project sits in the Invisibility Gap without a marketing strategy is momentum you can't get back.
                    List free on Wynston today, or book a consultation to explore the full Concierge service.
                </p>
                <div class="tam-cta-row mt-4">
                    <a href="create-account.php" class="tam-btn-primary">List Your Project — Free</a>
                    <a href="https://tamwynn.ca" target="_blank" rel="noopener" class="tam-btn-outline">
                        Visit TamWynn.ca <i class="fas fa-arrow-up-right-from-square ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="tam-cta-contact">
                <div class="tam-contact-card">
                    <div class="tam-contact-label">Get In Touch</div>
                    <div class="tam-contact-row">
                        <i class="fas fa-phone"></i>
                        <a href="tel:6047824689">(604) 782-4689</a>
                    </div>
                    <div class="tam-contact-row">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:sold@tamwynn.ca">sold@tamwynn.ca</a>
                    </div>
                    <div class="tam-contact-row">
                        <i class="fas fa-globe"></i>
                        <a href="https://tamwynn.ca" target="_blank">tamwynn.ca</a>
                    </div>
                    <div class="tam-contact-row">
                        <i class="fas fa-location-dot"></i>
                        <span>#100-1200 W 73rd Ave, Vancouver BC</span>
                    </div>
                    <div class="tam-contact-divider"></div>
                    <div style="font-size:10px;color:rgba(255,255,255,.3);line-height:1.7;">
                        Tam Nguyen, REALTOR® · Royal Pacific Realty Corporation<br>
                        Independently Owned &amp; Operated · REBGV · BCFSA
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ============================ CTA End ================================== -->


<style>
/* ── Variables ─────────────────────────────────────────────────────── */
:root {
    --navy:  #002446;
    --gold:  #c9a84c;
    --gold2: #e8d84b;
    --dark:  #001020;
    --cream: #f9f6f0;
}

/* ── Section labels & titles ───────────────────────────────────────── */
.tam-section-label {
    font-size: 11px; font-weight: 800; text-transform: uppercase;
    letter-spacing: 2px; color: var(--gold); margin-bottom: 8px;
}
.tam-section-title {
    font-size: clamp(24px,3vw,36px); font-weight: 800;
    color: var(--navy); line-height: 1.2; margin-bottom: 10px;
}
.tam-section-sub {
    font-size: 15px; color: #888; max-width: 560px;
}

/* ══════════════════════════════════════════════════════════════════════
   HERO
══════════════════════════════════════════════════════════════════════ */
.tam-hero {
    position: relative;
    background: linear-gradient(135deg, #001428 0%, var(--navy) 60%, #003a70 100%);
    padding: 80px 0 70px;
    overflow: hidden;
}
.tam-hero-bg {
    position: absolute; inset: 0;
    background: url('/assets/img/new-banner.jpg') center center / cover no-repeat;
    opacity: .08;
}
.tam-hero::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(201,168,76,.1) 0%, transparent 70%);
    top: -150px; right: -150px;
    border-radius: 50%;
}
.tam-hero-inner {
    position: relative;
    display: flex; gap: 60px; align-items: center;
}
/* Photo */
.tam-hero-photo-wrap {
    flex-shrink: 0; position: relative;
    width: 240px; height: 240px;
}
.tam-hero-photo-ring {
    position: absolute; inset: -8px;
    border-radius: 50%;
    border: 2px solid rgba(201,168,76,.4);
    animation: spin-slow 20s linear infinite;
}
@keyframes spin-slow { to { transform: rotate(360deg); } }
.tam-hero-photo {
    width: 240px; height: 240px;
    border-radius: 50%; object-fit: cover;
    border: 4px solid rgba(201,168,76,.5);
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
}
.tam-hero-badge {
    position: absolute; bottom: 8px; right: 0;
    background: linear-gradient(135deg, var(--gold), var(--gold2));
    color: var(--navy); font-size: 10px; font-weight: 800;
    padding: 5px 12px; border-radius: 20px;
    box-shadow: 0 4px 12px rgba(201,168,76,.4);
}
/* Intro text */
.tam-hero-eyebrow {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 2px; color: var(--gold); margin-bottom: 8px;
}
.tam-hero-name {
    font-size: clamp(36px,5vw,56px); font-weight: 900;
    color: #fff; line-height: 1; margin-bottom: 6px;
    letter-spacing: -1px;
}
.tam-hero-title {
    font-size: 16px; color: var(--gold); font-weight: 600; margin-bottom: 10px;
}
.tam-hero-creds {
    font-size: 12px; color: rgba(255,255,255,.4);
    line-height: 1.8; margin-bottom: 20px;
}
.tam-hero-bio {
    font-size: 15px; color: rgba(255,255,255,.65);
    line-height: 1.8; max-width: 540px; margin-bottom: 28px;
}
.tam-hero-bio strong { color: var(--gold); }
/* Stats */
.tam-stat-row { display: flex; gap: 28px; margin-bottom: 28px; flex-wrap: wrap; }
.tam-stat-n { font-size: 28px; font-weight: 900; color: var(--gold); line-height: 1; }
.tam-stat-l { font-size: 10px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }
/* Buttons */
.tam-cta-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.tam-btn-primary {
    display: inline-flex; align-items: center;
    background: linear-gradient(135deg, var(--gold), var(--gold2));
    color: var(--navy) !important; font-size: 13px; font-weight: 800;
    padding: 12px 24px; border-radius: 8px; text-decoration: none;
    transition: opacity .2s, transform .2s; border: none;
}
.tam-btn-primary:hover { opacity: .88; transform: translateY(-2px); text-decoration: none; }
.tam-btn-outline {
    display: inline-flex; align-items: center;
    background: transparent; color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.25);
    font-size: 13px; font-weight: 700;
    padding: 12px 24px; border-radius: 8px; text-decoration: none;
    transition: border-color .2s, background .2s;
}
.tam-btn-outline:hover { border-color: var(--gold); background: rgba(201,168,76,.08); text-decoration: none; }
.tam-btn-phone {
    display: inline-flex; align-items: center; gap: 8px;
    color: rgba(255,255,255,.6) !important; font-size: 13px; font-weight: 600;
    padding: 12px 0; text-decoration: none;
    transition: color .2s;
}
.tam-btn-phone:hover { color: var(--gold) !important; text-decoration: none; }
/* Social */
.tam-social-row { display: flex; gap: 12px; }
.tam-social-row a {
    width: 36px; height: 36px;
    border: 1.5px solid rgba(255,255,255,.15); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.5) !important; font-size: 14px;
    text-decoration: none; transition: border-color .2s, color .2s;
}
.tam-social-row a:hover { border-color: var(--gold); color: var(--gold) !important; }

/* ══════════════════════════════════════════════════════════════════════
   AWARDS
══════════════════════════════════════════════════════════════════════ */
.tam-awards-section { padding: 60px 0; background: var(--cream); }
.tam-awards-row { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 28px; }
.tam-award-card {
    flex: 1; min-width: 140px;
    background: #fff; border: 1px solid #ede8e0;
    border-radius: 14px; padding: 24px 20px;
    text-align: center;
    transition: box-shadow .2s, transform .2s;
    animation: fadeUp .5s ease both;
}
.tam-award-card:hover { box-shadow: 0 8px 30px rgba(0,36,70,.1); transform: translateY(-4px); }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
.tam-award-year { font-size: 11px; font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
.tam-award-icon { font-size: 24px; color: var(--gold); margin-bottom: 10px; }
.tam-award-title { font-size: 13px; font-weight: 700; color: var(--navy); line-height: 1.4; }

/* ══════════════════════════════════════════════════════════════════════
   PROCESS
══════════════════════════════════════════════════════════════════════ */
.tam-process-section {
    padding: 70px 0;
    background: linear-gradient(135deg, #001428, var(--navy));
    position: relative; overflow: hidden;
}
.tam-process-section::before {
    content: '';
    position: absolute; width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(201,168,76,.08) 0%, transparent 70%);
    bottom: -200px; right: -200px; border-radius: 50%;
}
.tam-phases { display: flex; flex-direction: column; gap: 12px; position: relative; }
.tam-phase-card {
    display: flex; align-items: flex-start; gap: 16px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px; padding: 18px 20px;
    transition: background .2s, border-color .2s;
}
.tam-phase-card:hover { background: rgba(201,168,76,.06); border-color: rgba(201,168,76,.2); }
.tam-phase-num {
    flex-shrink: 0; font-size: 11px; font-weight: 900;
    color: var(--gold); letter-spacing: 1px; opacity: .6;
    padding-top: 2px;
}
.tam-phase-icon {
    flex-shrink: 0; width: 36px; height: 36px;
    background: rgba(201,168,76,.12); border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 14px;
}
.tam-phase-title { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 5px; }
.tam-phase-desc  { font-size: 12px; color: rgba(255,255,255,.45); line-height: 1.7; }

/* ══════════════════════════════════════════════════════════════════════
   PAST SALES
══════════════════════════════════════════════════════════════════════ */
.tam-sales-section { padding: 70px 0; background: #fff; }
.tam-sale-card {
    border: 1px solid #e8e4dd; border-radius: 12px; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.tam-sale-card:hover { box-shadow: 0 8px 30px rgba(0,36,70,.1); transform: translateY(-3px); }
.tam-sale-img { position: relative; height: 130px; }
.tam-sale-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, var(--navy), #003a70);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.2); font-size: 28px;
}
.tam-sale-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,20,50,.7) 0%, transparent 55%);
}
.tam-sale-type {
    position: absolute; top: 8px; left: 10px;
    background: rgba(0,0,0,.5); color: #fff;
    font-size: 9px; font-weight: 700; padding: 3px 8px; border-radius: 4px;
}
.tam-sale-badge {
    position: absolute; top: 8px; right: 10px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff; font-size: 9px; font-weight: 700;
    padding: 3px 8px; border-radius: 4px;
}
.tam-sale-body { padding: 12px 14px; }
.tam-sale-title { font-size: 13px; font-weight: 700; color: var(--navy); }

/* ══════════════════════════════════════════════════════════════════════
   PARTNERS
══════════════════════════════════════════════════════════════════════ */
.tam-partners-section { padding: 70px 0; background: var(--cream); }
.tam-partner-card {
    display: block; text-decoration: none;
    background: #fff; border: 1px solid #ede8e0;
    border-radius: 14px; padding: 32px 28px; text-align: center;
    transition: box-shadow .2s, transform .2s, border-color .2s;
}
.tam-partner-card:hover { box-shadow: 0 8px 30px rgba(0,36,70,.1); transform: translateY(-4px); border-color: var(--gold); text-decoration: none; }
.tam-partner-icon { font-size: 28px; color: var(--gold); margin-bottom: 14px; }
.tam-partner-name { font-size: 18px; font-weight: 800; color: var(--navy); margin-bottom: 5px; }
.tam-partner-role { font-size: 13px; color: #888; margin-bottom: 14px; }
.tam-partner-link { font-size: 12px; font-weight: 700; color: var(--navy); }
.tam-partner-card:hover .tam-partner-link { color: var(--gold); }

/* ══════════════════════════════════════════════════════════════════════
   CTA
══════════════════════════════════════════════════════════════════════ */
.tam-cta-section {
    padding: 70px 0;
    background: linear-gradient(135deg, #001428, var(--navy));
}
.tam-cta-inner { display: flex; gap: 60px; align-items: center; flex-wrap: wrap; }
.tam-cta-content { flex: 1; min-width: 280px; }
.tam-contact-card {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(201,168,76,.2);
    border-radius: 16px; padding: 28px;
    min-width: 260px;
}
.tam-contact-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--gold); margin-bottom: 18px; }
.tam-contact-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.tam-contact-row i { color: var(--gold); font-size: 13px; margin-top: 2px; flex-shrink: 0; }
.tam-contact-row a, .tam-contact-row span { font-size: 13px; color: rgba(255,255,255,.7); text-decoration: none; line-height: 1.5; }
.tam-contact-row a:hover { color: var(--gold); }
.tam-contact-divider { height: 1px; background: rgba(255,255,255,.08); margin: 18px 0; }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .tam-hero-inner { flex-direction: column; text-align: center; gap: 32px; }
    .tam-hero-photo-wrap { margin: 0 auto; }
    .tam-stat-row, .tam-cta-row, .tam-social-row { justify-content: center; }
    .tam-hero-bio { margin: 0 auto 28px; }
    .tam-cta-inner { flex-direction: column; }
}
@media (max-width: 600px) {
    .tam-awards-row { flex-direction: column; }
    .tam-phase-card { flex-direction: column; gap: 10px; }
}
</style>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>