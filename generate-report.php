<?php
/**
 * Wynston W.I.N — Report Generator (HTML + Browser Print)
 * GET: pid, prepared_for, path (strata|rental)
 * Returns: Full HTML page — builder clicks "Save as PDF" to print
 */

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_domain'   => 'wynston.ca',
]);

header('Cache-Control: no-store');

// ── Auth ─────────────────────────────────────────────────────────────────────
if (empty($_SESSION['dev_id'])) {
    header('Location: /log-in.php');
    exit;
}

// ── DB ───────────────────────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4",
        'u990588858_Multiplex', 'Concac1979$', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) { die('Database error.'); }

// ── PHPMailer ────────────────────────────────────────────────────────────────
$mailer_loaded = false;
foreach ([__DIR__.'/PHPMailer/src/PHPMailer.php', __DIR__.'/vendor/phpmailer/phpmailer/src/PHPMailer.php'] as $mp) {
    if (file_exists($mp)) {
        require_once $mp;
        require_once dirname($mp).'/SMTP.php';
        require_once dirname($mp).'/Exception.php';
        $mailer_loaded = true; break;
    }
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

// ── Slug map ─────────────────────────────────────────────────────────────────
require_once __DIR__.'/includes/slug_map.php';

// ── Input ────────────────────────────────────────────────────────────────────
$pid            = trim($_GET['pid'] ?? $_POST['pid'] ?? '');
$prepared_for   = trim($_GET['prepared_for'] ?? $_POST['prepared_for'] ?? 'Valued Client');
$pro_forma_path = in_array($_GET['path'] ?? 'strata', ['strata','rental']) ? ($_GET['path'] ?? 'strata') : 'strata';
if (empty($pid)) die('PID required.');
$dev_id = (int)$_SESSION['dev_id'];

// ── Schema fixes ──────────────────────────────────────────────────────────────
try { $pdo->exec("ALTER TABLE developers ADD COLUMN IF NOT EXISTS subscription_tier ENUM('free','pro','white_label') DEFAULT 'free', ADD COLUMN IF NOT EXISTS report_logo_path VARCHAR(500) DEFAULT NULL, ADD COLUMN IF NOT EXISTS report_bio TEXT DEFAULT NULL, ADD COLUMN IF NOT EXISTS report_title VARCHAR(100) DEFAULT NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE pdf_log ADD COLUMN IF NOT EXISTS report_id VARCHAR(20) DEFAULT NULL"); } catch(PDOException $e){}

// ── Rate limit ────────────────────────────────────────────────────────────────
$r = $pdo->prepare("SELECT COUNT(*) FROM pdf_log WHERE developer_id=? AND DATE(generated_at)=CURDATE()");
$r->execute([$dev_id]);
if ((int)$r->fetchColumn() >= 5) die('Daily report limit reached (5/day). Contact Wynston for higher limits.');

// ── Developer ─────────────────────────────────────────────────────────────────
$ds = $pdo->prepare("SELECT id,email,full_name,subscription_tier,report_logo_path,report_bio,report_title FROM developers WHERE id=?");
$ds->execute([$dev_id]); $developer = $ds->fetch();
if (!$developer) die('Account not found.');
$agent_name  = !empty($developer['full_name']) ? $developer['full_name'] : 'Tam Nguyen';
$agent_bio   = $developer['report_bio']   ?? 'Wynston specializes in missing middle multiplex development intelligence for Metro Vancouver.';
$agent_title = $developer['report_title'] ?? 'Realtor® · Wynston Concierge Real Estate';
$sub_tier    = $developer['subscription_tier'] ?? 'free';

// ── Lot ───────────────────────────────────────────────────────────────────────
$ls = $pdo->prepare("SELECT p.*, cf.heritage_category, cf.peat_zone, cf.covenant_present, cf.covenant_types, cf.easement_present, cf.easement_types FROM plex_properties p LEFT JOIN constraint_flags cf ON cf.pid=p.pid WHERE p.pid=?");
$ls->execute([$pid]); $lot = $ls->fetch();
if (!$lot) die('Lot not found.');

$address          = $lot['address'] ?? 'Address unavailable';
$pid_fmt          = preg_replace('/(\d{3})(\d{3})(\d{3})/', '$1-$2-$3', preg_replace('/\D/','',$pid));
$lat              = (float)($lot['lat'] ?? 0);
$lng              = (float)($lot['lng'] ?? 0);
$lot_width_m      = (float)($lot['lot_width_m']  ?? 0);
$lot_depth_m      = (float)($lot['lot_depth_m']  ?? 0);
$lot_area_sqm     = (float)($lot['lot_area_sqm'] ?? 0);
$lot_width_ft     = round($lot_width_m/0.3048, 1);
$lot_depth_ft     = round($lot_depth_m/0.3048, 1);
$lot_area_sqft    = round($lot_area_sqm*10.7639);
$lane_access      = (bool)($lot['lane_access']      ?? false);
$transit_prox     = (bool)($lot['transit_proximate'] ?? false);
$has_permit       = (bool)($lot['has_active_permit'] ?? false);
$heritage         = $lot['heritage_category'] ?? 'none';
$peat_zone        = (bool)($lot['peat_zone']         ?? false);
$covenant_present = (bool)($lot['covenant_present']  ?? false);
$covenant_types   = $lot['covenant_types'] ?? '';
$assessed_land    = (int)($lot['assessed_land_value'] ?? 0);
if ($assessed_land===0 && $lot_area_sqm>0) $assessed_land = (int)($lot_area_sqm*10.7639*850);
$assessment_year  = $lot['assessment_year'] ?? date('Y');
$nb_slug          = wynston_resolve_slug($lot['neighbourhood_slug'] ?? '');

$nb_map = ['renfrew-collingwood'=>'Renfrew-Collingwood','mount-pleasant'=>'Mount Pleasant','hastings-sunrise'=>'Hastings-Sunrise','kensington-cedar-cottage'=>'Kensington-Cedar-Cottage','knight'=>'Knight','grandview-woodland'=>'Grandview-Woodland','victoria-fraserview'=>'Victoria-Fraserview','killarney'=>'Killarney','fraser-ve'=>'Fraser','south-marine'=>'South Marine','main'=>'Main','fairview-vw'=>'Fairview','kerrisdale'=>'Kerrisdale','marpole'=>'Marpole','oakridge'=>'Oakridge','south-cambie'=>'South Cambie','shaughnessy'=>'Shaughnessy','riley-park'=>'Riley Park','kitsilano'=>'Kitsilano','west-point-grey'=>'West Point Grey','downtown'=>'Downtown','west-end'=>'West End'];
$nb_display = $nb_map[$nb_slug] ?? ucwords(str_replace('-',' ',$nb_slug));

// ── Eligibility ───────────────────────────────────────────────────────────────
if ($lot_width_m>=15.1 && $lot_area_sqm>=557 && $transit_prox && $lane_access) {
    $max_units=6; $unit_label='6-Unit Multiplex'; $elig_label='6-Unit Eligible'; $elig_color='#22c55e';
} elseif ($lot_width_m>=10.0 && $lot_area_sqm>=306 && $lane_access) {
    $max_units=4; $unit_label='4-Unit Multiplex'; $elig_label='4-Unit Eligible'; $elig_color='#14b8a6';
} elseif ($lot_width_m>=7.5 && $lot_area_sqm>=200 && $lane_access) {
    $max_units=3; $unit_label='Duplex / 3-Unit'; $elig_label='Duplex / 3-Unit'; $elig_color='#f59e0b';
} else {
    $max_units=0; $unit_label='Below Minimum'; $elig_label='Below Minimum'; $elig_color='#94a3b8';
}
$fsr = $pro_forma_path==='rental' ? 1.00 : 0.70;
$buildable_sqm  = $lot_area_sqm * $fsr;
$buildable_sqft = round($buildable_sqm * 10.7639);

// ── Market data ───────────────────────────────────────────────────────────────
$ms = $pdo->prepare("SELECT avg_sold_psf_duplex, avg_rent_1br, avg_rent_2br, avg_rent_3br, sales_count_duplex AS sales_count, data_month FROM monthly_market_stats WHERE neighbourhood_slug=? AND is_active=1 ORDER BY data_month DESC LIMIT 1");
$ms->execute([$nb_slug]); $market = $ms->fetch();
$metro = $pdo->query("SELECT AVG(avg_sold_psf_duplex) as p FROM monthly_market_stats WHERE is_active=1 AND data_month>=DATE_SUB(CURDATE(),INTERVAL 3 MONTH)")->fetch();
$current_psf = (float)($market['avg_sold_psf_duplex'] ?? $metro['p'] ?? 985);
$comp_count  = (int)($market['sales_count'] ?? 0);
$data_as_of  = !empty($market['data_month']) ? date('F Y', strtotime($market['data_month'])) : 'Metro Vancouver Benchmark';
$conf_label  = $comp_count>=5 ? 'High' : ($comp_count>=2 ? 'Moderate' : 'Indicative');
$conf_color  = $comp_count>=5 ? '#22c55e' : ($comp_count>=2 ? '#f59e0b' : '#94a3b8');

// ── DOM ───────────────────────────────────────────────────────────────────────
$dom_data=[];
foreach(['duplex','detached'] as $dt) {
    try {
        $d2=$pdo->prepare("SELECT dom_{$dt} as v FROM neighbourhood_hpi_history WHERE neighbourhood_id=(SELECT id FROM neighbourhoods WHERE slug=? LIMIT 1) AND dom_{$dt}>0 ORDER BY month_year DESC LIMIT 2");
        $d2->execute([$nb_slug]); $rows=$d2->fetchAll();
        $curr=!empty($rows[0])?(int)$rows[0]['v']:null; $prev=!empty($rows[1])?(int)$rows[1]['v']:null;
        $diff=($curr&&$prev)?$curr-$prev:null;
        $dom_data[$dt]=['current'=>$curr,'diff'=>$diff,'label'=>$diff===null?'—':($diff<-1?'↓ Faster':'↑ Slower'),'color'=>$diff===null?'#94a3b8':($diff<-1?'#22c55e':'#f59e0b')];
    } catch(PDOException $e){ $dom_data[$dt]=['current'=>null,'diff'=>null,'label'=>'—','color'=>'#94a3b8']; }
}

// ── HPI ───────────────────────────────────────────────────────────────────────
$hpi_yoy=null;
try { $hs=$pdo->prepare("SELECT hpi_change_yoy FROM neighbourhood_hpi_history WHERE neighbourhood_id=(SELECT id FROM neighbourhoods WHERE slug=? LIMIT 1) ORDER BY month_year DESC LIMIT 1"); $hs->execute([$nb_slug]); $hr=$hs->fetch(); if($hr) $hpi_yoy=(float)$hr['hpi_change_yoy']; } catch(PDOException $e){}

// ── Comps ─────────────────────────────────────────────────────────────────────
$cs=$pdo->prepare("SELECT address,data_month,sqft,price_per_sqft,days_on_market,csv_type FROM monthly_market_stats WHERE neighbourhood_slug=? AND is_active=1 AND csv_type IN('duplex','detached') AND yr_blt>=2024 ORDER BY data_month DESC LIMIT 5");
$cs->execute([$nb_slug]); $comps=$cs->fetchAll(); $comps_expanded=false;
if (count($comps)<3) {
    $need=5-count($comps);
    $as2=$pdo->prepare("SELECT address,data_month,sqft,price_per_sqft,days_on_market,csv_type FROM monthly_market_stats WHERE neighbourhood_slug!=? AND is_active=1 AND csv_type IN('duplex','detached') AND yr_blt>=2024 ORDER BY data_month DESC LIMIT $need");
    $as2->execute([$nb_slug]); foreach($as2->fetchAll() as $a){$a['expanded']=true;$comps[]=$a;} $comps_expanded=true;
}

// ── Build costs ───────────────────────────────────────────────────────────────
$bcs=$pdo->prepare("SELECT cost_standard_low,cost_standard_high FROM construction_costs WHERE neighbourhood_slug=? ORDER BY updated_at DESC LIMIT 1");
$bcs->execute([$nb_slug]); $bc=$bcs->fetch();
$build_psf = $bc ? (((float)$bc['cost_standard_low']+(float)$bc['cost_standard_high'])/2) : 420;

// ── Pro forma ─────────────────────────────────────────────────────────────────
$hard_build    = $buildable_sqft * $build_psf;
$density_bonus = 0;
if ($pro_forma_path==='rental') { $density_bonus=($lot_area_sqm*0.30*10.7639)*40; }
$dcl_city   = $buildable_sqft * 18.45;
$dcl_util   = $buildable_sqft * 2.95;
$permit_fees= ($hard_build/1000)*13.70;
$peat_cost  = $peat_zone ? 150000 : 0;
$total_cost = $assessed_land + $hard_build + $density_bonus + $dcl_city + $dcl_util + $permit_fees + $peat_cost;

$rental_rows=[]; $gross_monthly=0; $gross_annual=0; $noi=0; $unit_mix=[]; $saleable=0;
if ($pro_forma_path==='strata') {
    $saleable   = $buildable_sqft * 0.85;
    $exit_value = $saleable * $current_psf;
    $profit     = $exit_value - $total_cost;
    $roi        = $total_cost>0 ? ($profit/$total_cost)*100 : 0;
    if ($max_units===6)      { foreach([1,2,2,2,2,3] as $br) $unit_mix[]=['br'=>$br,'sqft'=>round($saleable/6),'price'=>round(($saleable/6)*$current_psf)]; }
    elseif ($max_units===4)  { foreach([1,2,2,3] as $br) $unit_mix[]=['br'=>$br,'sqft'=>round($saleable/4),'price'=>round(($saleable/4)*$current_psf)]; }
    else                     { foreach([2,2] as $br) $unit_mix[]=['br'=>$br,'sqft'=>round($saleable/max($max_units,2)),'price'=>round(($saleable/max($max_units,2))*$current_psf)]; }
} else {
    $cmhcs=$pdo->prepare("SELECT benchmark_1br, cmhc_rent_2br AS benchmark_2br, benchmark_3br FROM cmhc_benchmarks WHERE neighbourhood_slug=? ORDER BY year DESC LIMIT 1");
    $cmhcs->execute([$nb_slug]); $cmhc=$cmhcs->fetch();
    $r1=(int)($market['avg_rent_1br']??$cmhc['benchmark_1br']??2100); $r2=(int)($market['avg_rent_2br']??$cmhc['benchmark_2br']??2750); $r3=(int)($market['avg_rent_3br']??$cmhc['benchmark_3br']??3200);
    $c1=(int)($cmhc['benchmark_1br']??1875); $c2=(int)($cmhc['benchmark_2br']??2400); $c3=(int)($cmhc['benchmark_3br']??2900);
    $rental_rows=[['t'=>'1BR','curr'=>$r1,'cmhc'=>$c1],['t'=>'2BR','curr'=>$r2,'cmhc'=>$c2],['t'=>'3BR','curr'=>$r3,'cmhc'=>$c3]];
    $gross_monthly=$max_units>=6?(2*$r1+3*$r2+$r3):($max_units===4?($r1+2*$r2+$r3):($r1+$r2));
    $gross_annual=$gross_monthly*12; $noi=$gross_annual*0.95*0.75;
    $exit_value=$noi; $profit=$noi-$total_cost*0.065;
    $roi=$total_cost>0?($noi/$total_cost)*100:0; $saleable=$buildable_sqft;
}
$current_margin = $current_psf - $build_psf;

// ── Outlook ───────────────────────────────────────────────────────────────────
$outlook_pct=null; $outlook_psf=null; $proj_margin=null; $outlook_data=null; $outlook_quarter=null; $outlook_sources=[];
try {
    $os=$pdo->prepare("SELECT weighted_outlook,outlook_psf,macro_signal,local_momentum,pipeline_signal,population_signal,confidence_band_low,confidence_band_high,quarter,confidence_tier FROM wynston_outlook WHERE neighbourhood_slug=? AND is_active=1 ORDER BY calculated_at DESC LIMIT 1");
    $os->execute([$nb_slug]); $or2=$os->fetch();
    if ($or2) {
        $outlook_pct=$or2['weighted_outlook']; $outlook_psf=$or2['outlook_psf']??($current_psf*(1+$outlook_pct/100));
        $proj_margin=$outlook_psf-$build_psf; $outlook_quarter=$or2['quarter'];
        $tier=$or2['confidence_tier']??'tier3';
        $mw=$tier==='tier1'?0.38:($tier==='tier2'?0.53:0.68); $lw=$tier==='tier1'?0.38:($tier==='tier2'?0.23:0.08);
        $outlook_data=['macro'=>$or2['macro_signal'],'local'=>$or2['local_momentum'],'pipeline'=>$or2['pipeline_signal'],'population'=>$or2['population_signal']??0,'mw'=>$mw,'lw'=>$lw,'pw'=>0.12,'pw2'=>0.12,'low'=>$or2['confidence_band_low']??null,'high'=>$or2['confidence_band_high']??null];
        $srcs=$pdo->query("SELECT DISTINCT source_name FROM wynston_outlook_inputs WHERE is_active=1 ORDER BY id LIMIT 6"); $outlook_sources=$srcs->fetchAll(PDO::FETCH_COLUMN);
    }
} catch(PDOException $e){}

// ── Blueprint ─────────────────────────────────────────────────────────────────
$blueprint=null;
try { $bps=$pdo->prepare("SELECT * FROM design_catalogue WHERE is_active=1 AND min_lot_width<=? AND max_lot_width>=? AND (transit_required=0 OR (transit_required=1 AND ?=1)) ORDER BY min_lot_width DESC LIMIT 1"); $bps->execute([$lot_width_m,$lot_width_m,(int)$transit_prox]); $blueprint=$bps->fetch()?:null; } catch(PDOException $e){}

// ── Nearest stop ──────────────────────────────────────────────────────────────
$stop_name=null; $stop_dist=null;
if ($lat&&$lng) { try { $ss=$pdo->prepare("SELECT stop_name,(6371000*ACOS(COS(RADIANS(?))*COS(RADIANS(lat))*COS(RADIANS(lng)-RADIANS(?))+SIN(RADIANS(?))*SIN(RADIANS(lat)))) AS d FROM transit_stops ORDER BY d ASC LIMIT 1"); $ss->execute([$lat,$lng,$lat]); $sr=$ss->fetch(); if($sr){$stop_name=$sr['stop_name'];$stop_dist=round($sr['d']);} } catch(PDOException $e){} }

// ── Aerial ────────────────────────────────────────────────────────────────────
$aerial_base64='';
if ($lat&&$lng) {
    $url="https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/static/{$lng},{$lat},17,0/800x400@2x?access_token=pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg";
    $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_FOLLOWLOCATION=>true]);
    $data=curl_exec($ch); curl_close($ch);
    if ($data&&strlen($data)>1000) $aerial_base64=base64_encode($data);
}

