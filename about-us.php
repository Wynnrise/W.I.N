<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets'; 
require_once "$base_dir/db.php";

ob_start();
include "$base_dir/navbar.php"; 
$navlink_content = ob_get_clean(); 
$page= 'nav';
$fpage= 'foot';

ob_start();
?>

<!-- PAGE TITLE -->
<div class="page-title" style="background:#002446;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <h2 class="ipt-title" style="color:#fff;">About Wynston</h2>
                <span class="ipn-subtitle" style="color:rgba(255,255,255,.6);">Metro Vancouver's Real Estate Intelligence Platform</span>
            </div>
        </div>
    </div>
</div>

<!-- WHO WE ARE -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 col-md-6">
                <img src="<?php echo $static_url; ?>/img/sb.png" class="img-fluid rounded" alt="Wynston — Metro Vancouver" />
            </div>
            <div class="col-lg-6 col-md-6">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Who We Are</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:20px;">We Built the Platform Small Developers Always Deserved.<br>Then We Made It Work for Everyone.</h2>
                <p style="font-size:16px;color:#555;line-height:1.8;">In Metro Vancouver, small developers building 4-to-20 unit multiplexes face a problem larger developers don't. Under BC's Real Estate Development Marketing Act, they cannot market their projects for sale until occupancy permits are issued — which can be 18 months or more after construction begins.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">Meanwhile, large developers run full pre-sale campaigns — showrooms, renderings, waitlists — long before a shovel hits the ground. Small builders lose months of market momentum, buyer awareness, and pricing leverage. Then they sell at completion into a market that doesn't know they exist.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;"><strong style="color:#002446;">Wynston was built to close that gap.</strong> But we didn't stop at new construction tracking. We built a platform that serves every side of the Metro Vancouver market — buyers who want early access, sellers who want to know their true value, and developers who deserve a full marketing department behind every project they build.</p>
                <div style="background:#f9f6f0;border-left:4px solid #c9a84c;padding:18px 24px;border-radius:0 8px 8px 0;margin-top:24px;">
                    <p style="font-size:15px;font-style:italic;color:#444;margin:0;">"We don't wait for the market to happen — we help our clients build it."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- THE PLATFORM — THREE AUDIENCES -->
<section style="padding:80px 0;background:#002446;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">One Platform. Every Side of the Market.</p>
                <h2 style="font-size:34px;font-weight:700;color:#fff;margin-bottom:16px;">Buyers, Sellers, and Developers — All Connected.</h2>
                <p style="font-size:16px;color:rgba(255,255,255,.75);max-width:660px;margin:0 auto;line-height:1.8;">Wynston is not a listing platform. It is where every side of the Metro Vancouver real estate market comes together — and where being informed gives you a real edge over everyone who isn't.</p>
            </div>
        </div>

        <div class="row g-4 mt-5">

            <!-- Developers -->
            <div class="col-lg-4 col-md-12">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.25);border-radius:16px;padding:36px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-building" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;margin-bottom:12px;">For Developers</div>
                    <h4 style="font-size:19px;font-weight:800;color:#fff;margin-bottom:14px;">From Data to Deal — Before Shovel Hits Ground</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.7);line-height:1.8;margin-bottom:20px;">A developer researches a lot on W.I.N, runs the pro forma, and makes a confident acquisition. They build. Wynston Concierge starts marketing 18 months before completion — building a buyer pool while the foundation is still being poured. By the time units are listed, informed buyers are already waiting. At full price. Not discounted.</p>
                    <a href="concierge.php" style="font-size:13px;color:#c9a84c;font-weight:700;text-decoration:underline;">See Wynston Concierge →</a>
                </div>
            </div>

            <!-- Sellers -->
            <div class="col-lg-4 col-md-12">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.25);border-radius:16px;padding:36px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-house-circle-check" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;margin-bottom:12px;">For Sellers</div>
                    <h4 style="font-size:19px;font-weight:800;color:#fff;margin-bottom:14px;">List Where the Serious Buyers Already Are</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.7);line-height:1.8;margin-bottom:20px;">A homeowner checks their property on W.I.N and sees active developer acquisition interest in their neighbourhood. They list with Tam. Tam connects them directly to builders in the Wynston network who are actively searching that area — giving the seller access to a buyer pool most agents simply cannot reach. Often before the property ever hits the open market.</p>
                    <a href="portal.php?tab=seller" style="font-size:13px;color:#c9a84c;font-weight:700;text-decoration:underline;">Research Your Property →</a>
                </div>
            </div>

            <!-- Buyers -->
            <div class="col-lg-4 col-md-12">
                <div style="background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.35);border-radius:16px;padding:36px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:#c9a84c;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-magnifying-glass" style="color:#002446;font-size:20px;"></i>
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;margin-bottom:12px;">For Buyers</div>
                    <h4 style="font-size:19px;font-weight:800;color:#fff;margin-bottom:14px;">Research Before Everyone Else Even Knows to Look</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.75);line-height:1.8;margin-bottom:20px;">A buyer discovers a coming-soon development on Wynston months before it hits MLS. They use W.I.N to research neighbourhood pricing, track what is being built nearby, and walk into every showing with data most buyers and agents simply don't have. By the time units are listed, they are already informed and ready — not scrambling in a bidding war with everyone who just saw it go live.</p>
                    <a href="half-map.php" style="font-size:13px;color:#c9a84c;font-weight:700;text-decoration:underline;">Browse Coming Soon →</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- W.I.N — THE INTELLIGENCE LAYER -->
