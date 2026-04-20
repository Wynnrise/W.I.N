<?php
require_once __DIR__ . '/../dev-auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>W.I.N — Wynston Intelligent Navigator</title>
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --navy:#002446; --gold:#c9a84c; --cream:#f9f6f0; --blue:#0065ff;
  --green:#22c55e; --teal:#14b8a6; --amber:#f59e0b; --gray:#94a3b8; --red:#ef4444;
  --panel-w:400px; --header-h:56px;
}
html,body{height:100%;overflow:hidden;font-family:'Segoe UI',system-ui,sans-serif;background:var(--navy)}
.w-header{position:fixed;top:0;left:0;right:0;z-index:200;height:var(--header-h);background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:0 16px;border-bottom:1px solid rgba(201,168,76,.25)}
.w-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.w-logo img{height:30px}
.w-logo-text{font-size:13px;font-weight:800;color:#fff;letter-spacing:.5px}
.w-logo-badge{font-size:10px;color:var(--gold);letter-spacing:1.5px;font-weight:700}
.w-header-right{display:flex;align-items:center;gap:12px}
.w-btn-sm{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:none;transition:.2s}
.w-btn-ghost{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.15)}
.w-btn-gold{background:var(--gold);color:var(--navy)}
.w-btn-ghost:hover{background:rgba(255,255,255,.15)}
.w-btn-gold:hover{background:#d4b35c}
.w-search-wrap{position:fixed;top:calc(var(--header-h) + 12px);left:50%;transform:translateX(-50%);z-index:150;width:340px;max-width:calc(100vw - 160px)}
.w-search-inner{display:flex;align-items:center;background:rgba(0,0,0,.82);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:24px;padding:0 14px;gap:8px;transition:border-color .2s}
.w-search-inner:focus-within{border-color:rgba(201,168,76,.5);box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.w-search-inner i{color:rgba(255,255,255,.4);font-size:13px;flex-shrink:0}
.w-search-input{flex:1;background:none;border:none;outline:none;color:#fff;font-size:13px;padding:10px 0;font-family:inherit}
.w-search-input::placeholder{color:rgba(255,255,255,.35)}
.w-search-clear{background:none;border:none;cursor:pointer;color:rgba(255,255,255,.4);font-size:14px;padding:4px;display:none;line-height:1}
.w-search-clear:hover{color:rgba(255,255,255,.8)}
.w-search-clear.visible{display:block}
.w-search-results{position:absolute;top:calc(100% + 6px);left:0;right:0;background:rgba(10,15,30,.95);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.4);display:none}
.w-search-results.open{display:block}
.w-search-result{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .15s}
.w-search-result:last-child{border-bottom:none}
.w-search-result:hover{background:rgba(255,255,255,.07)}
.w-search-result-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px}
.w-search-result-icon.green{background:rgba(34,197,94,.2);color:var(--green)}
.w-search-result-icon.teal{background:rgba(20,184,166,.2);color:var(--teal)}
.w-search-result-icon.amber{background:rgba(245,158,11,.2);color:var(--amber)}
.w-search-result-icon.gray{background:rgba(148,163,184,.2);color:var(--gray)}
.w-search-result-body{flex:1;min-width:0}
.w-search-result-addr{font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.w-search-result-meta{font-size:11px;color:rgba(255,255,255,.4);margin-top:1px}
.w-search-no-results{padding:16px 14px;text-align:center;color:rgba(255,255,255,.35);font-size:13px}
#map{position:fixed;top:var(--header-h);left:0;right:0;bottom:0;transition:right .3s ease}
#map.panel-open{right:var(--panel-w)}
.w-tool-menu{position:fixed;top:calc(var(--header-h) + 80px);left:16px;z-index:120;user-select:none}
.w-tool-menu-handle{background:rgba(0,20,46,.92);backdrop-filter:blur(12px);border:1px solid rgba(201,168,76,.3);border-radius:14px;padding:6px;box-shadow:0 4px 20px rgba(0,0,0,.4);min-width:200px}
.w-tool-menu-drag{display:flex;align-items:center;gap:8px;padding:6px 10px 8px;cursor:grab;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:4px}
.w-tool-menu-drag:active{cursor:grabbing}
.w-tool-menu-title{font-size:10px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);flex:1}
.w-tool-menu-drag i{color:rgba(255,255,255,.3);font-size:11px}
.w-tool-section{padding:4px 0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:2px}
.w-tool-section:last-child{border-bottom:none;margin-bottom:0}
.w-tool-section-label{font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.3);padding:4px 10px 2px}
.w-tool-toggle{display:flex;align-items:center;gap:10px;padding:7px 10px;cursor:pointer;border-radius:8px;transition:background .15s}
.w-tool-toggle:hover{background:rgba(255,255,255,.06)}
.w-tool-toggle-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:.2s}
.w-tool-toggle-label{flex:1;font-size:12px;font-weight:600;color:rgba(255,255,255,.7);transition:color .15s}
.w-tool-switch{width:32px;height:18px;border-radius:9px;background:rgba(255,255,255,.15);position:relative;flex-shrink:0;transition:background .2s}
.w-tool-switch::after{content:'';position:absolute;width:12px;height:12px;border-radius:50%;background:#fff;top:3px;left:3px;transition:transform .2s}
.w-tool-toggle.active .w-tool-switch{background:var(--gold)}
.w-tool-toggle.active .w-tool-switch::after{transform:translateX(14px)}
.w-tool-toggle.active .w-tool-toggle-label{color:#fff}
.icon-gold{background:rgba(201,168,76,.15);color:var(--gold)}
.icon-green{background:rgba(34,197,94,.15);color:var(--green)}
.icon-teal{background:rgba(20,184,166,.15);color:var(--teal)}
.icon-amber{background:rgba(245,158,11,.15);color:var(--amber)}
.icon-gray{background:rgba(148,163,184,.15);color:var(--gray)}
.icon-blue{background:rgba(0,101,255,.15);color:#60a5fa}
.w-tool-info{position:relative;display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;background:rgba(255,255,255,.1);color:rgba(255,255,255,.4);font-size:10px;font-weight:800;cursor:default;flex-shrink:0;transition:background .15s,color .15s;font-style:normal}
.w-tool-info:hover{background:rgba(201,168,76,.25);color:var(--gold)}
.w-tool-info .w-tooltip{display:none;position:absolute;left:22px;top:50%;transform:translateY(-50%);background:rgba(0,10,25,.97);border:1px solid rgba(201,168,76,.3);border-radius:8px;padding:8px 11px;width:210px;font-size:11px;line-height:1.5;color:rgba(255,255,255,.8);font-weight:400;z-index:999;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,.5)}
.w-tool-info:hover .w-tooltip{display:block}
.w-tool-menu-minimize{background:none;border:none;cursor:pointer;color:rgba(255,255,255,.4);font-size:12px;padding:2px 4px;line-height:1;transition:color .15s}
.w-tool-menu-minimize:hover{color:var(--gold)}
.w-panel{position:fixed;top:var(--header-h);right:0;bottom:0;width:var(--panel-w);background:var(--cream);transform:translateX(100%);transition:transform .3s ease;z-index:150;display:flex;flex-direction:column;overflow:hidden}
.w-panel.open{transform:translateX(0)}
.w-panel-head{background:var(--navy);padding:14px 16px;flex-shrink:0}
.w-panel-close{float:right;background:none;border:none;color:rgba(255,255,255,.5);font-size:18px;cursor:pointer;line-height:1;margin-top:2px}
.w-panel-close:hover{color:#fff}
.w-panel-address{font-size:14px;font-weight:800;color:#fff;margin-bottom:2px}
.w-panel-pid{font-size:11px;color:rgba(255,255,255,.45);font-family:monospace}
.w-elig-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;margin-top:8px}
.badge-green{background:rgba(34,197,94,.18);color:var(--green);border:1px solid rgba(34,197,94,.3)}
.badge-teal{background:rgba(20,184,166,.18);color:var(--teal);border:1px solid rgba(20,184,166,.3)}
.badge-amber{background:rgba(245,158,11,.18);color:var(--amber);border:1px solid rgba(245,158,11,.3)}
.badge-gray{background:rgba(148,163,184,.18);color:var(--gray);border:1px solid rgba(148,163,184,.3)}
.badge-gold{background:rgba(201,168,76,.18);color:var(--gold);border:1px solid rgba(201,168,76,.3)}
.w-confidence{margin-top:8px;padding:6px 10px;border-radius:6px;font-size:11px;display:flex;align-items:center;gap:6px}
.conf-green{background:rgba(34,197,94,.12);color:var(--green)}
.conf-amber{background:rgba(245,158,11,.12);color:var(--amber)}
.conf-gray{background:rgba(148,163,184,.12);color:var(--gray)}
.w-confidence strong{font-weight:800}
.w-panel-body{flex:1;overflow-y:auto;padding:0;scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.15) transparent}
.w-panel-body::-webkit-scrollbar{width:4px}
.w-panel-body::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:2px}
.w-section{padding:14px 16px;border-bottom:1px solid rgba(0,0,0,.07)}
.w-section-title{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:10px}
.w-specs{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.w-spec{background:#fff;border-radius:8px;padding:8px 10px}
.w-spec-label{font-size:10px;color:#999;margin-bottom:2px}
.w-spec-val{font-size:14px;font-weight:800;color:var(--navy)}
.w-spec-sub{font-size:10px;color:#aaa}
.w-flag{display:flex;align-items:flex-start;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:6px;font-size:12px;line-height:1.4}
.w-flag:last-child{margin-bottom:0}
.w-flag-red{background:#fff5f5;border:1px solid #fdd;color:#c00}
.w-flag-yellow{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
.w-flag-blue{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8}
.w-flag-gold{background:rgba(201,168,76,.08);border:1px solid rgba(201,168,76,.3);color:#7a5800}
.w-flag i{margin-top:1px;flex-shrink:0}
.w-tabs{display:flex;border-bottom:1px solid rgba(0,0,0,.08);padding:0 16px;background:#fff}
.w-tab{padding:10px 14px;font-size:12px;font-weight:700;color:#999;cursor:pointer;border-bottom:2px solid transparent;transition:.2s;user-select:none}
.w-tab.active{color:var(--navy);border-bottom-color:var(--navy)}
.w-tab:hover:not(.active){color:#666}
.w-pf-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:13px;color:#555;border-bottom:1px solid rgba(0,0,0,.04)}
.w-pf-row:last-child{border-bottom:none}
.w-pf-row.total{font-weight:800;color:var(--navy);border-top:2px solid rgba(0,36,70,.1);margin-top:6px;padding-top:8px;font-size:14px}
.w-pf-val{font-weight:700;color:var(--navy);font-size:13px}
.w-pf-val.positive{color:var(--green)}
.w-pf-val.negative{color:var(--red)}
.w-profit-box{background:var(--navy);border-radius:10px;padding:14px;margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:10px}
.w-profit-item{text-align:center}
.w-profit-label{font-size:10px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.8px}
.w-profit-val{font-size:20px;font-weight:800;color:var(--gold);margin-top:2px}
.w-profit-val.negative{color:#f87171}
.w-rent-table{width:100%;border-collapse:collapse;font-size:12px}
.w-rent-table th{padding:4px 6px;color:#999;font-weight:700;text-align:left;font-size:10px;text-transform:uppercase}
.w-rent-table td{padding:6px 6px;border-top:1px solid rgba(0,0,0,.05)}
/* ── Editable pro forma rows ── */
.w-pf-edit-wrap{display:flex;align-items:center;gap:6px}
.w-pf-edit-btn{background:none;border:none;cursor:pointer;color:#bbb;font-size:11px;padding:2px 4px;border-radius:4px;transition:.15s;flex-shrink:0}
.w-pf-edit-btn:hover{color:var(--gold);background:rgba(201,168,76,.1)}
.w-pf-edit-btn.active{color:var(--gold)}
.w-pf-edit-input{width:80px;border:1px solid var(--gold);border-radius:4px;padding:2px 6px;font-size:12px;font-weight:700;color:var(--navy);text-align:right;background:#fff}
.w-pf-edit-input:focus{outline:none;box-shadow:0 0 0 2px rgba(201,168,76,.3)}
.w-pf-sub{font-size:10px;color:#bbb;padding:0 0 5px;line-height:1.4}
/* ── HPI toggle switch ── */
.w-hpi-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0}
.w-hpi-label{font-size:12px;color:#555}
.w-hpi-val{font-size:12px;font-weight:700;color:#555}
.w-hpi-row.active .w-hpi-label{color:var(--navy)}
.w-hpi-row.active .w-hpi-val{color:var(--navy)}
.w-hpi-row.muted .w-hpi-label{color:#ccc}
.w-hpi-row.muted .w-hpi-val{color:#ccc;text-decoration:line-through}
.w-toggle{position:relative;display:inline-block;width:34px;height:18px;flex-shrink:0}
.w-toggle input{opacity:0;width:0;height:0}
.w-toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ddd;border-radius:18px;transition:.2s}
.w-toggle-slider:before{position:absolute;content:"";height:14px;width:14px;left:2px;bottom:2px;background:#fff;border-radius:50%;transition:.2s}
.w-toggle input:checked+.w-toggle-slider{background:var(--navy)}
.w-toggle input:checked+.w-toggle-slider:before{transform:translateX(16px)}
.w-exit-note{font-size:10px;color:var(--amber);padding:2px 0 4px;font-style:italic}
.w-rent-dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:4px}
.w-outlook-block{background:#fff;border-radius:10px;padding:12px;margin-top:10px}
.w-psf-row{display:flex;justify-content:space-between;font-size:12px;padding:4px 0;color:#666}
.w-psf-val{font-weight:800;color:var(--navy)}
.w-outlook-pct{font-size:28px;font-weight:800;color:var(--navy);margin:6px 0 2px}
.w-outlook-pct.positive::before{content:'+'}
.w-conf-range{font-size:11px;color:#aaa}
.w-dom-row{display:flex;align-items:center;gap:8px}
.w-dom-arrow{font-size:18px;font-weight:800}
.w-dom-arrow.green{color:var(--green)}
.w-dom-arrow.amber{color:var(--amber)}
.w-dom-arrow.gray{color:var(--gray)}
.w-dom-label{font-size:12px;color:#666}
.w-design{background:#fff;border-radius:10px;overflow:hidden;display:flex;gap:10px;align-items:center;padding:10px}
.w-design-thumb{width:64px;height:64px;border-radius:6px;object-fit:cover;background:#eee;flex-shrink:0}
.w-design-info{flex:1;min-width:0}
.w-design-name{font-size:13px;font-weight:800;color:var(--navy)}
.w-design-saving{font-size:11px;color:var(--green);margin-top:2px}
.w-design-link{display:inline-block;margin-top:4px;font-size:11px;color:var(--blue);text-decoration:none;font-weight:700}
.w-gate-blur{filter:blur(4px);pointer-events:none;user-select:none}
.w-gate-overlay{position:absolute;inset:0;z-index:10;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(249,246,240,.8);backdrop-filter:blur(2px);text-align:center;padding:24px}
.w-gate-overlay h4{font-size:16px;font-weight:800;color:var(--navy);margin-bottom:8px}
.w-gate-overlay p{font-size:13px;color:#666;margin-bottom:16px;line-height:1.5}
.w-gate-btn{background:var(--navy);color:#fff;padding:10px 24px;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;display:inline-block;transition:.2s}
.w-gate-btn:hover{background:#003a7a;color:#fff}
.w-actions{display:flex;flex-direction:column;gap:8px;padding:14px 16px}
.w-action-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.2s;text-align:center}
.w-action-primary{background:var(--navy);color:#fff}
.w-action-secondary{background:#fff;color:var(--navy);border:1.5px solid rgba(0,36,70,.2)}
.w-action-gold{background:var(--gold);color:var(--navy)}
.w-action-primary:hover{background:#003a7a}
.w-action-secondary:hover{border-color:var(--navy)}
.w-action-gold:hover{background:#d4b35c}
.w-skeleton{background:linear-gradient(90deg,#e8e4dd 25%,#f0ede8 50%,#e8e4dd 75%);background-size:200% 100%;animation:skeleton-shimmer 1.5s infinite;border-radius:6px;height:14px;margin:4px 0}
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.w-warning-149{background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.4);border-radius:8px;padding:10px 12px;font-size:12px;color:#7a5800;display:flex;align-items:flex-start;gap:8px;margin-top:8px}
.w-panel-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#bbb;text-align:center;padding:32px}
.w-panel-empty i{font-size:36px;margin-bottom:12px}
.w-panel-empty p{font-size:13px;line-height:1.6}
.w-permit-banner{background:linear-gradient(135deg,rgba(0,36,70,.95),rgba(0,20,46,.95));border-bottom:2px solid var(--gold);padding:0}
.w-permit-img{width:100%;height:160px;object-fit:cover;display:block}
.w-permit-img-placeholder{width:100%;height:120px;background:linear-gradient(135deg,#001a35,#002446);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:32px}
.w-permit-status-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);color:var(--gold);font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase;padding:4px 10px;border-radius:20px;margin-top:8px}
/* ── Toast notifications ──────────────────────────── */
.w-toast-wrap{position:fixed;bottom:32px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;align-items:center;gap:8px;pointer-events:none}
.w-toast{background:rgba(0,20,46,.97);backdrop-filter:blur(12px);border:1px solid rgba(201,168,76,.4);color:#fff;font-size:13px;font-weight:600;padding:10px 20px;border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,.4);opacity:0;transform:translateY(12px);transition:opacity .25s,transform .25s;pointer-events:none;white-space:nowrap}
.w-toast.show{opacity:1;transform:translateY(0)}
.w-toast.success i{color:var(--green)}
.w-toast.info i{color:var(--gold)}
.w-toast.warn i{color:var(--amber)}
/* ── Square lot markers ───────────────────────────── */
/* Replaced circle pins with rounded squares — */
/* gives a property-footprint feel on the map   */
.mapboxgl-canvas{cursor:default}
@media(max-width:700px){:root{--panel-w:100vw}.w-tool-menu{top:calc(var(--header-h) + 70px)}}
/* Standardized Design credit checkbox — lives in cost section of both tabs */
.w-stddesign{margin:8px 0 4px;padding:10px 12px;background:rgba(0,36,70,.04);border:1px solid rgba(0,36,70,.1);border-radius:6px;display:flex;align-items:flex-start;gap:10px;cursor:pointer;transition:.15s}
.w-stddesign:hover{background:rgba(0,36,70,.07);border-color:rgba(0,36,70,.2)}
.w-stddesign input[type=checkbox]{margin-top:2px;cursor:pointer;accent-color:var(--navy);flex-shrink:0}
.w-stddesign-body{flex:1;font-size:11px;line-height:1.4;color:#333}
.w-stddesign-title{font-weight:700;color:var(--navy);margin-bottom:2px}
.w-stddesign-credit{color:#166534;font-weight:700}
.sp-btn{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:11px;font-weight:600;padding:5px 11px;border-radius:20px;cursor:pointer;transition:.15s;white-space:nowrap}
.sp-btn:hover{background:rgba(201,168,76,.15);border-color:var(--gold);color:var(--gold)}
.sp-btn.active{background:rgba(201,168,76,.2);border-color:var(--gold);color:var(--gold)}
</style>
</head>
<body>

<header class="w-header">
  <a href="/" class="w-logo">
    <div><div class="w-logo-text"></div><div class="w-logo-badge">W.I.N — Wynston Intelligence Navigator</div></div>
  </a>
  <div class="w-header-right">
    <button class="w-btn-sm w-btn-ghost" onclick="toggleHeader()" title="Hide header"><i class="fas fa-compress-alt"></i></button>
    <button class="w-btn-sm w-btn-ghost" id="map-style-btn" onclick="toggleMapStyle()" title="Toggle light/dark map"><i class="fas fa-moon" id="map-style-icon"></i></button>
    <button class="w-btn-sm w-btn-ghost" onclick="location.href='/developer-dashboard.php'"><i class="fas fa-th-large" style="margin-right:5px"></i>Dashboard</button>
    <button class="w-btn-sm w-btn-gold" onclick="location.href='/wynston-concierge.php'">Concierge</button>
  </div>
</header>

<div class="w-search-wrap" id="search-wrap">
  <div class="w-search-inner">
    <i class="fas fa-search"></i>
    <input class="w-search-input" id="search-input" type="text" placeholder="Search address or PID…" autocomplete="off" spellcheck="false">
    <button class="w-search-clear" id="search-clear" onclick="clearSearch()" title="Clear">×</button>
  </div>
  <div class="w-search-results" id="search-results"></div>
</div>

<div class="w-tool-menu" id="tool-menu">
  <div class="w-tool-menu-handle">
    <div class="w-tool-menu-drag" id="tool-drag-handle">
      <i class="fas fa-grip-vertical"></i>
      <span class="w-tool-menu-title">Map Layers</span>
      <button class="w-tool-menu-minimize" onclick="event.stopPropagation();toggleToolMenu()" title="Minimize">
        <i class="fas fa-minus" id="tool-menu-icon"></i>
      </button>
    </div>
    <div id="tool-sections">
      <div class="w-tool-section">
        <div class="w-tool-section-label">Visibility</div>
        <div class="w-tool-toggle" id="tgl-halos" onclick="toolToggle('halos')">
          <div class="w-tool-toggle-icon icon-gold"><i class="fas fa-circle"></i></div>
          <span class="w-tool-toggle-label">Transit Halos</span>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle active" id="tgl-skytrain" onclick="toolToggle('skytrain')">
          <div class="w-tool-toggle-icon icon-blue"><i class="fas fa-train"></i></div>
          <span class="w-tool-toggle-label">SkyTrain Stations</span>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle active" id="tgl-permits" onclick="toolToggle('permits')">
          <div class="w-tool-toggle-icon icon-gold"><i class="fas fa-star"></i></div>
          <span class="w-tool-toggle-label">Active Permits</span>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle" id="tgl-neighbourhoods" onclick="toolToggle('neighbourhoods')">
          <div class="w-tool-toggle-icon" style="background:rgba(201,168,76,0.15);"><i class="fas fa-draw-polygon" style="font-size:11px;color:#c9a84c;"></i></div>
          <span class="w-tool-toggle-label">Neighbourhood Borders</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Shows the 22 COV official neighbourhood boundaries. Useful for understanding which market data applies to each lot.</span></i>
          <div class="w-tool-switch"></div>
        </div>
      </div>
      <div class="w-tool-section">
        <div class="w-tool-section-label">Filter Lots</div>
        <div class="w-tool-toggle active" id="tgl-6unit" onclick="toolToggle('6unit')">
          <div class="w-tool-toggle-icon icon-green"><span style="font-size:10px;font-weight:800">6U</span></div>
          <span class="w-tool-toggle-label">6-Unit Eligible</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Lots ≥15.1m wide with lane access and transit proximity. Eligible for the maximum 6-unit multiplex under COV R1-1 zoning.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle active" id="tgl-4unit" onclick="toolToggle('4unit')">
          <div class="w-tool-toggle-icon icon-teal"><span style="font-size:10px;font-weight:800">4U</span></div>
          <span class="w-tool-toggle-label">4-Unit Eligible</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Lots 10.0m–15.09m wide with lane access. Eligible for a 4-unit multiplex. May qualify for 6 units with a neighbour buyout.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle active" id="tgl-duplex" onclick="toolToggle('duplex')">
          <div class="w-tool-toggle-icon icon-amber"><span style="font-size:10px;font-weight:800">2U</span></div>
          <span class="w-tool-toggle-label">Duplex / 3-Unit Eligible</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Lots 7.5m–9.99m wide with lane access. Eligible for a duplex or 3-unit build — the entry point for small-scale multiplex development.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle" id="tgl-buyout" onclick="toolToggle('buyout')">
          <div class="w-tool-toggle-icon icon-gray"><i class="fas fa-arrows-alt-h" style="font-size:10px"></i></div>
          <span class="w-tool-toggle-label">Buyout Potential</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Lots 14.5m–15.09m wide with lane access and transit proximity — just under the 6-unit threshold. Acquiring the neighbouring lot could unlock a higher-density 6-unit build.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle" id="tgl-nopark" onclick="toolToggle('nopark')">
          <div class="w-tool-toggle-icon icon-blue"><i class="fas fa-car-slash" style="font-size:10px"></i></div>
          <span class="w-tool-toggle-label">No Parking Required</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Lots within 400m of a SkyTrain or major transit stop. COV exempts these from parking stall requirements — saving $80K–$150K+ in build cost depending on unit count.</span></i>
          <div class="w-tool-switch"></div>
        </div>
      </div>
      <div class="w-tool-section">
        <div class="w-tool-section-label">Constraint Overlays</div>
        <div class="w-tool-toggle" id="tgl-heritage" onclick="toolToggle('heritage')">
          <div class="w-tool-toggle-icon" style="background:rgba(30,64,175,0.15);"><i class="fas fa-landmark" style="font-size:11px;color:#1e40af;"></i></div>
          <span class="w-tool-toggle-label">Heritage Properties</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Highlights COV heritage-designated lots in dark blue. Category A/B require Heritage Revitalization Agreement. Category C requires inspection.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle" id="tgl-peat" onclick="toolToggle('peat')">
          <div class="w-tool-toggle-icon" style="background:rgba(120,53,15,0.15);"><i class="fas fa-layer-group" style="font-size:11px;color:#78350f;"></i></div>
          <span class="w-tool-toggle-label">Peat Zone</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Shows lots within known Vancouver peat bog zones. Peat soil requires helical pile foundations — adds approximately $150,000 to build cost.</span></i>
          <div class="w-tool-switch"></div>
        </div>
        <div class="w-tool-toggle" id="tgl-flood" onclick="toolToggle('flood')">
          <div class="w-tool-toggle-icon" style="background:rgba(37,99,235,0.15);"><i class="fas fa-water" style="font-size:11px;color:#2563eb;"></i></div>
          <span class="w-tool-toggle-label">Floodplain</span>
          <i class="w-tool-info">ⓘ<span class="w-tooltip">Shows lots within COV designated floodplains and Still Creek floodplain. May affect construction permits, financing, and insurance.</span></i>
          <div class="w-tool-switch"></div>
        </div>

      </div>
      <div class="w-tool-section" style="display:none"><!-- placeholder close -->
      </div>
    </div>
  </div>
</div>

<button id="header-restore-btn" onclick="toggleHeader()" style="position:fixed;top:8px;right:8px;z-index:300;background:rgba(0,20,46,.92);backdrop-filter:blur(8px);border:1px solid rgba(201,168,76,.4);border-radius:20px;padding:6px 14px;font-size:12px;font-weight:700;color:var(--gold);cursor:pointer;display:none;letter-spacing:.5px;font-family:inherit;">☰ Show Menu</button>

<div id="map"></div>

<aside class="w-panel" id="panel">
  <div class="w-panel-head" id="panel-head">
    <button class="w-panel-close" onclick="closePanel()">×</button>
    <div class="w-panel-address" id="ph-address">Loading…</div>
    <div class="w-panel-pid" id="ph-pid"></div>
    <div id="ph-badge"></div>
    <div id="ph-confidence"></div>
  </div>
  <div class="w-tabs" id="pf-tabs" style="display:none">
    <div class="w-tab active" id="tab-strata"  onclick="switchTab('strata')">Multiplex / Sale</div>
    <div class="w-tab"        id="tab-rental"  onclick="switchTab('rental')">Secured Rental</div>
    <div class="w-tab"        id="tab-compare" onclick="switchTab('compare')">Compare</div>
  </div>
  <div class="w-panel-body" id="panel-body">
    <div class="w-panel-empty" id="panel-empty">
      <i class="fas fa-map-pin"></i>
      <p>Click any lot on the map to see its development feasibility analysis.</p>
    </div>
  </div>
  <div class="w-actions" id="panel-actions" style="display:none"></div>
</aside>

<div class="w-toast-wrap" id="toast-wrap"></div>

<script>
// ── Config ────────────────────────────────────────────────────
const MAPBOX_TOKEN = 'pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg';
const IS_LOGGED_IN = <?= isset($_SESSION['dev_id']) ? 'true' : 'false' ?>;

// ── State ─────────────────────────────────────────────────────
let map, currentLot = null, currentLotLat = 0, currentLotLng = 0, fetchSeq = 0;
let currentPath = 'strata', currentData = null;
let toolState = { halos:false, skytrain:true, permits:true, neighbourhoods:false, '6unit':true, '4unit':true, duplex:true, buyout:false, nopark:false, heritage:false, peat:false, flood:false };

// ── Property highlight ─────────────────────────────────────────
// Pulsing gold ring shown on the selected lot — makes it easy to
// locate the clicked/searched lot even when zoomed out.
let highlightAnimFrame = null;
let highlightOpacity   = 1;
let highlightDirection = -1;

function setSelectedLot(lat, lng) {
  currentLotLat = lat;
  currentLotLng = lng;
  if (!map.getSource('selected-lot')) return;
  map.getSource('selected-lot').setData({
    type: 'FeatureCollection',
    features: [{ type: 'Feature', geometry: { type: 'Point', coordinates: [lng, lat] } }]
  });
  if (highlightAnimFrame) cancelAnimationFrame(highlightAnimFrame);
  highlightOpacity  = 1;
  highlightDirection = -1;
  animateHighlight();
}

function clearSelectedLot() {
  if (!map.getSource('selected-lot')) return;
  map.getSource('selected-lot').setData({ type: 'FeatureCollection', features: [] });
  if (highlightAnimFrame) { cancelAnimationFrame(highlightAnimFrame); highlightAnimFrame = null; }
}

function animateHighlight() {
  highlightOpacity += highlightDirection * 0.025;
  if (highlightOpacity <= 0.15) highlightDirection = 1;
  if (highlightOpacity >= 1.0)  highlightDirection = -1;
  if (map.getLayer('selected-lot-ring')) {
    map.setPaintProperty('selected-lot-ring', 'circle-stroke-opacity', highlightOpacity);
    map.setPaintProperty('selected-lot-ring', 'circle-radius', 14 + highlightOpacity * 7);
  }
  highlightAnimFrame = requestAnimationFrame(animateHighlight);
}
// ──────────────────────────────────────────────────────────────

// ── Map init ──────────────────────────────────────────────────
mapboxgl.accessToken = MAPBOX_TOKEN;
map = new mapboxgl.Map({
  container: 'map',
  style: 'mapbox://styles/mapbox/dark-v11',
  center: [-123.1207, 49.2497],
  zoom: 12, minZoom: 10,
  maxBounds: [[-123.8, 49.0], [-122.3, 49.6]],
});
map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');
map.addControl(new mapboxgl.ScaleControl({ unit: 'metric' }), 'bottom-right');

// ── Add all map sources and layers ────────────────────────────
function addMapSourcesAndLayers() {
  if (!map.getSource('lots'))
    map.addSource('lots', { type:'geojson', data:'/api/lots.php?v=2', generateId:true });
  if (!map.getSource('permits'))
    map.addSource('permits', { type:'geojson', data:'/api/permits.php', generateId:true });
  if (!map.getSource('transit-halos'))
    map.addSource('transit-halos', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
  if (!map.getSource('skytrain-stops'))
    map.addSource('skytrain-stops', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });

  // Selected lot highlight source
  if (!map.getSource('selected-lot'))
    map.addSource('selected-lot', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });

  // Neighbourhood boundary source — static GeoJSON file
  if (!map.getSource('neighbourhood-boundaries'))
    map.addSource('neighbourhood-boundaries', {
      type: 'geojson',
      data: '/neighbourhood-boundaries.geojson'
    });

  // Constraint overlay sources (populated from lots GeoJSON after load)
  if (!map.getSource('flood-overlay'))
    map.addSource('flood-overlay', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
  if (!map.getSource('heritage-overlay'))
    map.addSource('heritage-overlay', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
  if (!map.getSource('peat-overlay'))
    map.addSource('peat-overlay', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });


  // Transit halos
  if (!map.getLayer('transit-halos'))
    map.addLayer({ id:'transit-halos', type:'circle', source:'transit-halos',
      paint:{ 'circle-radius':{ stops:[[10,20],[12,40],[14,80],[16,160]], base:2 },
        'circle-color':'rgba(220,38,38,0.04)',
        'circle-stroke-color':'rgba(220,38,38,0.15)', 'circle-stroke-width':1 } });

  // SkyTrain
  if (!map.getLayer('skytrain-stops-ring'))
    map.addLayer({ id:'skytrain-stops-ring', type:'circle', source:'skytrain-stops',
      paint:{ 'circle-radius':['interpolate',['linear'],['zoom'],10,6,13,10,16,14],
        'circle-color':'rgba(0,20,46,0.9)', 'circle-stroke-color':'#dc2626',
        'circle-stroke-width':2.5, 'circle-opacity':1 } });
  if (!map.getLayer('skytrain-stops'))
    map.addLayer({ id:'skytrain-stops', type:'circle', source:'skytrain-stops',
      paint:{ 'circle-radius':['interpolate',['linear'],['zoom'],10,2,13,4,16,5],
        'circle-color':'#c9a84c', 'circle-opacity':1 } });

  // ── Neighbourhood boundary layers ─────────────────────────
  // Dark casing layer underneath the gold outline — gives the line a clear
  // silhouette against colored pins and street colors, readable at any zoom.
  if (!map.getLayer('neighbourhood-casing'))
    map.addLayer({ id:'neighbourhood-casing', type:'line', source:'neighbourhood-boundaries',
      layout:{ visibility:'none' },
      paint:{
        'line-color':'#001426',
        'line-width':['interpolate',['linear'],['zoom'],10,3,14,5,16,6.5],
        'line-opacity':0.85,
      }});

  // Gold outline sits on top of the casing
  if (!map.getLayer('neighbourhood-lines'))
    map.addLayer({ id:'neighbourhood-lines', type:'line', source:'neighbourhood-boundaries',
      layout:{ visibility:'none' },
      paint:{
        'line-color':'#c9a84c',
        'line-width':['interpolate',['linear'],['zoom'],10,1.5,14,2.5,16,3],
        'line-opacity':1,
      }});

  // Labels — bold white text, thick dark navy halo, hides at close zoom
  if (!map.getLayer('neighbourhood-labels'))
    map.addLayer({ id:'neighbourhood-labels', type:'symbol', source:'neighbourhood-boundaries',
      layout:{
        visibility:'none',
        'text-field':['get','name'],
        'text-size':['interpolate',['linear'],['zoom'],10,11,13,15,15,17],
        'text-font':['DIN Offc Pro Bold','Arial Unicode MS Bold'],
        'text-anchor':'center',
        'text-max-width':8,
        'text-allow-overlap':false,
        'text-ignore-placement':false,
        'symbol-placement':'point',
      },
      paint:{
        'text-color':'#ffffff',
        'text-halo-color':'rgba(0,10,25,0.95)',
        'text-halo-width':2.5,
        'text-halo-blur':0.5,
      },
      maxzoom: 15.5   // hide labels when zoomed in close to individual lots
    });

  // ── Constraint overlay layers (below lot pins) ────────────
  // Floodplain — blue halo
  if (!map.getLayer('flood-overlay'))
    map.addLayer({ id:'flood-overlay', type:'circle', source:'flood-overlay',
      layout:{ visibility:'none' },
      paint:{
        'circle-radius':{ stops:[[10,10],[12,20],[14,40],[16,70]], base:2 },
        'circle-color':'rgba(37,99,235,0.15)',
        'circle-stroke-color':'rgba(37,99,235,0.55)',
        'circle-stroke-width':1.5,
      }});

  // Peat zone — brown translucent halo
  if (!map.getLayer('peat-overlay'))
    map.addLayer({ id:'peat-overlay', type:'circle', source:'peat-overlay',
      layout:{ visibility:'none' },
      paint:{
        'circle-radius':{ stops:[[10,8],[12,16],[14,30],[16,50]], base:2 },
        'circle-color':'rgba(120,53,15,0.18)',
        'circle-stroke-color':'rgba(120,53,15,0.5)',
        'circle-stroke-width':1.5,
        'circle-opacity':1,
      }});



  // Lot pins
  // ── Lot pins — flat square markers ───────────────────────────
  // circle-pitch-alignment:'map' makes markers lie flat on the
  // map surface (like a property footprint) instead of facing
  // the camera. Combined with a tight stroke they read as
  // small rectangular lot indicators at higher zoom levels.
  // Single layer — simpler, faster, no sprite dependency.
  if (!map.getLayer('lot-pins'))
    map.addLayer({ id:'lot-pins', type:'circle', source:'lots',
      paint:{
        // Slightly larger than before + flat on map = footprint feel
        'circle-radius':['interpolate',['linear'],['zoom'],11,3.5,13,5,14,7,16,9,18,12],
        'circle-color':['case',
          ['all',['!=',['get','heritage_category'],'none'],['!=',['get','heritage_category'],null]],'#1e40af',
          ['all',['>=',['get','lot_width_m'],15.1],['==',['get','transit_proximate'],1],['==',['get','lane_access'],1]],'#22c55e',
          ['all',['>=',['get','lot_width_m'],10.0],['==',['get','lane_access'],1]],'#14b8a6',
          ['all',['>=',['get','lot_width_m'],7.5],['==',['get','lane_access'],1]],'#f59e0b',
          '#94a3b8'],
        'circle-opacity':0.9,
        'circle-stroke-width':['interpolate',['linear'],['zoom'],11,0,13,1,15,1.5],
        'circle-stroke-color':['case',
          ['all',['!=',['get','heritage_category'],'none'],['!=',['get','heritage_category'],null]],'rgba(30,64,175,0.9)',
          'rgba(0,0,0,0.25)'],
        'circle-stroke-opacity':0.8,
        'circle-pitch-alignment':'map',   // ← lies flat on map surface
        'circle-pitch-scale':'map',       // ← scales with map zoom perspective
      }
    });

  // Heritage pin label ring — extra dark blue stroke on heritage lots
  if (!map.getLayer('heritage-ring'))
    map.addLayer({ id:'heritage-ring', type:'circle', source:'heritage-overlay',
      layout:{ visibility:'none' },
      paint:{
        'circle-radius':['interpolate',['linear'],['zoom'],11,5,14,9,16,12],
        'circle-color':'transparent',
        'circle-stroke-width':2.5,
        'circle-stroke-color':'#1e40af',
        'circle-opacity':0,
      }});

  // ── Gold pulsing ring — selected lot highlight ─────────────
  // Sits above lot-pins so it's always visible.
  // Ring is transparent fill + gold stroke; animateHighlight() drives opacity + radius.
  if (!map.getLayer('selected-lot-ring'))
    map.addLayer({ id:'selected-lot-ring', type:'circle', source:'selected-lot',
      paint:{
        'circle-radius': 18,
        'circle-color': 'transparent',
        'circle-stroke-width': 2.5,
        'circle-stroke-color': '#c9a84c',
        'circle-stroke-opacity': 1,
        'circle-opacity': 0,
        'circle-pitch-alignment': 'map',
      } });
  // ──────────────────────────────────────────────────────────

  // Permit pins — always topmost
  if (!map.getLayer('permit-pins'))
    map.addLayer({ id:'permit-pins', type:'symbol', source:'permits',
      layout:{ 'text-field':'★', 'text-size':20, 'text-anchor':'center', 'text-allow-overlap':true },
      paint:{ 'text-color':'#c9a84c', 'text-halo-color':'rgba(0,0,0,0.7)', 'text-halo-width':2 } });

  // ── Saved lot hearts — topmost layer ─────────────────────
  // Red heart ♥ renders on top of the lot pin for saved lots.
  // Source is empty on init — populated by updateSavedLotLayer()
  // after saved PIDs are fetched.
  if (!map.getSource('saved-lots'))
    map.addSource('saved-lots', { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
  if (!map.getLayer('saved-lot-hearts'))
    map.addLayer({ id:'saved-lot-hearts', type:'symbol', source:'saved-lots',
      layout:{
        'text-field':'♥',
        'text-size':['interpolate',['linear'],['zoom'],11,10,13,14,15,18,17,22],
        'text-anchor':'center',
        'text-allow-overlap':true,
        'text-ignore-placement':true,
      },
      paint:{
        'text-color':'#ef4444',
        'text-halo-color':'rgba(255,255,255,0.9)',
        'text-halo-width':1.5,
      }
    });

  loadTransitData();
  applyToolState();
}

map.on('load', () => {
  addMapSourcesAndLayers();
  loadSavedPids();
  // ── Auto-open saved property from dashboard link ──────────
  // Dashboard passes ?pid=XXX to return user to their saved lot
  const urlPid = new URLSearchParams(window.location.search).get('pid');
  if (urlPid && IS_LOGGED_IN) {
    // Wait for lots layer to load then fly to the lot
    map.once('idle', () => {
      // Find the lot coordinates from the loaded GeoJSON
      const src = map.getSource('lots');
      if (src && src._data && src._data.features) {
        const feat = src._data.features.find(f => f.properties.pid === urlPid);
        if (feat) {
          const [lng, lat] = feat.geometry.coordinates;
          map.flyTo({ center: [lng, lat], zoom: 17, duration: 1000 });
          setTimeout(() => {
            setSelectedLot(lat, lng);
            openPanel(urlPid);
          }, 1100);
          return;
        }
      }
      // Fallback: open panel directly — feasibility.php has the coords
      openPanel(urlPid);
    });
  }
});

// ── Transit data ──────────────────────────────────────────────
function loadTransitData() {
  fetch('/api/lots.php?v=2').then(r=>r.json()).then(data=>{
    // If saved PIDs already loaded, render hearts now
    if(savedLotPids.size>0) _renderHearts();
    const seen = new Set();
    const halos = data.features
      .filter(f=>f.properties.transit_proximate===1)
      .filter(f=>{ const k=`${Math.round(f.geometry.coordinates[0]*150)},${Math.round(f.geometry.coordinates[1]*150)}`; if(seen.has(k))return false; seen.add(k);return true; })
      .map(f=>({ type:'Feature', geometry:f.geometry, properties:{} }));
    if (map.getSource('transit-halos')) map.getSource('transit-halos').setData({ type:'FeatureCollection', features:halos });

    // Heritage overlay — lots with any heritage category
    const heritageFeats = data.features.filter(f => f.properties.heritage_category && f.properties.heritage_category !== 'none');
    if (map.getSource('heritage-overlay')) map.getSource('heritage-overlay').setData({ type:'FeatureCollection', features:heritageFeats });

    // Flood overlay
    const floodFeats = data.features.filter(f => f.properties.floodplain_risk && f.properties.floodplain_risk !== 'none');
    if (map.getSource('flood-overlay')) map.getSource('flood-overlay').setData({ type:'FeatureCollection', features:floodFeats });

    // Peat overlay
    const peatFeats = data.features.filter(f => f.properties.peat_zone === 1);
    if (map.getSource('peat-overlay')) map.getSource('peat-overlay').setData({ type:'FeatureCollection', features:peatFeats });

  }).catch(()=>{});
  fetch('/api/transit_stops.php?v=3').then(r=>r.json()).then(data=>{
    if (map.getSource('skytrain-stops')) map.getSource('skytrain-stops').setData(data);
  }).catch(()=>{});
}

// ── Cursors ───────────────────────────────────────────────────
map.on('mouseenter','lot-pins',    ()=>{ map.getCanvas().style.cursor='pointer'; });
map.on('mouseleave','lot-pins',    ()=>{ map.getCanvas().style.cursor=''; });
map.on('mouseenter','permit-pins', ()=>{ map.getCanvas().style.cursor='pointer'; });
map.on('mouseleave','permit-pins', ()=>{ map.getCanvas().style.cursor=''; });
map.on('mouseenter','skytrain-stops',()=>{ map.getCanvas().style.cursor='pointer'; });
map.on('mouseleave','skytrain-stops',()=>{ map.getCanvas().style.cursor=''; });

// ── SkyTrain click ────────────────────────────────────────────
map.on('click','skytrain-stops',(e)=>{
  const p=e.features[0].properties, name=p.name||'Station';
  function detectLine(name,zone){ const n=name.toLowerCase();
    if(/waterfront|vancouver city centre|yaletown|olympic village|broadway.city hall|king edward|oakridge|langara|marine drive|bridgeport|richmond|brighouse|capstan|sea island|yvr|templeton/.test(n))return 'Canada Line';
    if(/waterfront|granville|burrard|stadium|main st|science world|nanaimo|29th|joyce|patterson|metrotown|royal oak|edmonds|22nd|new westminster|columbia|scott road|gateway|surrey central|king george/.test(n))return 'Expo Line';
    if(/vcc|clark|commercial|renfrew|rupert|gilmore|brentwood|holdom|sperling|lougheed|burquitlam|moody|inlet|coquitlam/.test(n))return 'Millennium Line';
    if(/lincoln|lafarge|coquitlam central|port coquitlam|phibbs/.test(n))return 'Evergreen Extension';
    if(/port haney|maple meadows|mission/.test(n))return 'West Coast Express';
    if(/lonsdale|seabus/.test(n))return 'SeaBus';
    return 'SkyTrain';
  }
  const line=detectLine(name,p.zone||'');
  const cleanName=name.replace(/\s*@\s*Bay\s*\d+/i,'').replace(/\s*Station\s*$/i,' Station').replace(/\s+/g,' ').trim();
  new mapboxgl.Popup({ offset:12, maxWidth:'220px', closeButton:false })
    .setLngLat(e.lngLat)
    .setHTML(`<div style="font-family:'Segoe UI',sans-serif;min-width:160px"><div style="font-weight:800;font-size:14px;color:#002446;margin-bottom:4px">${cleanName}</div><div style="display:inline-block;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;letter-spacing:.5px">${line}</div></div>`)
    .addTo(map);
});

// ── Lot pin click — set highlight then open panel ─────────────
map.on('click','lot-pins',(e)=>{
  const coords = e.features[0].geometry.coordinates;
  setSelectedLot(coords[1], coords[0]);
  openPanel(e.features[0].properties.pid);
});

// ── Permit pin click — set highlight then open panel ──────────
map.on('click','permit-pins',(e)=>{
  const p=e.features[0].properties;
  const coords=e.features[0].geometry.coordinates;
  setSelectedLot(coords[1], coords[0]);
  p._lng=coords[0]; p._lat=coords[1];
  openPermitPanel(p);
});

// ── Permit panel ──────────────────────────────────────────────
let permitMeta = null;
function openPermitPanel(p) {
  permitMeta=p;
  const lat=parseFloat(p._lat??p.lat??0), lng=parseFloat(p._lng??p.lng??0);
  fetch(`/api/nearest_lot.php?lat=${lat}&lng=${lng}`)
    .then(r=>r.json()).then(result=>{ if(result&&result.pid)openPanel(result.pid); else showPermitOnlyPanel(p); })
    .catch(()=>showPermitOnlyPanel(p));
}
function showPermitOnlyPanel(p) {
  permitMeta=null;
  function el(id){return document.getElementById(id);}
  el('panel')&&el('panel').classList.add('open');
  el('map')&&el('map').classList.add('panel-open');
  if(el('pf-tabs'))el('pf-tabs').style.display='none';
  if(el('ph-address'))el('ph-address').textContent=p.address||'—';
  if(el('ph-pid'))el('ph-pid').textContent=p.neighbourhood||p.neighborhood||'';
  if(el('ph-badge'))el('ph-badge').innerHTML='<div class="w-elig-badge badge-gold"><i class="fas fa-hard-hat"></i> Active Building Permit</div>';
  if(el('ph-confidence'))el('ph-confidence').innerHTML='';
  if(el('panel-body'))el('panel-body').innerHTML=`<div class="w-section"><div class="w-flag w-flag-gold" style="margin-bottom:0"><i class="fas fa-hard-hat"></i><span><strong>Development In Progress</strong> — Feasibility data based on current market conditions.</span></div></div><div class="w-section" style="color:#aaa;font-size:13px">No matching lot found in database.</div>`;
  if(el('panel-actions'))el('panel-actions').style.display='none';
  setTimeout(()=>map.resize(),310);
}

// ── Open lot panel ────────────────────────────────────────────
function openPanel(pid) {
  currentLot=pid; currentPath='strata'; currentData=null;
  window._pfOverrides = {};
  window._pfRentalOverrides = {};
  window._financingScenario = 'cmhc_mli';
  // Financing pencil-edits (Session B) — cleared on new lot open
  window._pfFinOverrides = {};
  // Session 15 — Standardized Design toggle — resets per lot
  window._useStdDesign = false;
  // Session 16 — Strata financing scenario ('construction' default or 'all_cash')
  window._strataFinScenario = 'construction';
  function el(id){return document.getElementById(id);}
  el('panel')&&el('panel').classList.add('open');
  el('map')&&el('map').classList.add('panel-open');
  const emptyEl=el('panel-empty'); if(emptyEl)emptyEl.style.display='none';
  if(el('pf-tabs'))el('pf-tabs').style.display=IS_LOGGED_IN?'flex':'none';
  if(el('ph-address'))el('ph-address').textContent='Loading…';
  if(el('ph-pid'))el('ph-pid').textContent='';
  if(el('ph-badge'))el('ph-badge').innerHTML='';
  if(el('ph-confidence'))el('ph-confidence').innerHTML='';
  if(el('panel-body'))el('panel-body').innerHTML=skeletonHTML();
  if(el('panel-actions'))el('panel-actions').style.display='none';
  document.querySelectorAll('.w-tab').forEach(t=>t.classList.remove('active'));
  if(el('tab-strata'))el('tab-strata').classList.add('active');
  setTimeout(()=>map.resize(),310);

  if(!IS_LOGGED_IN){
    fetch('/api/lots.php?v=2').then(r=>r.json()).then(data=>{
      const feature=data.features&&data.features.find(f=>f.properties.pid===pid);
      if(feature)renderGate1(feature.properties); else showError('Lot not found');
    }).catch(()=>showError()); return;
  }

  const seq=++fetchSeq;
  fetch(`/api/feasibility.php?pid=${encodeURIComponent(pid)}&path=${currentPath}`)
    .then(r=>r.json()).then(d=>{ if(seq!==fetchSeq)return; currentData=d; renderPanel(d,currentPath); })
    .catch(()=>{ if(seq===fetchSeq)showError(); });
}

// ── Close panel — also clears highlight ───────────────────────
function closePanel() {
  document.getElementById('panel').classList.remove('open');
  document.getElementById('map').classList.remove('panel-open');
  clearSelectedLot();
  currentLot=null; currentData=null;
  setTimeout(()=>map.resize(),310);
}

function switchTab(tab) {
  currentPath=tab;
  document.querySelectorAll('.w-tab').forEach(t=>t.classList.remove('active'));
  document.getElementById(`tab-${tab}`).classList.add('active');
  if(!currentData)return;
  renderPanelBody(currentData,tab);
  // Update Generate Report button label to reflect tab context
  const reportBtn = document.querySelector('#panel-actions .w-action-gold');
  if (reportBtn) {
    const label = tab === 'compare' ? 'Generate Full Comparison Report' : 'Generate PDF Report';
    reportBtn.innerHTML = `<i class="fas fa-file-pdf"></i> ${label}`;
  }
}

// ── Tool toggles ──────────────────────────────────────────────
function toolToggle(name) {
  // Toggle the clicked control
  toolState[name] = !toolState[name];
  document.getElementById('tgl-'+name).classList.toggle('active', toolState[name]);

  // Buyout and No Parking are exclusive-view filters — last-one-wins.
  // Turning one ON automatically turns the other OFF.
  if (name === 'buyout' && toolState[name] && toolState['nopark']) {
    toolState['nopark'] = false;
    document.getElementById('tgl-nopark').classList.remove('active');
  }
  if (name === 'nopark' && toolState[name] && toolState['buyout']) {
    toolState['buyout'] = false;
    document.getElementById('tgl-buyout').classList.remove('active');
  }

  applyToolState();
}
function applyToolState() {
  // ── Simple visibility toggles ────────────────────────────
  if(map.getLayer('transit-halos'))        map.setLayoutProperty('transit-halos','visibility',toolState.halos?'visible':'none');
  if(map.getLayer('skytrain-stops'))       map.setLayoutProperty('skytrain-stops','visibility',toolState.skytrain?'visible':'none');
  if(map.getLayer('skytrain-stops-ring'))  map.setLayoutProperty('skytrain-stops-ring','visibility',toolState.skytrain?'visible':'none');
  if(map.getLayer('permit-pins'))          map.setLayoutProperty('permit-pins','visibility',toolState.permits?'visible':'none');

  const nbVis = toolState.neighbourhoods ? 'visible' : 'none';
  if(map.getLayer('neighbourhood-lines'))   map.setLayoutProperty('neighbourhood-lines','visibility', nbVis);
  if(map.getLayer('neighbourhood-casing'))  map.setLayoutProperty('neighbourhood-casing','visibility', nbVis);
  if(map.getLayer('neighbourhood-labels'))  map.setLayoutProperty('neighbourhood-labels','visibility', nbVis);

  // When neighbourhood borders are ON, ensure they sit on top of the pins.
  // Mapbox render order = layer add order; moveLayer() pushes to the top.
  if (nbVis === 'visible') {
    try {
      if (map.getLayer('neighbourhood-casing')) map.moveLayer('neighbourhood-casing');
      if (map.getLayer('neighbourhood-lines'))  map.moveLayer('neighbourhood-lines');
      if (map.getLayer('neighbourhood-labels')) map.moveLayer('neighbourhood-labels');
    } catch(e) { /* layer order is a nice-to-have, not critical */ }
  }

  // ── Lot pin filter logic ─────────────────────────────────
  // Eligibility toggles (6unit/4unit/duplex) are independent show/hide switches.
  // Buyout and No Parking are exclusive filters — when ON, they hide all
  // eligibility pins and show only matching lots (last-one-wins).
  //
  // Pin color categories (from lot-pins addLayer paint expression):
  //   • Heritage (any category)                 → dark blue
  //   • width ≥15.1 + transit + lane            → green (6-unit)
  //   • width ≥10.0 + lane                      → teal (4-unit)
  //   • width ≥7.5 + lane                       → amber (duplex/3-unit)
  //   • everything else                         → gray (below-min)
  //
  // We match the same buckets when building the filter, so toggling a
  // category on/off corresponds exactly to its visible pin color.

  let filterExpr = null;

  if (toolState.buyout) {
    // Exclusive: show only 14.5m ≤ width < 15.1m + lane + transit
    filterExpr = ['all',
      ['>=',['get','lot_width_m'],14.5],
      ['<', ['get','lot_width_m'],15.1],
      ['==',['get','lane_access'],1],
      ['==',['get','transit_proximate'],1]
    ];
  } else if (toolState.nopark) {
    // Exclusive: show only lots within transit walking distance
    filterExpr = ['==',['get','transit_proximate'],1];
  } else {
    // Independent show/hide per eligibility tier.
    // Build an OR expression of whichever tiers are enabled.
    // Tier definitions mirror the lot-pins color logic exactly.
    const tierConditions = [];

    // Below-minimum (gray): always visible (user said keep option C).
    // Gray = NOT heritage AND NOT (width>=7.5 AND lane)
    const grayCondition = ['all',
      ['any',
        ['==',['get','heritage_category'],'none'],
        ['==',['get','heritage_category'],null]
      ],
      ['any',
        ['<', ['get','lot_width_m'],7.5],
        ['==',['get','lane_access'],0]
      ]
    ];
    tierConditions.push(grayCondition);

    // Heritage pins — always visible regardless of eligibility toggles
    // (they're colored dark blue, not green/teal/amber, so the toggles don't map to them)
    tierConditions.push(['all',
      ['!=',['get','heritage_category'],'none'],
      ['!=',['get','heritage_category'],null]
    ]);

    if (toolState['6unit']) {
      // Green tier: width ≥15.1 + transit + lane, NOT heritage
      tierConditions.push(['all',
        ['any',
          ['==',['get','heritage_category'],'none'],
          ['==',['get','heritage_category'],null]
        ],
        ['>=',['get','lot_width_m'],15.1],
        ['==',['get','transit_proximate'],1],
        ['==',['get','lane_access'],1]
      ]);
    }
    if (toolState['4unit']) {
      // Teal tier: width ≥10.0 + lane, excluding 6-unit tier, NOT heritage
      tierConditions.push(['all',
        ['any',
          ['==',['get','heritage_category'],'none'],
          ['==',['get','heritage_category'],null]
        ],
        ['>=',['get','lot_width_m'],10.0],
        ['==',['get','lane_access'],1],
        // Exclude lots that already match 6-unit tier
        ['any',
          ['<', ['get','lot_width_m'],15.1],
          ['==',['get','transit_proximate'],0]
        ]
      ]);
    }
    if (toolState['duplex']) {
      // Amber tier: width ≥7.5 + lane, excluding 4-unit tier, NOT heritage
      tierConditions.push(['all',
        ['any',
          ['==',['get','heritage_category'],'none'],
          ['==',['get','heritage_category'],null]
        ],
        ['>=',['get','lot_width_m'],7.5],
        ['<', ['get','lot_width_m'],10.0],
        ['==',['get','lane_access'],1]
      ]);
    }

    // Union of all enabled tiers
    filterExpr = tierConditions.length > 0 ? ['any'].concat(tierConditions) : null;
  }

  if(map.getLayer('lot-pins'))map.setFilter('lot-pins', filterExpr);

  // Saved-lot hearts always stay visible regardless of filter state
  if(map.getLayer('saved-lot-hearts'))map.setFilter('saved-lot-hearts',null);

  // ── Constraint overlays ──────────────────────────────────
  const herVis   = toolState.heritage ? 'visible' : 'none';
  const peatVis  = toolState.peat     ? 'visible' : 'none';
  const floodVis = toolState.flood    ? 'visible' : 'none';
  if(map.getLayer('heritage-ring'))  map.setLayoutProperty('heritage-ring','visibility', herVis);
  if(map.getLayer('peat-overlay'))   map.setLayoutProperty('peat-overlay','visibility',  peatVis);
  if(map.getLayer('flood-overlay'))  map.setLayoutProperty('flood-overlay','visibility', floodVis);
}

// ── Draggable tool menu ───────────────────────────────────────
(function(){
  const menu=document.getElementById('tool-menu'), handle=document.getElementById('tool-drag-handle');
  let dragging=false,startX,startY,origX,origY;
  handle.addEventListener('mousedown',e=>{ dragging=true;startX=e.clientX;startY=e.clientY;const rect=menu.getBoundingClientRect();origX=rect.left;origY=rect.top;menu.style.transition='none';e.preventDefault(); });
  document.addEventListener('mousemove',e=>{ if(!dragging)return;const dx=e.clientX-startX,dy=e.clientY-startY;menu.style.left=Math.max(0,Math.min(window.innerWidth-menu.offsetWidth,origX+dx))+'px';menu.style.top=Math.max(56,Math.min(window.innerHeight-menu.offsetHeight,origY+dy))+'px'; });
  document.addEventListener('mouseup',()=>{ dragging=false; });
  handle.addEventListener('touchstart',e=>{ const t=e.touches[0];dragging=true;startX=t.clientX;startY=t.clientY;const rect=menu.getBoundingClientRect();origX=rect.left;origY=rect.top; },{passive:true});
  document.addEventListener('touchmove',e=>{ if(!dragging)return;const t=e.touches[0],dx=t.clientX-startX,dy=t.clientY-startY;menu.style.left=Math.max(0,Math.min(window.innerWidth-menu.offsetWidth,origX+dx))+'px';menu.style.top=Math.max(56,Math.min(window.innerHeight-menu.offsetHeight,origY+dy))+'px'; },{passive:true});
  document.addEventListener('touchend',()=>{ dragging=false; });
})();

// ── Gate 1 render ─────────────────────────────────────────────
function renderGate1(d) {
  if(d.error){showError(d.error);return;}
  document.getElementById('ph-address').textContent=d.address||'—';
  document.getElementById('ph-pid').textContent=`PID ${d.pid}`;
  const elig=getEligBadge(d.lot_width_m,d.transit_proximate,d.lane_access);
  document.getElementById('ph-badge').innerHTML=`<div class="w-elig-badge ${elig.cls}">${elig.label}</div>`;
  document.getElementById('panel-body').innerHTML=`
    <div class="w-section"><div class="w-section-title">Property Details</div><div class="w-specs">
      <div class="w-spec"><div class="w-spec-label">Width</div><div class="w-spec-val">${(d.lot_width_m/0.3048).toFixed(1)} ft</div><div class="w-spec-sub">${d.lot_width_m}m</div></div>
      <div class="w-spec"><div class="w-spec-label">Area</div><div class="w-spec-val">${Math.round(d.lot_area_sqm*10.7639).toLocaleString()} sf</div><div class="w-spec-sub">${d.lot_area_sqm}m²</div></div>
      <div class="w-spec"><div class="w-spec-label">Lane</div><div class="w-spec-val">${d.lane_access?'✓ Yes':'✗ No'}</div></div>
      <div class="w-spec"><div class="w-spec-label">Transit</div><div class="w-spec-val">${d.transit_proximate?'✓ Yes':'✗ No'}</div></div>
    </div></div>
    <div style="position:relative">
      <div class="w-section w-gate-blur"><div class="w-section-title">Pro Forma</div>
        <div class="w-pf-row"><span>Exit Value</span><span class="w-pf-val">$X,XXX,XXX</span></div>
        <div class="w-pf-row"><span>Build Cost</span><span class="w-pf-val">$X,XXX,XXX</span></div>
        <div class="w-pf-row total"><span>Profit</span><span class="w-pf-val positive">$XXX,XXX</span></div>
        <div class="w-profit-box"><div class="w-profit-item"><div class="w-profit-label">ROI</div><div class="w-profit-val">XX%</div></div><div class="w-profit-item"><div class="w-profit-label">Margin/sqft</div><div class="w-profit-val">$XXX</div></div></div>
      </div>
      <div class="w-gate-overlay"><h4>Sign in to unlock</h4><p>Full pro forma, comparable sales, Wynston Outlook, and design recommendations.</p><a href="/log-in.php?next=/plex-map/" class="w-gate-btn">Sign In → Free Access</a></div>
    </div>`;
}

// ── Gate 2 render ─────────────────────────────────────────────
function renderPanel(d,tab) {
  if(d.error){showError(d.error);return;}
  document.getElementById('ph-address').textContent=d.property.address;
  document.getElementById('ph-pid').textContent=`PID ${d.property.pid}`;
  const elig=getEligBadge(d.property.lot_width_m,d.property.transit_proximate,d.property.lane_access);
  if(permitMeta){
    document.getElementById('ph-badge').innerHTML=`<div class="w-elig-badge badge-gold"><i class="fas fa-hard-hat"></i> Active Building Permit</div>`;
    document.getElementById('ph-confidence').innerHTML=`<div class="w-confidence conf-amber"><i class="fas fa-hard-hat" style="font-size:10px"></i><span><strong>Development In Progress</strong> — Feasibility data based on current market conditions.</span></div>`;
    permitMeta=null;
  } else {
    document.getElementById('ph-badge').innerHTML=`<div class="w-elig-badge ${elig.cls}">${elig.icon} ${elig.label}</div>`;
    const conf=d.confidence;
    document.getElementById('ph-confidence').innerHTML=`<div class="w-confidence conf-${conf.colour}"><i class="fas fa-chart-bar" style="font-size:10px"></i><span><strong>${conf.label}</strong> — ${conf.description}</span></div>`;
  }
  renderPanelBody(d,tab);
  const actions=document.getElementById('panel-actions');
  actions.style.display='flex';
  const reportBtnLabel = tab === 'compare' ? 'Generate Full Comparison Report' : 'Generate PDF Report';
  actions.innerHTML=`
    <button class="w-action-btn w-action-gold" onclick="openReport('${d.property.pid}')"><i class="fas fa-file-pdf"></i> ${reportBtnLabel}</button>
    <button class="w-action-btn w-action-primary" onclick="inquireAcquisition('${d.property.pid}','${escHtml(d.property.address)}')"><i class="fas fa-handshake"></i> Inquire for Acquisition</button>
    <button class="w-action-btn w-action-secondary" id="save-lot-btn" onclick="saveLot('${d.property.pid}')"><i class="far fa-heart"></i> Save Lot</button>`;
  updateSaveButton(d.property.pid);
}

function renderPanelBody(d,tab) {
  const body=document.getElementById('panel-body');
  if(tab==='strata')body.innerHTML=renderStrataTab(d);
  else if(tab==='rental')body.innerHTML=renderRentalTab(d);
  else if(tab==='compare')body.innerHTML=renderCompareTab(d);
}

function renderStrataTab(d) {
  const p=d.property, e=d.eligibility, s=d.strata, dom=d.dom, md=d.market_data||{};

  // ── Overrides (land cost and build $/sqft) ──────────────────────────────────
  if (!window._pfOverrides) window._pfOverrides = {};
  const origBuildPsf = s.buildable_sqft > 0 ? (s.build_cost / s.buildable_sqft) : 420;
  const landVal      = window._pfOverrides.land  != null ? window._pfOverrides.land  : s.land_cost;
  const buildPsf     = window._pfOverrides.build != null ? window._pfOverrides.build : origBuildPsf;

  // ── Construction Financing overrides (Session NEW) ──────────────────────────
  // Stored as whole numbers (65, 7, 15) in _pfOverrides.cfin_*, converted to fractions at calc time
  const cfinLtcPct   = window._pfOverrides.cfin_ltc  != null ? window._pfOverrides.cfin_ltc  : 65;
  const cfinRatePct  = window._pfOverrides.cfin_rate != null ? window._pfOverrides.cfin_rate : 7;
  const cfinTermMo   = window._pfOverrides.cfin_term != null ? window._pfOverrides.cfin_term : 15;
  const cfinEdited   = window._pfOverrides.cfin_ltc != null || window._pfOverrides.cfin_rate != null || window._pfOverrides.cfin_term != null;

  // Session 16 — Strata financing scenario (Construction Financing vs All Cash)
  const strataFinScenario = window._strataFinScenario || 'construction';
  const isAllCashStrata   = strataFinScenario === 'all_cash';

  const isEdited     = window._pfOverrides.land  != null || window._pfOverrides.build != null || cfinEdited || isAllCashStrata;

  // ── HPI selection ───────────────────────────────────────────────────────────
  const useDetached    = window._pfOverrides.useDetached === true;
  const dupPsf         = md.avg_sold_psf   || 985;
  const detPsf         = md.detached_benchmark ? md.detached_benchmark.avg_psf : null;
  const activePsf      = (useDetached && detPsf) ? detPsf : dupPsf;

  // ── Session 15 — Standardized Design credit ($35k saved on soft costs) ─────
  const useStd = window._useStdDesign === true;
  const stdCredit = useStd ? 35000 : 0;

  // ── Recalculate with overrides — city fees always fixed ────────────────────
  const hardBuild  = s.buildable_sqft * buildPsf;
  const fixedFees  = s.dcl_city_wide + s.dcl_utilities + (s.metro_dcc||0) + s.permit_fees;
  const costBeforeFin = landVal + hardBuild + fixedFees + s.contingency - stdCredit;

  // Construction financing cost: (Land + Build + Fees) × LTC × Rate × (Term/12) / 2
  // /2 = average outstanding balance during draw (interest-only convention)
  // Session 16: all-cash skips the calc → no debt, no financing cost
  const cfinCost   = isAllCashStrata
    ? 0
    : costBeforeFin * (cfinLtcPct/100) * (cfinRatePct/100) * (cfinTermMo/12) * 0.5;

  const totalCost  = costBeforeFin + cfinCost;
  const exitVal    = s.saleable_sqft * activePsf;
  const profit     = exitVal - totalCost;
  const roi        = totalCost > 0 ? (profit / totalCost * 100) : 0;

  const warn149 = e.warning_149m
    ? `<div class="w-warning-149 w-section"><i class="fas fa-exclamation-triangle"></i><span><strong>0.2m from 6-unit eligibility.</strong> Neighbour buyout may unlock significantly higher exit value.</span></div>`
    : '';

  return `${warn149}${buildFlags(p)}
    <div class="w-section"><div class="w-section-title">Property Details</div><div class="w-specs">
      <div class="w-spec"><div class="w-spec-label">Width</div><div class="w-spec-val">${p.lot_width_ft} ft</div><div class="w-spec-sub">${p.lot_width_m}m</div></div>
      <div class="w-spec"><div class="w-spec-label">Area</div><div class="w-spec-val">${Number(p.lot_area_sqft).toLocaleString()} sf</div><div class="w-spec-sub">${p.lot_area_sqm}m²</div></div>
      <div class="w-spec"><div class="w-spec-label">FSR (Strata)</div><div class="w-spec-val">0.70</div></div>
      <div class="w-spec"><div class="w-spec-label">Buildable</div><div class="w-spec-val">${Math.round(s.buildable_sqft).toLocaleString()} sf</div></div>
      <div class="w-spec"><div class="w-spec-label">Saleable</div><div class="w-spec-val">${Math.round(s.saleable_sqft).toLocaleString()} sf</div></div>
      <div class="w-spec"><div class="w-spec-label">Parking</div><div class="w-spec-val">${e.parking_req===0?'None ✓':e.parking_req+' stalls'}</div></div>
    </div></div>

    <div class="w-section">
      <div class="w-section-title">Strata Pro Forma ${isEdited?'<span style="font-size:10px;color:var(--amber);font-weight:600;margin-left:6px">✎ Adjusted</span>':''}</div>

      <div class="w-pf-row"><span>Land Cost</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pf-land-display">${fmt(landVal)}</span>
          <input class="w-pf-edit-input" id="pf-land-input" type="text" value="${Math.round(landVal).toLocaleString()}" style="display:none" onblur="pfCommit('land')" onkeydown="if(event.key==='Enter')pfCommit('land')">
          <button class="w-pf-edit-btn" id="pf-land-btn" onclick="pfToggle('land')" title="Adjust acquisition price"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-sub">BC Assessment: ${fmt(s.land_cost)}${window._pfOverrides.land!=null?' · <span style="color:var(--amber)">Using adjusted price</span>':''}</div>

      <div class="w-pf-row"><span>Build Cost</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pf-build-display">${fmt(hardBuild)}</span>
          <input class="w-pf-edit-input" id="pf-build-input" type="text" value="${Math.round(buildPsf)}" style="display:none" onblur="pfCommit('build')" onkeydown="if(event.key==='Enter')pfCommit('build')">
          <button class="w-pf-edit-btn" id="pf-build-btn" onclick="pfToggle('build')" title="Adjust $/sqft"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-sub">$${Math.round(buildPsf).toLocaleString()}/sqft × ${Math.round(s.buildable_sqft).toLocaleString()} sqft${window._pfOverrides.build!=null?' · <span style="color:var(--amber)">Using adjusted rate</span>':''}</div>

      <div class="w-pf-row"><span>DCL + Metro DCC + Permit</span><span class="w-pf-val">${fmt(fixedFees)}</span></div>
      <div class="w-pf-sub">City fees — fixed</div>
      ${s.contingency>0?`<div class="w-pf-row"><span>Peat Contingency</span><span class="w-pf-val" style="color:var(--amber)">${fmt(s.contingency)}</span></div>`:''}

      <label class="w-stddesign">
        <input type="checkbox" ${useStd?'checked':''} onchange="toggleStdDesign(this.checked)">
        <div class="w-stddesign-body">
          <div class="w-stddesign-title">BC Standardized Design adopted</div>
          Skip custom architectural drawings — use a BC Provincial or CMHC pre-approved multiplex design. <span class="w-stddesign-credit">Credit: −$35,000</span>
        </div>
      </label>
      ${useStd?`<div class="w-pf-row"><span>Standardized design credit</span><span class="w-pf-val" style="color:#166534">−${fmt(stdCredit)}</span></div>`:''}

      ${isAllCashStrata
        ? `<div class="w-pf-row"><span>Construction Financing — All Cash</span><span class="w-pf-val">$0</span></div>
           <div class="w-pf-sub" style="color:#166534">No construction debt — project fully equity-funded.</div>`
        : `<div class="w-pf-row"><span>Construction Financing</span><span class="w-pf-val">${fmt(cfinCost)}</span></div>
           <div class="w-pf-sub">Editable below · ${cfinEdited?'<span style="color:var(--amber)">Adjusted</span>':'default assumption'}</div>`
      }

      <div class="w-pf-row total"><span>Total Project Cost</span><span class="w-pf-val">${fmt(totalCost)}</span></div>

      <div style="border-top:1px solid rgba(0,0,0,.07);margin:10px 0 6px"></div>

      <div class="w-hpi-row ${!useDetached?'active':'muted'}">
        <span class="w-hpi-label">New Duplex Only HPI</span>
        <span class="w-hpi-val">$${Math.round(dupPsf).toLocaleString()}/sqft${md.using_fallback?' <span style="color:#ccc;font-size:10px">(fallback)</span>':''}</span>
      </div>

      <div class="w-hpi-row ${useDetached?'active':''}">
        <span class="w-hpi-label">New Detached HPI</span>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="w-hpi-val">${detPsf?'$'+Math.round(detPsf).toLocaleString()+'/sqft':'<span style="color:#ccc;font-size:10px">No data</span>'}</span>
          ${detPsf?`<label class="w-toggle" title="Use Detached HPI for exit value">
            <input type="checkbox" ${useDetached?'checked':''} onchange="pfToggleDetached(this.checked)">
            <span class="w-toggle-slider"></span>
          </label>`:''}
        </div>
      </div>
      ${useDetached?`<div class="w-exit-note">Exit value using New Detached HPI</div>`:''}

      <div class="w-pf-row" style="margin-top:6px"><span>Exit Value</span><span class="w-pf-val">${fmt(exitVal)}</span></div>
      <div class="w-profit-box">
        <div class="w-profit-item"><div class="w-profit-label">Profit</div><div class="w-profit-val ${profit<0?'negative':''}">${fmt(profit)}</div></div>
        <div class="w-profit-item"><div class="w-profit-label">ROI</div><div class="w-profit-val ${roi<0?'negative':''}">${roi.toFixed(1)}%</div></div>
      </div>
      ${isEdited?`<div style="text-align:center;margin-top:8px"><button onclick="pfReset()" style="background:none;border:none;color:#bbb;font-size:11px;cursor:pointer;text-decoration:underline">Reset to original values</button></div>`:''}
    </div>

    <div class="w-section">
      <div class="w-section-title">Financing Path</div>
      <select id="strata-fin-scenario-dd" onchange="changeStrataFinScenario(this.value)"
              style="width:100%;padding:10px 12px;border:1px solid #d4d4d4;border-radius:6px;font-size:13px;background:#fff;color:#002446;font-weight:600;cursor:pointer;font-family:inherit">
        <option value="construction" ${!isAllCashStrata?'selected':''}>Construction Financing</option>
        <option value="all_cash"     ${ isAllCashStrata?'selected':''}>All Cash</option>
      </select>
      <div style="font-size:10px;color:#999;margin-top:6px;font-style:italic">Select All Cash to model a project with no construction debt.</div>
    </div>

    ${isAllCashStrata ? `
    <div class="w-section">
      <div class="w-section-title">Construction Financing — All Cash</div>
      <div class="w-pf-row"><span>Financing Cost</span><span class="w-pf-val">$0</span></div>
      <div style="font-size:11px;color:#666;margin-top:8px;line-height:1.5;font-style:italic">No construction debt — project fully equity-funded. In practice, most builders use land + construction financing; selecting All Cash isolates the impact of carrying cost on ROI.</div>
    </div>
    ` : `
    <div class="w-section">
      <div class="w-section-title">Construction Financing ${cfinEdited?'<span style="font-size:10px;color:var(--amber);font-weight:600;margin-left:6px">✎ Adjusted</span>':''}</div>
      <div class="w-pf-row"><span>Loan-to-cost (LTC)</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pf-cfin-ltc-display">${cfinLtcPct}%</span>
          <input class="w-pf-edit-input" id="pf-cfin-ltc-input" type="text" value="${cfinLtcPct}" style="display:none" onblur="pfCfinCommit('ltc')" onkeydown="if(event.key==='Enter')pfCfinCommit('ltc')">
          <button class="w-pf-edit-btn" id="pf-cfin-ltc-btn" onclick="pfCfinToggle('ltc')" title="Adjust loan-to-cost"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-row"><span>Interest rate</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pf-cfin-rate-display">${cfinRatePct}%</span>
          <input class="w-pf-edit-input" id="pf-cfin-rate-input" type="text" value="${cfinRatePct}" style="display:none" onblur="pfCfinCommit('rate')" onkeydown="if(event.key==='Enter')pfCfinCommit('rate')">
          <button class="w-pf-edit-btn" id="pf-cfin-rate-btn" onclick="pfCfinToggle('rate')" title="Adjust interest rate"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-row"><span>Term (months)</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pf-cfin-term-display">${cfinTermMo} mo</span>
          <input class="w-pf-edit-input" id="pf-cfin-term-input" type="text" value="${cfinTermMo}" style="display:none" onblur="pfCfinCommit('term')" onkeydown="if(event.key==='Enter')pfCfinCommit('term')">
          <button class="w-pf-edit-btn" id="pf-cfin-term-btn" onclick="pfCfinToggle('term')" title="Adjust construction term"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-row total"><span>Financing Cost</span><span class="w-pf-val">${fmt(cfinCost)}</span></div>
    </div>
    `}

    <div class="w-section"><div class="w-section-title">Market Velocity</div>
      <div class="w-dom-row"><span class="w-dom-arrow ${dom.colour}">${dom.arrow}</span><span class="w-pf-val">${dom.duplex_current} days</span><span class="w-dom-label">${dom.label}</span></div>
      ${p.neighbourhood?`<div style="font-size:11px;color:#aaa;margin-top:4px">${p.neighbourhood}</div>`:''}
    </div>`;
}

function renderRentalTab(d) {
  const r=d.rental;
  const p=d.property;
  const fin=r.financing||{};
  const cf=r.cash_flow||{};
  const proj=cf.projections||{};

  // ── Rental overrides (independent from strata) ──────────────────────────────
  if (!window._pfRentalOverrides) window._pfRentalOverrides = {};

  // Derive original build $/sqft from total_build_cost (excludes density bonus) / total_buildable
  // total_build_cost = base_build_cost + density_bonus_cost
  // base_build_cost = total_buildable_sqft * build_psf
  const origBuildPsf = r.total_buildable_sqft > 0
    ? ((r.base_build_cost || (r.total_build_cost - (r.density_bonus_cost||0))) / r.total_buildable_sqft)
    : 420;

  const landVal   = window._pfRentalOverrides.land  != null ? window._pfRentalOverrides.land  : r.land_cost;
  const buildPsf  = window._pfRentalOverrides.build != null ? window._pfRentalOverrides.build : origBuildPsf;
  const isEdited  = window._pfRentalOverrides.land != null || window._pfRentalOverrides.build != null;

  // ── Session 15 — Standardized Design credit ($35k saved on soft costs) ─────
  const useStd = window._useStdDesign === true;
  const stdCredit = useStd ? 35000 : 0;

  // ── Recalculate with overrides ──────────────────────────────────────────────
  // Keep density bonus, fees, contingency as-is from server response (unaffected by land/build edits)
  const baseBuild   = r.total_buildable_sqft * buildPsf;
  const totalBuild  = baseBuild + (r.density_bonus_cost || 0);
  const totalCost   = landVal + totalBuild + r.total_fees + r.contingency - stdCredit;

  // Recalculate cap rate + stabilized value with new total cost
  // NOI stays the same (rents haven't changed)
  const capRate         = totalCost > 0 ? (r.annual_noi / totalCost) * 100 : 0;
  const marketCap       = r.market_cap_rate || 0.04;
  const stabilizedValue = marketCap > 0 ? r.annual_noi / marketCap : 0;
  const valueCreated    = stabilizedValue - totalCost;

  // Rent rows (with subtle fallback indicator)
  const fb = r.fallback_used || {};
  const rentRows=['1br','2br','3br'].map(type=>{
    const rb=r.rent_breakdown[type]; if(!rb||rb.unit_count===0)return '';
    const dotColor=rb.variance_colour==='green'?'#22c55e':rb.variance_colour==='amber'?'#f59e0b':'#94a3b8';
    const fbMark = fb[type] ? ' <span style="font-size:9px;color:#bbb" title="Limited source data">◇</span>' : '';
    return `<tr><td>${rb.unit_count}× ${type.toUpperCase()}${fbMark}</td><td>${fmtK(rb.market_rent)}/mo</td><td><span class="w-rent-dot" style="background:${dotColor}"></span>${rb.variance_pct>0?'+':''}${rb.variance_pct.toFixed(0)}% vs CMHC</td></tr>`;
  }).join('');

 // Year 1 / 5 / 10 projection rows (Session B: pencil-edit aware)
  // If user edited financing terms, swap in recalculated annual debt service.
  // Rent growth and opex growth stay as server-returned (not user-editable).
  const finOvCf = window._pfFinOverrides || {};
  const finCfEdited = finOvCf.ltc != null || finOvCf.rate != null || finOvCf.amort != null;
  let adjY1 = proj.year_1, adjY5 = proj.year_5, adjY10 = proj.year_10;

  if (finCfEdited && !fin.is_all_cash && fin.equity_required) {
    const uLtc2   = finOvCf.ltc   != null ? finOvCf.ltc   : fin.ltc_pct;
    const uRate2  = finOvCf.rate  != null ? finOvCf.rate  : fin.interest_rate_pct;
    const uAmort2 = finOvCf.amort != null ? finOvCf.amort : fin.amort_years;
    const insPf   = (fin.scenario_key==='cmhc_mli') ? ((fin.insurance_prem_pct||0)/100) : 0;
    const tpc     = fin.ltc_pct > 0 ? (fin.equity_required / (1 - fin.ltc_pct/100)) : 0;
    const lb      = tpc * (uLtc2/100);
    const lt      = lb * (1 + insPf);
    const mr2     = (uRate2/100) / 12;
    const np2     = uAmort2 * 12;
    let mp2 = 0;
    if (lt > 0 && np2 > 0) {
      mp2 = (mr2 > 0)
        ? lt * (mr2 * Math.pow(1+mr2, np2)) / (Math.pow(1+mr2, np2) - 1)
        : lt / np2;
    }
    const newAnnualDebt = mp2 * 12;
    // Swap debt service + recalc cash flow per year; NOI is unchanged
    const swap = (p) => p ? { ...p, debt_service: newAnnualDebt, cash_flow: p.noi - newAnnualDebt } : p;
    adjY1  = swap(proj.year_1);
    adjY5  = swap(proj.year_5);
    adjY10 = swap(proj.year_10);
  }

  const y1 = adjY1, y5 = adjY5, y10 = adjY10;
  const ytp=cf.year_to_positive;

  const cfRow = (yr, p) => {
    if (!p) return '';
    const cfColor = p.cash_flow >= 0 ? 'var(--green,#22c55e)' : '#dc2626';
    const cfSign  = p.cash_flow < 0 ? '–' : '';
    return `<tr>
      <td style="font-weight:600">Year ${yr}</td>
      <td style="text-align:right;color:#666">${fmt(p.noi)}</td>
      <td style="text-align:right;color:#666">–${fmt(p.debt_service)}</td>
      <td style="text-align:right;font-weight:700;color:${cfColor}">${cfSign}${fmt(Math.abs(p.cash_flow))}</td>
    </tr>`;
  };

  // Headline metric colors
  const yieldColor = capRate >= (marketCap * 100) ? 'var(--green,#22c55e)' : 'var(--amber,#f59e0b)';
  const valueColor = valueCreated >= 0 ? 'var(--green,#22c55e)' : '#dc2626';

  return `<div class="w-section"><div class="w-section-title">Hold Metrics</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
        <div style="background:#f9f6f0;padding:10px;border-radius:6px">
          <div style="font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Yield on Cost</div>
          <div style="font-size:20px;font-weight:800;color:${yieldColor}">${capRate.toFixed(2)}%</div>
          <div style="font-size:10px;color:#999;margin-top:2px">vs ${(marketCap*100).toFixed(1)}% market cap</div>
        </div>
        <div style="background:#f9f6f0;padding:10px;border-radius:6px">
          <div style="font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Stabilized Value</div>
          <div style="font-size:20px;font-weight:800;color:var(--navy,#002446)">${fmt(stabilizedValue)}</div>
        </div>
        <div style="background:#f9f6f0;padding:10px;border-radius:6px">
          <div style="font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Value Created</div>
          <div style="font-size:20px;font-weight:800;color:${valueColor}">${valueCreated>=0?'+':''}${fmt(valueCreated)}</div>
        </div>
      </div>
    </div>

    <div class="w-section"><div class="w-section-title">Hold-Through Projection</div>
      <table style="width:100%;font-size:12px;border-collapse:collapse">
        <thead>
          <tr style="color:#666;text-transform:uppercase;letter-spacing:.05em;font-size:10px">
            <th style="text-align:left;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.1)">&nbsp;</th>
            <th style="text-align:right;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.1)">NOI</th>
            <th style="text-align:right;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.1)">Debt</th>
            <th style="text-align:right;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.1)">Cash Flow</th>
          </tr>
        </thead>
        <tbody>
          ${cfRow(1,y1)}${cfRow(5,y5)}${cfRow(10,y10)}
        </tbody>
      </table>
      ${ytp?`<div style="font-size:10px;color:#999;margin-top:8px">Project cash-flow positive by <strong style="color:var(--green,#22c55e)">Year ${ytp}</strong>.</div>`:''}
    </div>

    <div class="w-section">
      <div class="w-section-title">Rental Pro Forma (1.00 FSR) ${isEdited?'<span style="font-size:10px;color:var(--amber);font-weight:600;margin-left:6px">✎ Adjusted</span>':''}</div>

      <div class="w-pf-row"><span>Land Cost</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pfr-land-display">${fmt(landVal)}</span>
          <input class="w-pf-edit-input" id="pfr-land-input" type="text" value="${Math.round(landVal).toLocaleString()}" style="display:none" onblur="pfRentalCommit('land')" onkeydown="if(event.key==='Enter')pfRentalCommit('land')">
          <button class="w-pf-edit-btn" id="pfr-land-btn" onclick="pfRentalToggle('land')" title="Adjust acquisition price"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-sub">BC Assessment: ${fmt(r.land_cost)}${window._pfRentalOverrides.land!=null?' · <span style="color:var(--amber)">Using adjusted price</span>':''}</div>

      <div class="w-pf-row"><span>Build Cost</span>
        <div class="w-pf-edit-wrap">
          <span class="w-pf-val" id="pfr-build-display">${fmt(totalBuild)}</span>
          <input class="w-pf-edit-input" id="pfr-build-input" type="text" value="${Math.round(buildPsf)}" style="display:none" onblur="pfRentalCommit('build')" onkeydown="if(event.key==='Enter')pfRentalCommit('build')">
          <button class="w-pf-edit-btn" id="pfr-build-btn" onclick="pfRentalToggle('build')" title="Adjust $/sqft"><i class="fas fa-pencil-alt"></i></button>
        </div>
      </div>
      <div class="w-pf-sub">$${Math.round(buildPsf).toLocaleString()}/sqft × ${Math.round(r.total_buildable_sqft).toLocaleString()} sqft + density bonus ${window._pfRentalOverrides.build!=null?' · <span style="color:var(--amber)">Adjusted</span>':''}</div>

      <div class="w-pf-row"><span>DCL + Metro DCC + Permit</span><span class="w-pf-val">${fmt(r.total_fees)}</span></div>
      <div class="w-pf-sub">City fees — fixed</div>
      ${r.contingency>0?`<div class="w-pf-row"><span>Peat Contingency</span><span class="w-pf-val" style="color:var(--amber)">${fmt(r.contingency)}</span></div>`:''}

      <label class="w-stddesign">
        <input type="checkbox" ${useStd?'checked':''} onchange="toggleStdDesign(this.checked)">
        <div class="w-stddesign-body">
          <div class="w-stddesign-title">BC Standardized Design adopted</div>
          Skip custom architectural drawings — use a BC Provincial or CMHC pre-approved multiplex design. <span class="w-stddesign-credit">Credit: −$35,000</span>
        </div>
      </label>
      ${useStd?`<div class="w-pf-row"><span>Standardized design credit</span><span class="w-pf-val" style="color:#166534">−${fmt(stdCredit)}</span></div>`:''}

      <div class="w-pf-row total"><span>Total Project Cost</span><span class="w-pf-val">${fmt(totalCost)}</span></div>

      ${isEdited?`<div style="text-align:center;margin-top:8px"><button onclick="pfRentalReset()" style="background:none;border:none;color:#bbb;font-size:11px;cursor:pointer;text-decoration:underline">Reset to original values</button></div>`:''}
    </div>

    <div class="w-section"><div class="w-section-title">Rental Income</div>
      <table class="w-rent-table"><thead><tr><th>Mix</th><th>Market Rent</th><th>vs CMHC</th></tr></thead><tbody>${rentRows}</tbody></table>
      <div style="margin-top:10px">
        <div class="w-pf-row"><span>Gross Monthly</span><span class="w-pf-val">${fmtK(r.gross_monthly)}/mo</span></div>
        <div class="w-pf-row"><span>Annual Gross</span><span class="w-pf-val">${fmt(r.annual_gross)}</span></div>
        <div class="w-pf-row"><span>Vacancy (${(r.vacancy_rate*100).toFixed(0)}%)</span><span class="w-pf-val" style="color:var(--amber)">–${fmt(r.annual_gross-r.effective_gross)}</span></div>
        <div class="w-pf-row"><span>Operating Expenses (${(r.operating_expense_rate*100).toFixed(0)}%)</span><span class="w-pf-val" style="color:var(--amber)">–${fmt(r.opex_amount||r.effective_gross-r.annual_noi)}</span></div>
        <div class="w-pf-row total"><span>Net Operating Income</span><span class="w-pf-val positive">${fmt(r.annual_noi)}</span></div>
      </div>
    </div>

    ${(() => {
      const scenarioKey    = fin.scenario_key   || 'cmhc_mli';
      const scenarioLabel  = fin.scenario_label || 'CMHC MLI Select';
      const isAllCash      = fin.is_all_cash    === true;
      const needsCovenant  = fin.requires_covenant === true;

      // Dropdown is always visible
      const dropdown = `
        <div class="w-section">
          <div class="w-section-title">Financing Path</div>
          <select id="financing-scenario-dd" onchange="changeFinancingScenario(this.value)"
                  style="width:100%;padding:10px 12px;border:1px solid #d4d4d4;border-radius:6px;font-size:13px;background:#fff;color:#002446;font-weight:600;cursor:pointer;font-family:inherit">
            <option value="cmhc_mli"     ${scenarioKey==='cmhc_mli'    ?'selected':''}>CMHC MLI Select</option>
            <option value="conventional" ${scenarioKey==='conventional'?'selected':''}>Conventional Rental</option>
            <option value="private"      ${scenarioKey==='private'     ?'selected':''}>Private / B-Lender</option>
            <option value="all_cash"     ${scenarioKey==='all_cash'    ?'selected':''}>All Cash</option>
          </select>
          <div style="font-size:10px;color:#999;margin-top:6px;font-style:italic">Switching paths resets financing edits to this scenario's defaults.</div>
        </div>`;

      // All-Cash short-circuit: no debt to edit
      if (isAllCash) {
        return dropdown + `
        <div class="w-section">
          <div class="w-section-title">${scenarioLabel}</div>
          <div class="w-pf-row"><span>Equity Required (100%)</span><span class="w-pf-val" style="font-weight:700">${fmt(fin.equity_required)}</span></div>
          <div style="font-size:11px;color:#666;margin-top:8px;line-height:1.5;font-style:italic">Structured as equity investment — no debt service.</div>
        </div>`;
      }

      if (!fin.equity_required) return dropdown;

      // ── Client-side recalc with user's pencil-edits ───────────────────────────
      const finOv = window._pfFinOverrides || {};
      // User-editable terms (fall back to scenario defaults from server)
      const uLtc   = finOv.ltc   != null ? finOv.ltc   : fin.ltc_pct;            // %
      const uRate  = finOv.rate  != null ? finOv.rate  : fin.interest_rate_pct;  // %
      const uAmort = finOv.amort != null ? finOv.amort : fin.amort_years;        // years
      const isEdited = finOv.ltc != null || finOv.rate != null || finOv.amort != null;

      // Insurance premium: auto-applied per scenario (CMHC only), not user-editable
      const insPremFrac = scenarioKey === 'cmhc_mli' ? ((fin.insurance_prem_pct || 0) / 100) : 0;

      // Recalc loan, payment, debt service from user's terms
      // Total project cost = loan_total_est / (ltc * (1+premium))  — back it out from server
      // Safer: total project cost = equity_required / (1 - server_ltc/100)
      const totalProjectCost = fin.ltc_pct > 0 ? (fin.equity_required / (1 - fin.ltc_pct/100)) : 0;
      const uLoanBase   = totalProjectCost * (uLtc / 100);
      const uLoanTotal  = uLoanBase * (1 + insPremFrac);
      const uMonthlyRate = (uRate / 100) / 12;
      const uNPayments   = uAmort * 12;
      let uMonthlyPmt = 0;
      if (uLoanTotal > 0 && uNPayments > 0) {
        if (uMonthlyRate > 0) {
          uMonthlyPmt = uLoanTotal * (uMonthlyRate * Math.pow(1+uMonthlyRate, uNPayments)) / (Math.pow(1+uMonthlyRate, uNPayments) - 1);
        } else {
          uMonthlyPmt = uLoanTotal / uNPayments;
        }
      }
      const uAnnualDebt = uMonthlyPmt * 12;
      const uEquity     = totalProjectCost * (1 - uLtc / 100);

      const editedBadge = isEdited ? ' <span style="font-size:9px;color:var(--amber,#f59e0b);font-weight:700;letter-spacing:.3px">· EDITED</span>' : '';

      // Pencil-edit helper (generates display+input pair for one field)
      const pencilRow = (field, label, value, suffix, step, min, max) => `
        <div class="w-pf-row" style="align-items:center">
          <span>${label}</span>
          <span style="display:flex;align-items:center;gap:6px">
            <span id="pff-${field}-display" class="w-pf-val">${value}${suffix}</span>
            <input id="pff-${field}-input" type="number" step="${step}" min="${min}" max="${max}" value="${value}"
                   style="display:none;width:80px;padding:4px 7px;border:1px solid var(--gold,#c9a84c);border-radius:4px;font-size:13px;text-align:right;font-weight:600"
                   onblur="pfFinCommit('${field}')" onkeydown="if(event.key==='Enter')pfFinCommit('${field}');if(event.key==='Escape'){document.getElementById('pff-${field}-input').value='${value}';pfFinCommit('${field}')}">
            <button id="pff-${field}-btn" onclick="pfFinToggle('${field}')" type="button"
                    style="background:none;border:0;cursor:pointer;color:#999;padding:2px 4px;font-size:11px"
                    title="Edit"><i class="fas fa-pencil-alt"></i></button>
          </span>
        </div>`;

      const resetBtn = isEdited ? `
        <div style="text-align:right;margin-top:6px">
          <button type="button" onclick="pfFinReset()" style="background:none;border:0;color:var(--amber,#f59e0b);font-size:11px;cursor:pointer;font-weight:600;text-decoration:underline">↺ Reset to scenario defaults</button>
        </div>` : '';

      const financingBlock = `
        <div class="w-section">
          <div class="w-section-title">${scenarioLabel} Financing${editedBadge}</div>
          <div class="w-pf-row"><span>Equity Required</span><span class="w-pf-val" style="font-weight:700">${fmt(uEquity)}</span></div>
          <div class="w-pf-row"><span>${scenarioKey==='cmhc_mli'?'Insured Loan':'Loan Amount'}</span><span class="w-pf-val">${fmt(uLoanTotal)}</span></div>
          <div class="w-pf-row"><span>Monthly Payment</span><span class="w-pf-val">${fmt(uMonthlyPmt)}/mo</span></div>
          <div class="w-pf-row"><span>Annual Debt Service</span><span class="w-pf-val">${fmt(uAnnualDebt)}</span></div>
          <div style="height:1px;background:#e5e7eb;margin:10px 0"></div>
          <div style="font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Lender Terms · Tap pencil to edit</div>
          ${pencilRow('ltc',   'Loan-to-Cost',       uLtc,   '%',  '0.5',  '0',   '95')}
          ${pencilRow('rate',  'Interest Rate',      uRate,  '%',  '0.05', '0',   '20')}
          ${pencilRow('amort', 'Amortization',       uAmort, ' yr','1',    '1',   '50')}
          ${insPremFrac > 0 ? `<div class="w-pf-row"><span style="color:#999;font-size:11px">CMHC Insurance Premium</span><span class="w-pf-val" style="color:#999;font-size:11px">${(insPremFrac*100).toFixed(2)}%</span></div>` : ''}
          ${resetBtn}
        </div>`;

      const covenantBlock = needsCovenant ? `
        <div class="w-section" style="background:rgba(245,158,11,0.07);border-left:3px solid var(--amber,#f59e0b);padding:10px 12px">
          <div style="font-size:11px;color:#78350f;line-height:1.5"><strong>Section 219 Rental Covenant:</strong> The 1.00 FSR density bonus requires all units remain rental tenure (typically 60 years). Individual strata sales prohibited. Building may be sold as a single income-producing asset.</div>
        </div>` : '';

      return dropdown + financingBlock + covenantBlock;
    })()}`;
}

function renderCompareTab(d) {
  const p = d.property, s = d.strata, r = d.rental, md = d.market_data || {};
  if (!s || !r) {
    return `<div class="w-section" style="color:#888;font-size:13px;text-align:center;padding:32px"><i class="fas fa-balance-scale" style="font-size:24px;margin-bottom:12px;display:block;color:#ddd"></i>Comparison requires both Strata and Rental data.</div>`;
  }

  // ── Strata side — apply same overrides as Strata tab ───────────────────────
  if (!window._pfOverrides) window._pfOverrides = {};
  const sOrigBuildPsf = s.buildable_sqft > 0 ? (s.build_cost / s.buildable_sqft) : 420;
  const sLandVal   = window._pfOverrides.land  != null ? window._pfOverrides.land  : s.land_cost;
  const sBuildPsf  = window._pfOverrides.build != null ? window._pfOverrides.build : sOrigBuildPsf;
  const sCfinLtc   = window._pfOverrides.cfin_ltc  != null ? window._pfOverrides.cfin_ltc  : 65;
  const sCfinRate  = window._pfOverrides.cfin_rate != null ? window._pfOverrides.cfin_rate : 7;
  const sCfinTerm  = window._pfOverrides.cfin_term != null ? window._pfOverrides.cfin_term : 15;
  const sUseDetached = window._pfOverrides.useDetached === true;
  const sDupPsf      = md.avg_sold_psf || 985;
  const sDetPsf      = md.detached_benchmark ? md.detached_benchmark.avg_psf : null;
  const sActivePsf   = (sUseDetached && sDetPsf) ? sDetPsf : sDupPsf;

  const sHardBuild  = s.buildable_sqft * sBuildPsf;
  const sFixedFees  = s.dcl_city_wide + s.dcl_utilities + (s.metro_dcc||0) + s.permit_fees;
  const sCostBeforeFin = sLandVal + sHardBuild + sFixedFees + s.contingency;
  const sCfinCost   = sCostBeforeFin * (sCfinLtc/100) * (sCfinRate/100) * (sCfinTerm/12) * 0.5;
  const sTotalCost  = sCostBeforeFin + sCfinCost;
  const sExitVal    = s.saleable_sqft * sActivePsf;
  const sProfit     = sExitVal - sTotalCost;
  const sRoi        = sTotalCost > 0 ? (sProfit / sTotalCost * 100) : 0;

  // ── Rental side — apply same overrides as Rental tab ───────────────────────
  if (!window._pfRentalOverrides) window._pfRentalOverrides = {};
  const rOrigBuildPsf = r.total_buildable_sqft > 0
    ? ((r.base_build_cost || (r.total_build_cost - (r.density_bonus_cost||0))) / r.total_buildable_sqft)
    : 420;
  const rLandVal   = window._pfRentalOverrides.land  != null ? window._pfRentalOverrides.land  : r.land_cost;
  const rBuildPsf  = window._pfRentalOverrides.build != null ? window._pfRentalOverrides.build : rOrigBuildPsf;

  const rBaseBuild   = r.total_buildable_sqft * rBuildPsf;
  const rTotalBuild  = rBaseBuild + (r.density_bonus_cost || 0);
  const rTotalCost   = rLandVal + rTotalBuild + r.total_fees + r.contingency;
  const rCapRate     = rTotalCost > 0 ? (r.annual_noi / rTotalCost) * 100 : 0;
  const rMarketCap   = r.market_cap_rate || 0.04;
  const rStabilizedValue = rMarketCap > 0 ? r.annual_noi / rMarketCap : 0;
  const rValueCreated    = rStabilizedValue - rTotalCost;

  // Rental cash flow — Year 1 (approximation, matches rental tab)
  const fin  = r.financing || {};
  const cf   = r.cash_flow || {};
  const proj = cf.projections || {};
  const y1   = proj.year_1 || {};
  const y1CashFlow = y1.cash_flow || 0;
  const y10  = proj.year_10;
  const ytp  = cf.year_to_positive;

  // Row helper
  const cmpRow = (label, strataVal, rentalVal, opts={}) => {
    const sClass = opts.sClass || '';
    const rClass = opts.rClass || '';
    const note = opts.note ? `<div style="font-size:10px;color:#999;font-weight:400;margin-top:2px">${opts.note}</div>` : '';
    return `<tr>
      <td style="padding:9px 6px;border-bottom:1px solid rgba(0,0,0,.05);color:#444;font-size:12px">${label}${note}</td>
      <td style="padding:9px 6px;border-bottom:1px solid rgba(0,0,0,.05);text-align:right;font-weight:600;font-size:12px;background:rgba(22,101,52,0.04)" class="${sClass}">${strataVal}</td>
      <td style="padding:9px 6px;border-bottom:1px solid rgba(0,0,0,.05);text-align:right;font-weight:600;font-size:12px;background:rgba(29,78,216,0.04)" class="${rClass}">${rentalVal}</td>
    </tr>`;
  };

  const strataEdited = window._pfOverrides.land != null || window._pfOverrides.build != null
                    || window._pfOverrides.cfin_ltc != null || window._pfOverrides.cfin_rate != null || window._pfOverrides.cfin_term != null
                    || sUseDetached;
  const rentalEdited = window._pfRentalOverrides.land != null || window._pfRentalOverrides.build != null;
  const anyEdited    = strataEdited || rentalEdited;

  return `<div class="w-section">
      <div class="w-section-title">Path Comparison ${anyEdited?'<span style="font-size:10px;color:var(--amber);font-weight:600;margin-left:6px">✎ Adjusted</span>':''}</div>

      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="padding:8px 6px;border-bottom:2px solid rgba(0,0,0,.1);text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#666">&nbsp;</th>
            <th style="padding:8px 6px;border-bottom:2px solid rgba(0,0,0,.1);text-align:right;font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#166534;background:rgba(22,101,52,0.08)">Strata / Sell</th>
            <th style="padding:8px 6px;border-bottom:2px solid rgba(0,0,0,.1);text-align:right;font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#1d4ed8;background:rgba(29,78,216,0.08)">Rental / Hold</th>
          </tr>
        </thead>
        <tbody>
          ${cmpRow('FSR', '0.70', '1.00')}
          ${cmpRow('Buildable Area', Math.round(s.buildable_sqft).toLocaleString()+' sf', Math.round(r.total_buildable_sqft).toLocaleString()+' sf')}
          ${cmpRow('Land Cost', fmt(sLandVal), fmt(rLandVal))}
          ${cmpRow('Build Cost', fmt(sHardBuild), fmt(rTotalBuild))}
          ${cmpRow('City Fees', fmt(sFixedFees), fmt(r.total_fees))}
          ${cmpRow('Construction Financing', fmt(sCfinCost), '—')}
          ${cmpRow('Total Project Cost', '<strong>'+fmt(sTotalCost)+'</strong>', '<strong>'+fmt(rTotalCost)+'</strong>')}
          <tr><td colspan="3" style="height:8px"></td></tr>
          ${cmpRow('Exit / Income', fmt(sExitVal)+' sale', fmt(r.annual_noi)+'/yr NOI')}
          ${cmpRow('Return', sRoi.toFixed(1)+'% ROI', rCapRate.toFixed(2)+'% cap')}
          ${cmpRow('Equity Required', 'N/A <span style="font-size:10px;color:#999">(sell)</span>', fin.equity_required ? fmt(fin.equity_required) : '—')}
          ${cmpRow('Year 1 Outcome', '<span style="color:'+(sProfit>=0?'#166534':'#dc2626')+'">'+(sProfit>=0?'+':'')+fmt(sProfit)+' profit</span>', '<span style="color:'+(y1CashFlow>=0?'#166534':'#dc2626')+'">'+(y1CashFlow>=0?'+':'–')+fmt(Math.abs(y1CashFlow))+' cash flow</span>')}
          ${cmpRow('Day-1 Equity', '—', '<span style="color:'+(rValueCreated>=0?'#166534':'#dc2626')+'">'+(rValueCreated>=0?'+':'')+fmt(rValueCreated)+'</span>')}
          ${cmpRow('Long-term Value', fmt(sExitVal)+' <span style="color:#999;font-size:10px">(one-time)</span>', fmt(rStabilizedValue)+' <span style="color:#999;font-size:10px">(stabilized)</span>')}
          <tr><td colspan="3" style="height:8px"></td></tr>
          ${cmpRow('Covenant', 'None', '60-yr rental lock', {note: 'Section 219, Land Title Act'})}
          ${cmpRow('Capital Horizon', '12–18 months', '10+ years')}
          ${cmpRow('Unit Sale', '✓ Individual strata', '✗ Sell as single asset')}
        </tbody>
      </table>
    </div>`;
}

function buildFlags(p) {
  let f='';
  if(p.heritage_category==='A'||p.heritage_category==='B')f+=`<div class="w-flag w-flag-red"><i class="fas fa-landmark"></i><span><strong>Heritage Category ${p.heritage_category}</strong> — Permit delays likely. HRA required.</span></div>`;
  else if(p.heritage_category==='C')f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-landmark"></i><span><strong>Heritage Category C</strong> — Inspection may be required.</span></div>`;
  if(p.peat_zone)f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-exclamation-triangle"></i><span><strong>Peat Zone</strong> — $150,000 contingency added.</span></div>`;
  if(p.covenant_present)f+=`<div class="w-flag w-flag-blue"><i class="fas fa-file-contract"></i><span><strong>Covenant on Title</strong> — Obtain a full title search before proceeding.</span></div>`;
  if(p.easement_present)f+=`<div class="w-flag w-flag-blue"><i class="fas fa-road"></i><span><strong>Easement / Right of Way</strong> — ${p.easement_types||'Registered easement'}. Verify with a real estate lawyer.</span></div>`;
  if(!p.lane_access)f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-road"></i><span><strong>No lane access detected</strong> — Verify with COV.</span></div>`;
  if(p.floodplain_risk==='high')f+=`<div class="w-flag w-flag-red"><i class="fas fa-water"></i><span><strong>Floodplain</strong> — COV designated flood area. May affect permits, financing, and insurance.</span></div>`;

  return f?`<div class="w-section">${f}</div>`:'';
}

function getEligBadge(width,transit,lane) {
  if(width>=15.1&&transit&&lane)return{cls:'badge-green',icon:'●',label:'6-Unit Eligible'};
  if(width>=10.0&&lane)         return{cls:'badge-teal', icon:'●',label:'4-Unit Eligible'};
  if(width>=7.5&&lane)          return{cls:'badge-amber',icon:'●',label:'Duplex / 3-Unit'};
  return{cls:'badge-gray',icon:'○',label:'Below Minimum'};
}


// ── Action handlers ───────────────────────────────────────────
function inquireAcquisition(pid,address){
  if(confirm(`Submit acquisition inquiry for:\n${address}\n\nOur team will contact you within 4 hours.`))
    fetch('/api/acquisition.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({pid,address})}).then(r=>r.json()).then(d=>alert(d.success?'✓ Inquiry submitted.':'Something went wrong.'));
}

// ── Toast notification system ─────────────────────────────────
function showToast(msg,type='success',duration=3000){
  const wrap=document.getElementById('toast-wrap');
  if(!wrap)return;
  const icons={success:'<i class="fas fa-check-circle" style="margin-right:7px"></i>',info:'<i class="fas fa-info-circle" style="margin-right:7px"></i>',warn:'<i class="fas fa-exclamation-triangle" style="margin-right:7px"></i>'};
  const el=document.createElement('div');
  el.className=`w-toast ${type}`;
  el.innerHTML=(icons[type]||'')+escHtml(msg);
  wrap.appendChild(el);
  requestAnimationFrame(()=>requestAnimationFrame(()=>el.classList.add('show')));
  setTimeout(()=>{el.classList.remove('show');setTimeout(()=>el.remove(),280);},duration);
}

// ── Saved lot state ───────────────────────────────────────────
let savedLotPids=new Set();
// Cache of all lot features — populated once lots layer loads
let _lotsCache = [];

function loadSavedPids(){
  if(!IS_LOGGED_IN)return;
  fetch('/api/saved_lots.php').then(r=>r.json()).then(d=>{
    if(d.success&&d.lots){
      savedLotPids=new Set(d.lots.map(l=>l.pid));
      updateSavedLotLayer();
    }
  }).catch(()=>{});
}

// Build heart layer from saved PIDs + lots GeoJSON cache
function updateSavedLotLayer(){
  if(!map.getSource('saved-lots')) return;
  // If we don't have the lots cache yet, fetch it
  if(_lotsCache.length===0){
    const src=map.getSource('lots');
    // Try to get from loaded source first
    if(src&&src._data&&src._data.features&&src._data.features.length>0){
      _lotsCache=src._data.features;
    } else {
      // Source not loaded yet — fetch directly
      fetch('/api/lots.php?v=2').then(r=>r.json()).then(data=>{
        if(data.features){ _lotsCache=data.features; _renderHearts(); }
      }).catch(()=>{});
      return;
    }
  }
  _renderHearts();
}

function _renderHearts(){
  if(!map.getSource('saved-lots')) return;
  const heartFeatures = _lotsCache.filter(f=>savedLotPids.has(f.properties.pid));
  map.getSource('saved-lots').setData({
    type:'FeatureCollection',
    features: heartFeatures
  });
}

function updateSaveButton(pid){
  const btn=document.getElementById('save-lot-btn');
  if(!btn)return;
  const saved=savedLotPids.has(pid);
  btn.innerHTML=saved
    ?'<i class="fas fa-heart" style="color:#ef4444"></i> Saved'
    :'<i class="far fa-heart"></i> Save Lot';
  btn.style.borderColor=saved?'rgba(239,68,68,.4)':'';
}

function saveLot(pid){
  fetch('/api/save_lot.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({pid})})
  .then(r=>r.json()).then(d=>{
    if(d.success){
      if(d.saved){savedLotPids.add(pid);showToast('Saved to Project Planner','success');}
      else{savedLotPids.delete(pid);showToast('Removed from saved list','info');}
      updateSaveButton(pid);
      updateSavedLotLayer(); // ← refresh hearts on map
    } else {showToast('Could not save lot. Please try again.','warn');}
  }).catch(()=>showToast('Network error. Please try again.','warn'));
}

function inquireAcquisition(pid,address){
  const msg=prompt(`Acquisition inquiry for:\n${address}\n\nAdd a message (optional — press Cancel to abort):`);
  if(msg===null)return;
  fetch('/api/inquire.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({pid,message:msg.trim()})})
  .then(r=>r.json()).then(d=>{
    if(d.success)showToast(d.already_exists?'Inquiry already open — our team will be in touch.':'✓ Inquiry submitted. We\'ll contact you within 4 hours.',d.already_exists?'info':'success',4500);
    else showToast('Something went wrong. Please try again.','warn');
  }).catch(()=>showToast('Network error. Please try again.','warn'));
}

// ── Search ────────────────────────────────────────────────────
let searchTimer=null;
const searchInput=document.getElementById('search-input');
const searchResults=document.getElementById('search-results');
const searchClear=document.getElementById('search-clear');

searchInput.addEventListener('input',()=>{
  const q=searchInput.value.trim();
  searchClear.classList.toggle('visible',q.length>0);
  if(q.length<2){closeSearchDropdown();return;}
  clearTimeout(searchTimer);
  searchTimer=setTimeout(()=>runSearch(q),250);
});
searchInput.addEventListener('keydown',e=>{
  if(e.key==='Escape'){clearSearch();return;}
  if(e.key==='Enter'){const f=searchResults.querySelector('.w-search-result');if(f)f.click();}
  if(e.key==='ArrowDown'){e.preventDefault();const items=searchResults.querySelectorAll('.w-search-result');if(items.length)items[0].focus();}
});
searchResults.addEventListener('keydown',e=>{
  const items=[...searchResults.querySelectorAll('.w-search-result')];
  const idx=items.indexOf(document.activeElement);
  if(e.key==='ArrowDown'&&idx<items.length-1){e.preventDefault();items[idx+1].focus();}
  if(e.key==='ArrowUp'){e.preventDefault();idx>0?items[idx-1].focus():searchInput.focus();}
  if(e.key==='Escape')clearSearch();
});
document.addEventListener('click',e=>{if(!document.getElementById('search-wrap').contains(e.target))closeSearchDropdown();});

function runSearch(q){
  fetch(`/api/search.php?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(results=>renderSearchResults(results,q)).catch(()=>{});
}
function renderSearchResults(results,q){
  if(!results.length){searchResults.innerHTML=`<div class="w-search-no-results">No lots found for "${escHtml(q)}"</div>`;searchResults.classList.add('open');return;}
  searchResults.innerHTML=results.map(r=>{
    const elig=getEligBadge(r.lot_width_m,r.transit_proximate,r.lane_access);
    const iconColor=elig.cls.replace('badge-','');
    return `<div class="w-search-result" tabindex="0"
      onclick="selectSearchResult(${r.lat},${r.lng},'${escHtml(r.pid)}')"
      onkeydown="if(event.key==='Enter')selectSearchResult(${r.lat},${r.lng},'${escHtml(r.pid)}')">
      <div class="w-search-result-icon ${iconColor}">●</div>
      <div class="w-search-result-body">
        <div class="w-search-result-addr">${highlightMatch(r.address,q)}</div>
        <div class="w-search-result-meta">${r.lot_width_m}m wide · PID ${r.pid}</div>
      </div></div>`;
  }).join('');
  searchResults.classList.add('open');
}

// Search result — fly to lot AND set highlight ring simultaneously
function selectSearchResult(lat,lng,pid){
  closeSearchDropdown();
  searchInput.value='';
  searchClear.classList.remove('visible');
  map.flyTo({center:[lng,lat],zoom:17,duration:900});
  setSelectedLot(lat,lng);
  setTimeout(()=>openPanel(pid),300);
}

function clearSearch(){searchInput.value='';searchClear.classList.remove('visible');closeSearchDropdown();searchInput.focus();}
function closeSearchDropdown(){searchResults.classList.remove('open');searchResults.innerHTML='';}
function highlightMatch(address,q){
  const safe=escHtml(address),safeQ=q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
  try{return safe.replace(new RegExp(`(${safeQ})`,'i'),'<strong style="color:var(--gold)">$1</strong>')}catch(e){return safe;}
}

// ── Tool menu minimize ────────────────────────────────────────
let toolMenuMinimized=false;
function toggleToolMenu(){
  toolMenuMinimized=!toolMenuMinimized;
  const sections=document.getElementById('tool-sections'),icon=document.getElementById('tool-menu-icon');
  if(sections)sections.style.display=toolMenuMinimized?'none':'block';
  if(icon)icon.className=toolMenuMinimized?'fas fa-plus':'fas fa-minus';
}

// ── Header minimize ───────────────────────────────────────────
let headerMinimized=false;
function toggleHeader(){
  headerMinimized=!headerMinimized;
  const header=document.querySelector('.w-header'),restoreBtn=document.getElementById('header-restore-btn');
  const searchWrap=document.getElementById('search-wrap'),mapEl=document.getElementById('map');
  const panel=document.getElementById('panel'),toolMenu=document.getElementById('tool-menu');
  if(headerMinimized){
    header.style.cssText='height:0;overflow:hidden;border:none;padding:0';
    if(restoreBtn)restoreBtn.style.display='block';
    if(searchWrap)searchWrap.style.top='10px';
    if(mapEl){mapEl.style.top='0';mapEl.style.bottom='0';mapEl.style.height='100%';}
    if(panel){panel.style.top='0';panel.style.height='100%';}
    if(toolMenu)toolMenu.style.top='60px';
    setTimeout(()=>map.resize(),50);
  } else {
    header.style.cssText='';
    if(restoreBtn)restoreBtn.style.display='none';
    if(searchWrap)searchWrap.style.top='';
    if(mapEl){mapEl.style.top='';mapEl.style.bottom='';mapEl.style.height='';}
    if(panel){panel.style.top='';panel.style.height='';}
    if(toolMenu)toolMenu.style.top='';
    setTimeout(()=>map.resize(),50);
  }
}

// ── Map style toggle ──────────────────────────────────────────
let mapIsDark=true;
const STYLE_DARK='mapbox://styles/mapbox/dark-v11';
const STYLE_LIGHT='mapbox://styles/mapbox/streets-v12';
function toggleMapStyle(){
  mapIsDark=!mapIsDark;
  const icon=document.getElementById('map-style-icon');
  if(icon)icon.className=mapIsDark?'fas fa-moon':'fas fa-sun';
  map.setStyle(mapIsDark?STYLE_DARK:STYLE_LIGHT);
  map.once('style.load',()=>{ addMapSourcesAndLayers(); });
}

// ── Utilities ─────────────────────────────────────────────────
function fmt(n){return '$'+Math.round(n).toLocaleString('en-CA');}
function fmtK(n){return '$'+Math.round(n).toLocaleString('en-CA');}

// ── Pro forma override helpers ─────────────────────────────────────────────────
function pfToggle(field) {
  const display = document.getElementById('pf-'+field+'-display');
  const input   = document.getElementById('pf-'+field+'-input');
  const btn     = document.getElementById('pf-'+field+'-btn');
  if (!display || !input) return;
  if (input.style.display !== 'none') { pfCommit(field); return; }
  display.style.display = 'none';
  input.style.display   = 'inline-block';
  if (btn) btn.classList.add('active');
  input.focus(); input.select();
}

function pfCommit(field) {
  const input   = document.getElementById('pf-'+field+'-input');
  const display = document.getElementById('pf-'+field+'-display');
  const btn     = document.getElementById('pf-'+field+'-btn');
  if (!input || !currentData) return;
  const raw = parseFloat(input.value.replace(/[^0-9.]/g,''));
  if (!isNaN(raw) && raw > 0) {
    if (!window._pfOverrides) window._pfOverrides = {};
    window._pfOverrides[field] = raw;
  }
  if (display) display.style.display = '';
  input.style.display = 'none';
  if (btn) btn.classList.remove('active');
  // Re-render whichever tab is active (strata or rental both use pfCommit for their strata-specific fields)
  if (currentPath === 'rental') {
    document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
  } else {
    document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
  }
}

function pfToggleDetached(checked) {
  if (!window._pfOverrides) window._pfOverrides = {};
  window._pfOverrides.useDetached = checked;
  if (currentData) document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
}

function pfReset() {
  window._pfOverrides = {};
  if (currentData) document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
}

// ── Session 15 — Standardized Design credit toggle ───────────────────────────
// Client-side only. $35k credit is applied in both renderStrataTab and
// renderRentalTab at display time. Setting persists across tab switches for
// the current lot, resets on new lot open.
function toggleStdDesign(checked) {
  window._useStdDesign = !!checked;
  if (!currentData) return;
  if (currentPath === 'rental') {
    document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
  } else {
    document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
  }
}

// ── Strata financing scenario switcher (Session 16) ──────────────────────────
// Flips between 'construction' (default, with pencil-editable LTC/Rate/Term)
// and 'all_cash' (no debt, zero financing cost). Triggers a full re-render
// of the strata tab so the cost stack + section below update together.
function changeStrataFinScenario(scenarioKey) {
  if (scenarioKey !== 'all_cash' && scenarioKey !== 'construction') return;
  window._strataFinScenario = scenarioKey;
  if (currentData) {
    document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
  }
}

// ── Strata Construction Financing edit helpers (Session NEW) ──────────────────
// Edits: LTC %, Rate %, Term months. Stored in _pfOverrides under cfin_* keys.
function pfCfinToggle(field) {
  const display = document.getElementById('pf-cfin-'+field+'-display');
  const input   = document.getElementById('pf-cfin-'+field+'-input');
  const btn     = document.getElementById('pf-cfin-'+field+'-btn');
  if (!display || !input) return;
  if (input.style.display !== 'none') { pfCfinCommit(field); return; }
  display.style.display = 'none';
  input.style.display   = 'inline-block';
  if (btn) btn.classList.add('active');
  input.focus(); input.select();
}
function pfCfinCommit(field) {
  const input   = document.getElementById('pf-cfin-'+field+'-input');
  const display = document.getElementById('pf-cfin-'+field+'-display');
  const btn     = document.getElementById('pf-cfin-'+field+'-btn');
  if (!input || !currentData) return;
  const raw = parseFloat(input.value.replace(/[^0-9.]/g,''));
  if (!isNaN(raw) && raw >= 0) {
    if (!window._pfOverrides) window._pfOverrides = {};
    // Store with key like 'cfin_ltc', 'cfin_rate', 'cfin_term'
    window._pfOverrides['cfin_'+field] = raw;
  }
  if (display) display.style.display = '';
  input.style.display = 'none';
  if (btn) btn.classList.remove('active');
  document.getElementById('panel-body').innerHTML = renderStrataTab(currentData);
}

// ── Rental pencil-edit helpers (Session NEW) ──────────────────────────────────
// Independent from strata — rental overrides stored in _pfRentalOverrides
// ── Financing scenario re-fetch (Session B) ───────────────────────────────────
// Changes financing_scenario param, preserves all other rental overrides.
// Dropdown onChange calls this; feasibility re-fetches; renderPanelBody re-renders.
function changeFinancingScenario(newKey) {
  const validKeys = ['cmhc_mli','conventional','private','all_cash'];
  if (!validKeys.includes(newKey)) return;
  if (!currentLot) return;
  window._financingScenario = newKey;
  // Clear financing pencil-edits on scenario change (fresh slate per Session B)
  window._pfFinOverrides = {};

  // Preserve rental land/build overrides through re-fetch
  const rov = window._pfRentalOverrides || {};
  let url = `/api/feasibility.php?pid=${encodeURIComponent(currentLot)}&path=rental&financing_scenario=${encodeURIComponent(newKey)}`;
  if (rov.land  != null) url += '&rental_land_override='      + Math.round(rov.land);
  if (rov.build != null) url += '&rental_build_psf_override=' + Math.round(rov.build);

  const seq = ++fetchSeq;
  const dd = document.getElementById('financing-scenario-dd');
  if (dd) dd.disabled = true;

  fetch(url)
    .then(r => r.json())
    .then(d => {
      if (seq !== fetchSeq) return;
      currentData = d;
      if (currentPath === 'rental' || currentPath === 'compare') {
        renderPanelBody(d, currentPath);
      }
      if (dd) dd.disabled = false;
    })
    .catch(() => {
      if (dd) dd.disabled = false;
    });
}
function pfRentalToggle(field) {
  const display = document.getElementById('pfr-'+field+'-display');
  const input   = document.getElementById('pfr-'+field+'-input');
  const btn     = document.getElementById('pfr-'+field+'-btn');
  if (!display || !input) return;
  if (input.style.display !== 'none') { pfRentalCommit(field); return; }
  display.style.display = 'none';
  input.style.display   = 'inline-block';
  if (btn) btn.classList.add('active');
  input.focus(); input.select();
}
function pfRentalCommit(field) {
  const input   = document.getElementById('pfr-'+field+'-input');
  const display = document.getElementById('pfr-'+field+'-display');
  const btn     = document.getElementById('pfr-'+field+'-btn');
  if (!input || !currentData) return;
  const raw = parseFloat(input.value.replace(/[^0-9.]/g,''));
  if (!isNaN(raw) && raw > 0) {
    if (!window._pfRentalOverrides) window._pfRentalOverrides = {};
    window._pfRentalOverrides[field] = raw;
  }
  if (display) display.style.display = '';
  input.style.display = 'none';
  if (btn) btn.classList.remove('active');
  document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
}
// ── Financing pencil-edit helpers (Session B) ─────────────────────────────────
// LTC / Rate / Amort editable by user on top of selected scenario.
// Stored in _pfFinOverrides; client-side recalc in renderRentalTab.
function pfFinToggle(field) {
  const display = document.getElementById('pff-'+field+'-display');
  const input   = document.getElementById('pff-'+field+'-input');
  const btn     = document.getElementById('pff-'+field+'-btn');
  if (!display || !input) return;
  if (input.style.display !== 'none') { pfFinCommit(field); return; }
  display.style.display = 'none';
  input.style.display   = 'inline-block';
  if (btn) btn.classList.add('active');
  input.focus(); input.select();
}
function pfFinCommit(field) {
  const input = document.getElementById('pff-'+field+'-input');
  if (!input || !currentData) return;
  const raw = parseFloat(input.value.replace(/[^0-9.]/g,''));
  if (!isNaN(raw) && raw >= 0) {
    if (!window._pfFinOverrides) window._pfFinOverrides = {};
    const fin = (currentData.rental && currentData.rental.financing) || {};
    const defaults = { ltc: fin.ltc_pct, rate: fin.interest_rate_pct, amort: fin.amort_years };
    // Drop override if it matches the scenario default (clean edit state)
    if (Math.abs(raw - (defaults[field] || 0)) < 0.001) {
      delete window._pfFinOverrides[field];
    } else {
      window._pfFinOverrides[field] = raw;
    }
  }
  if (currentPath === 'rental') {
    document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
  } else if (currentPath === 'compare') {
    document.getElementById('panel-body').innerHTML = renderCompareTab(currentData);
  }
}
function pfFinReset() {
  window._pfFinOverrides = {};
  if (currentData && currentPath === 'rental') {
    document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
  } else if (currentData && currentPath === 'compare') {
    document.getElementById('panel-body').innerHTML = renderCompareTab(currentData);
  }
}
function pfRentalReset() {
  window._pfRentalOverrides = {};
  window._pfFinOverrides    = {};
  if (currentData) document.getElementById('panel-body').innerHTML = renderRentalTab(currentData);
}

// ── Open report with current panel state as URL params ────────────────────────
function openReport(pid) {
  const ov  = window._pfOverrides       || {};
  const rov = window._pfRentalOverrides || {};
  const md  = currentData && currentData.market_data || {};
  // Tab to report path mapping:
  //   strata  → strata-only report (5-6 pages)
  //   rental  → rental-only report (5-6 pages)
  //   compare → combined report with Path Comparison + Wynston Outlook (v9's 'outlook' path)
  const reportPath = currentPath === 'compare' ? 'outlook' : currentPath;
  let url = '/generate-report.php?pid='+encodeURIComponent(pid)+'&path='+reportPath;
  // Strata overrides
  if (ov.land  != null) url += '&land_override='       + Math.round(ov.land);
  if (ov.build != null) url += '&build_psf_override='  + Math.round(ov.build);
  if (ov.useDetached && md.detached_benchmark) {
    url += '&psf_override=' + Math.round(md.detached_benchmark.avg_psf) + '&psf_mode=detached';
  }
  // Strata construction financing overrides
  if (ov.cfin_ltc  != null) url += '&strata_cfin_ltc='  + (ov.cfin_ltc  / 100); // store as fraction
  if (ov.cfin_rate != null) url += '&strata_cfin_rate=' + (ov.cfin_rate / 100); // store as fraction
  if (ov.cfin_term != null) url += '&strata_cfin_term=' + Math.round(ov.cfin_term);
  // Rental independent overrides
  if (rov.land  != null) url += '&rental_land_override='      + Math.round(rov.land);
  if (rov.build != null) url += '&rental_build_psf_override=' + Math.round(rov.build);
// Financing scenario (Session B) — pass through to rental + compare reports
  const scenKey = window._financingScenario || 'cmhc_mli';
  if ((reportPath === 'rental' || reportPath === 'outlook') && scenKey !== 'cmhc_mli') {
    url += '&financing_scenario=' + encodeURIComponent(scenKey);
  }
  // Financing pencil-edits (Session B) — LTC%, rate%, amort-years
  const fov = window._pfFinOverrides || {};
  if (reportPath === 'rental' || reportPath === 'outlook') {
    if (fov.ltc   != null) url += '&fin_ltc='   + (fov.ltc / 100);   // fraction
    if (fov.rate  != null) url += '&fin_rate='  + (fov.rate / 100);  // fraction
    if (fov.amort != null) url += '&fin_amort=' + Math.round(fov.amort);
  }
  // Session 15 — Standardized Design credit flag
  if (window._useStdDesign === true) url += '&use_std_design=1';
  // Session 16 — Strata All Cash flag (zero out construction financing in report)
  // Only send when strata or compare path — rental has its own financing scenario
  if ((reportPath === 'strata' || reportPath === 'outlook') && window._strataFinScenario === 'all_cash') {
    url += '&strata_all_cash=1';
  }
  window.open(url, '_blank');
}
function escHtml(s){return(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
function showError(msg){document.getElementById('panel-body').innerHTML=`<div class="w-section" style="color:#c00;text-align:center;padding:32px"><i class="fas fa-exclamation-circle" style="font-size:24px;margin-bottom:10px;display:block"></i>${msg||'Error loading lot data.'}</div>`;}
function skeletonHTML(){return`<div class="w-section"><div class="w-skeleton" style="width:60%;margin-bottom:12px"></div><div class="w-skeleton"></div><div class="w-skeleton" style="width:80%;margin-top:6px"></div></div><div class="w-section"><div class="w-skeleton" style="width:40%;margin-bottom:10px"></div><div class="w-skeleton"></div><div class="w-skeleton" style="width:75%;margin-top:6px"></div></div>`;}
</script>
</body>
</html>