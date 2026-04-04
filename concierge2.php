<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

ob_start();
include "$base_dir/navbar2.php";
$navlink_content = ob_get_clean();
$page= 'nav2';
$fpage= 'foot';

ob_start();
?>

<!-- HERO -->
<div class="hero-banner vedio-banner">
    <div class="overlay"></div>
    <video playsinline autoplay muted loop>
        <source src="<?php echo $static_url; ?>/img/banners.mp4" type="video/mp4">
    </video>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-11 col-sm-12">
                <div class="inner-banner-text text-center">
                    <p style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:16px;">Wynston Concierge</p>
                    <h2 class="text-light" style="font-size:46px;font-weight:900;line-height:1.2;">The Marketing Powerhouse<br>Your Project Deserves.</h2>
                    <p class="text-light mt-3" style="font-size:17px;opacity:.85;max-width:600px;margin:16px auto 0;">Rennies and Magnum exist for the big developers. Wynston Concierge exists for everyone else — giving Metro Vancouver's boutique builders the same launch power, strategy, and market presence, without the seven-figure price tag.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WHAT WE DO FOR YOU -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">What Wynston Concierge Does For You</p>
                <h2 style="font-size:36px;font-weight:600;color:#002446;margin-bottom:20px;">Everything Your Project Needs.<br>Under One Roof.</h2>
                <p style="font-size:17px;color:#555;line-height:1.8;max-width:680px;margin:0 auto;">Building a great home is hard enough. We handle the entire marketing side — from first impression to final sale — so you can focus on what you build best.</p>
            </div>
        </div>
        <div class="row g-4 mt-5">
            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-cube" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">3D Renderings & Virtual Tours</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Professional architectural renderings and virtual walkthroughs that sell your vision before a single wall goes up. First impressions sell projects — and yours will be unforgettable.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-drafting-compass" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Floorplan Design</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Clean, professional floorplans that communicate your layout clearly — the kind buyers and investors expect before they get serious about making an offer.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-globe" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Dedicated Project Website</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Your project gets its own branded page on Wynston — renderings, floorplans, neighbourhood highlights, and a direct line to Tam's team. A 24/7 sales centre buyers can visit from anywhere.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-couch" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Staging Consultation</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">When your units are ready to show, we guide you on presentation — finishes, furniture selection, and staging strategy that helps buyers picture themselves living there.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-bullhorn" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Pre-Market Agent Campaign</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Before you hit MLS, your project lands in front of 5,000+ realtors in our private network — with early access, co-op commission clarity, and buyer introductions already in motion.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div style="background:#002446;border-radius:16px;padding:32px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-handshake" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:10px;">Full Listing Representation</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:24px;">When your units are ready for sale, Tam Nguyen and her team handle the entire listing and sales process — negotiation, reporting, and closing — with a warm buyer pool already waiting.</p>
                    <a href="contact.php?ref=concierge" class="btn" style="background:#c9a84c;color:#002446;font-weight:700;padding:11px 24px;border-radius:8px;font-size:14px;">Book a Listing Presentation</a>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-12 text-center">
                <p style="font-size:16px;color:#555;margin-bottom:20px;">Not sure where to start? We walk you through everything in a private 30-minute presentation — no obligation, no pressure.</p>
                <a href="contact.php?ref=concierge" class="btn btn-primary px-5 py-3 rounded" style="font-weight:700;font-size:15px;">Book Your Free Listing Presentation</a>
            </div>
        </div>
    </div>
</section>