<section style="padding:80px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 col-md-6">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">W.I.N — Wynston Intelligent Navigator</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:20px;">The Data Layer That Separates Wynston from Every Other Platform.</h2>
                <p style="font-size:16px;color:#555;line-height:1.8;">Every other real estate platform shows you what already happened. W.I.N shows you what is happening right now — and what is coming next.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">Built on Vancouver Open Data, TransLink transit records, REBGV market data, and CMHC benchmarks — updated monthly — W.I.N is the only platform in BC that combines live zoning feasibility, real build-cost modelling, and pre-registry market velocity in a single tool. Developers use it to make smarter acquisition decisions. Sellers use it to understand their true market position. Buyers use it to research neighbourhoods before making the most important financial decision of their lives.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">This is not a Zestimate. This is not an algorithm estimate. This is actual market intelligence — the kind institutional buyers have always had, and boutique developers and individual sellers have never had access to. Until now.</p>
                <a href="portal.php" class="btn btn-outline-primary mt-3" style="font-weight:700;">Explore W.I.N Free</a>
            </div>
            <div class="col-lg-6 col-md-6">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <?php
                    $win_features = [
                        ["fa-map-location-dot", "Live Feasibility Map", "Every R1-1 zoned lot in Vancouver scored for 3, 6, and 8-unit eligibility — zoning, frontage, transit proximity, and lane access checked automatically."],
                        ["fa-calculator", "Instant Pro Forma", "Build cost, DCLs, permit fees, peat zone contingencies, and projected profit calculated the moment you click a lot. No spreadsheet, no consultant."],
                        ["fa-cube", "3D Architectural Visualizer", "Permit-ready, architecturally styled 3D models on any lot — scaled to actual Vancouver dimensions. Nothing like it exists anywhere else in Canada."],
                        ["fa-triangle-exclamation", "Constraint Intelligence", "Heritage flags, peat zones, covenants, easements — surfaced automatically for every lot so nothing catches you off guard at due diligence."],
                        ["fa-chart-line", "Real Market Velocity", "Pre-registry price movement by neighbourhood — not just sold prices. Month-over-month trends benchmarked against CMHC figures."],
                        ["fa-file-invoice-dollar", "The Wynston Report", "Investor-grade feasibility PDF covering every data point — available at \$19.99. Free and unlimited for Wynston Concierge clients."],
                    ];
                    foreach ($win_features as $f):
                    ?>
                    <div style="display:flex;gap:16px;align-items:flex-start;background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #ede8e0;">
                        <div style="width:40px;height:40px;border-radius:10px;background:#002446;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid <?php echo $f[0]; ?>" style="color:#c9a84c;font-size:16px;"></i>
                        </div>
                        <div>
                            <h5 style="font-size:14px;font-weight:800;color:#002446;margin:0 0 4px;"><?php echo $f[1]; ?></h5>
                            <p style="font-size:13px;color:#666;line-height:1.6;margin:0;"><?php echo $f[2]; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WYNSTON CONCIERGE — WHAT IT REALLY IS -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Wynston Concierge</p>
                <h2 style="font-size:34px;font-weight:600;color:#002446;margin-bottom:16px;">Your Dedicated Marketing Department.<br>Built Into Your Listing.</h2>
                <p style="font-size:16px;color:#666;max-width:680px;margin:0 auto;line-height:1.8;">A dedicated marketing agency for a single development project costs $50,000–$150,000 before a single unit sells. Wynston Concierge delivers every one of those services — coordinated and executed by Tam's team — at no cost beyond a standard real estate commission.</p>
            </div>
        </div>

        <div class="row g-4 mt-5">
            <?php
            $services = [
                ["fa-cube", "3D Renderings & Virtual Tours", "Photorealistic architectural renderings and cinematic virtual walkthroughs built from your plans before a single wall goes up. The single most powerful tool for pre-completion demand."],
                ["fa-drafting-compass", "Floorplan Design", "Investor-grade floorplans that communicate your layout with precision. Buyers and investors will not make serious offers without them."],
                ["fa-globe", "Dedicated Project Website", "Your own branded page on Wynston — a 24/7 digital sales centre with renderings, floorplans, neighbourhood data, and construction updates."],
                ["fa-bullhorn", "Pre-Market Agent Campaign", "Your project reaches 5,000+ realtors in our private network before MLS goes live — complete with floorplans, co-op commission details, and first-look access."],
                ["fa-mobile-screen", "Targeted Digital Campaigns", "Precision-targeted Instagram, Facebook, and email campaigns that reach your ideal buyers — families in key postal codes, pre-approved first-timers, local investors."],
                ["fa-camera", "Photography, Drone & Video", "Professional production at completion — high-resolution photography, drone footage, and cinematic video that positions your project as the premium product it is."],
                ["fa-couch", "Staging Strategy", "We guide you on every completion presentation decision — finishes, furniture, flow — and connect you with our trusted staging partners to maximise buyer appeal."],
                ["fa-handshake", "Negotiation & Closing", "Tam's team negotiates assertively on your behalf, manages every detail from offer to close, and keeps you informed with transparent reporting throughout."],
            ];
            foreach ($services as $i => $s):
                $dark = $i === 7; // Make last card dark
            ?>
            <div class="col-lg-3 col-md-6">
                <div style="background:<?php echo $dark ? '#002446' : '#fff'; ?>;border-radius:14px;padding:28px;height:100%;border:1px solid <?php echo $dark ? 'transparent' : '#ede8e0'; ?>;transition:box-shadow .2s;" <?php if(!$dark): ?>onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'"<?php endif; ?>>
                    <div style="width:44px;height:44px;border-radius:10px;background:<?php echo $dark ? 'rgba(201,168,76,.15)' : '#002446'; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                        <i class="fa-solid <?php echo $s[0]; ?>" style="color:#c9a84c;font-size:17px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:<?php echo $dark ? '#fff' : '#002446'; ?>;margin-bottom:8px;"><?php echo $s[1]; ?></h5>
                    <p style="font-size:13px;color:<?php echo $dark ? 'rgba(255,255,255,.65)' : '#666'; ?>;line-height:1.7;margin:0;"><?php echo $s[2]; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <p style="font-size:16px;color:#555;margin-bottom:20px;">All of the above. Standard commission only. No agency fees. No retainers.</p>
                <a href="concierge.php" class="btn btn-primary px-5 py-3 rounded me-3" style="font-weight:700;">See Full Concierge Details</a>
                <a href="contact.php?ref=concierge" class="btn btn-outline-primary px-5 py-3 rounded" style="font-weight:700;">Book a Private Presentation</a>
            </div>
        </div>
    </div>
