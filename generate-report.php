<?php
/**
 * Wynston W.I.N — Report Generator v2 (Session 13)
 * "The Architectural Ledger" design system
 * Fixed letter-size pages, institutional typography, no 1px borders
 * GET: pid, prepared_for, path (strata|rental)
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
$pro_forma_path = in_array($_GET['path'] ?? 'strata', ['strata','rental','outlook']) ? ($_GET['path'] ?? 'strata') : 'strata';
if (empty($pid)) die('PID required.');
$dev_id = (int)$_SESSION['dev_id'];

// ── Schema fixes ──────────────────────────────────────────────────────────────
try { $pdo->exec("ALTER TABLE developers ADD COLUMN IF NOT EXISTS subscription_tier ENUM('free','pro','white_label') DEFAULT 'free', ADD COLUMN IF NOT EXISTS report_logo_path VARCHAR(500) DEFAULT NULL, ADD COLUMN IF NOT EXISTS report_bio TEXT DEFAULT NULL, ADD COLUMN IF NOT EXISTS report_title VARCHAR(100) DEFAULT NULL, ADD COLUMN IF NOT EXISTS daily_report_limit INT DEFAULT 5, ADD COLUMN IF NOT EXISTS bonus_reports INT DEFAULT 0"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE pdf_log ADD COLUMN IF NOT EXISTS report_id VARCHAR(20) DEFAULT NULL"); } catch(PDOException $e){}

// ── Rate limit ────────────────────────────────────────────────────────────────
$dev_limits = $pdo->prepare("SELECT COALESCE(daily_report_limit,5) as dlimit, COALESCE(bonus_reports,0) as bonus FROM developers WHERE id=?");
$dev_limits->execute([$dev_id]);
$lrow = $dev_limits->fetch();
$daily_limit = (int)($lrow['dlimit'] ?? 5) + (int)($lrow['bonus'] ?? 0);
$r = $pdo->prepare("SELECT COUNT(*) FROM pdf_log WHERE developer_id=? AND DATE(generated_at)=CURDATE()");
$r->execute([$dev_id]);
if ((int)$r->fetchColumn() >= $daily_limit) die('Daily report limit reached ('.$daily_limit.'/day). Contact Wynston for higher limits.');

// ── Developer ─────────────────────────────────────────────────────────────────
$ds = $pdo->prepare("SELECT id,email,full_name,subscription_tier,report_logo_path,report_bio,report_title FROM developers WHERE id=?");
$ds->execute([$dev_id]); $developer = $ds->fetch();
if (!$developer) die('Account not found.');
$agent_name  = !empty($developer['full_name']) ? $developer['full_name'] : 'Tam Nguyen';
$agent_bio   = $developer['report_bio']   ?? 'Wynston specializes in missing middle multiplex development intelligence for Metro Vancouver.';
$agent_title = $developer['report_title'] ?? 'Realtor® · Wynston Concierge Real Estate';
$sub_tier    = $developer['subscription_tier'] ?? 'free';

// ── Lot ───────────────────────────────────────────────────────────────────────
// Read ALL constraint fields directly from plex_properties (the same source
// the map uses). Do NOT rely on constraint_flags — it may be sparsely populated.
// Also pull covenant/easement from constraint_flags as a secondary enrichment,
// but constraints (heritage, peat, floodplain) come from plex_properties first.
$ls = $pdo->prepare("SELECT * FROM plex_properties WHERE pid=? LIMIT 1");
$ls->execute([$pid]); $lot = $ls->fetch();
if (!$lot) die('Lot not found.');

// Secondary: covenant/easement from constraint_flags (these only live there)
$cf_row = [];
try {
    $cfs = $pdo->prepare("SELECT covenant_present, covenant_types, easement_present, easement_types FROM constraint_flags WHERE pid=? LIMIT 1");
    $cfs->execute([$pid]); $cf_row = $cfs->fetch() ?: [];
} catch(PDOException $e) {}

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
// Constraints — read from plex_properties (matches what lots.php / feasibility.php use)
$heritage         = $lot['heritage_category'] ?? 'none';
if (empty($heritage)) $heritage = 'none';
$peat_zone        = (bool)($lot['peat_zone'] ?? false);
$floodplain_risk  = $lot['floodplain_risk'] ?? 'none';
if (empty($floodplain_risk)) $floodplain_risk = 'none';
$in_floodplain    = ($floodplain_risk !== 'none');
// Covenant/easement from constraint_flags (secondary — graceful if missing)
$covenant_present = (bool)($cf_row['covenant_present'] ?? false);
$covenant_types   = $cf_row['covenant_types'] ?? '';
$assessed_land    = (int)($lot['assessed_land_value'] ?? 0);
if ($assessed_land===0 && $lot_area_sqm>0) $assessed_land = (int)($lot_area_sqm*10.7639*850);
$assessment_year  = $lot['assessment_year'] ?? date('Y');
$nb_slug          = wynston_resolve_slug($lot['neighbourhood_slug'] ?? '');

$nb_map = ['renfrew-collingwood'=>'Renfrew-Collingwood','mount-pleasant'=>'Mount Pleasant','hastings-sunrise'=>'Hastings-Sunrise','kensington-cedar-cottage'=>'Kensington-Cedar-Cottage','knight'=>'Knight','grandview-woodland'=>'Grandview-Woodland','victoria-fraserview'=>'Victoria-Fraserview','killarney'=>'Killarney','fraser-ve'=>'Fraser','south-marine'=>'South Marine','main'=>'Main','fairview-vw'=>'Fairview','kerrisdale'=>'Kerrisdale','marpole'=>'Marpole','oakridge'=>'Oakridge','south-cambie'=>'South Cambie','shaughnessy'=>'Shaughnessy','riley-park'=>'Riley Park','kitsilano'=>'Kitsilano','west-point-grey'=>'West Point Grey','downtown'=>'Downtown','west-end'=>'West End'];
$nb_display = $nb_map[$nb_slug] ?? ucwords(str_replace('-',' ',$nb_slug));

// ── Eligibility ───────────────────────────────────────────────────────────────
if ($lot_width_m>=15.1 && $lot_area_sqm>=557 && $transit_prox && $lane_access) {
    $max_units=6; $unit_label='6-Unit Multiplex'; $elig_label='6-Unit Eligible'; $elig_tier='tier-6';
} elseif ($lot_width_m>=10.0 && $lot_area_sqm>=306 && $lane_access) {
    $max_units=4; $unit_label='4-Unit Multiplex'; $elig_label='4-Unit Eligible'; $elig_tier='tier-4';
} elseif ($lot_width_m>=7.5 && $lot_area_sqm>=200 && $lane_access) {
    $max_units=3; $unit_label='Duplex / 3-Unit'; $elig_label='Duplex / 3-Unit'; $elig_tier='tier-d';
} else {
    $max_units=0; $unit_label='Below Minimum'; $elig_label='Below Minimum'; $elig_tier='tier-x';
}
$fsr = $pro_forma_path==='rental' ? 1.00 : 0.70;
$buildable_sqm  = $lot_area_sqm * $fsr;
$buildable_sqft = round($buildable_sqm * 10.7639);
$strata_sqft    = round($lot_area_sqm * 0.70 * 10.7639);
$rental_sqft    = round($lot_area_sqm * 1.00 * 10.7639);

// ── Market data ───────────────────────────────────────────────────────────────
$ms = $pdo->prepare("SELECT avg_sold_psf_duplex, avg_rent_1br, avg_rent_2br, avg_rent_3br, sales_count_duplex AS sales_count, data_month FROM monthly_market_stats WHERE neighbourhood_slug=? AND is_active=1 ORDER BY data_month DESC LIMIT 1");
$ms->execute([$nb_slug]); $market = $ms->fetch();
$metro = $pdo->query("SELECT AVG(avg_sold_psf_duplex) as p FROM monthly_market_stats WHERE is_active=1 AND data_month>=DATE_SUB(CURDATE(),INTERVAL 3 MONTH)")->fetch();
$current_psf = (float)($market['avg_sold_psf_duplex'] ?? $metro['p'] ?? 985);
$comp_count  = (int)($market['sales_count'] ?? 0);
$data_as_of  = !empty($market['data_month']) ? date('F Y', strtotime($market['data_month'])) : 'Metro Vancouver Benchmark';
$conf_label  = $comp_count>=5 ? 'High Confidence' : ($comp_count>=2 ? 'Moderate Confidence' : 'Indicative');
$conf_short  = $comp_count>=5 ? 'High' : ($comp_count>=2 ? 'Moderate' : 'Indicative');

// ── DOM ───────────────────────────────────────────────────────────────────────
$dom_data=[];
foreach(['duplex','detached'] as $dt) {
    try {
        $d2=$pdo->prepare("SELECT dom_{$dt} as v FROM neighbourhood_hpi_history WHERE neighbourhood_id=(SELECT id FROM neighbourhoods WHERE slug=? LIMIT 1) AND dom_{$dt}>0 ORDER BY month_year DESC LIMIT 2");
        $d2->execute([$nb_slug]); $rows=$d2->fetchAll();
        $curr=!empty($rows[0])?(int)$rows[0]['v']:null; $prev=!empty($rows[1])?(int)$rows[1]['v']:null;
        $diff=($curr&&$prev)?$curr-$prev:null;
        $dom_data[$dt]=['current'=>$curr,'diff'=>$diff,'arrow'=>$diff===null?'—':($diff<-1?'↓':($diff>1?'↑':'→')),'signal'=>$diff===null?'No data':($diff<-1?'Accelerating':($diff>1?'Slowing':'Stable'))];
    } catch(PDOException $e){ $dom_data[$dt]=['current'=>null,'diff'=>null,'arrow'=>'—','signal'=>'No data']; }
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

// ── CMHC MLI Select financing assumptions ────────────────────────────────────
$fa = null;
try { $fa = $pdo->query("SELECT * FROM financing_assumptions ORDER BY updated_at DESC LIMIT 1")->fetch(); } catch(PDOException $e){}
$fa_ltc        = (float)($fa['ltc_pct']              ?? 75)    / 100;
$fa_rate       = (float)($fa['interest_rate_pct']    ?? 5.25)  / 100;
$fa_amort      = (int)  ($fa['amortization_years']   ?? 40);
$fa_ins_prem   = (float)($fa['insurance_prem_pct']   ?? 4.00)  / 100;
$fa_cap_rate   = (float)($fa['market_cap_rate_pct']  ?? 4.50)  / 100;
$fa_vacancy    = (float)($fa['vacancy_rate_pct']     ?? 5.00)  / 100;
$fa_mgmt       = (float)($fa['mgmt_fee_pct']         ?? 8.00)  / 100;
$fa_ins_unit   = (float)($fa['insurance_per_unit']   ?? 150);
$fa_maint_unit = (float)($fa['maintenance_per_unit'] ?? 900);
$fa_tax_rate   = (float)($fa['property_tax_rate']    ?? 0.003);
$fa_name       = $fa['assumption_name'] ?? 'CMHC MLI Select';

// ── Shared cost base (both paths use same cost structure) ─────────────────────
// Strata uses 0.70 FSR, Rental/Outlook uses 1.00 FSR
$is_rental_path  = in_array($pro_forma_path, ['rental','outlook']);
$fsr_strata = 0.70; $fsr_rental = 1.00;

// Always calculate BOTH for comparison report
$strata_buildable_sqft = round($lot_area_sqm * $fsr_strata * 10.7639);
$rental_buildable_sqft = round($lot_area_sqm * $fsr_rental * 10.7639);

// Active path buildable
$buildable_sqft = $is_rental_path ? $rental_buildable_sqft : $strata_buildable_sqft;
$buildable_sqm  = $lot_area_sqm * ($is_rental_path ? $fsr_rental : $fsr_strata);

// ── Project costs — STRATA ────────────────────────────────────────────────────
$s_hard_build  = $strata_buildable_sqft * $build_psf;
$s_dcl_city    = $strata_buildable_sqft * 18.45;
$s_dcl_util    = $strata_buildable_sqft * 2.95;
$s_permit_fees = ($s_hard_build / 1000) * 13.70;
$s_peat_cost   = $peat_zone ? 150000 : 0;
$s_total_cost  = $assessed_land + $s_hard_build + $s_dcl_city + $s_dcl_util + $s_permit_fees + $s_peat_cost;

// ── Project costs — RENTAL ────────────────────────────────────────────────────
$r_density_bonus = ($lot_area_sqm * 0.30 * 10.7639) * 40;
$r_hard_build    = $rental_buildable_sqft * $build_psf;
$r_dcl_city      = $rental_buildable_sqft * 18.45;
$r_dcl_util      = $rental_buildable_sqft * 2.95;
$r_permit_fees   = ($r_hard_build / 1000) * 13.70;
$r_peat_cost     = $peat_zone ? 150000 : 0;
$r_total_cost    = $assessed_land + $r_hard_build + $r_density_bonus + $r_dcl_city + $r_dcl_util + $r_permit_fees + $r_peat_cost;

// Active path total cost
$total_cost    = $is_rental_path ? $r_total_cost : $s_total_cost;
$hard_build    = $is_rental_path ? $r_hard_build : $s_hard_build;
$density_bonus = $is_rental_path ? $r_density_bonus : 0;
$dcl_city      = $is_rental_path ? $r_dcl_city : $s_dcl_city;
$dcl_util      = $is_rental_path ? $r_dcl_util : $s_dcl_util;
$permit_fees   = $is_rental_path ? $r_permit_fees : $s_permit_fees;
$peat_cost     = $peat_zone ? 150000 : 0;

// ── STRATA exit calculations ──────────────────────────────────────────────────
$s_saleable    = $strata_buildable_sqft * 0.85;
$s_exit_value  = $s_saleable * $current_psf;
$s_profit      = $s_exit_value - $s_total_cost;
$s_roi         = $s_total_cost > 0 ? ($s_profit / $s_total_cost) * 100 : 0;

// Strata unit mix
$bw=[1=>0.75,2=>1.00,3=>1.35];
$bs=[6=>[1,2,2,2,2,3],4=>[1,2,2,3],3=>[2,2,2],2=>[2,2]];
$mb=$bs[min($max_units,6)]??[2,2];
$tw=0; foreach($mb as $b) $tw+=$bw[$b]??1;
$unit_mix=[];
foreach($mb as $b){
    $us=$tw>0?round($s_saleable*(($bw[$b]??1)/$tw)):round($s_saleable/count($mb));
    $unit_mix[]=['br'=>$b,'sqft'=>$us,'price'=>round($us*$current_psf)];
}

// ── RENTAL income calculations ────────────────────────────────────────────────
$cmhcs=$pdo->prepare("SELECT benchmark_1br, cmhc_rent_2br AS benchmark_2br, benchmark_3br FROM cmhc_benchmarks WHERE neighbourhood_slug=? ORDER BY year DESC LIMIT 1");
$cmhcs->execute([$nb_slug]); $cmhc_bench=$cmhcs->fetch();
$r1=(int)($market['avg_rent_1br']??$cmhc_bench['benchmark_1br']??2100);
$r2=(int)($market['avg_rent_2br']??$cmhc_bench['benchmark_2br']??2750);
$r3=(int)($market['avg_rent_3br']??$cmhc_bench['benchmark_3br']??3200);
$c1=(int)($cmhc_bench['benchmark_1br']??1875);
$c2=(int)($cmhc_bench['benchmark_2br']??2400);
$c3=(int)($cmhc_bench['benchmark_3br']??2900);

$rental_rows=[
    ['t'=>'1BR','curr'=>$r1,'cmhc'=>$c1,'units'=>($max_units>=6?2:1)],
    ['t'=>'2BR','curr'=>$r2,'cmhc'=>$c2,'units'=>($max_units>=6?3:($max_units>=4?2:1))],
    ['t'=>'3BR','curr'=>$r3,'cmhc'=>$c3,'units'=>1],
];

$r_gross_monthly = $max_units>=6 ? (2*$r1+3*$r2+$r3) : ($max_units===4 ? ($r1+2*$r2+$r3) : ($r1+$r2));
$r_gross_annual  = $r_gross_monthly * 12;

// EGI after vacancy
$r_egi = $r_gross_annual * (1 - $fa_vacancy);

// Operating expenses (detailed)
$r_prop_tax  = $r_total_cost * $fa_tax_rate;        // property tax on post-completion assessed value
$r_insurance = $fa_ins_unit  * $max_units;           // building insurance
$r_maint     = $fa_maint_unit * $max_units;          // maintenance & repairs
$r_mgmt_fee  = $r_egi * $fa_mgmt;                   // property management
$r_total_opex = $r_prop_tax + $r_insurance + $r_maint + $r_mgmt_fee;

// NOI
$r_noi = $r_egi - $r_total_opex;

// CMHC MLI Select mortgage
$r_loan_base   = $r_total_cost * $fa_ltc;
$r_ins_amount  = $r_loan_base * $fa_ins_prem;
$r_loan_total  = $r_loan_base + $r_ins_amount;       // insured loan amount
$r_monthly_rate = $fa_rate / 12;
$r_n_payments   = $fa_amort * 12;
// Standard amortizing mortgage payment formula
if ($r_monthly_rate > 0) {
    $r_monthly_pmt = $r_loan_total * ($r_monthly_rate * pow(1+$r_monthly_rate,$r_n_payments)) / (pow(1+$r_monthly_rate,$r_n_payments)-1);
} else {
    $r_monthly_pmt = $r_loan_total / $r_n_payments;
}
$r_annual_debt  = $r_monthly_pmt * 12;
$r_equity       = $r_total_cost * (1 - $fa_ltc);

// Cash flow & returns
$r_cash_flow    = $r_noi - $r_annual_debt;
$r_coc_return   = $r_equity > 0 ? ($r_cash_flow / $r_equity) * 100 : 0;
$r_cap_rate     = $r_total_cost > 0 ? ($r_noi / $r_total_cost) * 100 : 0;
$r_payback      = $r_noi > 0 ? $r_total_cost / $r_noi : 0;
$r_asset_value  = $fa_cap_rate > 0 ? $r_noi / $fa_cap_rate : 0;
$r_value_vs_cost = $r_asset_value - $r_total_cost;  // day-1 equity gain/loss

// Active path summary vars (used by shared sections like cover, back cover)
if ($pro_forma_path === 'strata') {
    $exit_value = $s_exit_value; $profit = $s_profit; $roi = $s_roi; $saleable = $s_saleable;
} elseif ($pro_forma_path === 'rental') {
    $exit_value = $r_noi; $profit = $r_cash_flow; $roi = $r_cap_rate; $saleable = $rental_buildable_sqft;
} else { // outlook — combined
    $exit_value = $s_exit_value; $profit = $s_profit; $roi = $s_roi; $saleable = $s_saleable;
}

$current_margin = $current_psf - $build_psf;

// ── Outlook ───────────────────────────────────────────────────────────────────
$outlook_pct=null; $outlook_psf=null; $proj_margin=null; $outlook_data=null; $outlook_quarter=null; $outlook_sources=[];
try {
    $os=$pdo->prepare("SELECT weighted_outlook, macro_signal, local_momentum, pipeline_signal, confidence_band_low, confidence_band_high, quarter, confidence_tier FROM wynston_outlook WHERE neighbourhood_slug=? AND is_active=1 ORDER BY calculated_at DESC LIMIT 1");
    $os->execute([$nb_slug]); $or2=$os->fetch();
    if ($or2) {
        $outlook_pct  = (float)$or2['weighted_outlook'];
        $outlook_psf  = round($current_psf * (1 + $outlook_pct / 100), 2);
        $proj_margin  = $outlook_psf - $build_psf;
        $outlook_quarter = $or2['quarter'];
        $tier_n = (int)$or2['confidence_tier'];
        $mw = $tier_n===1 ? 0.40 : ($tier_n===2 ? 0.55 : 0.70);
        $lw = $tier_n===1 ? 0.40 : ($tier_n===2 ? 0.25 : 0.10);
        $pw = 0.10; $pw2 = 0.10;
        $outlook_data = ['macro'=>(float)$or2['macro_signal'],'local'=>(float)$or2['local_momentum'],'pipeline'=>(float)$or2['pipeline_signal'],'population'=>0,'mw'=>$mw,'lw'=>$lw,'pw'=>$pw,'pw2'=>$pw2,'low'=>(float)$or2['confidence_band_low'],'high'=>(float)$or2['confidence_band_high']];
        $srcs=$pdo->query("SELECT DISTINCT source_name FROM wynston_outlook_inputs WHERE is_active=1 ORDER BY id LIMIT 6");
        $outlook_sources=$srcs->fetchAll(PDO::FETCH_COLUMN);
    }
} catch(PDOException $e){}

// ── Blueprint ─────────────────────────────────────────────────────────────────
$blueprint=null;
try { $bps=$pdo->prepare("SELECT * FROM design_catalogue WHERE is_active=1 AND min_lot_width<=? AND max_lot_width>=? AND (transit_required=0 OR (transit_required=1 AND ?=1)) ORDER BY min_lot_width DESC LIMIT 1"); $bps->execute([$lot_width_m,$lot_width_m,(int)$transit_prox]); $blueprint=$bps->fetch()?:null; } catch(PDOException $e){}

// ── Nearest stop ──────────────────────────────────────────────────────────────
$stop_name=null; $stop_dist=null;
if ($lat&&$lng) { try { $ss=$pdo->prepare("SELECT stop_name,(6371000*ACOS(COS(RADIANS(?))*COS(RADIANS(stop_lat))*COS(RADIANS(stop_lng)-RADIANS(?))+SIN(RADIANS(?))*SIN(RADIANS(stop_lat)))) AS d FROM transit_stops ORDER BY d ASC LIMIT 1"); $ss->execute([$lat,$lng,$lat]); $sr=$ss->fetch(); if($sr){$stop_name=$sr['stop_name'];$stop_dist=round($sr['d']);} } catch(PDOException $e){} }

// ── Aerial ────────────────────────────────────────────────────────────────────
$aerial_base64='';
if ($lat&&$lng) {
    $pin="pin-l+c9a84c({$lng},{$lat})";
    $url="https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/static/{$pin}/{$lng},{$lat},17,0/800x500@2x?access_token=pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg";
    $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_FOLLOWLOCATION=>true]);
    $data=curl_exec($ch); curl_close($ch);
    if ($data&&strlen($data)>1000) $aerial_base64=base64_encode($data);
}

// ── Static map (greyscale for constraints page) ──────────────────────────────
$map_base64='';
if ($lat&&$lng) {
    $pin2="pin-l+002446({$lng},{$lat})";
    $url2="https://api.mapbox.com/styles/v1/mapbox/light-v11/static/{$pin2}/{$lng},{$lat},16,0/700x400@2x?access_token=pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg";
    $ch2=curl_init($url2); curl_setopt_array($ch2,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_FOLLOWLOCATION=>true]);
    $data2=curl_exec($ch2); curl_close($ch2);
    if ($data2&&strlen($data2)>1000) $map_base64=base64_encode($data2);
}

// ── Next step ─────────────────────────────────────────────────────────────────
if ($elig_label==='6-Unit Eligible') $next_step="This lot qualifies for a 6-unit strata multiplex. Contact Wynston to begin acquisition analysis.";
elseif ($elig_label==='4-Unit Eligible') $next_step="This lot qualifies for a 4-unit multiplex. Contact Wynston to review acquisition opportunities.";
elseif ($elig_label==='Duplex / 3-Unit') $next_step="This lot qualifies for a duplex or 3-unit build. Consider neighbour buyout to unlock higher density.";
else $next_step="This lot does not meet current minimums. Contact Wynston to discuss assembly strategies.";

// ── Agent logo ─────────────────────────────────────────────────────────────────
$logo_b64=''; $logo_mime='image/png';
$lp=$developer['report_logo_path']??null;
$lf=$lp?__DIR__.'/'.ltrim($lp,'/'):null;
if($lf&&file_exists($lf)){
    $lext=strtolower(pathinfo($lf,PATHINFO_EXTENSION));
    $logo_mime=in_array($lext,['jpg','jpeg'])?'image/jpeg':($lext==='png'?'image/png':'image/webp');
    $logo_b64=base64_encode(file_get_contents($lf));
}

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
function sign($n) { return $n>=0?'+':'-'; }

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Wynston Report — <?= htmlspecialchars($address) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,600;0,700;1,400;1,700&family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ─── DESIGN TOKENS ────────────────────────────────────────────────────────── */
:root {
    --primary:               #000a1e;
    --primary-container:     #002147;
    --surface:               #f8f9fa;
    --surface-low:           #f3f4f5;
    --surface-lowest:        #ffffff;
    --surface-high:          #e7e8e9;
    --surface-highest:       #e1e3e4;
    --on-surface:            #000a1e;
    --on-surface-var:        #2d3a52;
    --on-primary:            #ffffff;
    --on-primary-container:  #708ab5;
    --tertiary-fixed:        #ffdea5;
    --tertiary-fixed-dim:    #e9c176;
    --outline-variant:       #c4c6cf;
    --error:                 #ba1a1a;
    --success:               #1a6634;
}