<!-- WYNSTON PORTAL — EXCLUSIVE CONCIERGE BENEFIT -->
<section style="padding:80px 0;background:#002446;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Exclusive to Concierge Clients</p>
                <h2 style="font-size:34px;font-weight:700;color:#fff;margin-bottom:16px;">And There Is One More Thing.</h2>
                <p style="font-size:16px;color:rgba(255,255,255,.75);line-height:1.8;max-width:680px;margin:0 auto;">Concierge clients get full, unlimited access to the Wynston Intelligence Portal — BC's most advanced feasibility and market data platform for multiplex developers. The same tool driving the next generation of informed builders in Vancouver, yours at no extra cost.</p>
            </div>
        </div>
        <div class="row g-4 mt-5">
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-map-location-dot" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Live Feasibility Map</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Every R1-1 zoned lot in Vancouver scored for 3, 6, and 8-unit eligibility in real time. Know exactly what's buildable before you make an offer.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-calculator" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Instant Pro Forma</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Build cost, DCLs, permit fees, peat zone contingencies, and projected profit — calculated automatically the moment you click a lot.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-cube" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">3D Architectural Visualizer</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Permit-ready, architecturally styled 3D models on any lot — scaled to actual dimensions, Vancouver setbacks applied. Nothing like it exists anywhere else in Canada.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-chart-line" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Real Market Velocity</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Pre-registry movement data by neighbourhood — not just sold prices. Month-over-month trends benchmarked against CMHC. The intelligence institutional buyers have always paid for.</p>
                </div>
            </div>
        </div>

        <!-- Wynston Report callout -->
        <div class="row mt-5 justify-content-center">
            <div class="col-lg-9">
                <div style="background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.35);border-radius:16px;padding:32px 40px;display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
                    <div style="flex:0 0 auto;">
                        <div style="width:60px;height:60px;border-radius:14px;background:#c9a84c;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-file-invoice-dollar" style="color:#002446;font-size:24px;"></i>
                        </div>
                    </div>
                    <div style="flex:1;min-width:220px;">
                        <h5 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:6px;">Unlimited Wynston Reports — Included Free.</h5>
                        <p style="font-size:14px;color:rgba(255,255,255,.7);margin:0;">The Wynston Report is an investor-grade feasibility PDF covering pro forma, constraint flags, market velocity, and full site analysis. Available to all portal users for $19.99 per report. Concierge clients get unlimited reports, printed under your own brand, at no charge.</p>
                    </div>
                    <div style="flex:0 0 auto;">
                        <a href="contact.php?ref=concierge" class="btn" style="background:#c9a84c;color:#002446;font-weight:700;padding:12px 24px;border-radius:8px;white-space:nowrap;">Find Out More</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 justify-content-center">
            <div class="col-lg-8 text-center">
                <p style="font-size:14px;color:rgba(255,255,255,.45);">Portal access is free for all registered developers. Concierge clients receive unlimited branded reports as part of their listing partnership.</p>
                <a href="portal.php" style="font-size:14px;color:#c9a84c;text-decoration:underline;">Explore the Wynston Portal &rarr;</a>
            </div>
        </div>
    </div>
</section>