</section>

<!-- LEGAL COMPLIANCE NOTE -->
<section style="padding:40px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:16px;">How We Operate</p>
                <h3 style="font-size:22px;font-weight:600;color:#002446;margin-bottom:16px;">Transparent. Compliant. Fully Disclosed.</h3>
                <p style="font-size:15px;color:#666;line-height:1.8;margin-bottom:0;">Every new construction listing on Wynston is based on publicly available permit and construction records. We publish these listings strictly for <strong style="color:#002446;">research and awareness purposes only</strong> — they do not constitute an offer for sale, and no purchase can be made through this platform. This is clearly disclosed to every visitor. Active MLS listings are provided under licence from the Real Estate Board of Greater Vancouver.</p>
            </div>
        </div>
    </div>
</section>

<!-- MEET TAM -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 col-md-5">
                <img src="<?php echo $static_url; ?>/img/user-6.jpg" class="img-fluid rounded shadow" alt="Tam Nguyen — Founder, Wynston" style="border-radius:16px!important;" />
            </div>
            <div class="col-lg-7 col-md-7">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">A Message from Our Founder</p>
                <h2 style="font-size:30px;font-weight:600;color:#002446;margin-bottom:20px;">Why I Built Wynston</h2>
                <p style="font-size:16px;color:#555;line-height:1.8;">"I've spent my career watching talented small developers in Metro Vancouver get outpaced — not because their projects weren't great, but because they didn't have the platform or the marketing muscle to compete with larger builders. BC's Real Estate Development Marketing Act creates an 18-month blind spot for small builders. Their projects get approved, construction begins, and buyers have no idea they exist until the 'For Sale' sign goes up. By then, the momentum window has already closed."</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">"Wynston started as my answer to that — a platform where new construction projects are tracked and made available for public research. But it became something bigger. When I started building W.I.N, I realised the same data gap that hurts developers also hurts sellers who don't know a developer wants their lot, and buyers who discover great homes the same day everyone else does. Wynston is now the platform that serves all three."</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">"And Wynston Concierge is the answer to the question I kept hearing from boutique developers: 'How do the big guys do it?' Now they can do it too. At no extra cost."</p>
                <p style="font-size:16px;color:#002446;font-weight:700;">"We don't just list properties. We build markets."</p>
                <div style="margin-top:28px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                    <img src="<?php echo $static_url; ?>/img/sign-pic.png" style="max-width:130px;" alt="Tam Nguyen Signature" />
                    <div>
                        <h5 style="font-size:17px;font-weight:800;color:#002446;margin:0;">Tam Nguyen</h5>
                        <span style="font-size:13px;color:#888;">Founder, Wynston Real Estate</span><br>
                        <span style="font-size:13px;color:#888;">Realtor® — Royal Pacific Realty</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LEGAL DISCLOSURE BAR -->
