<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';
require_once "$base_dir/db.php";

// Pull all developers with their stats
$dev_rows = $pdo->query("
    SELECT
        developer_name,
        COUNT(*)                                                            AS total,
        MIN(est_completion)                                                 AS earliest,
        MAX(est_completion)                                                 AS latest,
        SUM(CASE WHEN tier='concierge' OR is_paid=1 THEN 1 ELSE 0 END)    AS concierge_count,
        GROUP_CONCAT(DISTINCT property_type ORDER BY property_type SEPARATOR ', ') AS types,
        GROUP_CONCAT(DISTINCT neighborhood ORDER BY neighborhood SEPARATOR ', ')   AS hoods,
        MAX(img1)                                                           AS sample_img,
        MAX(developer_bio)                                                  AS bio,
        MAX(developer_logo)                                                  AS logo,
        MAX(developer_website)                                              AS website
    FROM multi_2025
    WHERE developer_name IS NOT NULL AND developer_name != ''
    GROUP BY developer_name
    ORDER BY concierge_count DESC, total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$total_devs     = count($dev_rows);
$total_projects = array_sum(array_column($dev_rows, 'total'));
$total_concierge= array_sum(array_column($dev_rows, 'concierge_count'));

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
                <div style="display:inline-block;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;padding:6px 14px;border-radius:20px;margin-bottom:20px;">
                    Developer Directory
                </div>
                <h1 class="ipt-title" style="color:#fff;">Vancouver's<br><span style="color:#c9a84c;">Active Developers</span></h1>
                <p style="color:rgba(255,255,255,.65);font-size:15px;margin-top:12px;max-width:520px;line-height:1.8;">
                    Wynston tracks <strong style="color:#fff;"><?= $total_devs ?> active developers</strong> across Metro Vancouver — from boutique multiplex builders to established mid-size firms. All data is updated in real time from permit records and developer submissions.
                </p>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-end gap-3">
                <?php foreach ([
                    [$total_devs,     'Active Developers'],
                    [$total_projects, 'Projects Tracked'],
                    [$total_concierge,'Concierge Listed'],
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

<!-- Developer grid -->
<section style="padding:48px 0 60px;background:#f9f6f0;">
<div class="container">

    <?php if (empty($dev_rows)): ?>
    <div style="text-align:center;padding:60px 20px;">
        <i class="fas fa-hard-hat" style="font-size:48px;color:#ddd;display:block;margin-bottom:16px;"></i>
        <h3 style="color:#002446;margin-bottom:10px;">No developer data yet</h3>
        <p style="color:#888;">Developer names will appear here as listings are added to the platform.</p>
        <a href="create-account.php" class="btn btn-primary rounded px-5 mt-3">Submit a Listing</a>
    </div>
    <?php else: ?>

    <div class="row g-4">
    <?php foreach ($dev_rows as $d):
        $is_concierge = $d['concierge_count'] > 0;
        $hoods_arr    = array_slice(array_filter(array_map('trim', explode(',', $d['hoods'] ?? ''))), 0, 3);
        $yr_range     = ($d['earliest'] && $d['latest'] && $d['earliest'] != $d['latest'])
                            ? $d['earliest'].'–'.$d['latest']
                            : ($d['earliest'] ?: 'T.B.D.');
    ?>
    <div class="col-lg-4 col-md-6">
        <div class="dev-card <?= $is_concierge ? 'dev-card-featured' : '' ?>">

            <!-- Card header -->
            <div class="dev-card-header">
                <?php if (!empty($d['logo'])): ?>
                    <img src="<?= htmlspecialchars($d['logo']) ?>" alt="<?= htmlspecialchars($d['developer_name']) ?>" class="dev-logo">
                <?php else: ?>
                    <div class="dev-logo-placeholder">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                <?php endif; ?>
                <div class="dev-header-info">
                    <div class="dev-name"><?= htmlspecialchars($d['developer_name']) ?></div>
                    <?php if ($is_concierge): ?>
                    <span class="dev-concierge-badge">⭐ Concierge Partner</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bio -->
            <?php if (!empty($d['bio'])): ?>
            <p class="dev-bio"><?= htmlspecialchars(mb_substr($d['bio'], 0, 120)) ?>…</p>
            <?php else: ?>
            <p class="dev-bio" style="color:#ccc;font-style:italic;">Active developer in Metro Vancouver.</p>
            <?php endif; ?>

            <!-- Stats row -->
            <div class="dev-stats">
                <div class="dev-stat">
                    <span class="dev-stat-n"><?= (int)$d['total'] ?></span>
                    <span class="dev-stat-l">Project<?= $d['total']!=1?'s':'' ?></span>
                </div>
                <div class="dev-stat">
                    <span class="dev-stat-n"><?= $yr_range ?></span>
                    <span class="dev-stat-l">Completion<?= strpos($yr_range,'–')!==false?'s':'' ?></span>
                </div>
                <?php if ($d['concierge_count'] > 0): ?>
                <div class="dev-stat">
                    <span class="dev-stat-n" style="color:#c9a84c;"><?= (int)$d['concierge_count'] ?></span>
                    <span class="dev-stat-l">Concierge</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Types & areas -->
            <div class="dev-tags">
                <?php foreach (array_filter(array_map('trim', explode(',', $d['types'] ?? ''))) as $t): ?>
                <span class="dev-tag"><?= htmlspecialchars($t) ?></span>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($hoods_arr)): ?>
            <div class="dev-hoods">
                <i class="fas fa-map-marker-alt" style="color:#c9a84c;font-size:10px;margin-right:4px;"></i>
                <?= htmlspecialchars(implode(', ', $hoods_arr)) ?>
                <?php if (count(array_filter(array_map('trim', explode(',', $d['hoods'] ?? '')))) > 3): ?>
                &amp; more
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="dev-card-footer">
                <a href="half-map.php?developer=<?= urlencode($d['developer_name']) ?>" class="dev-btn-projects">
                    View <?= (int)$d['total'] ?> Project<?= $d['total']!=1?'s':'' ?> <i class="fas fa-arrow-right"></i>
                </a>
                <?php if (!empty($d['website'])): ?>
                <a href="<?= htmlspecialchars($d['website']) ?>" target="_blank" rel="noopener" class="dev-btn-web">
                    <i class="fas fa-globe"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <?php endif; ?>

</div>
</section>

<!-- CTA for developers -->
<section style="padding:60px 0;background:#fff;border-top:1px solid #f0ece6;">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;">
            <div>
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:#c9a84c;margin-bottom:8px;">Are You a Developer?</div>
                <h2 style="font-size:clamp(20px,2.5vw,28px);font-weight:800;color:#002446;margin-bottom:10px;">Get Your Projects in Front of Buyers — Free</h2>
                <p style="font-size:14px;color:#888;max-width:500px;line-height:1.75;">List your developments on Wynston and step out of the Invisibility Gap. Free listing, no commission obligation, developer portal included.</p>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="create-account.php" class="btn btn-primary rounded px-5">List for Free</a>
                <a href="https://tamwynn.ca" target="_blank" rel="noopener" class="btn btn-outline-primary rounded px-5">Concierge Service →</a>
            </div>
        </div>
    </div>
</section>

<style>
.dev-card {
    background: #fff; border: 1px solid #e8e4dd; border-radius: 16px;
    padding: 24px; height: 100%; display: flex; flex-direction: column;
    gap: 14px; transition: box-shadow .2s, transform .2s;
}
.dev-card:hover { box-shadow: 0 10px 36px rgba(0,36,70,.1); transform: translateY(-3px); }
.dev-card-featured { border-color: #c9a84c; box-shadow: 0 2px 14px rgba(201,168,76,.15); }

.dev-card-header { display: flex; align-items: center; gap: 14px; }
.dev-logo { width: 52px; height: 52px; object-fit: contain; border-radius: 8px; border: 1px solid #e8e4dd; flex-shrink: 0; }
.dev-logo-placeholder {
    width: 52px; height: 52px; border-radius: 8px; flex-shrink: 0;
    background: linear-gradient(135deg,#002446,#003a70);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.4); font-size: 20px;
}
.dev-name { font-size: 15px; font-weight: 800; color: #002446; line-height: 1.3; }
.dev-concierge-badge {
    display: inline-block; margin-top: 4px;
    background: linear-gradient(135deg,#c9a84c,#e8d84b);
    color: #002446; font-size: 9px; font-weight: 800;
    padding: 2px 8px; border-radius: 4px;
}
.dev-bio { font-size: 12px; color: #888; line-height: 1.7; margin: 0; }

.dev-stats { display: flex; gap: 16px; flex-wrap: wrap; }
.dev-stat { display: flex; flex-direction: column; gap: 2px; }
.dev-stat-n { font-size: 16px; font-weight: 800; color: #002446; }
.dev-stat-l { font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: .4px; }

.dev-tags { display: flex; gap: 5px; flex-wrap: wrap; }
.dev-tag {
    font-size: 10px; font-weight: 700; color: #555;
    background: #f5f7ff; padding: 3px 10px; border-radius: 20px;
    border: 1px solid #e8e4dd; text-transform: capitalize;
}
.dev-hoods { font-size: 11px; color: #888; line-height: 1.5; }

.dev-card-footer {
    display: flex; align-items: center; gap: 8px;
    margin-top: auto; padding-top: 14px;
    border-top: 1px solid #f0ece6;
}
.dev-btn-projects {
    flex: 1; text-align: center;
    background: #002446; color: #fff !important;
    font-size: 12px; font-weight: 700; padding: 8px 14px;
    border-radius: 8px; text-decoration: none;
    transition: background .2s; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.dev-btn-projects:hover { background: #003a70; text-decoration: none; }
.dev-btn-web {
    width: 36px; height: 36px; flex-shrink: 0;
    border: 1.5px solid #e8e4dd; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #888 !important; text-decoration: none; font-size: 14px;
    transition: border-color .2s, color .2s;
}
.dev-btn-web:hover { border-color: #002446; color: #002446 !important; }
</style>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>