// ── Next step ─────────────────────────────────────────────────────────────────
if ($elig_label==='6-Unit Eligible') $next_step="This lot qualifies for a 6-unit strata multiplex. Contact Wynston to begin acquisition analysis.";
elseif ($elig_label==='4-Unit Eligible') $next_step="This lot qualifies for a 4-unit multiplex. Contact Wynston to review acquisition opportunities.";
elseif ($elig_label==='Duplex / 3-Unit') $next_step="This lot qualifies for a duplex or 3-unit build. Consider neighbour buyout to unlock higher density.";
else $next_step="This lot does not meet current minimums. Contact Wynston to discuss assembly strategies.";

// ── Log + email ───────────────────────────────────────────────────────────────
$report_id = strtoupper(substr(md5(uniqid($pid.$dev_id,true)),0,12));
try { $pdo->prepare("INSERT INTO pdf_log (developer_id,pid,address,report_id,generated_at) VALUES (?,?,?,?,NOW())")->execute([$dev_id,$pid,$address,$report_id]); }
catch(PDOException $e){ try{$pdo->prepare("INSERT INTO pdf_log (developer_id,pid,address,generated_at) VALUES (?,?,?,NOW())")->execute([$dev_id,$pid,$address]);}catch(PDOException $e2){} }

if ($mailer_loaded) { try {
    $m=new PHPMailer(true); $m->isSMTP(); $m->Host='smtp.hostinger.com'; $m->SMTPAuth=true; $m->Username='noreply@wynston.ca'; $m->Password='Concac1979$'; $m->SMTPSecure='ssl'; $m->Port=465;
    $m->setFrom('noreply@wynston.ca','Wynston W.I.N'); $m->addAddress('tam@wynston.ca','Tam Nguyen'); $m->isHTML(true);
    $m->Subject='🔥 Report Viewed — '.htmlspecialchars($address);
    $m->Body='<b>'.htmlspecialchars($address).'</b><br>Developer: '.htmlspecialchars($developer['email']).'<br>Prepared for: '.htmlspecialchars($prepared_for).'<br>Eligibility: '.htmlspecialchars($elig_label).'<br>Est. profit: $'.number_format($profit).'<br>Report ID: '.$report_id;
    $m->AltBody="{$address} | {$developer['email']} | Profit: $".number_format($profit);
    $m->send();
} catch(MailException $e){} }