/* ─── RESET ─────────────────────────────────────────────────────────────────── */
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:#d9dadb;font-family:'Work Sans',sans-serif;font-size:13px;color:var(--on-surface);-webkit-font-smoothing:antialiased}

/* ─── PRINT BAR (screen only) ────────────────────────────────────────────────── */
.print-bar{
    position:sticky;top:0;z-index:200;
    background:var(--primary);
    padding:12px 32px;
    display:flex;align-items:center;justify-content:space-between;
    font-family:'Work Sans',sans-serif;
}
.print-bar-logo{
    font-family:'Noto Serif',serif;
    font-style:italic;
    font-size:15px;
    letter-spacing:.15em;
    color:var(--tertiary-fixed);
}
.print-bar-id{font-size:11px;color:rgba(255,255,255,.4);letter-spacing:.05em}
.pb-actions{display:flex;gap:10px;align-items:center}
.btn-back{
    font-family:'Work Sans',sans-serif;font-size:11px;font-weight:500;
    letter-spacing:.1em;text-transform:uppercase;
    color:rgba(255,255,255,.6);background:transparent;border:none;
    text-decoration:none;cursor:pointer;padding:8px 16px;
    border:1px solid rgba(255,255,255,.2);
}
.btn-back:hover{color:#fff;border-color:rgba(255,255,255,.5)}
.btn-print{
    font-family:'Work Sans',sans-serif;font-size:11px;font-weight:600;
    letter-spacing:.12em;text-transform:uppercase;
    background:var(--tertiary-fixed);color:var(--primary);
    border:none;cursor:pointer;padding:10px 24px;
}

/* ─── PAGE WRAPPER ───────────────────────────────────────────────────────────── */
.report{width:816px;margin:24px auto 48px;background:var(--surface-lowest)}

/* ─── PAGE BASE: every .page is exactly letter size ─────────────────────────── */
.page{
    width:816px;
    min-height:1056px;
    position:relative;
    overflow:hidden;
    page-break-after:always;
    break-after:page;
    background:var(--surface-lowest);
}
.page:last-child{page-break-after:avoid;break-after:avoid}

/* ─── MICRO LABEL ────────────────────────────────────────────────────────────── */
.label-xs{
    font-family:'Work Sans',sans-serif;
    font-size:9px;font-weight:600;
    letter-spacing:.18em;text-transform:uppercase;
    color:var(--on-surface-var);
}
.label-xs.gold{color:var(--tertiary-fixed-dim)}
.label-xs.light{color:rgba(255,255,255,.5)}

/* ─── PAGE 1 — COVER ─────────────────────────────────────────────────────────── */
.cover{background:var(--primary);color:var(--on-primary)}
.cover-inner{padding:56px 64px;display:flex;flex-direction:column;min-height:1056px}
.cover-wordmark{
    font-family:'Noto Serif',serif;font-style:italic;
    font-size:13px;letter-spacing:.25em;color:var(--tertiary-fixed);
    margin-bottom:auto;
}
.cover-address-block{margin-top:40px}
.cover-address-label{
    font-family:'Work Sans',sans-serif;font-size:9px;font-weight:600;
    letter-spacing:.2em;text-transform:uppercase;
    color:rgba(255,255,255,.4);margin-bottom:12px;
}
.cover-address{
    font-family:'Noto Serif',serif;font-style:italic;
    font-size:52px;font-weight:400;line-height:1.05;
    color:#fff;margin-bottom:8px;
}
.cover-pid{
    font-family:'Work Sans',sans-serif;font-size:11px;font-weight:400;
    color:rgba(255,255,255,.4);letter-spacing:.05em;margin-bottom:40px;
}
.cover-aerial{
    width:100%;height:340px;object-fit:cover;
    display:block;
    filter:brightness(.9) saturate(.9);
    margin-bottom:0;
}
.cover-aerial-ph{
    width:100%;height:340px;
    background:var(--primary-container);
    display:flex;align-items:center;justify-content:center;
    color:rgba(255,255,255,.2);font-size:12px;letter-spacing:.1em;
}
.cover-stats{
    display:grid;grid-template-columns:1fr 1fr 1fr;
    border-top:1px solid rgba(255,255,255,.1);
    margin-top:0;
}
.cover-stat{
    padding:24px 0;
    border-right:1px solid rgba(255,255,255,.1);
}
.cover-stat:last-child{border-right:none}
.cover-stat-label{font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px}
.cover-stat-val{font-family:'Noto Serif',serif;font-size:26px;color:#fff;font-weight:400}
.cover-stat-sub{font-size:10px;color:rgba(255,255,255,.35);margin-top:4px}
.cover-footer{
    margin-top:auto;padding-top:24px;
    border-top:1px solid rgba(255,255,255,.08);
    display:flex;justify-content:space-between;align-items:flex-end;
    padding-bottom:4px;
}
.cover-footer-left{font-size:10px;color:rgba(255,255,255,.25);line-height:1.6;max-width:420px}
.cover-badges{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.badge-elig{
    font-family:'Work Sans',sans-serif;font-size:10px;font-weight:600;
    letter-spacing:.1em;text-transform:uppercase;
    padding:6px 14px;
}
.tier-6{background:var(--success);color:#fff}
.tier-4{background:#0f766e;color:#fff}
.tier-d{background:#b45309;color:#fff}
.tier-x{background:#475569;color:#fff}
.badge-conf{
    font-family:'Work Sans',sans-serif;font-size:10px;font-weight:500;
    letter-spacing:.06em;padding:5px 12px;
    border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.7);
}

/* ─── SECTION HEADER ─────────────────────────────────────────────────────────── */
.page-header{
    background:var(--primary);
    padding:24px 64px 20px;
    display:flex;align-items:baseline;justify-content:space-between;
}
.page-header-title{
    font-family:'Noto Serif',serif;font-style:italic;
    font-size:32px;color:#fff;font-weight:400;
}
.page-header-meta{font-size:10px;color:rgba(255,255,255,.35);letter-spacing:.05em}

/* ─── PAGE BODY ──────────────────────────────────────────────────────────────── */
.page-body{padding:40px 64px 48px}
.page-body.tight{padding:28px 64px 36px}

/* ─── DIVIDER — tonal shift, no line ────────────────────────────────────────── */
.tonal-divider{height:1px;background:var(--outline-variant);opacity:.2;margin:28px 0}

/* ─── METRIC ROW (3-column, tonal) ─────────────────────────────────────────── */
.metric-row{display:grid;grid-template-columns:repeat(3,1fr);background:var(--surface-low)}
.metric-cell{padding:28px 24px;border-right:1px solid rgba(0,0,0,.04)}
.metric-cell:last-child{border-right:none}
.metric-cell.dark{background:var(--primary-container)}
.metric-val{
    font-family:'Noto Serif',serif;font-size:34px;font-weight:400;
    color:var(--on-surface);line-height:1;margin-top:10px;
}
.metric-cell.dark .metric-val{color:var(--tertiary-fixed)}
.metric-sub{font-size:11px;color:var(--on-surface-var);margin-top:6px}
.metric-cell.dark .metric-sub{color:rgba(255,255,255,.4)}

/* ─── STAT STRIP (2-col tonal) ─────────────────────────────────────────────── */
.stat-strip{display:grid;grid-template-columns:1fr 1fr;background:var(--surface-low)}
.stat-strip-3{display:grid;grid-template-columns:1fr 1fr 1fr;background:var(--surface-low)}
.stat-cell{padding:20px 24px;border-right:1px solid rgba(0,0,0,.04)}
.stat-cell:last-child{border-right:none}
.stat-val{
    font-family:'Noto Serif',serif;font-size:24px;font-weight:400;
    color:var(--on-surface);margin-top:8px;
}
.stat-sub{font-size:10px;color:var(--on-surface-var);margin-top:4px}

/* ─── DARK BLOCK ─────────────────────────────────────────────────────────────── */
.dark-block{background:var(--primary);padding:32px 64px;color:#fff}
.dark-block-title{
    font-family:'Noto Serif',serif;font-style:italic;
    font-size:22px;color:var(--tertiary-fixed);margin-bottom:20px;
}
.pathway-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px}
.pathway-label{font-size:9px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:6px}
.pathway-name{font-family:'Noto Serif',serif;font-size:20px;color:var(--tertiary-fixed);margin-bottom:16px}
.pathway-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);font-size:12px}
.pathway-row:last-child{border-bottom:none}
.pathway-row .k{color:rgba(255,255,255,.4)}
.pathway-row .v{color:#fff;font-weight:500}
.dark-block-note{
    margin-top:20px;padding:14px 16px;
    background:var(--primary-container);
    border-left:2px solid var(--tertiary-fixed);
    font-size:11px;color:rgba(255,255,255,.55);line-height:1.6;font-style:italic;
}

/* ─── ELIGIBILITY CHECKLIST ─────────────────────────────────────────────────── */
.elig-list{margin:0;padding:0;list-style:none}
.elig-item{
    display:flex;align-items:flex-start;gap:16px;
    padding:16px 0;border-bottom:1px solid rgba(0,0,0,.05);
}
.elig-item:last-child{border-bottom:none}
.elig-check{
    width:20px;height:20px;flex-shrink:0;margin-top:1px;
    display:flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:700;
}
.elig-check.pass{border:1.5px solid var(--success);color:var(--success)}
.elig-check.fail{border:1.5px solid var(--error);color:var(--error)}
.elig-check.warn{border:1.5px solid #b45309;color:#b45309}
.elig-check.na{border:1.5px solid var(--outline-variant);color:var(--outline-variant)}
.elig-text-title{font-size:13px;font-weight:500;color:var(--on-surface);margin-bottom:3px}
.elig-text-sub{font-size:11px;color:var(--on-surface-var);line-height:1.5}

/* ─── CONSTRAINT CHECKLIST ──────────────────────────────────────────────────── */
.constraint-list{margin:0;padding:0;list-style:none}
.constraint-item{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid rgba(0,0,0,.05)}
.constraint-item:last-child{border-bottom:none}
.constraint-icon{
    width:18px;height:18px;flex-shrink:0;margin-top:2px;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:700;
}
.constraint-icon.clear{border:1.5px solid var(--success);color:var(--success)}
.constraint-icon.warn{border:1.5px solid #b45309;color:#b45309}
.constraint-icon.crit{border:1.5px solid var(--error);color:var(--error)}
.constraint-icon.info{border:1.5px solid var(--outline-variant);color:var(--on-surface-var)}
.constraint-title{font-size:12px;font-weight:600;color:var(--on-surface);margin-bottom:2px}
.constraint-sub{font-size:11px;color:var(--on-surface-var)}

/* ─── MAP IMAGE ──────────────────────────────────────────────────────────────── */
.map-container{
    position:relative;background:var(--surface-low);
    width:100%;height:320px;overflow:hidden;
}
.map-container img{width:100%;height:100%;object-fit:cover;filter:grayscale(30%) contrast(110%)}
.map-pin-label{
    position:absolute;bottom:16px;left:50%;transform:translateX(-50%);
    background:var(--surface-lowest);padding:6px 14px;
    font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;
    color:var(--on-surface);border:1px solid rgba(0,0,0,.08);
    white-space:nowrap;
}

/* ─── PSF THREE-NUMBER ROW ──────────────────────────────────────────────────── */
.psf-trio{display:grid;grid-template-columns:1fr 1fr 1fr;margin:0}
.psf-cell{padding:24px;background:var(--surface-low);border-right:1px solid rgba(0,0,0,.04)}
.psf-cell:last-child{border-right:none;background:var(--primary)}
.psf-label{font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--on-surface-var);margin-bottom:10px}
.psf-cell:last-child .psf-label{color:rgba(255,255,255,.4)}
.psf-val{font-family:'Noto Serif',serif;font-size:32px;color:var(--on-surface)}
.psf-cell:last-child .psf-val{color:var(--tertiary-fixed)}
.psf-note{font-size:10px;color:var(--on-surface-var);margin-top:6px}
.psf-cell:last-child .psf-note{color:rgba(255,255,255,.35)}

/* ─── MARGIN CALLOUT ────────────────────────────────────────────────────────── */
.margin-callout{
    background:var(--surface-low);
    padding:16px 24px;
    display:flex;justify-content:space-between;align-items:center;
    margin:2px 0;
}
.margin-callout-label{font-size:12px;color:var(--on-surface-var)}
.margin-callout-val{font-family:'Noto Serif',serif;font-size:22px;color:var(--on-surface)}
.margin-callout-delta{font-size:12px;font-weight:500;padding-left:12px}
.delta-up{color:var(--success)}
.delta-dn{color:var(--error)}

/* ─── DATA TABLE ─────────────────────────────────────────────────────────────── */
table.dt{width:100%;border-collapse:collapse;font-size:11px;margin:0}
table.dt th{
    font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;
    color:var(--on-surface-var);
    padding:10px 16px;background:var(--surface-low);
    text-align:left;border-bottom:1px solid rgba(0,0,0,.06);
}
table.dt td{
    padding:11px 16px;border-bottom:1px solid rgba(0,0,0,.04);
    color:var(--on-surface);vertical-align:middle;
}
table.dt tr:last-child td{border-bottom:none}
.dt-mono{font-family:'Noto Serif',serif;font-size:14px}
.dt-pass{color:var(--success);font-weight:600}
.dt-fail{color:var(--error);font-weight:600}
.dt-warn{color:#b45309;font-weight:600}
.dt-neutral{color:var(--on-surface-var)}

/* ─── PRO FORMA LINES ────────────────────────────────────────────────────────── */
.pf-section-title{
    font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;
    color:var(--on-surface-var);padding:16px 0 8px;
    border-bottom:1px solid rgba(0,0,0,.06);margin-bottom:4px;
}
.pf-line{display:flex;justify-content:space-between;padding:7px 0;font-size:12px;border-bottom:1px solid rgba(0,0,0,.03)}
.pf-line:last-child{border-bottom:none}
.pf-line.indent{padding-left:16px}
.pf-line .pf-label{color:var(--on-surface-var)}
.pf-line .pf-val{font-weight:500;color:var(--on-surface)}
.pf-total-bar{
    background:var(--primary);padding:14px 20px;
    display:flex;justify-content:space-between;align-items:center;
    margin-top:4px;
}
.pf-total-label{font-size:12px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.04em}
.pf-total-val{font-family:'Noto Serif',serif;font-size:24px;color:var(--tertiary-fixed)}
.profit-block{
    background:var(--surface-low);
    padding:24px;margin-top:16px;
    display:flex;justify-content:space-between;align-items:center;
}
.profit-block.positive{border-left:3px solid var(--success)}
.profit-block.negative{border-left:3px solid var(--error)}
.profit-label{font-size:11px;color:var(--on-surface-var);margin-bottom:4px}
.profit-sub{font-size:10px;color:var(--on-surface-var);margin-top:6px}
.profit-val{font-family:'Noto Serif',serif;font-size:40px;font-weight:400;color:var(--on-surface)}
.profit-val.positive{color:var(--success)}
.profit-val.negative{color:var(--error)}

/* ─── OUTLOOK LAYERS ────────────────────────────────────────────────────────── */
.outlook-layer-header{
    display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1.5fr;
    font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;
    color:var(--on-surface-var);padding:10px 0;border-bottom:2px solid rgba(0,0,0,.08);
    margin-bottom:0;
}
.outlook-layer-row{
    display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1.5fr;
    padding:13px 0;border-bottom:1px solid rgba(0,0,0,.04);
    font-size:12px;
}
.outlook-layer-row.total{
    background:var(--surface-low);margin:0 -20px;padding:13px 20px;
    border-bottom:none;font-weight:600;
}
.ol-name{color:var(--on-surface)}
.ol-sig{color:var(--on-surface);text-align:center}
.ol-wt{color:var(--on-surface-var);text-align:center}
.ol-ct{color:var(--on-surface);text-align:center;font-weight:600}
.ol-src{color:var(--on-surface-var);text-align:right;font-size:10px}

/* ─── RISK ITEMS ─────────────────────────────────────────────────────────────── */
.risk-item{padding:16px 0;border-bottom:1px solid rgba(0,0,0,.05);display:flex;gap:16px}
.risk-item:last-child{border-bottom:none}
.risk-num{
    font-family:'Noto Serif',serif;font-size:13px;color:var(--on-surface-var);
    width:20px;flex-shrink:0;padding-top:1px;
}
.risk-body{}
.risk-title{font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:4px}
.risk-desc{font-size:11px;color:var(--on-surface-var);line-height:1.6}
.risk-item.active .risk-title{color:#b45309}
.risk-item.critical .risk-title{color:var(--error)}

/* ─── BACK COVER ─────────────────────────────────────────────────────────────── */
.back-cover{background:var(--primary);color:#fff;height:1056px;padding:56px 64px;display:flex;flex-direction:column;overflow:hidden}
.back-logo{font-family:'Noto Serif',serif;font-style:italic;font-size:28px;color:var(--tertiary-fixed);letter-spacing:.2em;margin-bottom:4px}
.back-tagline{font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:0}
.back-agent-block{display:flex;gap:32px;align-items:flex-start;padding:28px 0;border-top:1px solid rgba(255,255,255,.1);border-bottom:1px solid rgba(255,255,255,.1);margin-top:24px}
.back-logo-img{max-width:100px;max-height:100px;object-fit:contain;mix-blend-mode:screen;flex-shrink:0}
.back-logo-ph{width:80px;height:80px;background:var(--primary-container);border:1px solid rgba(255,255,255,.1);flex-shrink:0}
.back-agent-name{font-family:'Noto Serif',serif;font-size:20px;color:var(--tertiary-fixed);margin-bottom:4px}
.back-agent-title{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:12px}
.back-agent-bio{font-size:11px;color:rgba(255,255,255,.6);line-height:1.6}
.back-contacts{margin-top:20px;display:flex;flex-direction:column;gap:6px}
.back-contact-line{font-size:11px;color:rgba(255,255,255,.5)}
.back-contact-line span{color:rgba(255,255,255,.25);margin-right:8px;font-size:9px;letter-spacing:.1em;text-transform:uppercase}
.back-nextstep{margin-top:20px;padding:20px 24px;background:var(--primary-container);border-left:2px solid var(--tertiary-fixed);flex-shrink:0}
.back-nextstep-label{font-size:9px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:8px}
.back-nextstep-text{font-size:13px;color:rgba(255,255,255,.8);line-height:1.6}
.back-sources{margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
.back-sources-label{font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:6px}
.back-sources-text{font-size:10px;color:rgba(255,255,255,.3);line-height:1.6}
.back-disclaimer{font-size:9px;color:rgba(255,255,255,.2);line-height:1.6;margin-top:8px}
.back-gold-bar{background:var(--tertiary-fixed);height:4px;margin:20px -64px 0;flex-shrink:0}

/* ─── INLINE CALLOUT BOXES ───────────────────────────────────────────────────── */
.callout{
    padding:14px 20px;margin:12px 0;font-size:11px;line-height:1.6;
    background:var(--surface-low);border-left:2px solid var(--tertiary-fixed-dim);
    color:var(--on-surface-var);
}
.callout strong{color:var(--on-surface)}
.callout.warn{border-left-color:#b45309;background:#fef9f0}
.callout.crit{border-left-color:var(--error);background:#fff5f5}
.callout.pass{border-left-color:var(--success);background:#f0fdf4}
.callout.buyout{
    border-left:none;background:var(--primary-container);color:rgba(255,255,255,.7);
    border:1px solid rgba(255,255,255,.1);padding:16px 20px;
}
.callout.buyout strong{color:var(--tertiary-fixed)}

/* ─── CONFIDENCE BADGE ───────────────────────────────────────────────────────── */
.conf-inline{display:inline-block;padding:4px 10px;font-size:10px;font-weight:600;letter-spacing:.06em;background:var(--surface-high);color:var(--on-surface-var)}

/* ─── PRINT OVERRIDES ────────────────────────────────────────────────────────── */
@media print {
    @page{margin:0;size:letter portrait}
    @page:first{margin:0}
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important}
    html,body{margin:0;padding:0;background:#fff}
    .print-bar{display:none!important}
    .report{width:100%;margin:0;box-shadow:none}
    .page{width:100%;margin:0;box-shadow:none}
    /* Back cover: exact page height — no overflow, no blank continuation */
    .back-cover{height:100vh!important;padding:40px 48px!important;overflow:hidden!important}
    .back-gold-bar{margin:16px -48px 0!important}
    /* Only prevent breaks on atomic elements */
    .metric-cell{page-break-inside:avoid;break-inside:avoid}
    .psf-cell{page-break-inside:avoid;break-inside:avoid}
    .elig-item{page-break-inside:avoid;break-inside:avoid}
    .constraint-item{page-break-inside:avoid;break-inside:avoid}
    .risk-item{page-break-inside:avoid;break-inside:avoid}
    tr{page-break-inside:avoid;break-inside:avoid}
    .pf-total-bar{page-break-inside:avoid;break-inside:avoid}
    .profit-block{page-break-inside:avoid;break-inside:avoid}
    .pathway-grid{page-break-inside:avoid;break-inside:avoid}
    .stat-strip,.stat-strip-3{page-break-inside:avoid;break-inside:avoid}
    .back-nextstep{page-break-inside:avoid;break-inside:avoid}
}
</style>
</head>
<body>

<!-- PRINT BAR -->
<div class="print-bar">
    <div>
        <div class="print-bar-logo">WYNSTON</div>
        <div class="print-bar-id">Report <?= $report_id ?> · <?= htmlspecialchars($address) ?></div>
    </div>
    <div class="pb-actions">
        <a class="btn-back" href="javascript:history.back()">← Map</a>
        <button class="btn-print" onclick="window.print()">Save as PDF</button>
    </div>
</div>

<div class="report">

<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 1 — COVER
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page cover">
<div class="cover-inner">

    <div class="cover-wordmark">WYNSTON — <?= $pro_forma_path==='rental' ? 'Secured Rental Analysis' : ($pro_forma_path==='outlook' ? 'Development Path Comparison' : 'Intelligent Navigator') ?></div>

    <div class="cover-address-block">
        <div class="cover-address-label">Subject Property</div>
        <div class="cover-address"><?= htmlspecialchars($address) ?></div>
        <div class="cover-pid">PID <?= htmlspecialchars($pid_fmt) ?> · R1-1 Zoning · City of Vancouver</div>
    </div>

    <?php if (!empty($aerial_base64)): ?>
        <img class="cover-aerial" src="data:image/jpeg;base64,<?= $aerial_base64 ?>" alt="Aerial view">
    <?php else: ?>
        <div class="cover-aerial-ph">Aerial imagery unavailable</div>
    <?php endif; ?>

    <div class="cover-stats">
        <div class="cover-stat" style="padding-left:0">
            <div class="cover-stat-label">Development Tier</div>
            <div class="cover-stat-val" style="font-size:20px;margin-top:6px">
                <span class="badge-elig <?= $elig_tier ?>" style="display:inline-block"><?= htmlspecialchars($elig_label) ?></span>
            </div>
        </div>
        <div class="cover-stat" style="padding-left:24px">
            <div class="cover-stat-label">Frontage</div>
            <div class="cover-stat-val"><?= $lot_width_ft ?> <span style="font-size:16px;color:rgba(255,255,255,.4)">ft</span></div>
            <div class="cover-stat-sub"><?= number_format($lot_width_m,2) ?> m</div>
        </div>
        <div class="cover-stat" style="padding-left:24px">
            <div class="cover-stat-label">Data Confidence</div>
            <div class="cover-stat-val" style="font-size:20px;margin-top:6px">
                <span class="badge-conf"><?= htmlspecialchars($conf_short) ?></span>
            </div>
        </div>
    </div>

    <div class="cover-footer">
        <div class="cover-footer-left">
            Prepared for <?= htmlspecialchars($prepared_for) ?> · Report <?= $report_id ?> · <?= date('F j, Y') ?><br>
            Confidential. For informational purposes only. Not financial or investment advice.
        </div>
        <div style="font-size:10px;color:rgba(255,255,255,.2);text-align:right;line-height:1.6">
            Data as of <?= htmlspecialchars($data_as_of) ?><br>
            <?= htmlspecialchars($nb_display) ?> · Vancouver, BC
        </div>
    </div>

</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 2 — SITE BUILDABILITY
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Site Buildability.</div>
    <div class="page-header-meta">Dimensional analysis · <?= htmlspecialchars($pid_fmt) ?></div>
</div>

<!-- Lot dimensions tonal strip -->
<div class="stat-strip-3">
    <div class="stat-cell">
        <div class="label-xs">Frontage</div>
        <div class="stat-val"><?= $lot_width_ft ?> <span style="font-size:14px;color:var(--on-surface-var)">ft</span></div>
        <div class="stat-sub"><?= number_format($lot_width_m,2) ?> m</div>
    </div>
    <div class="stat-cell">
        <div class="label-xs">Depth</div>
        <div class="stat-val"><?= $lot_depth_ft>0 ? $lot_depth_ft.' <span style="font-size:14px;color:var(--on-surface-var)">ft</span>' : '<span style="font-size:18px;color:var(--on-surface-var)">—</span>' ?></div>
        <div class="stat-sub"><?= $lot_depth_m>0 ? number_format($lot_depth_m,2).' m' : 'Not recorded' ?></div>
    </div>
    <div class="stat-cell">
        <div class="label-xs">Lot Area</div>
        <div class="stat-val"><?= number_format($lot_area_sqft) ?> <span style="font-size:14px;color:var(--on-surface-var)">sqft</span></div>
        <div class="stat-sub"><?= number_format($lot_area_sqm) ?> m²</div>
    </div>
</div>

<!-- Zoning context -->
<div class="page-body tight">

<?php if ($lot_width_m>=14.5&&$lot_width_m<15.1): ?>
<div class="callout buyout" style="margin-bottom:20px">
    <strong>Neighbour Buyout Opportunity</strong> — This lot is <?= number_format(15.1-$lot_width_m,2) ?>m below the 6-unit eligibility threshold at 15.1m. Acquiring the adjacent property could unlock full 6-unit entitlement and significantly improve the development economics.
</div>
<?php endif; ?>

<!-- Development Options -->
<div style="margin-bottom:20px">
    <div class="label-xs" style="margin-bottom:16px">Development Options — R1-1 Zoning</div>
    <ul class="elig-list">
        <?php
        $elig_rows=[
            ['Duplex / 3-Unit','7.5m / 24.6ft','200 m²','Not required','Required',$lot_width_m>=7.5&&$lot_area_sqm>=200&&$lane_access,'pass',$lot_width_m>=7.5&&$lot_area_sqm>=200&&!$lane_access,'warn'],
            ['4-Unit Multiplex','10.0m / 32.8ft','306 m²','Not required','Required',$lot_width_m>=10.0&&$lot_area_sqm>=306&&$lane_access,'pass',$lot_width_m>=10.0&&$lot_area_sqm>=306&&!$lane_access,'warn'],
            ['6-Unit (Strata)','15.1m / 49.5ft','557 m²','400m FTN','Required',$lot_width_m>=15.1&&$lot_area_sqm>=557&&$transit_prox&&$lane_access,'pass',$lot_width_m>=15.1&&$lot_area_sqm>=557&&!$transit_prox,'warn'],
            ['8-Unit (Rental)','15.1m / 49.5ft','557 m²','400m FTN','Required',$lot_width_m>=15.1&&$lot_area_sqm>=557&&$transit_prox&&$lane_access,'pass',$lot_width_m>=15.1&&$lot_area_sqm>=557&&!$transit_prox,'warn'],
        ];
        foreach($elig_rows as $er):
            if($er[5]){$cs='pass';$ct='✓';$sub='Eligible';}
            elseif($er[7]){$cs='warn';$ct='~';$sub='Missing: lane access';}
            else{$cs='fail';$ct='✗';$sub='Does not meet requirements';}
        ?>
        <li class="elig-item">
            <div class="elig-check <?= $cs ?>"><?= $ct ?></div>
            <div>
                <div class="elig-text-title"><?= $er[0] ?></div>
                <div class="elig-text-sub">Min frontage <?= $er[1] ?> · Area <?= $er[2] ?> · Transit <?= $er[3] ?> · Lane <?= $er[4] ?> <span style="margin-left:8px;font-weight:600;color:<?= $cs==='pass'?'#1a6634':($cs==='warn'?'#b45309':'#ba1a1a') ?>"><?= $sub ?></span></div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

</div><!-- /page-body -->

<!-- Buildability Pathways dark block -->
<div class="dark-block">
    <div class="dark-block-title">Buildability Pathways</div>
    <div class="pathway-grid">
        <div>
            <div class="pathway-label">Pathway A</div>
            <div class="pathway-name">Strata Ownership</div>
            <div class="pathway-row"><span class="k">FSR</span><span class="v">0.70</span></div>
            <div class="pathway-row"><span class="k">Buildable area</span><span class="v"><?= number_format($strata_sqft) ?> sqft</span></div>
            <div class="pathway-row"><span class="k">Saleable area (85%)</span><span class="v"><?= number_format(round($strata_sqft*0.85)) ?> sqft</span></div>
            <div class="pathway-row"><span class="k">Parking</span><span class="v"><?= $transit_prox?'0 stalls':''.number_format($max_units*0.5,1).' stalls' ?></span></div>
        </div>
        <div>
            <div class="pathway-label">Pathway B</div>
            <div class="pathway-name">Secured Rental</div>
            <div class="pathway-row"><span class="k">FSR</span><span class="v" style="font-weight:700">1.00</span></div>
            <div class="pathway-row"><span class="k">Buildable area</span><span class="v" style="font-weight:700"><?= number_format($rental_sqft) ?> sqft</span></div>
            <div class="pathway-row"><span class="k">Full buildable</span><span class="v">100% rentable</span></div>
            <div class="pathway-row"><span class="k">Parking</span><span class="v"><?= $transit_prox?'0 stalls':''.number_format($max_units*0.5,1).' stalls' ?></span></div>
        </div>
    </div>
    <div class="dark-block-note">
        * The Rental pathway allows for 100% FSR utilization (1.0 vs 0.70) under current housing affordability mandates.
        <?= $transit_prox?' Parking waived — property is within 400m of an FTN transit stop.':' Parking required at 0.5 stalls/unit as this property is outside the 400m transit zone.' ?>
    </div>
</div>

</div><!-- /page 2 -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 3 — SITE CONSTRAINTS
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Site Constraints.</div>
    <div class="page-header-meta">Compliance audit · <?= htmlspecialchars($nb_display) ?></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;min-height:800px">

<!-- Left: constraint checklist -->
<div style="padding:36px 40px 36px 64px;border-right:1px solid rgba(0,0,0,.05)">
    <div class="label-xs" style="margin-bottom:20px">Compliance Audit</div>

    <ul class="constraint-list">
        <!-- Lane Access -->
        <?php $lc=$lane_access?'clear':'warn'; $lt=$lane_access?'✓':'!'; ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $lc ?>"><?= $lt ?></div>
            <div>
                <div class="constraint-title">Lane Access</div>
                <div class="constraint-sub"><?= $lane_access?'Unobstructed secondary access verified.':'No rear lane detected — may restrict 4+ unit eligibility.' ?></div>
            </div>
        </li>

        <!-- Transit Proximity -->
        <?php $tc=$transit_prox?'clear':'info'; $tt=$transit_prox?'✓':'○'; ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $tc ?>"><?= $tt ?></div>
            <div>
                <div class="constraint-title">Transit Proximity</div>
                <div class="constraint-sub">
                    <?php if($transit_prox): ?>Within 400m of FTN stop<?= $stop_name?' — '.htmlspecialchars($stop_name).($stop_dist?' ('.number_format($stop_dist).'m)':''):'' ?>. Parking waived.
                    <?php else: ?>Outside 400m transit zone. <?= $stop_name?'Nearest: '.htmlspecialchars($stop_name).($stop_dist?' ('.number_format($stop_dist).'m)':'').'.':'' ?> 6-unit requires transit proximity.<?php endif; ?>
                </div>
            </div>
        </li>

        <!-- Heritage -->
        <?php
        $hc=$heritage==='none'?'clear':($heritage==='C'?'warn':'crit');
        $ht=$heritage==='none'?'✓':($heritage==='C'?'!':'✗');
        ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $hc ?>"><?= $ht ?></div>
            <div>
                <div class="constraint-title">Heritage Designation</div>
                <div class="constraint-sub">
                    <?php if($heritage==='none'):?>No heritage structures or protections identified.
                    <?php elseif($heritage==='A'||$heritage==='B'):?>Category <?= $heritage ?> — HRA likely required. Expect 12–24+ month delays.
                    <?php else:?>Category C — heritage inspection required before permit application.<?php endif; ?>
                </div>
            </div>
        </li>

        <!-- Peat Zone -->
        <?php $pc=$peat_zone?'warn':'clear'; $pt=$peat_zone?'!':'✓'; ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $pc ?>"><?= $pt ?></div>
            <div>
                <div class="constraint-title">Peat Zone</div>
                <div class="constraint-sub"><?= $peat_zone?'Site identified in geotechnical peat zone. Helical pile foundation likely required. $150,000 contingency added to pro forma.':'Site sits outside identified geotechnical peat hazard zones.' ?></div>
            </div>
        </li>

        <!-- Floodplain -->
        <?php $fc=$in_floodplain?'warn':'clear'; $ft=$in_floodplain?'!':'✓'; ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $fc ?>"><?= $ft ?></div>
            <div>
                <div class="constraint-title">Floodplain Risk</div>
                <div class="constraint-sub">
                    <?php if($in_floodplain):?>Property is within a designated floodplain zone (risk level: <?= htmlspecialchars(ucfirst($floodplain_risk)) ?>). Engineering assessment and flood-proofing measures likely required.
                    <?php else:?>No floodplain designation on record for this site.<?php endif; ?>
                </div>
            </div>
        </li>

        <!-- Covenant -->
        <?php $cc=$covenant_present?'warn':'clear'; $ck=$covenant_present?'!':'✓'; ?>
        <li class="constraint-item">
            <div class="constraint-icon <?= $cc ?>"><?= $ck ?></div>
            <div>
                <div class="constraint-title">Title Encumbrances</div>
                <div class="constraint-sub">
                    <?php if($covenant_present):?>Covenant registered: <?= htmlspecialchars($covenant_types) ?>. Obtain full LTSA title search before proceeding.
                    <?php else:?>No covenant or easement flags on record.<?php endif; ?>
                </div>
            </div>
        </li>

        <!-- Active Permit -->
        <?php if($has_permit): ?>
        <li class="constraint-item">
            <div class="constraint-icon warn">!</div>
            <div>
                <div class="constraint-title">Active Building Permit</div>
                <div class="constraint-sub">An active building permit is on record for this property. Verify development status with the City.</div>
            </div>
        </li>
        <?php endif; ?>

        <?php if(!empty($blueprint)): ?>
        <li class="constraint-item" style="padding-top:20px;margin-top:4px">
            <div class="constraint-icon clear">↗</div>
            <div>
                <div class="constraint-title">Standardized Design Match</div>
                <div class="constraint-sub"><?= htmlspecialchars($blueprint['catalogue']) ?> · <?= htmlspecialchars($blueprint['design_id']) ?> — <?= htmlspecialchars($blueprint['design_name']) ?>. Using this plan can save ~$35,000 in architectural fees and up to 4 months in permit time.</div>
            </div>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tonal-divider"></div>

    <!-- Refined conclusion -->
    <div style="background:var(--surface-low);padding:20px;margin-top:0">
        <div class="label-xs" style="margin-bottom:10px">Refined Feasibility Study</div>
        <div style="font-size:12px;color:var(--on-surface-var);line-height:1.7">
            <?php
            $issues=[];
            if(!$lane_access) $issues[]='no verified lane access';
            if(!$transit_prox&&$max_units>=6) $issues[]='outside transit zone';
            if($heritage!=='none') $issues[]='heritage designation ('.htmlspecialchars($heritage).')';
            if($peat_zone) $issues[]='peat zone contingency ($150k added)';
            if($in_floodplain) $issues[]='floodplain risk ('.htmlspecialchars(ucfirst($floodplain_risk)).')';
            if($covenant_present) $issues[]='title encumbrance on record';
            if(empty($issues)):
                echo 'No material constraints identified. This lot presents a clean development profile for a '.$unit_label.'.';
            else:
                echo 'The following constraints warrant attention before proceeding: '.implode('; ',$issues).'. Verify each item with the City of Vancouver and a licensed professional.';
            endif;
            ?>
        </div>
    </div>

</div>

<!-- Right: map -->
<div style="position:relative;background:var(--surface-low)">
    <?php if(!empty($map_base64)): ?>
    <div class="map-container" style="height:480px">
        <img src="data:image/jpeg;base64,<?= $map_base64 ?>" alt="Property map">
        <div class="map-pin-label">Subject Property</div>
    </div>
    <?php else: ?>
    <div style="height:480px;background:var(--surface-high);display:flex;align-items:center;justify-content:center">
        <span style="font-size:11px;color:var(--on-surface-var);letter-spacing:.1em">Map unavailable</span>
    </div>
    <?php endif; ?>

    <div style="padding:24px 32px">
        <div class="label-xs" style="margin-bottom:10px">Property Record</div>
        <div style="font-size:11px;color:var(--on-surface-var);line-height:2">
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,.05);padding-bottom:6px;margin-bottom:6px"><span>PID</span><strong style="color:var(--on-surface)"><?= htmlspecialchars($pid_fmt) ?></strong></div>
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,.05);padding-bottom:6px;margin-bottom:6px"><span>Zoning</span><strong style="color:var(--on-surface)">R1-1</strong></div>
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,.05);padding-bottom:6px;margin-bottom:6px"><span>Neighbourhood</span><strong style="color:var(--on-surface)"><?= htmlspecialchars($nb_display) ?></strong></div>
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,.05);padding-bottom:6px;margin-bottom:6px"><span>BC Assessment</span><strong style="color:var(--on-surface)"><?= money($assessed_land) ?> (<?= $assessment_year ?>)</strong></div>
            <div style="display:flex;justify-content:space-between"><span>Report ID</span><strong style="color:var(--on-surface)"><?= $report_id ?></strong></div>
        </div>
    </div>
</div>

</div>
</div><!-- /page 3 -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 4 — MARKET ANALYSIS
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Market Analysis.</div>
    <div class="page-header-meta"><?= htmlspecialchars($nb_display) ?> · <?= htmlspecialchars($data_as_of) ?></div>
</div>

<!-- PSF three-number row -->
<div class="psf-trio">
    <div class="psf-cell">
        <div class="psf-label">Build Cost</div>
        <div class="psf-val"><?= money($build_psf) ?></div>
        <div class="psf-note">/ sqft · current estimate</div>
    </div>
    <div class="psf-cell">
        <div class="psf-label">Finished Sale Price</div>
        <div class="psf-val"><?= money($current_psf) ?></div>
        <div class="psf-note">/ sqft · <?= htmlspecialchars($data_as_of) ?></div>
    </div>
    <div class="psf-cell">
        <div class="psf-label">Wynston Outlook</div>
        <div class="psf-val"><?= !empty($outlook_psf)?money($outlook_psf):'—' ?></div>
        <div class="psf-note">/ sqft · 12-month projection</div>
    </div>
</div>

<!-- Margin callouts -->
<div class="margin-callout">
    <div class="margin-callout-label">Current margin (sold − build)</div>
    <div style="display:flex;align-items:baseline">
        <div class="margin-callout-val"><?= money($current_margin) ?>/sqft</div>
        <?php if(!empty($proj_margin)): ?>
        <div class="margin-callout-delta <?= $proj_margin>=$current_margin?'delta-up':'delta-dn' ?>">
            · Projected: <?= money($proj_margin) ?>/sqft (<?= $proj_margin>=$current_margin?'▲':'▼' ?> <?= money(abs($proj_margin-$current_margin)) ?>)
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="page-body tight">

<!-- Confidence -->
<div style="background:var(--surface-low);padding:20px 24px;margin-bottom:20px;display:flex;align-items:flex-start;gap:20px">
    <div>
        <div class="label-xs" style="margin-bottom:6px">Data Confidence</div>
        <div style="font-size:20px;font-family:'Noto Serif',serif;color:var(--on-surface)"><?= htmlspecialchars($conf_label) ?></div>
    </div>
    <div style="flex:1;border-left:1px solid rgba(0,0,0,.06);padding-left:20px">
        <div style="font-size:11px;color:var(--on-surface-var);line-height:1.7;margin-top:4px">
            <?php if($conf_short==='High'):?>Based on <?= $comp_count ?> comparable sales in <?= htmlspecialchars($nb_display) ?> — REBGV MLS, new builds 2024+. High confidence figures.
            <?php elseif($conf_short==='Moderate'):?>Based on <?= $comp_count ?> comparable sale<?= $comp_count!==1?'s':''?> in <?= htmlspecialchars($nb_display) ?>. Some figures use Metro Vancouver benchmarks as supplementary data.
            <?php else:?>Fewer than 2 local comparable sales. Pro forma uses Metro Vancouver benchmarks ($<?= number_format($current_psf) ?>/sqft). Upload REBGV data via admin to improve accuracy.<?php endif;?>
        </div>
    </div>
</div>

<!-- DOM table -->
<div class="label-xs" style="margin-bottom:12px">Days on Market — <?= htmlspecialchars($nb_display) ?></div>
<table class="dt" style="margin-bottom:20px">
    <thead>
        <tr>
            <th>Property Type</th>
            <th>Current DOM</th>
            <th>vs Last Month</th>
            <th>Market Signal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($dom_data as $dt=>$dd): ?>
        <tr>
            <td style="font-weight:500"><?= ucfirst($dt) ?></td>
            <td class="dt-mono"><?= $dd['current']!==null?$dd['current'].' days':'—' ?></td>
            <td><?= $dd['diff']!==null?($dd['diff']<0?'↓ '.abs($dd['diff']).' days faster':($dd['diff']>0?'↑ '.$dd['diff'].' days slower':'Stable')):'—' ?></td>
            <td class="<?= $dd['diff']!==null&&$dd['diff']<-1?'dt-pass':($dd['diff']!==null&&$dd['diff']>1?'dt-warn':'dt-neutral') ?>">
                <?= $dd['signal'] ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!is_null($hpi_yoy)): ?>
        <tr>
            <td style="font-weight:500">HPI Year-over-Year</td>
            <td class="dt-mono" colspan="2"><?= $hpi_yoy>=0?'+':'' ?><?= pct($hpi_yoy) ?></td>
            <td class="<?= $hpi_yoy>=0?'dt-pass':'dt-warn' ?>"><?= $hpi_yoy>=0?'Appreciating':'Declining' ?></td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Comparable Sales -->
<div class="label-xs" style="margin-bottom:12px">Comparable Sales — New Builds 2024+ · <?= htmlspecialchars($nb_display) ?></div>
<?php if(!empty($comps)): ?>
<table class="dt">
    <thead>
        <tr>
            <th>Address</th>
            <th>Sale Date</th>
            <th>Sqft</th>
            <th>$/sqft</th>
            <th>DOM</th>
            <th>Type</th>
            <th style="text-align:right">Area</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($comps as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['address']??'—') ?></td>
            <td><?= !empty($c['data_month'])?date('M Y',strtotime($c['data_month'])):'—' ?></td>
            <td><?= !empty($c['sqft'])?number_format($c['sqft']):'—' ?></td>
            <td class="dt-mono"><?= !empty($c['price_per_sqft'])?money($c['price_per_sqft']):money($current_psf) ?></td>
            <td><?= !empty($c['days_on_market'])?$c['days_on_market'].'d':'—' ?></td>
            <td><?= ucfirst($c['csv_type']??'—') ?></td>
            <td style="text-align:right;font-size:10px;color:var(--on-surface-var)"><?= !empty($c['expanded'])?'Adjacent':htmlspecialchars($nb_display) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if($comps_expanded): ?>
<div style="font-size:10px;color:var(--on-surface-var);font-style:italic;margin-top:8px">Adjacent neighbourhood data included — fewer than 3 local comps available in <?= htmlspecialchars($nb_display) ?>.</div>
<?php endif; ?>
<?php else: ?>
<div class="callout">No comparable sales data. Pro forma uses Metro Vancouver benchmarks (<?= money($current_psf) ?>/sqft). Upload REBGV monthly data via the admin panel.</div>
<?php endif; ?>

<div style="font-size:10px;color:var(--on-surface-var);margin-top:10px">Source: REBGV MLS · R1-1 zoned · Year Built 2024+ · Duplex and multiplex types. This is not an appraisal.</div>

</div>
</div><!-- /page 4 -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 5 — PRO FORMA (branches: strata | rental | outlook/comparison)
═══════════════════════════════════════════════════════════════════════════ -->

<?php if ($pro_forma_path === 'strata'): ?>
<!-- ───────────────── PAGE 5A — STRATA PRO FORMA ───────────────── -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Development Pro Forma.</div>
    <div class="page-header-meta">Strata Ownership · 0.70 FSR · <?= $max_units ?>-unit · <?= htmlspecialchars($nb_display) ?></div>
</div>
<div class="page-body">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px">

<!-- Left: costs -->
<div>
    <div class="pf-section-title">Project Costs</div>
    <div class="pf-line"><span class="pf-label">Land — BC Assessment <?= $assessment_year ?></span><span class="pf-val"><?= money($assessed_land) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Hard build (<?= number_format($strata_buildable_sqft) ?> sqft × <?= money($build_psf) ?>/sqft)</span><span class="pf-val"><?= money($s_hard_build) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">City-wide DCL ($18.45/sqft)</span><span class="pf-val"><?= money($s_dcl_city) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Utilities DCL ($2.95/sqft)</span><span class="pf-val"><?= money($s_dcl_util) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Permit fees ($13.70/$1,000)</span><span class="pf-val"><?= money($s_permit_fees) ?></span></div>
    <?php if($peat_zone): ?><div class="pf-line indent"><span class="pf-label" style="color:#b45309">Peat zone contingency</span><span class="pf-val">$150,000</span></div><?php endif; ?>
    <div class="pf-total-bar"><span class="pf-total-label">Total Project Cost</span><span class="pf-total-val"><?= money($s_total_cost) ?></span></div>

    <div class="pf-section-title" style="margin-top:20px">Exit Value — Strata Sale</div>
    <div class="pf-line"><span class="pf-label">Saleable area (<?= number_format($strata_buildable_sqft) ?> × 85%)</span><span class="pf-val"><?= number_format(round($s_saleable)) ?> sqft</span></div>
    <div class="pf-line"><span class="pf-label">Avg $/sqft (<?= htmlspecialchars($conf_short) ?> confidence)</span><span class="pf-val"><?= money($current_psf) ?>/sqft</span></div>
    <div class="pf-total-bar"><span class="pf-total-label">Total Exit Value</span><span class="pf-total-val"><?= money($s_exit_value) ?></span></div>

    <div class="profit-block <?= $s_profit>=0?'positive':'negative' ?>" style="margin-top:16px">
        <div><div class="profit-label">Estimated Profit</div><div class="profit-sub">Before tax & professional fees</div></div>
        <div class="profit-val <?= $s_profit>=0?'positive':'negative' ?>"><?= $s_profit<0?'−':'' ?><?= money(abs($s_profit)) ?></div>
    </div>
    <div style="display:flex;gap:24px;margin-top:8px;font-size:11px;color:var(--on-surface-var)">
        <span>ROI: <strong style="color:var(--on-surface)"><?= pct($s_roi) ?></strong></span>
        <span>Per unit: <strong style="color:var(--on-surface)"><?= money($s_profit/max($max_units,1)) ?></strong></span>
    </div>
</div>

<!-- Right: unit mix + donut chart -->
<div>
    <div class="pf-section-title">Unit Mix — Vancouver 2026 Bedroom Rules</div>
    <table class="dt" style="margin:0 0 20px">
        <thead><tr><th>#</th><th>Type</th><th>Est. Sqft</th><th style="text-align:right">Projected Sale</th></tr></thead>
        <tbody>
        <?php foreach($unit_mix as $i=>$u): ?>
        <tr><td style="color:var(--on-surface-var)"><?= $i+1 ?></td><td><?= $u['br'] ?>BR</td><td><?= number_format($u['sqft']) ?> sqft</td><td style="text-align:right;font-weight:500"><?= money($u['price']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    // Count units by bedroom type for donut
    $br_counts=[1=>0,2=>0,3=>0];
    foreach($unit_mix as $u) $br_counts[$u['br']]++;
    $total_u=count($unit_mix);
    // SVG donut — navy/gold/grey palette
    $donut_colors=['#000a1e','#c9a84c','#8da3c0'];
    $donut_labels=['2 Bedroom','1 Bedroom','3 Bedroom'];
    $donut_vals=[$br_counts[2],$br_counts[1],$br_counts[3]];
    $cx=100;$cy=100;$r=70;$stroke=28;
    $circumference=2*M_PI*$r;
    $offset=0; $segs=[];
    foreach($donut_vals as $i=>$v){
        $pct_v=$total_u>0?$v/$total_u:0;
        $dash=$pct_v*$circumference;
        $gap=$circumference-$dash;
        $segs[]=[$dash,$gap,$offset,$donut_colors[$i],$donut_labels[$i],$v,$pct_v];
        $offset+=$dash;
    }
    ?>
    <svg viewBox="0 0 200 200" style="width:160px;height:160px;display:block;margin:0 auto 12px">
        <?php foreach($segs as $s): if($s[5]===0)continue; ?>
        <circle cx="<?=$cx?>" cy="<?=$cy?>" r="<?=$r?>"
            fill="none" stroke="<?=$s[3]?>" stroke-width="<?=$stroke?>"
            stroke-dasharray="<?=$s[0]?> <?=$s[1]?>"
            stroke-dashoffset="-<?=$s[2]?>"
            transform="rotate(-90 <?=$cx?> <?=$cy?>)"/>
        <?php endforeach; ?>
        <text x="<?=$cx?>" y="<?=$cy-8?>" text-anchor="middle" font-family="Noto Serif,serif" font-size="28" font-weight="700" fill="#000a1e"><?=$total_u?></text>
        <text x="<?=$cx?>" y="<?=$cy+10?>" text-anchor="middle" font-family="Work Sans,sans-serif" font-size="8" font-weight="600" letter-spacing="1" fill="#2d3a52">TOTAL UNITS</text>
    </svg>
    <div style="display:flex;flex-direction:column;gap:6px;font-size:11px">
        <?php foreach($segs as $s): if($s[5]===0)continue; ?>
        <div style="display:flex;align-items:center;gap:8px">
            <span style="width:12px;height:12px;background:<?=$s[3]?>;flex-shrink:0;display:inline-block"></span>
            <?= $s[4] ?> (<?= round($s[6]*100) ?>%)
        </div>
        <?php endforeach; ?>
    </div>
</div>
</div><!-- /grid -->
<div style="font-size:10px;color:var(--on-surface-var);margin-top:16px;line-height:1.6">
    Pro forma uses <?= htmlspecialchars($nb_display) ?> data as of <?= htmlspecialchars($data_as_of) ?>.
    <?= $conf_short!=='High'?'Metro Vancouver benchmarks used where local data is unavailable. ':'' ?>Verify with a qualified financial advisor.
</div>
</div>
</div><!-- /page 5a strata -->

<?php elseif ($pro_forma_path === 'rental'): ?>
<!-- ───────────────── PAGE 5B — RENTAL PRO FORMA ───────────────── -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Rental Income Analysis.</div>
    <div class="page-header-meta">Secured Rental · 1.00 FSR · <?= $max_units ?> units · <?= htmlspecialchars($nb_display) ?></div>
</div>
<div class="page-body tight">

<!-- Gross income by bedroom -->
<div class="label-xs" style="margin-bottom:12px">Rental Income by Bedroom Type</div>
<table class="dt" style="margin-bottom:20px">
    <thead><tr><th>Type</th><th>Units</th><th>Market Rent</th><th>Monthly Total</th><th>CMHC Benchmark</th><th>Variance</th></tr></thead>
    <tbody>
    <?php foreach($rental_rows as $rr):
        $var=$rr['cmhc']>0?(($rr['curr']-$rr['cmhc'])/$rr['cmhc'])*100:0;
        $row_total=$rr['curr']*$rr['units'];
    ?>
    <tr>
        <td style="font-weight:600"><?= $rr['t'] ?></td>
        <td style="color:var(--on-surface-var)"><?= $rr['units'] ?></td>
        <td><?= money($rr['curr']) ?>/mo</td>
        <td style="font-weight:500"><?= money($row_total) ?>/mo</td>
        <td><?= money($rr['cmhc']) ?>/mo</td>
        <td class="<?= $var>2?'dt-pass':($var<-2?'dt-warn':'dt-neutral') ?>"><?= $var>=0?'+':'' ?><?= pct($var) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Income waterfall + expenses side by side -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:20px">
<div>
    <div class="pf-section-title">Income Waterfall</div>
    <div class="pf-line"><span class="pf-label">Gross monthly income</span><span class="pf-val"><?= money($r_gross_monthly) ?></span></div>
    <div class="pf-line"><span class="pf-label">Annual gross income</span><span class="pf-val"><?= money($r_gross_annual) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Less vacancy (<?= round($fa_vacancy*100,1) ?>%)</span><span class="pf-val" style="color:#b45309">−<?= money($r_gross_annual*$fa_vacancy) ?></span></div>
    <div class="pf-total-bar"><span class="pf-total-label">Effective Gross Income (EGI)</span><span class="pf-total-val"><?= money($r_egi) ?>/yr</span></div>
</div>
<div>
    <div class="pf-section-title">Operating Expenses</div>
    <div class="pf-line"><span class="pf-label">Property management (<?= round($fa_mgmt*100) ?>% of EGI)</span><span class="pf-val"><?= money($r_mgmt_fee) ?></span></div>
    <div class="pf-line"><span class="pf-label">Property tax (~<?= round($fa_tax_rate*100,2) ?>% of cost)</span><span class="pf-val"><?= money($r_prop_tax) ?></span></div>
    <div class="pf-line"><span class="pf-label">Building insurance (<?= money($fa_ins_unit) ?>/unit/yr)</span><span class="pf-val"><?= money($r_insurance) ?></span></div>
    <div class="pf-line"><span class="pf-label">Maintenance & repairs (<?= money($fa_maint_unit) ?>/unit/yr)</span><span class="pf-val"><?= money($r_maint) ?></span></div>
    <div class="pf-total-bar"><span class="pf-total-label">Total Operating Expenses</span><span class="pf-total-val"><?= money($r_total_opex) ?>/yr</span></div>
</div>
</div>

<!-- NOI highlight -->
<div class="margin-callout" style="margin:0 0 20px;background:var(--primary)">
    <div style="font-size:12px;color:rgba(255,255,255,.6)">Net Operating Income (NOI) = EGI − Operating Expenses</div>
    <div style="font-family:'Noto Serif',serif;font-size:28px;color:var(--tertiary-fixed)"><?= money($r_noi) ?>/yr</div>
</div>

</div>
</div><!-- /page 5b rental income -->

<!-- ───────────────── PAGE 5B-2 — CMHC MLI FINANCING ───────────────── -->
<div class="page">
<div class="page-header">
    <div class="page-header-title"><?= htmlspecialchars($fa_name) ?> Financing.</div>
    <div class="page-header-meta">Purpose-built rental financing · <?= number_format($fa_ltc*100) ?>% LTC · <?= number_format($fa_rate*100,2) ?>% interest · <?= $fa_amort ?>-yr amortization</div>
</div>
<div class="page-body">

<!-- Project cost summary -->
<div class="label-xs" style="margin-bottom:12px">Total Project Cost — Rental Path (1.00 FSR)</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:24px">
<div>
    <div class="pf-line"><span class="pf-label">Land — BC Assessment <?= $assessment_year ?></span><span class="pf-val"><?= money($assessed_land) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Hard build (<?= number_format($rental_buildable_sqft) ?> sqft × <?= money($build_psf) ?>/sqft)</span><span class="pf-val"><?= money($r_hard_build) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Density bonus (bonus FSR × $40/sqft)</span><span class="pf-val"><?= money($r_density_bonus) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">City-wide DCL ($18.45/sqft)</span><span class="pf-val"><?= money($r_dcl_city) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Utilities DCL ($2.95/sqft)</span><span class="pf-val"><?= money($r_dcl_util) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">Permit fees</span><span class="pf-val"><?= money($r_permit_fees) ?></span></div>
    <?php if($peat_zone): ?><div class="pf-line indent"><span class="pf-label" style="color:#b45309">Peat zone contingency</span><span class="pf-val">$150,000</span></div><?php endif; ?>
    <div class="pf-total-bar"><span class="pf-total-label">Total Project Cost</span><span class="pf-total-val"><?= money($r_total_cost) ?></span></div>
</div>
<div>
    <div class="pf-section-title"><?= htmlspecialchars($fa_name) ?> Loan Structure</div>
    <div class="pf-line"><span class="pf-label">Loan-to-cost (LTC)</span><span class="pf-val"><?= number_format($fa_ltc*100) ?>%</span></div>
    <div class="pf-line"><span class="pf-label">Base loan amount</span><span class="pf-val"><?= money($r_loan_base) ?></span></div>
    <div class="pf-line indent"><span class="pf-label">CMHC insurance premium (<?= number_format($fa_ins_prem*100) ?>%)</span><span class="pf-val"><?= money($r_ins_amount) ?></span></div>
    <div class="pf-total-bar"><span class="pf-total-label">Total Insured Loan</span><span class="pf-total-val"><?= money($r_loan_total) ?></span></div>
    <div class="pf-line" style="margin-top:8px"><span class="pf-label">Equity required (<?= number_format((1-$fa_ltc)*100) ?>%)</span><span class="pf-val" style="font-weight:700"><?= money($r_equity) ?></span></div>
    <div class="pf-line"><span class="pf-label">Interest rate</span><span class="pf-val"><?= number_format($fa_rate*100,2) ?>% per annum</span></div>
    <div class="pf-line"><span class="pf-label">Amortization</span><span class="pf-val"><?= $fa_amort ?> years</span></div>
    <div class="pf-line"><span class="pf-label">Monthly mortgage payment</span><span class="pf-val" style="font-weight:700"><?= money($r_monthly_pmt) ?>/mo</span></div>
    <div class="pf-line"><span class="pf-label">Annual debt service</span><span class="pf-val" style="font-weight:700"><?= money($r_annual_debt) ?>/yr</span></div>
</div>
</div>

<!-- Cash flow & investor metrics -->
<div class="label-xs" style="margin-bottom:12px">Investor Return Metrics</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);background:var(--surface-low);margin-bottom:20px">
    <div class="metric-cell">
        <div class="label-xs">Annual Cash Flow</div>
        <div class="metric-val" style="font-size:22px;color:<?= $r_cash_flow>=0?'var(--success)':'#ba1a1a' ?>"><?= $r_cash_flow<0?'−':'' ?><?= money(abs($r_cash_flow)) ?></div>
        <div class="metric-sub">NOI − debt service</div>
    </div>
    <div class="metric-cell">
        <div class="label-xs">Cash-on-Cash Return</div>
        <div class="metric-val" style="font-size:22px"><?= pct($r_coc_return) ?></div>
        <div class="metric-sub">on <?= money($r_equity) ?> equity</div>
    </div>
    <div class="metric-cell">
        <div class="label-xs">Cap Rate</div>
        <div class="metric-val" style="font-size:22px"><?= pct($r_cap_rate) ?></div>
        <div class="metric-sub">NOI ÷ total cost</div>
    </div>
    <div class="metric-cell">
        <div class="label-xs">Payback Period</div>
        <div class="metric-val" style="font-size:22px"><?= round($r_payback,1) ?> <span style="font-size:14px">yrs</span></div>
        <div class="metric-sub">total cost ÷ NOI</div>
    </div>
</div>

<!-- Asset sale value -->
<div style="background:var(--primary);padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:16px">
    <div>
        <div style="font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px">Stabilised Asset Value</div>
        <div style="font-family:'Noto Serif',serif;font-size:32px;color:var(--tertiary-fixed)"><?= money($r_asset_value) ?></div>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">NOI ÷ <?= number_format($fa_cap_rate*100,2) ?>% market cap rate (<?= htmlspecialchars($nb_display) ?>)</div>
    </div>
    <div>
        <div style="font-size:9px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px">Day-1 Equity Position</div>
        <div style="font-family:'Noto Serif',serif;font-size:32px;color:<?= $r_value_vs_cost>=0?'#4ade80':'#f87171' ?>"><?= $r_value_vs_cost>=0?'+':'' ?><?= money($r_value_vs_cost) ?></div>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">Asset value vs total project cost at stabilisation</div>
    </div>
</div>

<!-- Rental covenant notice -->
<div class="callout warn">
    <strong>Secured Rental Covenant — Section 219, Land Title Act:</strong> The 1.00 FSR density bonus is granted on condition that all units remain rental tenure for the covenant period (typically 60 years). Individual strata-title sale of units is prohibited while the covenant is in force. The building may be sold as a single income-producing asset to another investor. Confirm covenant terms with the City of Vancouver and a real estate lawyer before proceeding.
</div>

<div style="font-size:10px;color:var(--on-surface-var);margin-top:12px;line-height:1.6">
    Financing assumptions: <?= htmlspecialchars($fa_name) ?> · <?= number_format($fa_ltc*100) ?>% LTC · <?= number_format($fa_rate*100,2) ?>% interest · <?= $fa_amort ?>-year amortization · <?= number_format($fa_ins_prem*100) ?>% CMHC premium.
    All figures are estimates. Verify with a licensed mortgage broker and financial advisor before making investment decisions.
</div>
</div>
</div><!-- /page 5b-2 financing -->

<?php else: // outlook — comparison report ?>
<!-- ───────────────── PAGE 5C — COMPARISON: STRATA VS RENTAL ───────────────── -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Path Comparison.</div>
    <div class="page-header-meta">Strata / Sell vs Secured Rental / Hold · <?= $max_units ?> units · <?= htmlspecialchars($nb_display) ?></div>
</div>
<div class="page-body tight">

<!-- Side by side summary table -->
<div class="label-xs" style="margin-bottom:16px">Development Path Analysis</div>
<table class="dt" style="margin-bottom:24px">
    <thead>
        <tr>
            <th style="width:35%">Metric</th>
            <th style="text-align:center;background:#f0fdf4;color:#166534">Strata / Sell</th>
            <th style="text-align:center;background:#eff6ff;color:#1d4ed8">Rental / Hold</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>FSR</td><td style="text-align:center;font-weight:600">0.70</td><td style="text-align:center;font-weight:600">1.00</td></tr>
        <tr><td>Buildable Area</td><td style="text-align:center"><?= number_format($strata_buildable_sqft) ?> sqft</td><td style="text-align:center"><?= number_format($rental_buildable_sqft) ?> sqft</td></tr>
        <tr><td>Total Project Cost</td><td style="text-align:center;font-weight:600"><?= money($s_total_cost) ?></td><td style="text-align:center;font-weight:600"><?= money($r_total_cost) ?></td></tr>
        <tr><td>Equity Required</td><td style="text-align:center">N/A (sell on completion)</td><td style="text-align:center;font-weight:600"><?= money($r_equity) ?> (<?= number_format((1-$fa_ltc)*100) ?>%)</td></tr>
        <tr><td>Exit / Annual Income</td><td style="text-align:center;font-weight:600;color:#166534"><?= money($s_exit_value) ?> sale</td><td style="text-align:center;font-weight:600;color:#1d4ed8"><?= money($r_noi) ?>/yr NOI</td></tr>
        <tr><td>Profit / Cash Flow</td><td style="text-align:center;font-weight:700;color:<?= $s_profit>=0?'#166534':'#ba1a1a' ?>"><?= money($s_profit) ?></td><td style="text-align:center;font-weight:700;color:<?= $r_cash_flow>=0?'#166634':'#ba1a1a' ?>"><?= money($r_cash_flow) ?>/yr</td></tr>
        <tr><td>ROI / Cap Rate</td><td style="text-align:center"><?= pct($s_roi) ?> ROI</td><td style="text-align:center"><?= pct($r_cap_rate) ?> cap rate</td></tr>
        <tr><td>Cash-on-Cash Return</td><td style="text-align:center;color:var(--on-surface-var)">—</td><td style="text-align:center;font-weight:600"><?= pct($r_coc_return) ?></td></tr>
        <tr><td>Payback Period</td><td style="text-align:center;color:var(--on-surface-var)">N/A (one-time sale)</td><td style="text-align:center"><?= round($r_payback,1) ?> years</td></tr>
        <tr><td>Stabilised Asset Value</td><td style="text-align:center;color:var(--on-surface-var)">—</td><td style="text-align:center;font-weight:600"><?= money($r_asset_value) ?></td></tr>
        <tr><td>Can Sell Individual Units</td><td style="text-align:center;color:#166534;font-weight:600">✓ Yes</td><td style="text-align:center;color:#b45309;font-weight:600">✗ Covenant restricted</td></tr>
    </tbody>
</table>

<!-- Visual bar comparison -->
<?php
$max_bar = max($s_exit_value, $r_asset_value, 1);
$bars = [
    ['label'=>'Strata Exit Value','val'=>$s_exit_value,'color'=>'#000a1e'],
    ['label'=>'Rental Asset Value','val'=>$r_asset_value,'color'=>'#1d4ed8'],
    ['label'=>'Strata Profit','val'=>max($s_profit,0),'color'=>'#166534'],
    ['label'=>'Annual NOI (×10)','val'=>$r_noi*10,'color'=>'#0369a1'],
];
?>
<div class="label-xs" style="margin-bottom:12px">Visual Comparison</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
<?php foreach($bars as $bar): $w=round(($bar['val']/$max_bar)*100); ?>
<div style="display:flex;align-items:center;gap:12px;font-size:11px">
    <div style="width:160px;flex-shrink:0;color:var(--on-surface-var)"><?= $bar['label'] ?></div>
    <div style="flex:1;background:var(--surface-low);height:24px;position:relative">
        <div style="width:<?= min($w,100) ?>%;height:100%;background:<?= $bar['color'] ?>;opacity:.85"></div>
    </div>
    <div style="width:90px;text-align:right;font-weight:600;color:var(--on-surface)"><?= money($bar['val']) ?></div>
</div>
<?php endforeach; ?>
<div style="font-size:10px;color:var(--on-surface-var);font-style:italic">* Annual NOI shown at 10× for scale comparison with capital values.</div>
</div>

<!-- Unit mix donut — strata -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start">
<div>
    <div class="label-xs" style="margin-bottom:10px">Strata Unit Mix</div>
    <?php
    $br_counts2=[1=>0,2=>0,3=>0];
    foreach($unit_mix as $u) $br_counts2[$u['br']]++;
    $total_u2=count($unit_mix);
    $segs2=[];$offset2=0;
    $dv2=[$br_counts2[2],$br_counts2[1],$br_counts2[3]];
    $dc2=['#000a1e','#c9a84c','#8da3c0'];
    $dl2=['2 Bedroom','1 Bedroom','3 Bedroom'];
    foreach($dv2 as $i=>$v){$p=$total_u2>0?$v/$total_u2:0;$d=$p*$circumference;$g=$circumference-$d;$segs2[]=[$d,$g,$offset2,$dc2[$i],$dl2[$i],$v,$p];$offset2+=$d;}
    ?>
    <svg viewBox="0 0 200 200" style="width:130px;height:130px;display:block;margin:0 auto 8px">
        <?php foreach($segs2 as $s): if($s[5]===0)continue; ?>
        <circle cx="100" cy="100" r="70" fill="none" stroke="<?=$s[3]?>" stroke-width="28"
            stroke-dasharray="<?=$s[0]?> <?=$s[1]?>" stroke-dashoffset="-<?=$s[2]?>"
            transform="rotate(-90 100 100)"/>
        <?php endforeach; ?>
        <text x="100" y="96" text-anchor="middle" font-family="Noto Serif,serif" font-size="26" font-weight="700" fill="#000a1e"><?=$total_u2?></text>
        <text x="100" y="112" text-anchor="middle" font-family="Work Sans,sans-serif" font-size="7" letter-spacing="1" fill="#2d3a52">UNITS</text>
    </svg>
    <div style="display:flex;flex-direction:column;gap:4px;font-size:10px">
        <?php foreach($segs2 as $s): if($s[5]===0)continue; ?>
        <div style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;background:<?=$s[3]?>;display:inline-block;flex-shrink:0"></span><?=$s[4]?> (<?=round($s[6]*100)?>%)</div>
        <?php endforeach; ?>
    </div>
</div>
<div>
    <div class="label-xs" style="margin-bottom:10px">Key Consideration</div>
    <div style="font-size:11px;color:var(--on-surface-var);line-height:1.8">
        <strong style="color:var(--on-surface)">Choose Strata if:</strong> you need capital returned on completion, have no long-term equity goals, or market conditions favour pre-sales.<br><br>
        <strong style="color:var(--on-surface)">Choose Rental if:</strong> you want long-term income, have access to <?= money($r_equity) ?> equity, and qualify for CMHC MLI Select financing. The <?= number_format($fa_amort) ?>-year amortization materially reduces monthly payments vs conventional.<br><br>
        <strong style="color:var(--on-surface)">Note:</strong> The rental path carries a Section 219 covenant — individual unit sales are prohibited during the covenant period.
    </div>
</div>
</div>

</div>
</div><!-- /page 5c comparison -->

<?php endif; // end path branching for page 5 ?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 6 — WYNSTON OUTLOOK
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Wynston Outlook.</div>
    <div class="page-header-meta">12-month $/sqft intelligence · <?= htmlspecialchars($nb_display) ?> · <?= htmlspecialchars($outlook_quarter??'Current Quarter') ?></div>
</div>

<div class="page-body">

<?php if(!empty($outlook_data)): ?>

<div class="psf-trio" style="margin:0 -64px">
    <div class="psf-cell" style="padding-left:64px">
        <div class="psf-label">Build Cost</div>
        <div class="psf-val"><?= money($build_psf) ?></div>
        <div class="psf-note">/ sqft · current</div>
    </div>
    <div class="psf-cell">
        <div class="psf-label">Finished Sale Price</div>
        <div class="psf-val"><?= money($current_psf) ?></div>
        <div class="psf-note">/ sqft · <?= htmlspecialchars($data_as_of) ?></div>
    </div>
    <div class="psf-cell" style="padding-right:64px">
        <div class="psf-label">Wynston Outlook</div>
        <div class="psf-val"><?= money($outlook_psf) ?></div>
        <div class="psf-note">/ sqft · 12-month projection</div>
    </div>
</div>

<div style="height:32px"></div>

<div class="margin-callout" style="margin:0 -64px;padding:16px 64px">
    <div class="margin-callout-label">Current margin (sold − build)</div>
    <div class="margin-callout-val"><?= money($current_margin) ?>/sqft</div>
</div>
<div class="margin-callout" style="margin:2px -64px 0;padding:16px 64px">
    <div class="margin-callout-label">Projected margin (12-month)</div>
    <div style="display:flex;align-items:baseline;gap:12px">
        <div class="margin-callout-val"><?= money($proj_margin) ?>/sqft</div>
        <div class="margin-callout-delta <?= $proj_margin>=$current_margin?'delta-up':'delta-dn' ?>">
            <?= $proj_margin>=$current_margin?'▲':'▼' ?> <?= money(abs($proj_margin-$current_margin)) ?>/sqft
        </div>
    </div>
</div>

<div style="height:36px"></div>

<!-- Three-layer methodology -->
<div class="label-xs" style="margin-bottom:16px">Three-Layer Methodology — Weighted Outlook Formula</div>

<div class="outlook-layer-header">
    <div class="ol-name">Layer</div>
    <div class="ol-sig" style="text-align:center">Signal</div>
    <div class="ol-wt" style="text-align:center">Weight</div>
    <div class="ol-ct" style="text-align:center">Contribution</div>
    <div class="ol-src" style="text-align:right">Source</div>
</div>

<?php foreach([
    ['Macro Signal',     $outlook_data['macro'],      $outlook_data['mw'], '6 institutional forecasts'],
    ['Local Momentum',   $outlook_data['local'],      $outlook_data['lw'], 'Neighbourhood HPI history'],
    ['Pipeline Signal',  $outlook_data['pipeline'],   $outlook_data['pw'], 'Active permit count'],
    ['Population Signal',$outlook_data['population'], $outlook_data['pw2'],'Stats Canada Census'],
] as $l): ?>
<div class="outlook-layer-row">
    <div class="ol-name"><?= $l[0] ?></div>
    <div class="ol-sig"><?= $l[1]>=0?'+':'' ?><?= pct($l[1]) ?></div>
    <div class="ol-wt"><?= round($l[2]*100) ?>%</div>
    <div class="ol-ct"><?= number_format($l[1]*$l[2],2) ?>%</div>
    <div class="ol-src"><?= $l[3] ?></div>
</div>
<?php endforeach; ?>

<div class="outlook-layer-row total">
    <div class="ol-name">Combined Wynston Outlook</div>
    <div class="ol-sig"><?= $outlook_pct>=0?'+':'' ?><?= pct($outlook_pct) ?></div>
    <div class="ol-wt">100%</div>
    <div class="ol-ct"><?= $outlook_pct>=0?'+':'' ?><?= pct($outlook_pct) ?></div>
    <div class="ol-src"></div>
</div>

<div class="tonal-divider"></div>

<?php if(!empty($outlook_data['low'])&&!empty($outlook_data['high'])): ?>
<div style="display:flex;gap:32px;margin-bottom:16px">
    <div>
        <div class="label-xs" style="margin-bottom:4px">Confidence Range</div>
        <div style="font-size:14px;color:var(--on-surface)"><?= pct($outlook_data['low']) ?> to <?= pct($outlook_data['high']) ?></div>
    </div>
    <div>
        <div class="label-xs" style="margin-bottom:4px">Quarter</div>
        <div style="font-size:14px;color:var(--on-surface)"><?= htmlspecialchars($outlook_quarter) ?></div>
    </div>
    <?php if(!empty($outlook_sources)): ?>
    <div style="flex:1">
        <div class="label-xs" style="margin-bottom:4px">Macro Sources</div>
        <div style="font-size:11px;color:var(--on-surface-var)"><?= htmlspecialchars(implode(' · ',$outlook_sources)) ?></div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="callout">Wynston Outlook not yet available for <?= htmlspecialchars($nb_display) ?>. Enter quarterly bank/broker forecasts via the admin panel (Wynston Outlook tab) to enable this section.</div>
<?php endif; ?>

<div style="font-size:10px;color:var(--on-surface-var);margin-top:20px;line-height:1.7;border-top:1px solid rgba(0,0,0,.06);padding-top:16px">
    <strong>Disclaimer:</strong> Wynston Outlook is compiled from publicly available institutional forecasts and proprietary neighbourhood market data. It is provided for informational purposes only and does not constitute financial, investment, or real estate advice. Past performance does not guarantee future results. Always consult a licensed professional before making investment decisions.
</div>

</div>
</div><!-- /page 6 -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 7 — RISK ANALYSIS
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="page-header">
    <div class="page-header-title">Risk Analysis.</div>
    <div class="page-header-meta">Standard and conditional risk factors</div>
</div>

<div class="page-body">

<div class="label-xs" style="margin-bottom:20px">Standard Risk Factors</div>

<div class="risk-item">
    <div class="risk-num">1</div>
    <div class="risk-body">
        <div class="risk-title">Construction Cost Inflation</div>
        <div class="risk-desc">BC Stats BCPI has averaged 3–5% annually. If construction costs rise 10%, estimated profit reduces to <?= money($profit-$hard_build*0.10) ?>. At 20% cost increase: <?= money($profit-$hard_build*0.20) ?>. Pro forma uses current-month actuals from COV permits.</div>
    </div>
</div>

<div class="risk-item">
    <div class="risk-num">2</div>
    <div class="risk-body">
        <div class="risk-title">Interest Rate Environment</div>
        <div class="risk-desc">A 1% rate increase on a <?= money($total_cost*0.65) ?> construction facility (65% LTC) adds approximately <?= money($total_cost*0.65*0.01*1.25) ?> in financing costs over a 15-month construction period. Consult your construction mortgage broker.</div>
    </div>
</div>

<div class="risk-item <?php if(!empty($dom_data['duplex']['diff'])&&$dom_data['duplex']['diff']>5)echo 'active';?>">
    <div class="risk-num">3</div>
    <div class="risk-body">
        <div class="risk-title">Market Timing</div>
        <div class="risk-desc"><?php if(!empty($dom_data['duplex']['current'])):?>Duplex DOM in <?= htmlspecialchars($nb_display) ?>: <?= $dom_data['duplex']['current'] ?> days. <?= ($dom_data['duplex']['diff']??0)<-1?'Market accelerating — conditions favour proceeding now.':(($dom_data['duplex']['diff']??0)>5?'DOM rising — monitor conditions closely before committing.':'Market conditions stable.') ?><?php else:?>DOM data not yet available for this neighbourhood. Monitor market conditions closely.<?php endif;?></div>
    </div>
</div>

<div class="risk-item">
    <div class="risk-num">4</div>
    <div class="risk-body">
        <div class="risk-title">Permit Timeline</div>
        <div class="risk-desc">Vancouver multiplex permits average 6–14 months standard processing. <?= !empty($blueprint)?'The matched pre-approved design ('.$blueprint['design_id'].') may reduce this to ~4 months by using the standardized plan pathway.':'Using a BC Provincial or CMHC pre-approved design can reduce timelines to approximately 4 months.' ?></div>
    </div>
</div>

<div class="risk-item">
    <div class="risk-num">5</div>
    <div class="risk-body">
        <div class="risk-title">Resale Absorption</div>
        <div class="risk-desc"><?php if($comp_count>=5):?>Strong buyer activity in <?= htmlspecialchars($nb_display) ?> (<?= $comp_count ?> recent comparable sales). Healthy absorption evident.<?php elseif($comp_count>=2):?>Moderate buyer activity (<?= $comp_count ?> comps). Monitor pre-sale activity in adjacent areas before committing to strata path.<?php else:?>Limited comp data in this neighbourhood. Consider the rental hold path if strata pre-sales are challenging.<?php endif;?></div>
    </div>
</div>

<?php if($pro_forma_path==='rental'||$pro_forma_path==='outlook'): ?>
<div class="risk-item active">
    <div class="risk-num">6</div>
    <div class="risk-body">
        <div class="risk-title">Interest Rate Reset Risk — Rental Path</div>
        <div class="risk-desc">CMHC MLI Select is typically fixed for 5-year terms. At current assumptions (<?= number_format($fa_rate*100,2) ?>%), annual debt service is <?= money($r_annual_debt) ?>. A 1% rate increase at renewal adds approximately <?= money($r_loan_total*0.01) ?>/year, reducing annual cash flow from <?= money($r_cash_flow) ?> to approximately <?= money($r_cash_flow-$r_loan_total*0.01) ?>. Stress-test your hold strategy at rates up to 7.5% before committing.</div>
    </div>
</div>
<div class="risk-item active">
    <div class="risk-num">7</div>
    <div class="risk-body">
        <div class="risk-title">Covenant Lock-in — Rental Path</div>
        <div class="risk-desc">The Section 219 rental covenant registered as a condition of the 1.00 FSR density bonus prohibits strata-titling or individual unit sale, typically for 60 years. If market conditions improve, you cannot convert to strata to capture per-unit appreciation. The only exit is sale of the entire building as a single income-producing asset. Confirm your investment horizon is compatible with this restriction before committing.</div>
    </div>
</div>
<div class="risk-item">
    <div class="risk-num">8</div>
    <div class="risk-body">
        <div class="risk-title">Vacancy & Operating Cost Creep — Rental Path</div>
        <div class="risk-desc">Current pro forma assumes <?= number_format($fa_vacancy*100,1) ?>% vacancy — conservative vs Vancouver's current ~1–2% purpose-built vacancy rate. However, operating expenses tend to increase 3–5% annually. A scenario where vacancy rises to 8% and costs increase 20% would reduce NOI from <?= money($r_noi) ?>/yr to approximately <?= money(round($r_noi*0.72)) ?>/yr — still positive under current financing terms.</div>
    </div>
</div>
<?php endif; ?>

<?php if($heritage==='A'||$heritage==='B'): ?>
<div class="risk-item critical">
    <div class="risk-num">⚠</div>
    <div class="risk-body">
        <div class="risk-title">Heritage Category <?= $heritage ?> — Permit Risk</div>
        <div class="risk-desc">Heritage Revitalization Agreement (HRA) likely required. Expect 12–24+ month delays beyond standard permit timelines. Budget $15,000–$40,000 for heritage consultant fees. Confirm with City of Vancouver Heritage Planning before proceeding.</div>
    </div>
</div>
<?php elseif($heritage==='C'): ?>
<div class="risk-item active">
    <div class="risk-num">⚠</div>
    <div class="risk-body">
        <div class="risk-title">Heritage Category C — Inspection Required</div>
        <div class="risk-desc">Heritage inspection required before permit application. Budget 4–8 additional weeks for inspection process.</div>
    </div>
</div>
<?php endif; ?>

<?php if($peat_zone): ?>
<div class="risk-item active">
    <div class="risk-num">⚠</div>
    <div class="risk-body">
        <div class="risk-title">Peat Zone — Foundation Cost Uncertainty</div>
        <div class="risk-desc">Helical piles or engineered foundation system likely required. The $150,000 contingency in the pro forma is an estimate — actual costs depend on soil depth and structural design. Commission a geotechnical report before acquisition.</div>
    </div>
</div>
<?php endif; ?>

<?php if($in_floodplain): ?>
<div class="risk-item active">
    <div class="risk-num">⚠</div>
    <div class="risk-body">
        <div class="risk-title">Floodplain Zone — Engineering Assessment Required</div>
        <div class="risk-desc">This property carries a floodplain designation (risk level: <?= htmlspecialchars(ucfirst($floodplain_risk)) ?>). Flood-proofing measures, engineered fill, or elevated construction may be required. Confirm flood construction level (FCL) with the City of Vancouver and a registered professional engineer before proceeding.</div>
    </div>
</div>
<?php endif; ?>

<?php if($covenant_present): ?>
<div class="risk-item active">
    <div class="risk-num">⚠</div>
    <div class="risk-body">
        <div class="risk-title">Covenant / Easement — Title Review Required</div>
        <div class="risk-desc">Registered encumbrance type: <?= htmlspecialchars($covenant_types) ?>. The specific terms of this covenant may restrict development or impose conditions. Obtain a full LTSA title search and legal review before proceeding.</div>
    </div>
</div>
<?php endif; ?>

<div class="tonal-divider"></div>

<div style="font-size:10px;color:var(--on-surface-var);line-height:1.7">
    Risk scenarios are illustrative only. Actual outcomes will vary. This analysis does not constitute legal, financial, or investment advice. Always engage qualified professionals — including a licensed real estate lawyer, certified general accountant, and registered professional engineer — before making investment decisions.
</div>

</div>
</div><!-- /page 7 -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 8 — BACK COVER
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">
<div class="back-cover">

    <div class="back-logo">WYNSTON</div>
    <div class="back-tagline">Intelligent Navigator · W.I.N · wynston.ca</div>

    <div class="back-agent-block">
        <?php if(!empty($logo_b64)): ?>
        <img class="back-logo-img" src="data:<?= $logo_mime ?>;base64,<?= $logo_b64 ?>" alt="<?= htmlspecialchars($agent_name) ?>">
        <?php else: ?>
        <div class="back-logo-ph"></div>
        <?php endif; ?>
        <div>
            <div class="back-agent-name"><?= htmlspecialchars($agent_name) ?></div>
            <div class="back-agent-title"><?= htmlspecialchars($agent_title) ?></div>
            <div class="back-agent-bio"><?= htmlspecialchars($agent_bio) ?></div>
            <div class="back-contacts" style="margin-top:14px">
                <div class="back-contact-line"><span>Web</span>wynston.ca · Vancouver, BC</div>
                <div class="back-contact-line"><span>Report</span><?= $report_id ?> · Generated <?= date('F j, Y') ?></div>
            </div>
        </div>
    </div>

    <!-- Recommended next step -->
    <div class="back-nextstep">
        <div class="back-nextstep-label">Recommended Next Step</div>
        <div class="back-nextstep-text"><?= htmlspecialchars($next_step) ?></div>
    </div>

    <?php if($in_floodplain||$peat_zone||($heritage!=='none')||$covenant_present): ?>
    <div style="margin-top:16px;padding:16px 20px;background:rgba(186,26,26,.15);border-left:2px solid rgba(186,26,26,.5);font-size:11px;color:rgba(255,255,255,.7);line-height:1.6;flex-shrink:0">
        <strong style="color:rgba(255,255,255,.9)">Active Constraints:</strong>
        <?php
        $flags=[];
        if($heritage!=='none') $flags[]='Heritage Category '.$heritage;
        if($peat_zone) $flags[]='Peat Zone (+$150k contingency)';
        if($in_floodplain) $flags[]='Floodplain Risk ('.ucfirst($floodplain_risk).')';
        if($covenant_present) $flags[]='Title Encumbrance';
        echo htmlspecialchars(implode(' · ',$flags));
        ?> — See risk analysis on page 7.
    </div>
    <?php endif; ?>

    <div class="back-sources">
        <div class="back-sources-label">Data Sources</div>
        <div class="back-sources-text">REBGV MLS (new builds 2024+) · City of Vancouver Open Data · BC Assessment · TransLink GTFS · CMHC · BC Stats BCPI · Stats Canada Census (2021) · RBC / TD / BMO / BCREA / RE/MAX / Royal LePage institutional forecasts</div>
        <div class="back-disclaimer">This report is prepared by Wynston Concierge Real Estate for informational purposes only. It does not constitute financial, investment, legal, or real estate advice. All data sourced from publicly available datasets and proprietary market intelligence. Pro forma figures are estimates only and subject to change. Past performance does not guarantee future results. Always consult a licensed professional before making investment decisions. © Wynston Concierge Real Estate <?= date('Y') ?>. Report ID: <?= $report_id ?>.</div>
        <div style="font-size:10px;color:rgba(255,255,255,.25);margin-top:8px">Construction mortgage professionals: contact Wynston for project-specific data packages.</div>
    </div>

    <div class="back-gold-bar"></div>

</div>
</div><!-- /page 8 -->


</div><!-- /.report -->

<script>
if (new URLSearchParams(window.location.search).get('print')==='1') {
    window.addEventListener('load',function(){
        // Wait for Google Fonts to load before printing
        document.fonts.ready.then(function(){
            setTimeout(function(){window.print();},600);
        });
    });
}
</script>
</body>
</html>