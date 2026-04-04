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
                    <p style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:16px;">Wynston Concierge — Your Dedicated Marketing Department</p>
                    <h2 class="text-light" style="font-size:46px;font-weight:900;line-height:1.2;">Stop Listing Your Projects.<br>Start Launching Them.</h2>
                    <p class="text-light mt-3" style="font-size:17px;opacity:.85;max-width:660px;margin:16px auto 0;">Large developers don't list their projects — they launch them. With a full marketing department, a pre-warmed buyer database, and a sales strategy that starts 18 months before completion. Wynston Concierge gives every boutique builder in BC that same machine. </p>
                </div>
               
                                </div>
                            </div>
                        </div>
                    </div>
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
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Your Marketing Department. On Call.</p>
                <h2 style="font-size:36px;font-weight:600;color:#002446;margin-bottom:20px;">Every Service a Marketing Agency Provides —<br>Built Into Your Listing.</h2>
                <p style="font-size:17px;color:#555;line-height:1.8;max-width:700px;margin:0 auto;">A dedicated marketing agency for a single project costs $50,000–$150,000 before a single unit sells. Wynston Concierge delivers every one of those services — coordinated, executed, and managed by Tam's team — at no cost beyond a standard real estate commission. Here is exactly what that includes.</p>
            </div>
        </div>

        <div class="row g-4 mt-5">

            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-cube" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">3D Renderings &amp; Cinematic Virtual Tours</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Photorealistic architectural renderings and cinematic virtual walkthroughs built from your plans — before a single wall goes up. Buyers form emotional attachment to homes they can walk through. This is the single most powerful tool for pre-completion demand, and it is standard in every Concierge listing.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-drafting-compass" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Professional Floorplan Design</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Investor-grade floorplans that communicate your layout with precision and clarity. Buyers and investors will not make serious offers without them — and the quality of your floorplan directly signals the quality of your build. Ours are designed to convert interest into inquiries.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-globe" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Dedicated Project Website — Your 24/7 Sales Centre</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Your project gets its own branded page on Wynston — renderings, floorplans, neighbourhood data, construction progress updates, and a direct inquiry line to Tam's team. While other developers rely on a basic MLS entry, your project has a full digital showroom operating around the clock, on every device, in every market.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-couch" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Staging Strategy &amp; Completion Presentation</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">At completion, we guide you through every presentation decision — finishes, furniture selection, flow, and lifestyle positioning. Staged properties sell faster and at higher prices. We connect you with our trusted staging partners and manage the process so your units show as the premium product they are.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#fff;border-radius:16px;padding:32px;height:100%;border:1px solid #ede8e0;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,36,70,.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:52px;height:52px;border-radius:12px;background:#002446;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-bullhorn" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#002446;margin-bottom:10px;">Exclusive Pre-Market Agent Campaign</h4>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin:0;">Before a single MLS listing goes live, your project is in the inboxes of 5,000+ realtors in our private network — complete with floorplans, co-op commission details, and exclusive first-look access. We turn Metro Vancouver's realtor community into your active sales force, generating offers before your neighbours even know you're selling.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div style="background:#002446;border-radius:16px;padding:32px;height:100%;">
                    <div style="width:52px;height:52px;border-radius:12px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <i class="fa-solid fa-handshake" style="color:#c9a84c;font-size:20px;"></i>
                    </div>
                    <h4 style="font-size:17px;font-weight:800;color:#fff;margin-bottom:10px;">Full Listing, Negotiation &amp; Closing</h4>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:24px;">When your units are complete, Tam's team activates MLS with a buyer pool already primed and waiting. We negotiate assertively using live market data and buyer feedback, manage every detail from accepted offer to keys-in-hand, and keep you informed at every stage with transparent reporting. You focus on the build. We protect the price.</p>
                    <a href="contact.php?ref=concierge" class="btn" style="background:#c9a84c;color:#002446;font-weight:700;padding:11px 24px;border-radius:8px;font-size:14px;">Book a Listing Presentation</a>
                </div>
            </div>

        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <p style="font-size:16px;color:#555;margin-bottom:20px;">Every project is different. In a private 30-minute presentation, we map out exactly how Wynston Concierge would launch your specific development — the timeline, the tools, the campaign, and the projected outcome. No obligation. No generic pitch deck. Just a real plan for your real project.</p>
                <a href="contact.php?ref=concierge" class="btn btn-primary px-5 py-3 rounded" style="font-weight:700;font-size:15px;">Book Your Free Listing Presentation</a>
            </div>
        </div>

    </div>