// ── Helpers ───────────────────────────────────────────────────────────────────
function money($n) { return '$'.number_format((float)$n); }
function pct($n,$d=1) { return number_format((float)$n,$d).'%'; }

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Wynston Report — <?= htmlspecialchars($address) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;font-size:13px;color:#1e293b;background:#e2e8f0;line-height:1.5}
.report-wrap{max-width:900px;margin:0 auto;background:#fff;box-shadow:0 4px 32px rgba(0,0,0,.15)}

/* Print bar */
.print-bar{background:#002446;padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.print-bar-title{color:#c9a84c;font-size:13px;font-weight:600;letter-spacing:1px}
.btn-print{background:#c9a84c;color:#002446;border:none;padding:9px 22px;border-radius:4px;font-size:13px;font-weight:700;cursor:pointer}
.btn-back{background:transparent;color:#f9f6f0;border:1px solid rgba(255,255,255,.3);padding:9px 18px;border-radius:4px;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block;margin-right:8px}

/* Cover */
.cover{background:#002446;color:#f9f6f0}
.gold-bar{background:#c9a84c;height:6px}
.cover-inner{padding:48px 56px 36px}
.logo{font-size:28px;font-weight:800;color:#c9a84c;letter-spacing:3px;margin-bottom:4px}
.logo-sub{font-size:11px;color:rgba(249,246,240,.6);letter-spacing:4px;text-transform:uppercase;margin-bottom:32px}
.report-label{font-size:11px;color:#c9a84c;letter-spacing:3px;text-transform:uppercase;margin-bottom:10px}
.cover-address{font-size:32px;font-weight:800;line-height:1.15;margin-bottom:8px}
.cover-pid{font-size:13px;color:#c9a84c;margin-bottom:24px}
.cover-aerial{width:100%;height:320px;object-fit:cover;border:2px solid #c9a84c;display:block;margin-bottom:24px}
.cover-aerial-ph{width:100%;height:200px;background:#0a3060;border:2px solid #c9a84c;margin-bottom:24px;display:flex;align-items:center;justify-content:center;color:#c9a84c}
.cover-meta{font-size:13px;margin-bottom:5px}
.cover-meta b{color:#c9a84c}
.cover-badges{margin-top:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.badge-e{display:inline-block;padding:8px 18px;border-radius:4px;font-size:14px;font-weight:700;letter-spacing:.5px;color:#fff;background:<?= $elig_color ?>}
.badge-c{display:inline-block;border:1px solid #c9a84c;color:#c9a84c;padding:7px 14px;border-radius:4px;font-size:12px}
.cover-foot{font-size:10px;color:rgba(249,246,240,.4);padding:14px 56px 20px;border-top:1px solid rgba(255,255,255,.1);margin-top:24px}

/* Sections */
.sec{padding:40px 56px;border-bottom:1px solid #e2e8f0}
.sec:last-child{border-bottom:none}
.sec-hdr{border-bottom:2px solid #002446;padding-bottom:10px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-end}
.sec-title{font-size:20px;font-weight:800;color:#002446}
.sec-sub{font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5px;margin-top:3px}
.sec-rid{font-size:10px;color:#94a3b8}

/* KPI */
.kpi-row{display:flex;gap:12px;margin:20px 0}
.kpi{flex:1;background:#f9f6f0;border:1px solid #e2e8f0;border-top:3px solid #002446;padding:14px 10px;text-align:center}
.kpi-l{font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.kpi-v{font-size:18px;font-weight:800;color:#002446}
.kpi-s{font-size:10px;color:#94a3b8;margin-top:4px}
.pos{color:#166534}.neg{color:#991b1b}

/* Boxes */
.next-step{background:#002446;color:#f9f6f0;padding:16px 20px;border-left:4px solid #c9a84c;margin:16px 0;font-size:13px;line-height:1.6}
.next-step-l{font-size:9px;color:#c9a84c;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;font-weight:700}
.outlook-box{background:#f9f6f0;border:1px solid #c9a84c;border-left:4px solid #c9a84c;padding:14px 18px;margin:14px 0}
.outlook-box-l{font-size:9px;color:#c9a84c;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px;font-weight:700}
.outlook-box-v{font-size:22px;font-weight:800;color:#002446}

/* Dims */
.dim-row{display:flex;margin-bottom:20px}
.dim{flex:1;background:#f9f6f0;border:1px solid #e2e8f0;padding:16px;text-align:center}
.dim+.dim{border-left:none}
.dim-l{font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.dim-v{font-size:20px;font-weight:800;color:#002446}
.dim-s{font-size:11px;color:#94a3b8;margin-top:3px}

/* Tables */
table.t{width:100%;border-collapse:collapse;margin:12px 0;font-size:12px}
table.t th{background:#002446;color:#f9f6f0;padding:10px 12px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.5px;font-weight:600}
table.t td{padding:10px 12px;border-bottom:1px solid #e2e8f0;vertical-align:middle}
table.t tr:nth-child(even) td{background:#f9f6f0}
.cy{color:#22c55e;font-weight:700}.cn{color:#ef4444;font-weight:700}.cw{color:#f59e0b;font-weight:700}

/* Flags */
.flag{padding:10px 14px;margin:8px 0;font-size:12px;border-radius:3px}
.fg{background:#f0fdf4;border-left:4px solid #22c55e;color:#166534}
.fa{background:#fffbeb;border-left:4px solid #f59e0b;color:#92400e}
.fr{background:#fef2f2;border-left:4px solid #ef4444;color:#991b1b}
.fgr{background:#f8fafc;border-left:4px solid #94a3b8;color:#475569}
.buyout{background:#fffbeb;border:2px solid #f59e0b;padding:14px 18px;margin:14px 0;color:#92400e;font-size:12px;border-radius:3px}

/* PSF row */
.psf-row{display:flex;gap:12px;margin:16px 0}
.psf{flex:1;text-align:center;padding:16px 12px;background:#f9f6f0;border:1px solid #e2e8f0}
.psf.hl{background:#002446;border-color:#002446}
.psf-l{font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.psf.hl .psf-l{color:#c9a84c}
.psf-v{font-size:24px;font-weight:800;color:#002446}
.psf.hl .psf-v{color:#f9f6f0}
.psf-s{font-size:10px;color:#94a3b8;margin-top:4px}
.psf.hl .psf-s{color:#c9a84c}

/* Margin row */
.mrow{display:flex;justify-content:space-between;align-items:center;background:#f9f6f0;padding:12px 16px;margin:8px 0;border-radius:3px}
.mrow-l{font-size:12px;color:#374151}
.mrow-v{font-size:14px;font-weight:700;color:#002446}
.mrow-d{font-size:12px;color:#22c55e;padding-left:10px}

/* Proforma */
.pf-t{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5px;border-bottom:1px solid #e2e8f0;padding-bottom:6px;margin:16px 0 10px}
.pf-line{display:flex;justify-content:space-between;padding:5px 0;font-size:12px}
.pf-line.ind{padding-left:16px}
.pf-line .lbl{color:#374151}.pf-line .val{font-weight:600;color:#002446}
.pf-total{background:#002446;padding:12px 16px;display:flex;justify-content:space-between;margin-top:6px;border-radius:2px}
.pf-total .lbl{font-size:13px;font-weight:700;color:#f9f6f0}
.pf-total .val{font-size:16px;font-weight:800;color:#c9a84c}
.profit-box{border:2px solid #22c55e;background:#f0fdf4;padding:16px 20px;margin:14px 0;display:flex;justify-content:space-between;align-items:center;border-radius:3px}
.profit-box.loss{border-color:#ef4444;background:#fef2f2}
.profit-lbl{font-size:13px;font-weight:700;color:#374151}
.profit-lbl small{display:block;font-size:10px;font-weight:400;color:#94a3b8;margin-top:3px}
.profit-val{font-size:28px;font-weight:800;color:#166534}
.profit-val.loss{color:#991b1b}

/* Confidence */
.conf-box{background:#f9f6f0;border:1px solid #e2e8f0;padding:14px 18px;margin:14px 0}
.conf-title{font-size:15px;font-weight:700;color:#002446;margin-bottom:4px}

/* Blueprint */
.bp-outer{border:1px solid #c9a84c;padding:20px;margin:14px 0;display:flex;gap:20px}
.bp-img{width:35%;flex-shrink:0}
.bp-img img{width:100%;height:auto;border:1px solid #e2e8f0}
.bp-ph{width:100%;height:140px;background:#f9f6f0;border:1px solid #e2e8f0}
.bp-id{font-size:10px;color:#c9a84c;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px}
.bp-name{font-size:18px;font-weight:800;color:#002446;margin-bottom:12px}
.bp-save{background:#f9f6f0;border-left:3px solid #c9a84c;padding:8px 12px;margin:6px 0;font-size:12px}

/* Outlook layers */
.ol-row{display:flex;border-bottom:1px solid #e2e8f0;padding:10px 0;font-size:12px}
.ol-name{flex:3;font-weight:600;color:#002446}
.ol-sig{flex:2;text-align:center}
.ol-wt{flex:1.5;text-align:center;color:#94a3b8}
.ol-ct{flex:2;text-align:center;font-weight:700;color:#002446}
.ol-src{flex:2;text-align:right;font-size:10px;color:#94a3b8}
.ol-tot{background:#f9f6f0;font-weight:800}

/* Risk */
.risk{padding:12px 16px;margin-bottom:10px;border-left:3px solid #e2e8f0;background:#f8fafc;border-radius:0 3px 3px 0;font-size:12px}
.risk.act{border-left-color:#f59e0b;background:#fffbeb}
.risk.crit{border-left-color:#ef4444;background:#fef2f2}
.risk-t{font-size:13px;font-weight:700;color:#002446;margin-bottom:4px}

/* Back cover */
.back{background:#002446;color:#f9f6f0;padding:48px 56px}
.back-div{border-top:1px solid rgba(255,255,255,.15);margin:20px 0}
.agent-row{display:flex;gap:24px;align-items:flex-start;margin-bottom:20px}
.agent-ph{width:100px;height:120px;background:#0a3060;border:2px solid #c9a84c;border-radius:4px;flex-shrink:0}
.agent-name{font-size:20px;font-weight:800;color:#c9a84c;margin-bottom:3px}
.agent-title{font-size:10px;color:rgba(249,246,240,.7);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:10px}
.agent-bio{font-size:12px;color:rgba(249,246,240,.8);line-height:1.6}
.ct{font-size:12px;margin:5px 0}
.ct b{color:#c9a84c}
.back-src{font-size:10px;color:rgba(249,246,240,.5);line-height:1.6;margin-top:12px}
.back-disc{font-size:10px;color:rgba(249,246,240,.35);line-height:1.6;margin-top:10px}
.back-gbar{background:#c9a84c;height:6px;margin:28px -56px -48px}

/* PRINT */
@media print{
    @page{margin:14mm 12mm;size:letter portrait}
    body{background:#fff;font-size:11px}
    .print-bar{display:none!important}
    .report-wrap{max-width:100%;box-shadow:none}
    .sec{padding:20px 28px;page-break-inside:avoid}
    .cover{page-break-after:always}
    .cover-inner{padding:28px 36px 24px}
    .cover-address{font-size:24px}
    .cover-aerial{height:220px}
    .sec-title{font-size:16px}
    .kpi-v{font-size:14px}
    .psf-v{font-size:18px}
    .profit-val{font-size:22px}
    .dim-v{font-size:16px}
    table.t th,table.t td{padding:7px 9px;font-size:10px}
    .back{padding:28px 36px;page-break-before:always}
    .bp-outer{page-break-inside:avoid}
    .risk{page-break-inside:avoid}
    .psf-row{gap:8px}
    .kpi-row{gap:8px}
}
</style>
</head>
<body>

<div class="print-bar">
    <div class="print-bar-title">WYNSTON W.I.N — Development Intelligence Report</div>
    <div>
        <a class="btn-back" href="javascript:history.back()">← Back to Map</a>
        <button class="btn-print" onclick="window.print()">⬇ Save as PDF</button>
    </div>
</div>

<div class="report-wrap">

<!-- COVER -->
<div class="cover">
    <div class="gold-bar"></div>
    <div class="cover-inner">
        <div class="logo">WYNSTON</div>
        <div class="logo-sub">Intelligent Navigator · W.I.N</div>
        <div class="report-label">Development Intelligence Report</div>
        <div class="cover-address"><?= htmlspecialchars($address) ?></div>
        <div class="cover-pid">PID: <?= htmlspecialchars($pid_fmt) ?> · Zoning: R1-1 · City of Vancouver</div>
        <?php if (!empty($aerial_base64)): ?>
            <img class="cover-aerial" src="data:image/jpeg;base64,<?= $aerial_base64 ?>">
        <?php else: ?>
            <div class="cover-aerial-ph">Aerial imagery unavailable</div>
        <?php endif; ?>
        <div class="cover-meta"><b>Prepared for:</b> <?= htmlspecialchars($prepared_for) ?></div>
        <div class="cover-meta"><b>Report ID:</b> <?= $report_id ?></div>
        <div class="cover-meta"><b>Generated:</b> <?= date('F j, Y') ?></div>
        <div class="cover-meta"><b>Data as of:</b> <?= htmlspecialchars($data_as_of) ?></div>
        <div class="cover-badges">
            <span class="badge-e"><?= htmlspecialchars($elig_label) ?></span>
            <span class="badge-c"><?= htmlspecialchars($conf_label) ?> Confidence</span>
        </div>
    </div>
    <div class="cover-foot">CONFIDENTIAL — Prepared exclusively for <?= htmlspecialchars($prepared_for) ?>. For informational purposes only. Not financial or investment advice.</div>
</div>


<!-- EXECUTIVE SUMMARY -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Executive Summary</div><div class="sec-sub"><?= htmlspecialchars($address) ?></div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <div style="margin-bottom:16px">
        <span class="badge-e"><?= htmlspecialchars($elig_label) ?></span>
        <span class="badge-c" style="margin-left:8px"><?= htmlspecialchars($conf_label) ?> Confidence</span>
        <?php if ($comp_count>0): ?><span style="font-size:11px;color:#94a3b8;margin-left:10px">Based on <?= $comp_count ?> comparable sale<?= $comp_count!==1?'s':'' ?></span><?php endif; ?>
    </div>
    <div class="kpi-row">
        <div class="kpi"><div class="kpi-l">Max Units</div><div class="kpi-v"><?= $max_units ?></div><div class="kpi-s"><?= htmlspecialchars($unit_label) ?></div></div>
        <div class="kpi"><div class="kpi-l">Buildable Area</div><div class="kpi-v"><?= number_format($buildable_sqft) ?></div><div class="kpi-s">sqft (<?= number_format($buildable_sqm) ?> m²)</div></div>
        <div class="kpi"><div class="kpi-l">Exit Value</div><div class="kpi-v"><?= money($exit_value) ?></div><div class="kpi-s"><?= $pro_forma_path==='rental'?'Annual NOI':'Strata sale' ?></div></div>
        <div class="kpi"><div class="kpi-l">Est. Profit</div><div class="kpi-v <?= $profit>=0?'pos':'neg' ?>"><?= money($profit) ?></div><div class="kpi-s">Before tax</div></div>
        <div class="kpi"><div class="kpi-l">ROI</div><div class="kpi-v <?= $roi>=0?'pos':'neg' ?>"><?= pct($roi) ?></div><div class="kpi-s">Return on cost</div></div>
    </div>
    <?php if (!empty($outlook_pct)): ?>
    <div class="outlook-box">
        <div class="outlook-box-l">Wynston Outlook — 12-Month Projection</div>
        <div class="outlook-box-v"><?= $outlook_pct>=0?'+':'' ?><?= pct($outlook_pct) ?> · <?= money($outlook_psf) ?>/sqft projected</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px">Current: <?= money($current_psf) ?>/sqft · Projected margin improvement: <?= money($proj_margin-$current_margin) ?>/sqft</div>
    </div>
    <?php endif; ?>
    <div class="next-step"><div class="next-step-l">Recommended Next Step</div><?= htmlspecialchars($next_step) ?></div>
    <?php if ($conf_label==='Indicative'): ?><div class="flag fa"><strong>Indicative Confidence:</strong> Fewer than 2 comparable sales. Pro forma uses Metro Vancouver benchmarks. Upload REBGV data to improve accuracy.</div><?php endif; ?>
</div>


<!-- PROPERTY INTELLIGENCE -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Property Intelligence</div><div class="sec-sub">Lot Dimensions & Zoning Eligibility</div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <div class="dim-row">
        <div class="dim"><div class="dim-l">Frontage</div><div class="dim-v"><?= $lot_width_ft ?> ft</div><div class="dim-s"><?= number_format($lot_width_m,2) ?> m</div></div>
        <div class="dim"><div class="dim-l">Depth</div><div class="dim-v"><?= $lot_depth_ft>0?$lot_depth_ft.' ft':'—' ?></div><div class="dim-s"><?= $lot_depth_m>0?number_format($lot_depth_m,2).' m':'Not recorded' ?></div></div>
        <div class="dim"><div class="dim-l">Lot Area</div><div class="dim-v"><?= number_format($lot_area_sqft) ?> sqft</div><div class="dim-s"><?= number_format($lot_area_sqm) ?> m²</div></div>
    </div>
    <?php if ($lot_width_m>=14.5&&$lot_width_m<15.1): ?><div class="buyout"><strong>⚡ Neighbour Buyout Opportunity</strong> — This lot is <?= number_format(15.1-$lot_width_m,2) ?>m below the 6-unit eligibility threshold. Acquiring the adjacent property could unlock 6-unit entitlement.</div><?php endif; ?>
    <table class="t">
        <thead><tr><th>Unit Count</th><th>Frontage Req.</th><th>Area Req.</th><th>Transit Req.</th><th>Lane Req.</th><th>Status</th></tr></thead>
        <tbody>
            <tr><td><strong>Duplex / 3-Unit</strong></td><td>24.6 ft (7.5 m)</td><td>2,153 sqft (200 m²)</td><td>Not required</td><td>Required</td><td><?php if($lot_width_m>=7.5&&$lot_area_sqm>=200&&$lane_access):?><span class="cy">✓</span><?php elseif($lot_width_m>=7.5&&$lot_area_sqm>=200):?><span class="cw">~</span> No lane<?php else:?><span class="cn">✗</span><?php endif;?></td></tr>
            <tr><td><strong>4-Unit</strong></td><td>32.8 ft (10.0 m)</td><td>3,294 sqft (306 m²)</td><td>Not required</td><td>Required</td><td><?php if($lot_width_m>=10.0&&$lot_area_sqm>=306&&$lane_access):?><span class="cy">✓</span><?php elseif($lot_width_m>=10.0&&$lot_area_sqm>=306):?><span class="cw">~</span> No lane<?php else:?><span class="cn">✗</span><?php endif;?></td></tr>
            <tr><td><strong>6-Unit (Strata)</strong></td><td>49.5 ft (15.1 m)</td><td>5,995 sqft (557 m²)</td><td>Required (400m)</td><td>Required</td><td><?php if($lot_width_m>=15.1&&$lot_area_sqm>=557&&$transit_prox&&$lane_access):?><span class="cy">✓</span><?php elseif($lot_width_m>=15.1&&$lot_area_sqm>=557&&!$transit_prox):?><span class="cw">~</span> No transit<?php else:?><span class="cn">✗</span><?php endif;?></td></tr>
            <tr><td><strong>8-Unit (Rental)</strong></td><td>49.5 ft (15.1 m)</td><td>5,995 sqft (557 m²)</td><td>Required (400m)</td><td>Required</td><td><?php if($lot_width_m>=15.1&&$lot_area_sqm>=557&&$transit_prox&&$lane_access):?><span class="cy">✓</span><?php elseif($lot_width_m>=15.1&&$lot_area_sqm>=557&&!$transit_prox):?><span class="cw">~</span> No transit<?php else:?><span class="cn">✗</span><?php endif;?></td></tr>
        </tbody>
    </table>
    <table class="t" style="margin-top:16px">
        <thead><tr><th>Path</th><th>FSR</th><th>Buildable (sqft)</th><th>Buildable (m²)</th><th>Parking</th></tr></thead>
        <tbody>
            <tr><td><strong>Strata (sell units)</strong></td><td>0.70</td><td><?= number_format($lot_area_sqm*0.70*10.764) ?> sqft</td><td><?= number_format($lot_area_sqm*0.70) ?> m²</td><td><?= $transit_prox?'0 stalls (transit zone)':number_format($max_units*0.5,1).' stalls' ?></td></tr>
            <tr><td><strong>Rental (hold & rent)</strong></td><td>1.00</td><td><?= number_format($lot_area_sqm*1.00*10.764) ?> sqft</td><td><?= number_format($lot_area_sqm*1.00) ?> m²</td><td><?= $transit_prox?'0 stalls (transit zone)':number_format($max_units*0.5,1).' stalls' ?></td></tr>
        </tbody>
    </table>
    <div style="margin-top:16px">
        <div class="flag <?= $lane_access?'fg':'fa' ?>"><?= $lane_access?'✓ Lane access confirmed':'⚠ No lane access detected — may affect eligibility for 4+ unit builds' ?></div>
        <div class="flag <?= $transit_prox?'fg':'fgr' ?>"><?= $transit_prox?'✓ Transit proximate — within 400m FTN. Parking: 0 stalls.':'○ Outside 400m transit zone — parking required at 0.5 stalls/unit.' ?></div>
        <?php if ($stop_name): ?><div class="flag fgr">Nearest FTN stop: <strong><?= htmlspecialchars($stop_name) ?></strong><?= $stop_dist?' · '.number_format($stop_dist).'m away':'' ?></div><?php endif; ?>
        <div class="flag <?= $heritage==='none'?'fg':($heritage==='C'?'fa':'fr') ?>"><?php if($heritage==='none'):?>✓ No heritage designation<?php elseif($heritage==='A'||$heritage==='B'):?>⛔ Heritage Category <?= $heritage ?> — HRA may be required. Significant permit delays expected.<?php else:?>⚠ Heritage Category C — Inspection required before permit application.<?php endif;?></div>
        <div class="flag <?= $peat_zone?'fa':'fg' ?>"><?= $peat_zone?'⚠ Peat zone — Helical pile foundation likely required. $150,000 contingency added to pro forma.':'✓ No peat zone flag on record' ?></div>
        <div class="flag <?= $covenant_present?'fa':'fg' ?>"><?= $covenant_present?'⚠ Covenant registered: '.htmlspecialchars($covenant_types).' — Obtain title search before proceeding.':'✓ No covenant or easement flags on record' ?></div>
        <?php if ($has_permit): ?><div class="flag fa">⚠ Active building permit on record.</div><?php endif; ?>
    </div>
</div>


<!-- MARKET ANALYSIS -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Market Analysis</div><div class="sec-sub"><?= htmlspecialchars($nb_display) ?> · <?= htmlspecialchars($data_as_of) ?></div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <div class="psf-row">
        <div class="psf"><div class="psf-l">Build Cost</div><div class="psf-v"><?= money($build_psf) ?></div><div class="psf-s">/sqft · current</div></div>
        <div class="psf"><div class="psf-l">Finished Sale Price</div><div class="psf-v"><?= money($current_psf) ?></div><div class="psf-s">/sqft · <?= htmlspecialchars($data_as_of) ?></div></div>
        <div class="psf hl"><div class="psf-l">Wynston Outlook</div><div class="psf-v"><?= !empty($outlook_psf)?money($outlook_psf):'—' ?></div><div class="psf-s">/sqft · 12-month projection</div></div>
    </div>
    <div class="mrow"><div class="mrow-l">Current margin (sold − build)</div><div style="display:flex;align-items:center"><div class="mrow-v"><?= money($current_margin) ?>/sqft</div><?php if(!empty($proj_margin)):?><div class="mrow-d"> · Projected: <?= money($proj_margin) ?>/sqft (<?= $proj_margin>=$current_margin?'▲':'▼' ?> <?= money(abs($proj_margin-$current_margin)) ?>)</div><?php endif;?></div></div>
    <div class="conf-box"><div class="conf-title"><?= htmlspecialchars($conf_label) ?> Confidence</div><div style="font-size:12px;color:#374151;margin-top:4px"><?php if($conf_label==='High'):?>Based on <?= $comp_count ?> comparable sales in <?= htmlspecialchars($nb_display) ?> — REBGV MLS, new builds 2024+.<?php elseif($conf_label==='Moderate'):?>Based on <?= $comp_count ?> comparable sale<?= $comp_count!==1?'s':''?> in <?= htmlspecialchars($nb_display) ?>. Some figures use Metro Vancouver benchmarks.<?php else:?>Fewer than 2 comparable sales. Pro forma uses Metro Vancouver benchmarks. Upload REBGV data to improve accuracy.<?php endif;?></div></div>
    <table class="t" style="margin-top:16px">
        <thead><tr><th>Property Type</th><th>Current DOM</th><th>vs Last Month</th><th>Signal</th></tr></thead>
        <tbody><?php foreach($dom_data as $dt=>$dd):?><tr><td><strong><?= ucfirst($dt) ?></strong></td><td><?= $dd['current']!==null?$dd['current'].' days':'—' ?></td><td><?= $dd['diff']!==null?($dd['diff']<0?'↓ '.abs($dd['diff']).' days faster':($dd['diff']>0?'↑ '.$dd['diff'].' days slower':'Stable')):'—' ?></td><td style="color:<?= $dd['color'] ?>;font-weight:600"><?= $dd['label'] ?></td></tr><?php endforeach;?></tbody>
    </table>
    <?php if(!is_null($hpi_yoy)):?><div class="mrow" style="margin-top:12px"><div class="mrow-l"><?= htmlspecialchars($nb_display) ?> HPI Year-over-Year</div><div class="mrow-v" style="color:<?= $hpi_yoy>=0?'#166534':'#991b1b' ?>"><?= $hpi_yoy>=0?'+':'' ?><?= pct($hpi_yoy) ?></div></div><?php endif;?>
</div>


<!-- COMPARABLE SALES -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Comparable Sales</div><div class="sec-sub">New Builds 2024+ · <?= htmlspecialchars($nb_display) ?></div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <?php if (!empty($comps)): ?>
    <table class="t">
        <thead><tr><th>Address</th><th>Sale Date</th><th>Sqft</th><th>$/sqft</th><th>DOM</th><th>Type</th><th>Area</th></tr></thead>
        <tbody><?php foreach($comps as $c):?><tr><td><?= htmlspecialchars($c['address']??'—') ?></td><td><?= !empty($c['data_month'])?date('M Y',strtotime($c['data_month'])):'—' ?></td><td><?= !empty($c['sqft'])?number_format($c['sqft']):'—' ?></td><td><?= !empty($c['price_per_sqft'])?money($c['price_per_sqft']):money($current_psf) ?></td><td><?= !empty($c['days_on_market'])?$c['days_on_market'].' days':'—' ?></td><td><?= ucfirst($c['csv_type']??'—') ?></td><td style="font-size:10px;color:#94a3b8"><?= !empty($c['expanded'])?'Adjacent area':htmlspecialchars($nb_display) ?></td></tr><?php endforeach;?></tbody>
    </table>
    <?php if($comps_expanded):?><div style="font-size:10px;color:#94a3b8;font-style:italic;margin-top:6px">Note: Adjacent neighbourhood data included — fewer than 3 local comps available.</div><?php endif;?>
    <?php else: ?><div class="flag fgr">No comparable sales data yet. Pro forma uses Metro Vancouver benchmarks (<?= money($current_psf) ?>/sqft). Upload REBGV monthly data via the admin panel.</div><?php endif; ?>
    <div style="margin-top:12px;font-size:11px;color:#94a3b8">Filtered to: R1-1 zoned lots, Year Built 2024+, duplex and multiplex types. Source: REBGV MLS. This is not an appraisal.</div>
</div>


<!-- PRO FORMA -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Development Pro Forma</div><div class="sec-sub"><?= $pro_forma_path==='rental'?'Rental / Hold Path · 1.00 FSR':'Strata / Sell Path · 0.70 FSR' ?> · <?= $max_units ?>-Unit <?= htmlspecialchars($unit_label) ?></div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <div class="pf-t">Project Costs</div>
    <div class="pf-line"><span class="lbl">Land cost (BC Assessment <?= $assessment_year ?>)</span><span class="val"><?= money($assessed_land) ?></span></div>
    <div class="pf-line ind"><span class="lbl">Build cost (<?= number_format($buildable_sqft) ?> sqft × <?= money($build_psf) ?>/sqft)</span><span class="val"><?= money($hard_build) ?></span></div>
    <?php if($density_bonus>0):?><div class="pf-line ind"><span class="lbl">Density bonus (bonus FSR × $40/sqft)</span><span class="val"><?= money($density_bonus) ?></span></div><?php endif;?>
    <div class="pf-line ind"><span class="lbl">City-wide DCL ($18.45/sqft)</span><span class="val"><?= money($dcl_city) ?></span></div>
    <div class="pf-line ind"><span class="lbl">Utilities DCL ($2.95/sqft)</span><span class="val"><?= money($dcl_util) ?></span></div>
    <div class="pf-line ind"><span class="lbl">Permit fees ($13.70 per $1,000 construction value)</span><span class="val"><?= money($permit_fees) ?></span></div>
    <?php if($peat_zone):?><div class="pf-line ind"><span class="lbl">Peat zone contingency</span><span class="val">$150,000</span></div><?php endif;?>
    <div class="pf-total"><span class="lbl">Total Project Cost</span><span class="val"><?= money($total_cost) ?></span></div>

    <?php if($pro_forma_path==='strata'):?>
    <div class="pf-t">Exit Value — Strata Sale</div>
    <div class="pf-line"><span class="lbl">Saleable area (<?= number_format($buildable_sqft) ?> sqft × 85%)</span><span class="val"><?= number_format($saleable) ?> sqft</span></div>
    <div class="pf-line"><span class="lbl">Avg sold $/sqft (<?= $conf_label ?> confidence)</span><span class="val"><?= money($current_psf) ?>/sqft</span></div>
    <div class="pf-line"><span class="lbl">Total exit value</span><span class="val"><?= money($exit_value) ?></span></div>
    <?php if(!empty($unit_mix)):?>
    <div class="pf-t">Unit Mix (Vancouver 2026 Bedroom Rules)</div>
    <table class="t"><thead><tr><th>Unit</th><th>Bedrooms</th><th>Est. Sqft</th><th>Projected Sale</th></tr></thead>
    <tbody><?php foreach($unit_mix as $i=>$u):?><tr><td><?= $i+1 ?></td><td><?= $u['br'] ?>BR</td><td><?= number_format($u['sqft']) ?> sqft</td><td><?= money($u['price']) ?></td></tr><?php endforeach;?><tr style="font-weight:700"><td colspan="3">Total exit value</td><td><?= money($exit_value) ?></td></tr></tbody></table>
    <?php endif;?>
    <?php else:?>
    <div class="pf-t">Rental Income by Bedroom Type</div>
    <table class="t"><thead><tr><th>Type</th><th>Current Market</th><th>CMHC Benchmark</th><th>Variance</th></tr></thead>
    <tbody><?php foreach($rental_rows as $rr): $var=$rr['cmhc']>0?(($rr['curr']-$rr['cmhc'])/$rr['cmhc'])*100:0; $vc=$var>2?'#22c55e':($var<-2?'#ef4444':'#94a3b8');?><tr><td><strong><?= $rr['t'] ?></strong></td><td><?= money($rr['curr']) ?>/mo</td><td><?= money($rr['cmhc']) ?>/mo</td><td style="color:<?= $vc ?>;font-weight:600"><?= $var>=0?'+':'' ?><?= pct($var) ?></td></tr><?php endforeach;?></tbody></table>
    <div class="pf-t" style="margin-top:16px">Annual Income Summary</div>
    <div class="pf-line"><span class="lbl">Gross monthly income</span><span class="val"><?= money($gross_monthly) ?></span></div>
    <div class="pf-line"><span class="lbl">Annual gross income</span><span class="val"><?= money($gross_annual) ?></span></div>
    <div class="pf-line"><span class="lbl">Less 5% vacancy</span><span class="val">−<?= money($gross_annual*0.05) ?></span></div>
    <div class="pf-line"><span class="lbl">Less 25% operating expenses</span><span class="val">−<?= money($gross_annual*0.95*0.25) ?></span></div>
    <div class="pf-total"><span class="lbl">Net Operating Income (NOI)</span><span class="val"><?= money($noi) ?>/year</span></div>
    <?php endif;?>

    <div class="profit-box <?= $profit<0?'loss':'' ?>">
        <div class="profit-lbl">Estimated Profit<small>Before tax, financing costs, and professional fees</small></div>
        <div class="profit-val <?= $profit<0?'loss':'' ?>"><?= $profit<0?'-':'' ?><?= money(abs($profit)) ?></div>
    </div>
    <div style="text-align:right;font-size:12px;color:#374151;margin-top:6px">Return on cost: <strong><?= pct($roi) ?></strong> · Profit per unit: <strong><?= money($profit/max($max_units,1)) ?></strong></div>
    <div style="font-size:11px;color:#94a3b8;margin-top:12px">Pro forma uses <?= htmlspecialchars($nb_display) ?> data as of <?= htmlspecialchars($data_as_of) ?>. <?= $conf_label!=='High'?'Metro Vancouver benchmarks used where local data is unavailable. ':'' ?>Verify with your financial advisor.</div>
</div>


<!-- BLUEPRINT -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Standardized Design Match</div><div class="sec-sub">BC Provincial & CMHC Pre-Approved Plans</div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <?php if(!empty($blueprint)):?>
    <div class="bp-outer">
        <div class="bp-img"><?php if(!empty($blueprint['thumbnail_img'])&&file_exists(__DIR__.'/'.$blueprint['thumbnail_img'])):?><img src="<?= htmlspecialchars($blueprint['thumbnail_img']) ?>"><?php else:?><div class="bp-ph"></div><?php endif;?></div>
        <div style="flex:1">
            <div class="bp-id"><?= htmlspecialchars($blueprint['catalogue']) ?> · <?= htmlspecialchars($blueprint['design_id']) ?></div>
            <div class="bp-name"><?= htmlspecialchars($blueprint['design_name']) ?></div>
            <div class="bp-save">💰 ~$35,000 saved vs custom architectural drawings</div>
            <div class="bp-save">⏱ Up to 4 months faster permit approval</div>
            <div style="font-size:12px;margin-top:12px"><strong>Why this lot matches:</strong> Frontage <?= $lot_width_ft ?>ft (<?= number_format($lot_width_m,2) ?>m) within design range · <?= ($blueprint['transit_required']&&$transit_prox)?'Transit confirmed within 400m FTN':($blueprint['transit_required']?'Transit required — verify':'Transit not required') ?></div>
            <?php if(!empty($blueprint['cost_low_psf'])):?><div style="font-size:12px;margin-top:8px"><strong>Class D estimate:</strong> <?= money($blueprint['cost_low_psf']) ?>–<?= money($blueprint['cost_high_psf']) ?>/sqft</div><?php endif;?>
            <div style="font-size:11px;margin-top:10px;color:#374151"><strong>Download plans:</strong> <?= htmlspecialchars($blueprint['external_url']??'Contact Wynston') ?></div>
        </div>
    </div>
    <div style="font-size:10px;color:#94a3b8;font-style:italic;margin-top:8px">Plans provided by <?= htmlspecialchars($blueprint['catalogue']) ?>. Wynston does not host or modify these documents. Verify with City of Vancouver and a licensed professional.</div>
    <?php else:?><div class="flag fgr">No standardized design match found for this lot profile. Contact Wynston for custom design referrals.</div><?php endif;?>
</div>


<!-- WYNSTON OUTLOOK -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Wynston Outlook</div><div class="sec-sub">12-Month $/sqft Intelligence · <?= htmlspecialchars($nb_display) ?> · <?= htmlspecialchars($outlook_quarter??'Current Quarter') ?></div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <?php if(!empty($outlook_data)):?>
    <div class="psf-row">
        <div class="psf"><div class="psf-l">Build Cost</div><div class="psf-v"><?= money($build_psf) ?></div><div class="psf-s">/sqft</div></div>
        <div class="psf"><div class="psf-l">Current Finished Price</div><div class="psf-v"><?= money($current_psf) ?></div><div class="psf-s">/sqft</div></div>
        <div class="psf hl"><div class="psf-l">Wynston Outlook</div><div class="psf-v"><?= money($outlook_psf) ?></div><div class="psf-s">/sqft projected</div></div>
    </div>
    <div class="mrow"><div class="mrow-l">Current margin</div><div class="mrow-v"><?= money($current_margin) ?>/sqft</div></div>
    <div class="mrow"><div class="mrow-l">Projected margin (12-month)</div><div style="display:flex;align-items:center"><div class="mrow-v"><?= money($proj_margin) ?>/sqft</div><div class="mrow-d"> <?= $proj_margin>=$current_margin?'▲':'▼' ?> <?= money(abs($proj_margin-$current_margin)) ?>/sqft</div></div></div>
    <div style="margin-top:20px">
        <div class="pf-t">Three-Layer Methodology</div>
        <div class="ol-row" style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase"><div class="ol-name">Layer</div><div class="ol-sig">Signal</div><div class="ol-wt">Weight</div><div class="ol-ct">Contribution</div><div class="ol-src">Source</div></div>
        <?php foreach([['Macro Signal',$outlook_data['macro'],$outlook_data['mw'],'6 institutions'],['Local Momentum',$outlook_data['local'],$outlook_data['lw'],'Neighbourhood HPI'],['Pipeline Signal',$outlook_data['pipeline'],$outlook_data['pw'],'Active permits'],['Population Signal',$outlook_data['population'],$outlook_data['pw2'],'Stats Canada']] as $l):?>
        <div class="ol-row"><div class="ol-name"><?= $l[0] ?></div><div class="ol-sig"><?= $l[1]>=0?'+':'' ?><?= pct($l[1]) ?></div><div class="ol-wt"><?= round($l[2]*100) ?>%</div><div class="ol-ct"><?= number_format($l[1]*$l[2],2) ?>%</div><div class="ol-src"><?= $l[3] ?></div></div>
        <?php endforeach;?>
        <div class="ol-row ol-tot"><div class="ol-name">Combined Outlook</div><div class="ol-sig"><?= $outlook_pct>=0?'+':'' ?><?= pct($outlook_pct) ?></div><div class="ol-wt">100%</div><div class="ol-ct"><?= $outlook_pct>=0?'+':'' ?><?= pct($outlook_pct) ?></div><div class="ol-src"></div></div>
    </div>
    <?php if(!empty($outlook_sources)):?><div style="font-size:11px;color:#94a3b8;margin-top:8px">Sources: <?= htmlspecialchars(implode(', ',$outlook_sources)) ?></div><?php endif;?>
    <?php else:?><div class="flag fgr">Wynston Outlook not yet available. Enter quarterly forecasts via the admin panel.</div><?php endif;?>
    <div style="font-size:10px;color:#94a3b8;margin-top:12px">For informational purposes only. Not financial, investment, or real estate advice.</div>
</div>


<!-- RISK ANALYSIS -->
<div class="sec">
    <div class="sec-hdr">
        <div><div class="sec-title">Risk Analysis</div><div class="sec-sub">Standard & Conditional Risk Factors</div></div>
        <div class="sec-rid">Report <?= $report_id ?></div>
    </div>
    <div class="risk"><div class="risk-t">1. Construction Cost Inflation</div><div>BC Stats BCPI averaged 3–5% annually. If costs rise 10%: profit reduces to <?= money($profit-$hard_build*0.10) ?>. If 20%: <?= money($profit-$hard_build*0.20) ?>.</div></div>
    <div class="risk"><div class="risk-t">2. Interest Rate Environment</div><div>A 1% rate increase on a <?= money($total_cost*0.65) ?> construction facility adds ~<?= money($total_cost*0.65*0.01*1.25) ?> in financing costs over 15 months. Consult your mortgage broker.</div></div>
    <div class="risk <?= (isset($dom_data['duplex']['diff'])&&$dom_data['duplex']['diff']!==null&&$dom_data['duplex']['diff']>5)?'act':'' ?>"><div class="risk-t">3. Market Timing</div><div><?php if(!empty($dom_data['duplex']['current'])):?>Duplex DOM in <?= htmlspecialchars($nb_display) ?>: <?= $dom_data['duplex']['current'] ?> days. <?= ($dom_data['duplex']['diff']??0)<-1?'Market accelerating — conditions favour proceeding now.':(($dom_data['duplex']['diff']??0)>5?'DOM rising — monitor market closely.':'Market conditions stable.') ?><?php else:?>DOM data not available. Monitor market conditions closely.<?php endif;?></div></div>
    <div class="risk"><div class="risk-t">4. Permit Timeline</div><div>Vancouver multiplex permits average 6–14 months. <?= !empty($blueprint)?'Matched design ('.$blueprint['design_id'].') may reduce to ~4 months.':'A BC Provincial or CMHC pre-approved design can reduce timelines to ~4 months.' ?></div></div>
    <div class="risk"><div class="risk-t">5. Resale Absorption</div><div><?php if($comp_count>=5):?>Strong comparable sales (<?= $comp_count ?> comps) — healthy buyer absorption.<?php elseif($comp_count>=2):?>Moderate activity (<?= $comp_count ?> comps). Monitor pre-sale activity in adjacent areas.<?php else:?>Limited comp data. Consider rental path if strata pre-sales are slow.<?php endif;?></div></div>
    <?php if($heritage==='A'||$heritage==='B'):?><div class="risk crit"><div class="risk-t">⛔ Heritage Category <?= $heritage ?> — Permit Risk</div><div>HRA may be required. Expect 12–24+ month delays and $15,000–$40,000 in heritage consultant fees.</div></div><?php elseif($heritage==='C'):?><div class="risk act"><div class="risk-t">⚠ Heritage Category C — Inspection Required</div><div>Heritage inspection required before permit. Budget 4–8 additional weeks.</div></div><?php endif;?>
    <?php if($peat_zone):?><div class="risk act"><div class="risk-t">⚠ Peat Zone — Foundation Cost Uncertainty</div><div>Helical piles likely required. $150,000 contingency is an estimate — commission a geotechnical report before purchase.</div></div><?php endif;?>
    <?php if($covenant_present):?><div class="risk act"><div class="risk-t">⚠ Covenant / Easement — Title Review Required</div><div>Type: <?= htmlspecialchars($covenant_types) ?>. Obtain a full LTSA title search and legal review before proceeding.</div></div><?php endif;?>
</div>


<!-- BACK COVER -->
<div class="back">
    <div style="font-size:24px;font-weight:800;color:#c9a84c;letter-spacing:3px">WYNSTON</div>
    <div style="font-size:10px;color:rgba(249,246,240,.6);letter-spacing:4px;text-transform:uppercase;margin-bottom:20px">Intelligent Navigator · W.I.N</div>
    <div class="back-div"></div>
    <div class="agent-row">
        <div class="agent-ph"></div>
        <div>
            <div class="agent-name"><?= htmlspecialchars($agent_name) ?></div>
            <div class="agent-title"><?= htmlspecialchars($agent_title) ?></div>
            <div class="agent-bio"><?= htmlspecialchars($agent_bio) ?></div>
        </div>
    </div>
    <div class="back-div"></div>
    <div class="ct"><b>Website:</b> wynston.ca · Vancouver, BC</div>
    <div class="ct"><b>Report ID:</b> <?= $report_id ?> · Generated <?= date('F j, Y') ?></div>
    <div class="ct" style="margin-top:8px;font-size:11px">Construction mortgage professionals: contact Wynston for project-specific data packages.</div>
    <div class="back-div"></div>
    <div class="back-src"><strong style="color:#c9a84c">Data Sources:</strong> REBGV MLS (new builds 2024+) · City of Vancouver Open Data · BC Assessment · TransLink GTFS · CMHC · BC Stats BCPI · Stats Canada Census · RBC / TD / BMO / BCREA / RE/MAX / Royal LePage institutional forecasts</div>
    <div class="back-disc">This report is prepared by Wynston Concierge Real Estate for informational purposes only. It does not constitute financial, investment, legal, or real estate advice. All data sourced from publicly available datasets and proprietary market intelligence. Pro forma figures are estimates only and subject to change. Past performance does not guarantee future results. Always consult a licensed professional before making investment decisions. © Wynston Concierge Real Estate <?= date('Y') ?>. Report ID: <?= $report_id ?>.</div>
    <div class="back-gbar"></div>
</div>

</div><!-- /.report-wrap -->
<script>
if (new URLSearchParams(window.location.search).get('print')==='1') {
    window.addEventListener('load',function(){setTimeout(function(){window.print();},800);});
}
</script>
</body>
</html>