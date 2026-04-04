<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

// ── Stats ─────────────────────────────────────────────────────────────
$total       = (int)$pdo->query("SELECT COUNT(*) FROM multi_2025")->fetchColumn();
$hoods       = (int)$pdo->query("SELECT COUNT(DISTINCT neighborhood) FROM multi_2025 WHERE neighborhood != ''")->fetchColumn();
$concierge   = (int)$pdo->query("SELECT COUNT(*) FROM multi_2025 WHERE tier='concierge' OR is_paid=1")->fetchColumn();
$with_photos = (int)$pdo->query("SELECT COUNT(*) FROM multi_2025 WHERE img1 != '' AND img1 IS NOT NULL")->fetchColumn();
$current_yr  = date('Y');

// Completion breakdown
$by_year = $pdo->query("
    SELECT est_completion AS yr, COUNT(*) AS cnt
    FROM multi_2025
    WHERE est_completion IS NOT NULL AND est_completion != ''
    GROUP BY est_completion ORDER BY est_completion ASC
")->fetchAll(PDO::FETCH_ASSOC);

// By type
$by_type = $pdo->query("
    SELECT property_type, COUNT(*) AS cnt
    FROM multi_2025
    WHERE property_type IS NOT NULL AND property_type != ''
    GROUP BY property_type ORDER BY cnt DESC
")->fetchAll(PDO::FETCH_ASSOC);

// By neighbourhood top 10
$by_hood = $pdo->query("
    SELECT neighborhood, COUNT(*) AS cnt
    FROM multi_2025
    WHERE neighborhood != '' AND neighborhood IS NOT NULL
    GROUP BY neighborhood ORDER BY cnt DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$max_yr   = !empty($by_year) ? max(array_column($by_year,  'cnt')) : 1;
$max_type = !empty($by_type) ? max(array_column($by_type,  'cnt')) : 1;
$max_hood = !empty($by_hood) ? max(array_column($by_hood,  'cnt')) : 1;

ob_start();
include "$base_dir/navbar2.php";
$navlink_content = ob_get_clean();
$page  = 'nav2';
$fpage = 'foot';
ob_start();
?>

<!-- Hero -->
<div class="image-cover page-title" style="background:#002446 url(<?= $static_url ?>/img/new-banner.jpg) no-repeat center/cover;" data-overlay="7">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;padding:6px 14px;border-radius:20px;margin-bottom:20px;">
                    <span class="gap-ping"></span> Live Tracker
                </div>
                <h1 class="ipt-title" style="color:#fff;">The Invisibility<br><span style="color:#c9a84c;">Gap Tracker</span></h1>
                <p style="color:rgba(255,255,255,.65);font-size:15px;margin-top:12px;max-width:520px;line-height:1.8;">
                    Between permit approval and MLS listing, every development is invisible to most buyers.
                    Wynston tracks <strong style="color:#fff;"><?= number_format($total) ?> projects</strong> currently in this gap — across <?= $hoods ?> Metro Vancouver neighbourhoods.
                </p>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-end gap-3">
                <?php foreach ([
                    [number_format($total), 'Projects Tracked'],
                    [$hoods,                'Neighbourhoods'],
                    [$concierge,            'Concierge Listed'],
                ] as [$v,$l]): ?>
                <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:18px 22px;text-align:center;">
                    <div style="font-size:30px;font-weight:900;color:#fff;"><?= $v ?></div>
                    <div style="font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.6px;margin-top:4px;"><?= $l ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- What is the Gap -->
<section style="padding:60px 0;background:#fff;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:10px;">The Concept</div>
                <h2 style="font-size:clamp(24px,3vw,34px);font-weight:800;color:#002446;margin-bottom:18px;line-height:1.2;">What is the<br>Invisibility Gap?</h2>
                <p style="font-size:15px;color:#555;line-height:1.85;margin-bottom:16px;">The Invisibility Gap is the window of time between when a development permit is approved and when a project first appears on MLS®. This period typically spans <strong>18 to 36 months</strong> — and during this entire time, most buyers have no idea the project exists.</p>
                <p style="font-size:15px;color:#555;line-height:1.85;margin-bottom:24px;">Large developers use pre-sale campaigns to turn this gap into an advantage. Small and mid-size developers traditionally can't. Wynston changes that — by surfacing these projects the moment they enter the gap.</p>
                <a href="half-map.php" class="btn btn-primary rounded px-5">Browse Gap Projects</a>
            </div>
            <div class="col-lg-6">
                <!-- Timeline diagram -->
                <div style="background:#f9f6f0;border-radius:16px;padding:32px;">
                    <div style="font-size:12px;font-weight:700;color:#002446;margin-bottom:20px;text-transform:uppercase;letter-spacing:.5px;">Typical Development Timeline</div>
                    <?php
                    $timeline = [
                        ['label'=>'Land Acquired',       'sub'=>'Developer purchases site',                        'color'=>'#e8e4dd', 'text'=>'#888',    'gap'=>false],
                        ['label'=>'Permit Applied',      'sub'=>'Municipal application submitted',                  'color'=>'#dbeafe', 'text'=>'#1e40af', 'gap'=>false],
                        ['label'=>'← Gap Begins',        'sub'=>'Permit approved. Project invisible to buyers.',    'color'=>'#002446', 'text'=>'#fff',    'gap'=>true],
                        ['label'=>'Construction',        'sub'=>'18–36 months of building activity',                'color'=>'#002446', 'text'=>'rgba(255,255,255,.5)', 'gap'=>true],
                        ['label'=>'Wynston Tracks Here ✓','sub'=>'Real-time visibility throughout the gap',        'color'=>'#c9a84c', 'text'=>'#002446', 'gap'=>true],
                        ['label'=>'Gap Ends → MLS®',     'sub'=>'Occupancy permit. Most buyers see it here.',       'color'=>'#f3f4f6', 'text'=>'#555',    'gap'=>false],
                    ];
                    foreach ($timeline as $t): ?>
                    <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:10px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:<?= $t['color'] ?>;flex-shrink:0;margin-top:5px;border:<?= $t['gap']?'2px solid #c9a84c':'2px solid #e8e4dd' ?>;"></div>
                        <div style="flex:1;background:<?= $t['gap']?'rgba(0,36,70,.04)':'transparent' ?>;border-radius:8px;padding:<?= $t['gap']?'8px 12px':'4px 0' ?>;">
                            <div style="font-size:13px;font-weight:700;color:<?= $t['gap']?'#002446':'#555' ?>;"><?= $t['label'] ?></div>
                            <div style="font-size:11px;color:#888;margin-top:2px;"><?= $t['sub'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data Charts -->
<section style="padding:60px 0;background:#f9f6f0;">
    <div class="container">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:8px;">Breakdown</div>
        <h2 style="font-size:clamp(22px,3vw,32px);font-weight:800;color:#002446;margin-bottom:36px;">What's in the Gap</h2>

        <div class="row g-4">

            <!-- By completion year -->
            <div class="col-lg-4">
                <div style="background:#fff;border:1px solid #e8e4dd;border-radius:14px;padding:24px;height:100%;">
                    <div style="font-size:13px;font-weight:800;color:#002446;margin-bottom:18px;">By Completion Year</div>
                    <?php foreach ($by_year as $row): ?>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="font-size:12px;font-weight:700;color:#002446;width:42px;flex-shrink:0;"><?= htmlspecialchars($row['yr']) ?></div>
                        <div style="flex:1;height:8px;background:#f0f4ff;border-radius:20px;overflow:hidden;">
                            <div style="width:<?= round(($row['cnt']/$max_yr)*100) ?>%;height:100%;background:<?= $row['yr']==$current_yr?'#c9a84c':'linear-gradient(90deg,#002446,#0065ff)' ?>;border-radius:20px;"></div>
                        </div>
                        <div style="font-size:12px;color:#888;width:28px;text-align:right;"><?= $row['cnt'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- By property type -->
            <div class="col-lg-4">
                <div style="background:#fff;border:1px solid #e8e4dd;border-radius:14px;padding:24px;height:100%;">
                    <div style="font-size:13px;font-weight:800;color:#002446;margin-bottom:18px;">By Property Type</div>
                    <?php foreach ($by_type as $row): ?>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="font-size:11px;color:#555;width:90px;flex-shrink:0;line-height:1.3;"><?= htmlspecialchars($row['property_type']) ?></div>
                        <div style="flex:1;height:8px;background:#f0f4ff;border-radius:20px;overflow:hidden;">
                            <div style="width:<?= round(($row['cnt']/$max_type)*100) ?>%;height:100%;background:linear-gradient(90deg,#002446,#0065ff);border-radius:20px;"></div>
                        </div>
                        <div style="font-size:12px;color:#888;width:28px;text-align:right;"><?= $row['cnt'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top neighbourhoods -->
            <div class="col-lg-4">
                <div style="background:#fff;border:1px solid #e8e4dd;border-radius:14px;padding:24px;height:100%;">
                    <div style="font-size:13px;font-weight:800;color:#002446;margin-bottom:18px;">Top 10 Neighbourhoods</div>
                    <?php foreach ($by_hood as $row): ?>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <a href="neighbourhood.php?name=<?= urlencode($row['neighborhood']) ?>" style="font-size:11px;color:#002446;width:110px;flex-shrink:0;text-decoration:none;line-height:1.3;" onmouseover="this.style.color='#0065ff'" onmouseout="this.style.color='#002446'"><?= htmlspecialchars($row['neighborhood']) ?></a>
                        <div style="flex:1;height:8px;background:#f0f4ff;border-radius:20px;overflow:hidden;">
                            <div style="width:<?= round(($row['cnt']/$max_hood)*100) ?>%;height:100%;background:linear-gradient(90deg,#c9a84c,#e8d84b);border-radius:20px;"></div>
                        </div>
                        <div style="font-size:12px;color:#888;width:24px;text-align:right;"><?= $row['cnt'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section style="padding:60px 0;background:linear-gradient(135deg,#001428,#002446);">
    <div class="container text-center">
        <h2 style="font-size:clamp(24px,3vw,36px);font-weight:800;color:#fff;margin-bottom:14px;">Don't Wait for MLS.<br><span style="color:#c9a84c;">See What's Coming Now.</span></h2>
        <p style="color:rgba(255,255,255,.55);font-size:15px;max-width:480px;margin:0 auto 28px;line-height:1.8;">Browse all <?= number_format($total) ?> projects currently in the Invisibility Gap across Metro Vancouver.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="half-map.php" class="btn btn-warning rounded px-5" style="font-weight:700;">Browse All Gap Projects</a>
            <a href="neighbourhoods.php" class="btn btn-outline-light rounded px-5">By Neighbourhood</a>
        </div>
    </div>
</section>

<style>
.gap-ping {
    width:8px;height:8px;border-radius:50%;background:#c9a84c;display:inline-block;
    box-shadow:0 0 0 0 rgba(201,168,76,.4);
    animation:pulse-ping 2s ease infinite;
}
@keyframes pulse-ping {
    0%   { box-shadow:0 0 0 0 rgba(201,168,76,.4); }
    70%  { box-shadow:0 0 0 8px rgba(201,168,76,0); }
    100% { box-shadow:0 0 0 0 rgba(201,168,76,0); }
}
</style>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>