</section>


<!-- WHAT YOU ARE REALLY GETTING -->
<section style="padding:80px 0;background:#002446;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">The Real Value</p>
                <h2 style="font-size:34px;font-weight:700;color:#fff;margin-bottom:16px;">What This Would Cost You<br>If You Hired It All Separately.</h2>
                <p style="font-size:16px;color:rgba(255,255,255,.75);max-width:660px;margin:0 auto;line-height:1.8;">Every service in the Concierge package is something large developers pay independently — through marketing agencies, production companies, and listing teams. Here is what the market charges for what we include as standard.</p>
            </div>
        </div>

        <div class="row g-3 mt-5 justify-content-center">

            <div class="col-lg-10">
                <div style="background:rgba(255,255,255,.04);border:1px solid rgba(201,168,76,.15);border-radius:16px;overflow:hidden;">

                    <!-- Header row -->
                    <div style="display:grid;grid-template-columns:1fr 160px 160px;background:rgba(201,168,76,.12);padding:14px 28px;border-bottom:1px solid rgba(201,168,76,.2);">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;">Service</div>
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;text-align:center;">Market Rate</div>
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#c9a84c;text-align:center;">Concierge Cost</div>
                    </div>

                    <?php
                    $rows = [
                        ["3D Renderings &amp; Virtual Tours", "$3,500 – $8,000", "Included"],
                        ["Professional Floorplan Design", "$800 – $2,000", "Included"],
                        ["Dedicated Project Website", "$2,500 – $6,000", "Included"],
                        ["Targeted Digital Ad Campaign", "$1,500 – $5,000/mo", "Included"],
                        ["Pre-Market Agent Campaign", "$2,000 – $4,000", "Included"],
                        ["Professional Photography &amp; Video", "$1,200 – $3,500", "Included"],
                        ["Staging Consultation", "$500 – $1,500", "Included"],
                        ["Full Marketing Strategy", "$5,000 – $15,000", "Included"],
                        ["Negotiation &amp; Closing Support", "$3,000 – $8,000", "Included"],
                        ["W.I.N Portal + Unlimited Reports", "$19.99/report", "Included Free"],
                    ];
                    foreach ($rows as $i => $row):
                        $bg = $i % 2 === 0 ? 'rgba(255,255,255,.03)' : 'transparent';
                    ?>
                    <div style="display:grid;grid-template-columns:1fr 160px 160px;padding:14px 28px;background:<?php echo $bg; ?>;border-bottom:1px solid rgba(255,255,255,.05);">
                        <div style="font-size:14px;color:rgba(255,255,255,.85);font-weight:600;"><?php echo $row[0]; ?></div>
                        <div style="font-size:13px;color:rgba(255,255,255,.45);text-align:center;text-decoration:line-through;"><?php echo $row[1]; ?></div>
                        <div style="font-size:13px;color:#c9a84c;font-weight:700;text-align:center;"><?php echo $row[2]; ?></div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Total row -->
                    <div style="display:grid;grid-template-columns:1fr 160px 160px;padding:18px 28px;background:rgba(201,168,76,.1);border-top:1px solid rgba(201,168,76,.3);">
                        <div style="font-size:15px;color:#fff;font-weight:800;">Total Estimated Value</div>
                        <div style="font-size:15px;color:rgba(255,255,255,.5);text-align:center;text-decoration:line-through;">$20,000 – $50,000+</div>
                        <div style="font-size:15px;color:#c9a84c;font-weight:900;text-align:center;">Standard Commission</div>
                    </div>

                </div>
            </div>

        </div>

        <div class="row mt-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <p style="font-size:15px;color:rgba(255,255,255,.6);line-height:1.8;">Every one of these services is included in Wynston Concierge — coordinated, managed, and delivered by Tam's team from day one. You pay a standard listing commission at the time of sale. Nothing before. Nothing extra.</p>
                <a href="contact.php?ref=concierge" class="btn btn-lg mt-4" style="background:#c9a84c;color:#002446;font-weight:700;padding:14px 40px;border-radius:8px;">Book Your Private Presentation</a>
            </div>
        </div>

    </div>
</section>