<section style="padding:28px 0;background:#002446;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <p style="font-size:12px;color:rgba(255,255,255,.45);line-height:1.8;margin:0;">
                    Wynston Real Estate is operated by Tam Nguyen, Realtor® with Royal Pacific Realty.
                    MLS® listing data is provided under license from the Real Estate Board of Greater Vancouver.
                    New construction listings are based on publicly available records and are published for <strong style="color:rgba(255,255,255,.65);">information and awareness purposes only — they do not constitute an offer for sale</strong> under the Real Estate Development Marketing Act of British Columbia.
                    All trademarks are owned by the Canadian Real Estate Association (CREA).
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="bg-primary call-to-act-wrap">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-7">
                <h2 class="text-white">Ready to see what Wynston can do for your project?</h2>
                <p class="text-white opacity-75">Developers — list free or book a Concierge presentation. Sellers — research your property on W.I.N. Buyers — discover what's coming before it hits MLS.</p>
            </div>
            <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0" style="display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end;">
                <a href="concierge.php" class="btn btn-light" style="color:#002446;font-weight:700;">Wynston Concierge</a>
                <a href="portal.php" class="btn btn-outline-light" style="font-weight:700;">Explore W.I.N</a>
            </div>
        </div>
    </div>
</section>

<?php
$hero_content = ob_get_clean(); 
include "$base_dir/style/base.php";
?>