<!-- TWO PATHS -->
<section style="padding:80px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Two Ways to Work With Us</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:16px;">Two Levels of Exposure. One Platform.</h2>
                <p style="font-size:16px;color:#666;">Every developer on Wynston starts the same way — a free listing that puts your project in front of buyers researching new construction. When you are ready to go further, our Concierge partnership activates the full marketing machine.</p>
            </div>
        </div>
        <div class="row g-4 mt-4 justify-content-center">

            <!-- FREE -->
            <div class="col-lg-5 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:40px;height:100%;border:1px solid #ede8e0;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                        <span style="background:#f3f4f6;color:#666;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:4px 12px;border-radius:20px;">Foundation Listing</span>
                        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;">Free</span>
                    </div>
                    <h3 style="font-size:24px;font-weight:800;color:#002446;margin-bottom:12px;">Publish Your Project. Build Awareness.</h3>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin-bottom:24px;">Your project may already appear in our new construction tracker. Claim it, upload your photos, renderings and floorplans, and start building buyer awareness while you build. No contract. No commission. No catch.</p>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:28px;">
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            Listed on Wynston's new construction tracker
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            Upload photos, renderings and floorplans
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            Visible to buyers researching new construction in your area
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            Portal access — live feasibility map and pro forma tools
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            No contract required — option to purchase Creative Package
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            Wynston Report available at $19.99 per report
                        </div>
                    </div>
                    <a href="submit-property.php" class="btn btn-outline-primary full-width" style="font-weight:700;padding:12px;">Claim Your Free Listing</a>
                </div>
            </div>

            <!-- CONCIERGE -->
            <div class="col-lg-5 col-md-6">
                <div style="background:#002446;border-radius:16px;padding:40px;height:100%;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:0;right:0;background:#c9a84c;color:#002446;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:6px 20px;border-radius:0 0 0 12px;">Most Popular</div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                        <span style="background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:4px 12px;border-radius:20px;">Concierge Partnership</span>
                    </div>
                    <h3 style="font-size:24px;font-weight:800;color:#fff;margin-bottom:12px;">The Full Marketing Machine. Activated for Your Project.</h3>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:24px;">We act as your dedicated marketing agency and listing team — renderings to closing day. Standard commission only. No marketing fees, no retainers.</p>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:28px;">
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Professional 3D renderings and floorplans
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Full project marketing strategy and digital campaigns
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            18-month head start — marketing from land acquisition
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Dedicated project website with video and virtual tour
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Exclusive pre-market launch to 5,000+ agent network
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Staging consultation and photography at completion
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Exclusive listing agreement — Tam's team represents your sale
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Standard commission only — no marketing fees
                        </div>
                        <div style="height:1px;background:rgba(201,168,76,.25);margin:4px 0;"></div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#c9a84c;font-weight:700;">
                            <i class="fa-solid fa-star" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Full Wynston Portal access included
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#c9a84c;font-weight:700;">
                            <i class="fa-solid fa-star" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Unlimited branded Wynston Reports — free
                        </div>
                    </div>
                    <a href="contact.php?ref=concierge" class="btn full-width" style="background:#c9a84c;color:#002446;font-weight:700;padding:12px;border-radius:8px;">Book a Private Presentation</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">The Process</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:16px;">Simple to Start. Powerful at Completion.</h2>
                <p style="font-size:16px;color:#666;">Whether you list free today or go full Concierge, the process is designed around your build timeline — not ours.</p>
            </div>
        </div>
        <div class="row g-4 mt-4 justify-content-center">
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">1</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Your Project Gets Listed</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">Sign up and claim your project page. Upload whatever you have — even just an address and a rendering. Your listing goes live on our new construction tracker immediately.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">2</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Buyers Research You Early</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">Buyers researching new construction in your neighbourhood find your project. They follow it, ask questions, and build familiarity as construction progresses.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">3</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Concierge Activates</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">Want the full marketing package? We present our Concierge plan, sign an exclusive listing agreement, and get to work — renderings, staging, campaigns and all.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">4</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">List. With Buyers Ready.</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">When your units are ready for sale, Tam's team lists your project with an informed buyer pool already waiting. Faster closes. Stronger offers. Full price.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BRAND STORY -->
<section style="padding:80px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 col-md-6">
                <img src="<?php echo $static_url; ?>/img/vec-2.png" class="img-fluid" alt="Wynston Vision" />
            </div>
            <div class="col-lg-6 col-md-6">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Our Commitment</p>
                <h2 style="font-size:30px;font-weight:600;color:#002446;margin-bottom:20px;">Levelling the Playing Field. For Good.</h2>
                <p style="font-size:16px;color:#555;line-height:1.8;">Wynston was built on one belief: that every development in British Columbia deserves a world-class launch. Whether you are building a 4-unit multiplex or a 20-unit building, the quality of your market presence should be uncompromising.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">Rennies and Magnum have always had the tools, the reach, and the buyer relationships that drive high-velocity sales. We have spent over a decade in Vancouver's most competitive markets learning exactly how they do it — and we built Wynston to bring that same power to every project we represent.</p>
                <p style="font-size:16px;font-weight:700;color:#002446;">"Your project deserves more than a sign on a fence."</p>
                <a href="about-us.php" class="btn btn-outline-primary mt-3" style="font-weight:700;">Meet the Team Behind Wynston</a>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="bg-primary call-to-act-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="text-white">Your next project deserves buyers who are already informed.</h2>
                <p class="text-white mt-3 mb-4" style="opacity:.85;">They just haven't heard about it yet. Let's build that awareness — starting today.</p>
                <div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;">
                    <a href="submit-property.php" class="btn btn-light btn-lg" style="color:#002446;font-weight:700;">Publish Your Project Free</a>
                    <a href="contact.php?ref=concierge" class="btn btn-outline-light btn-lg" style="font-weight:700;">Book a Private Presentation</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>