<!-- TWO PATHS -->
<section style="padding:80px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Two Ways to Work With Wynston</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:16px;">Start Free. Or Go All In.</h2>
                <p style="font-size:16px;color:#666;max-width:640px;margin:0 auto;">Every developer starts with a free listing — zero cost, zero commitment, immediate visibility to active buyers. When you're ready to launch like a major developer, Concierge activates the full agency. Standard commission. No extra fees. No surprises. Ever.</p>
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
                    <h3 style="font-size:24px;font-weight:800;color:#002446;margin-bottom:12px;">Post Your Project. Start Building Demand Today.</h3>
                    <p style="font-size:14px;color:#666;line-height:1.7;margin-bottom:24px;">Your permit is already on our radar. Claim your project page, upload whatever you have — renderings, a floorplan, progress photos, or just an address — and your project goes live immediately on Wynston's new construction tracker. Buyers searching your neighbourhood start discovering you today. No contract. No commission. No catch.</p>
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
                            Project page with your contact details
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#444;">
                            <i class="fa-solid fa-check" style="color:#16a34a;font-size:12px;width:16px;"></i>
                            No contract required with option to purchase Creative Package 
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
                    <h3 style="font-size:24px;font-weight:800;color:#fff;margin-bottom:12px;">Your Own Marketing Department — Activated on Day One.</h3>
                    <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:24px;">From land acquisition through to final close, we function as your dedicated in-house marketing department. Strategy, creative, digital, agent network, photography, staging, negotiation — all of it. Coordinated. Executed. Delivered. What Rennies charges large developers $50,000–$150,000+ to provide, Concierge clients get on standard commission. Nothing more.</p>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:28px;">
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            18-month marketing head start — from land acquisition
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Photorealistic 3D renderings and cinematic virtual tours
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Professional floorplan design
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Dedicated project website — 24/7 digital sales centre
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Exclusive pre-market launch to 5,000+ agent network
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Targeted digital ad campaigns — Instagram, Facebook, email
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Professional photography, drone footage &amp; video at completion
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Staging strategy and completion presentation support
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Full negotiation, transparent reporting &amp; closing support
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Exclusive listing agreement — Tam's team represents your sale
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85);">
                            <i class="fa-solid fa-check" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            W.I.N portal access + unlimited branded reports — included free
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#c9a84c;font-weight:700;margin-top:4px;">
                            <i class="fa-solid fa-star" style="color:#c9a84c;font-size:12px;width:16px;"></i>
                            Standard commission only — no agency fees, no retainers, ever
                        </div>
                    </div>
                    <a href="contact.php?ref=concierge" class="btn full-width" style="background:#c9a84c;color:#002446;font-weight:700;padding:12px;border-radius:8px;">Book a Private Presentation</a>
                </div>
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
                <h2 style="font-size:34px;font-weight:700;color:#fff;margin-bottom:16px;">No Other Agent in BC Can Give You.</h2>
                <p style="font-size:16px;color:rgba(255,255,255,.75);line-height:1.8;max-width:680px;margin:0 auto;">Every Concierge client gets full access to the Wynston Intelligence Navigator  — BC's only platform combining live zoning feasibility, real build-cost modelling, and pre-registry market velocity data. The same intelligence institutional buyers pay consultants to produce, included in your partnership at no extra cost.</p>
            </div>
        </div>
        <div class="row g-4 mt-5">
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-map-location-dot" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Live Feasibility Map</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Every R1-1 zoned lot in Vancouver scored for duplex, triplex and 6 units or more eligibility in real time. Know what's buildable on your next project before you make an offer.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-calculator" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Instant Pro Forma</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Build cost, DCLs, permit fees, peat zone contingencies, and projected profit — calculated automatically estimated the moment you click a lot. No spreadsheet, no consultant.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-cube" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">3D Architectural Visualizer</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Permit-ready, architecturally styled 3D models on any lot — scaled to actual dimensions, Vancouver setbacks applied. Based on BC Provincial Standardized Designs. Nothing like it exists anywhere else in Canada.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;height:100%;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <i class="fa-solid fa-chart-line" style="color:#c9a84c;font-size:18px;"></i>
                    </div>
                    <h5 style="font-size:15px;font-weight:800;color:#fff;margin-bottom:8px;">Real Market Velocity</h5>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7;margin:0;">Pre-registry market movement by neighbourhood — not just sold prices. Month-over-month trends benchmarked against CMHC data. Know where the market is heading before your next acquisition.</p>
                </div>
            </div>
        </div>

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
                        <p style="font-size:14px;color:rgba(255,255,255,.7);margin:0;">The Wynston Report is an investor-grade feasibility PDF — covering pro forma, constraint flags, market velocity, and full site analysis. Available to all portal users for $19.99 each. Concierge clients generate unlimited reports, printed under your own brand, at no charge. Share them with partners, lenders, and co-investors with confidence.</p>
                    </div>
                    <div style="flex:0 0 auto;">
                        <a href="contact.php?ref=concierge" class="btn" style="background:#c9a84c;color:#002446;font-weight:700;padding:12px 24px;border-radius:8px;white-space:nowrap;">Book a Presentation</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 justify-content-center">
            <div class="col-lg-8 text-center">
                <a href="portal.php" style="font-size:14px;color:#c9a84c;text-decoration:underline;">Explore the Wynston Portal &rarr;</a>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section style="padding:80px 0;background:#f9f6f0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 text-center">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">How It Works</p>
                <h2 style="font-size:32px;font-weight:600;color:#002446;margin-bottom:16px;">Your Marketing Department Goes to Work<br>Before Your Crew Does.</h2>
                <p style="font-size:16px;color:#666;max-width:640px;margin:0 auto;">The earlier Wynston Concierge activates on your project, the more powerful the outcome. Here's why every stage matters.</p>
            </div>
        </div>
        <div class="row g-4 mt-4 justify-content-center">
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">1</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Day One — Your Project Goes Live</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">The moment you're ready, your project page launches on Wynston's new construction tracker. Renderings, floorplans, neighbourhood data — all live. Buyers actively searching your area find you from day one, not day of completion.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">2</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">While You Build — We Build Your Audience</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">Targeted digital campaigns, agent network outreach, and platform visibility run continuously while your project is under construction. Every month that passes, your buyer pool grows warmer. By the time you're ready to sell, the market already knows your address.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">3</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Completion Approach — Full Campaign Launches</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">As your occupancy permit approaches, the full Concierge machine activates — professional photography, final video, staging, MLS strategy, and an exclusive agent pre-market blitz. Your project hits the market with momentum, not hope.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div style="padding:24px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#002446;color:#c9a84c;font-size:20px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">4</div>
                    <h5 style="font-size:16px;font-weight:800;color:#002446;margin-bottom:10px;">Sold — At Full Price. Not a Discount.</h5>
                    <p style="font-size:13px;color:#666;line-height:1.7;margin:0;">Developers who arrive at completion with no pre-built demand are forced to negotiate. Concierge clients arrive with qualified buyers already waiting — which means stronger offers, shorter closing timelines, and full market value on every unit. That difference compounds across every project you build.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BRAND STORY -->
<section style="padding:80px 0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 col-md-6">
                <img src="<?php echo $static_url; ?>/img/vec-2.png" class="img-fluid" alt="Wynston Vision" />
            </div>
            <div class="col-lg-6 col-md-6">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:12px;">Why We Built This</p>
                <h2 style="font-size:30px;font-weight:600;color:#002446;margin-bottom:20px;">The Gap Was Always About Marketing.<br>We Closed It.</h2>
                <p style="font-size:16px;color:#555;line-height:1.8;">When big real estate  project launches, it arrives with months of buyer engagement already banked. The waitlist is built. The agent network is primed. The brand is established. That is not because large developers build better homes — it is because they have a dedicated marketing department working on their project from the day the land is acquired.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">Boutique developers in Metro Vancouver have always had to choose between paying agency retainers they can't afford or going to market cold with nothing but an MLS listing. Wynston Concierge eliminates that choice. The full agency infrastructure — strategy, creative, digital, network, photography, negotiation — is now built into your listing agreement at no additional cost.</p>
                <p style="font-size:16px;color:#555;line-height:1.8;">Whether you are building 4 units in East Van or 20 townhomes in Burnaby, the launch quality should not be determined by the size of your marketing budget. At Wynston, it isn't.</p>
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
                <h2 class="text-white">Every month you wait is a month of buyer demand you're not building.</h2>
                <p class="text-white mt-3 mb-4" style="opacity:.85;">The developers winning in this market started their marketing before their permits were approved. Book a private presentation and we'll show you exactly what that looks like for your project — with no obligation and no generic pitch.</p>
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