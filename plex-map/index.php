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

/* ── Header ── */
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

/* ── Search ── */
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

/* ── Map ── */
#map{position:fixed;top:var(--header-h);left:0;right:0;bottom:0;transition:right .3s ease}
#map.panel-open{right:var(--panel-w)}

/* ── Floating Tool Menu ── */
.w-tool-menu {
  position: fixed;
  top: calc(var(--header-h) + 80px);
  left: 16px;
  z-index: 120;
  user-select: none;
}
.w-tool-menu-handle {
  background: rgba(0,20,46,.92);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(201,168,76,.3);
  border-radius: 14px;
  padding: 6px;
  box-shadow: 0 4px 20px rgba(0,0,0,.4);
  min-width: 200px;
}
.w-tool-menu-drag {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px 8px;
  cursor: grab;
  border-bottom: 1px solid rgba(255,255,255,.08);
  margin-bottom: 4px;
}
.w-tool-menu-drag:active { cursor: grabbing; }
.w-tool-menu-title {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--gold);
  flex: 1;
}
.w-tool-menu-drag i { color: rgba(255,255,255,.3); font-size: 11px; }

/* Tool sections */
.w-tool-section {
  padding: 4px 0;
  border-bottom: 1px solid rgba(255,255,255,.06);
  margin-bottom: 2px;
}
.w-tool-section:last-child { border-bottom: none; margin-bottom: 0; }
.w-tool-section-label {
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: rgba(255,255,255,.3);
  padding: 4px 10px 2px;
}

/* Toggle rows */
.w-tool-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 10px;
  cursor: pointer;
  border-radius: 8px;
  transition: background .15s;
}
.w-tool-toggle:hover { background: rgba(255,255,255,.06); }
.w-tool-toggle-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
  transition: .2s;
}
.w-tool-toggle-label {
  flex: 1;
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,.7);
  transition: color .15s;
}
.w-tool-switch {
  width: 32px;
  height: 18px;
  border-radius: 9px;
  background: rgba(255,255,255,.15);
  position: relative;
  flex-shrink: 0;
  transition: background .2s;
}
.w-tool-switch::after {
  content: '';
  position: absolute;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #fff;
  top: 3px;
  left: 3px;
  transition: transform .2s;
}
.w-tool-toggle.active .w-tool-switch { background: var(--gold); }
.w-tool-toggle.active .w-tool-switch::after { transform: translateX(14px); }
.w-tool-toggle.active .w-tool-toggle-label { color: #fff; }

/* Icon colour variants */
.icon-gold  { background: rgba(201,168,76,.15);  color: var(--gold); }
.icon-green { background: rgba(34,197,94,.15);   color: var(--green); }
.icon-teal  { background: rgba(20,184,166,.15);  color: var(--teal); }
.icon-amber { background: rgba(245,158,11,.15);  color: var(--amber); }
.icon-gray  { background: rgba(148,163,184,.15); color: var(--gray); }
.icon-blue  { background: rgba(0,101,255,.15);   color: #60a5fa; }

/* ── Minimized states ── */
.w-tool-menu.minimized .w-tool-menu-handle { min-width: 0; }
.w-tool-menu.minimized .w-tool-sections { display: none; }
.w-tool-menu-minimize {
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,.4); font-size: 12px; padding: 2px 4px;
  line-height: 1; transition: color .15s;
}
.w-tool-menu-minimize:hover { color: var(--gold); }

/* Header minimize */
.w-header.minimized { height: 0; overflow: hidden; border: none; }
.w-header.minimized + * { top: 0 !important; }
.w-header-toggle {
  position: fixed; top: 8px; right: 8px; z-index: 300;
  background: rgba(0,20,46,.85); backdrop-filter: blur(8px);
  border: 1px solid rgba(201,168,76,.3); border-radius: 20px;
  padding: 5px 12px; font-size: 11px; font-weight: 700;
  color: var(--gold); cursor: pointer; display: none;
  letter-spacing: .5px;
}
.w-header.minimized ~ .w-header-toggle { display: block; }

/* ── Legend ── */
.w-legend{position:fixed;bottom:28px;left:16px;z-index:100;background:rgba(0,20,46,.85);backdrop-filter:blur(8px);border:1px solid rgba(201,168,76,.2);border-radius:10px;padding:10px 14px;display:flex;flex-direction:column;gap:6px}
.w-legend-item{display:flex;align-items:center;gap:8px;font-size:11px;color:rgba(255,255,255,.7)}
.w-legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.w-legend-star{font-size:12px}

/* ── 3D Button ── */
.w-3d-btn{position:fixed;bottom:28px;right:calc(var(--panel-w) + 12px);z-index:100;background:var(--navy);border:1.5px solid var(--gold);color:var(--gold);font-size:12px;font-weight:700;padding:8px 16px;border-radius:20px;cursor:pointer;transition:.2s;display:none}
.w-3d-btn:hover{background:var(--gold);color:var(--navy)}
.w-3d-btn.visible{display:block}

/* ── Side Panel ── */
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

/* Permit card in panel */
.w-permit-banner{background:linear-gradient(135deg,rgba(0,36,70,.95),rgba(0,20,46,.95));border-bottom:2px solid var(--gold);padding:0}
.w-permit-img{width:100%;height:160px;object-fit:cover;display:block}
.w-permit-img-placeholder{width:100%;height:120px;background:linear-gradient(135deg,#001a35,#002446);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:32px}
.w-permit-status-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);color:var(--gold);font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase;padding:4px 10px;border-radius:20px;margin-top:8px}

@media(max-width:700px){
  :root{--panel-w:100vw}
  .w-3d-btn{right:12px;bottom:80px}
  .w-tool-menu{top:calc(var(--header-h) + 70px)}
}
</style>
</head>
<body>

<!-- Header -->
<header class="w-header">
  <a href="/" class="w-logo">
    
    <div><div class="w-logo-text"></div><div class="w-logo-badge">W.I.N — Wynston Intelligence Navigator</div></div>
  </a>
  <div class="w-header-right">
    <button class="w-btn-sm w-btn-ghost" onclick="toggleHeader()" title="Hide header">
      <i class="fas fa-compress-alt"></i>
    </button>
    <button class="w-btn-sm w-btn-ghost" id="map-style-btn" onclick="toggleMapStyle()" title="Toggle light/dark map">
      <i class="fas fa-moon" id="map-style-icon"></i>
    </button>
    <button class="w-btn-sm w-btn-ghost" onclick="location.href='/developer-dashboard.php'">
      <i class="fas fa-th-large" style="margin-right:5px"></i>Dashboard
    </button>
    <button class="w-btn-sm w-btn-gold" onclick="location.href='/wynston-concierge.php'">Concierge</button>
  </div>
</header>

<!-- Search -->
<div class="w-search-wrap" id="search-wrap">
  <div class="w-search-inner">
    <i class="fas fa-search"></i>
    <input class="w-search-input" id="search-input" type="text" placeholder="Search address or PID…" autocomplete="off" spellcheck="false">
    <button class="w-search-clear" id="search-clear" onclick="clearSearch()" title="Clear">×</button>
  </div>
  <div class="w-search-results" id="search-results"></div>
</div>

<!-- Floating Tool Menu -->
<div class="w-tool-menu" id="tool-menu">
  <div class="w-tool-menu-handle">

    <!-- Drag handle -->
    <div class="w-tool-menu-drag" id="tool-drag-handle">
      <i class="fas fa-grip-vertical"></i>
      <span class="w-tool-menu-title">Map Layers</span>
      <button class="w-tool-menu-minimize" onclick="event.stopPropagation();toggleToolMenu()" title="Minimize">
        <i class="fas fa-minus" id="tool-menu-icon"></i>
      </button>
    </div>

    <!-- Tool sections (collapsible) -->
    <div id="tool-sections">
    <!-- Layers section -->
    <div class="w-tool-section">
      <div class="w-tool-section-label">Visibility</div>

      <div class="w-tool-toggle active" id="tgl-halos" onclick="toolToggle('halos')">
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

    </div>

    <!-- Filter section -->
    <div class="w-tool-section">
      <div class="w-tool-section-label">Filter Lots</div>

      <div class="w-tool-toggle" id="tgl-6unit" onclick="toolToggle('6unit')">
        <div class="w-tool-toggle-icon icon-green"><span style="font-size:10px;font-weight:800">6U</span></div>
        <span class="w-tool-toggle-label">6-Unit Eligible Only</span>
        <div class="w-tool-switch"></div>
      </div>

      <div class="w-tool-toggle" id="tgl-4unit" onclick="toolToggle('4unit')">
        <div class="w-tool-toggle-icon icon-teal"><span style="font-size:10px;font-weight:800">4U</span></div>
        <span class="w-tool-toggle-label">4-Unit Eligible Only</span>
        <div class="w-tool-switch"></div>
      </div>

      <div class="w-tool-toggle" id="tgl-duplex" onclick="toolToggle('duplex')">
        <div class="w-tool-toggle-icon icon-amber"><span style="font-size:10px;font-weight:800">2U</span></div>
        <span class="w-tool-toggle-label">Duplex / 3-Unit Only</span>
        <div class="w-tool-switch"></div>
      </div>

      <div class="w-tool-toggle" id="tgl-buyout" onclick="toolToggle('buyout')">
        <div class="w-tool-toggle-icon icon-gray"><i class="fas fa-arrows-alt-h" style="font-size:10px"></i></div>
        <span class="w-tool-toggle-label">Buyout Potential</span>
        <div class="w-tool-switch"></div>
      </div>

      <div class="w-tool-toggle" id="tgl-nopark" onclick="toolToggle('nopark')">
        <div class="w-tool-toggle-icon icon-blue"><i class="fas fa-car-slash" style="font-size:10px"></i></div>
        <span class="w-tool-toggle-label">No Parking Required</span>
        <div class="w-tool-switch"></div>
      </div>

    </div>
    </div><!-- /tool-sections -->
  </div>
</div>

<!-- Header restore button - always present, shown when header hidden -->
<button id="header-restore-btn" onclick="toggleHeader()" style="
  position:fixed; top:8px; right:8px; z-index:300;
  background:rgba(0,20,46,.92); backdrop-filter:blur(8px);
  border:1px solid rgba(201,168,76,.4); border-radius:20px;
  padding:6px 14px; font-size:12px; font-weight:700;
  color:var(--gold); cursor:pointer; display:none;
  letter-spacing:.5px; font-family:inherit;
">☰ Show Menu</button>

<!-- Map -->
<div id="map"></div>

<!-- Legend removed — use floating menu toggles -->

<!-- 3D Button -->
<button class="w-3d-btn" id="btn3d" onclick="toggle3D()">
  <i class="fas fa-cube" style="margin-right:5px"></i>Visualize Build
</button>

<!-- Side Panel -->
<aside class="w-panel" id="panel">
  <div class="w-panel-head" id="panel-head">
    <button class="w-panel-close" onclick="closePanel()">×</button>
    <div class="w-panel-address" id="ph-address">Loading…</div>
    <div class="w-panel-pid" id="ph-pid"></div>
    <div id="ph-badge"></div>
    <div id="ph-confidence"></div>
  </div>
  <div class="w-tabs" id="pf-tabs" style="display:none">
    <div class="w-tab active" id="tab-strata"  onclick="switchTab('strata')">Strata / Sell</div>
    <div class="w-tab"        id="tab-rental"  onclick="switchTab('rental')">Rental / Hold</div>
    <div class="w-tab"        id="tab-outlook" onclick="switchTab('outlook')">Outlook</div>
  </div>
  <div class="w-panel-body" id="panel-body">
    <div class="w-panel-empty" id="panel-empty">
      <i class="fas fa-map-pin"></i>
      <p>Click any lot on the map to see its development feasibility analysis.</p>
    </div>
  </div>
  <div class="w-actions" id="panel-actions" style="display:none"></div>
</aside>

<script>
// ── Config ────────────────────────────────────────────────────
const MAPBOX_TOKEN = 'pk.eyJ1IjoiaGVucmluZ3V5ZW4iLCJhIjoiY21uYjg3dTNnMHFkZjJwcHR0bjkwb29ueCJ9.De7GXPlYRlzTJOr9jd5BJg';
const IS_LOGGED_IN = <?= isset($_SESSION['dev_id']) ? 'true' : 'false' ?>;

// ── State ─────────────────────────────────────────────────────
let map, currentLot = null, is3D = false, fetchSeq = 0;
let currentPath = 'strata', currentData = null;
let toolState = { halos: true, skytrain: true, permits: true, '6unit': false, '4unit': false, duplex: false, buyout: false, nopark: false };

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

// ── Add all map sources and layers ───────────────────────────
function addMapSourcesAndLayers() {
  // Sources
  if (!map.getSource('lots'))
    map.addSource('lots', { type: 'geojson', data: '/api/lots.php', generateId: true });
  if (!map.getSource('permits'))
    map.addSource('permits', { type: 'geojson', data: '/api/permits.php', generateId: true });
  if (!map.getSource('transit-halos'))
    map.addSource('transit-halos', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
  if (!map.getSource('skytrain-stops'))
    map.addSource('skytrain-stops', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });

  // Transit halos
  if (!map.getLayer('transit-halos'))
    map.addLayer({
      id: 'transit-halos', type: 'circle', source: 'transit-halos',
      paint: {
        'circle-radius': { stops: [[10,20],[12,40],[14,80],[16,160]], base: 2 },
        'circle-color': 'rgba(220,38,38,0.04)',
        'circle-stroke-color': 'rgba(220,38,38,0.15)',
        'circle-stroke-width': 0.5,
      },
    });

  // SkyTrain station markers — navy circle with gold ring
  if (!map.getLayer('skytrain-stops-ring'))
    map.addLayer({
      id: 'skytrain-stops-ring', type: 'circle', source: 'skytrain-stops',
      paint: {
        'circle-radius': ['interpolate',['linear'],['zoom'], 10,6, 13,10, 16,14],
        'circle-color': 'rgba(0,20,46,0.9)',
        'circle-stroke-color': '#dc2626',
        'circle-stroke-width': 2.5,
        'circle-opacity': 1,
      },
    });
  if (!map.getLayer('skytrain-stops'))
    map.addLayer({
      id: 'skytrain-stops', type: 'circle', source: 'skytrain-stops',
      paint: {
        'circle-radius': ['interpolate',['linear'],['zoom'], 10,2, 13,4, 16,5],
        'circle-color': '#c9a84c',
        'circle-opacity': 1,
      },
    });

  // Lot pins
  if (!map.getLayer('lot-pins'))
    map.addLayer({
      id: 'lot-pins', type: 'circle', source: 'lots',
      paint: {
        'circle-radius': ['interpolate',['linear'],['zoom'], 11,3, 14,6, 16,8],
        'circle-color': ['case',
          ['all',['>=',['get','lot_width_m'],15.1],['==',['get','transit_proximate'],1],['==',['get','lane_access'],1]],'#22c55e',
          ['all',['>=',['get','lot_width_m'],10.0],['==',['get','lane_access'],1]],'#14b8a6',
          ['all',['>=',['get','lot_width_m'],7.5], ['==',['get','lane_access'],1]],'#f59e0b',
          '#94a3b8'],
        'circle-stroke-width': 1.5,
        'circle-stroke-color': 'rgba(0,0,0,0.3)',
        'circle-opacity': 0.85,
      },
    });

  // Permit star pins
  if (!map.getLayer('permit-pins'))
    map.addLayer({
      id: 'permit-pins', type: 'symbol', source: 'permits',
      layout: {
        'text-field': '★',
        'text-size': 20,
        'text-anchor': 'center',
        'text-allow-overlap': true,
      },
      paint: {
        'text-color': '#c9a84c',
        'text-halo-color': 'rgba(0,0,0,0.7)',
        'text-halo-width': 2,
      },
    });

  loadTransitData();
  applyToolState();
}

// ── Init map ──────────────────────────────────────────────────
map.on('load', () => {
  addMapSourcesAndLayers();
});

// ── Load transit stop data ────────────────────────────────────
function loadTransitData() {
  fetch('/api/lots.php')
    .then(r => r.json())
    .then(data => {
      if (!data.features) return;
      // Transit halos — deduplicated grid cells
      const seen = new Set();
      const halos = data.features
        .filter(f => f.properties.transit_proximate === 1)
        .filter(f => {
          const k = `${Math.round(f.geometry.coordinates[0]*150)},${Math.round(f.geometry.coordinates[1]*150)}`;
          if (seen.has(k)) return false;
          seen.add(k); return true;
        })
        .map(f => ({ type: 'Feature', geometry: f.geometry, properties: {} }));
      if (map.getSource('transit-halos')) map.getSource('transit-halos').setData({ type: 'FeatureCollection', features: halos });
    })
    .catch(() => {});

  // SkyTrain stops from transit_stops table via new endpoint
  fetch('/api/transit_stops.php?v=3')
    .then(r => r.json())
    .then(data => {
      if (map.getSource('skytrain-stops')) map.getSource('skytrain-stops').setData(data);
    })
    .catch(() => {});
}

// ── Cursor on hover ───────────────────────────────────────────
map.on('mouseenter', 'lot-pins',    () => { map.getCanvas().style.cursor = 'pointer'; });
map.on('mouseleave', 'lot-pins',    () => { map.getCanvas().style.cursor = ''; });
map.on('mouseenter', 'permit-pins', () => { map.getCanvas().style.cursor = 'pointer'; });
map.on('mouseleave', 'permit-pins', () => { map.getCanvas().style.cursor = ''; });
map.on('mouseenter', 'skytrain-stops', () => { map.getCanvas().style.cursor = 'pointer'; });
map.on('mouseleave', 'skytrain-stops', () => { map.getCanvas().style.cursor = ''; });

// ── Click SkyTrain station ────────────────────────────────────
map.on('click', 'skytrain-stops', (e) => {
  const p = e.features[0].properties;
  const name = p.name || 'Station';

  // Detect line from station name and zone
  function detectLine(name, zone) {
    const n = name.toLowerCase();
    // Canada Line stations
    if (/waterfront|vancouver city centre|yaletown|olympic village|broadway.city hall|king edward|oakridge|langara|marine drive|bridgeport|richmond|brighouse|capstan|sea island|yvr|templeton/.test(n)) return 'Canada Line';
    // Expo Line
    if (/waterfront|granville|burrard|stadium|main st|science world|nanaimo|29th|joyce|patterson|metrotown|royal oak|edmonds|22nd|new westminster|columbia|scott road|gateway|surrey central|king george/.test(n)) return 'Expo Line';
    // Millennium Line
    if (/vcc|clark|commercial|renfrew|rupert|gilmore|brentwood|holdom|sperling|lougheed|burquitlam|moody|inlet|coquitlam/.test(n)) return 'Millennium Line';
    // Evergreen
    if (/lincoln|lafarge|coquitlam central|port coquitlam|phibbs/.test(n)) return 'Evergreen Extension';
    // WCE
    if (/port haney|maple meadows|mission/.test(n)) return 'West Coast Express';
    // SeaBus
    if (/lonsdale|seabus/.test(n)) return 'SeaBus';
    if (zone === 'ZN 1') return 'SkyTrain';
    if (zone === 'ZN 2') return 'SkyTrain';
    return 'TransLink';
  }

  const line = detectLine(name, p.zone || '');

  // Clean station name — remove bay/platform suffixes
  const cleanName = name.replace(/\s*@\s*Bay\s*\d+/i, '')
                        .replace(/\s*Station\s*$/i, ' Station')
                        .replace(/\s+/g, ' ').trim();

  const html = `
    <div style="font-family:'Segoe UI',sans-serif;min-width:160px">
      <div style="font-weight:800;font-size:14px;color:#002446;margin-bottom:4px">${cleanName}</div>
      <div style="display:inline-block;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;letter-spacing:.5px">${line}</div>
    </div>`;

  new mapboxgl.Popup({ offset: 12, maxWidth: '220px', closeButton: false })
    .setLngLat(e.lngLat)
    .setHTML(html)
    .addTo(map);
});

// ── Click lot pin ─────────────────────────────────────────────
map.on('click', 'lot-pins', (e) => {
  openPanel(e.features[0].properties.pid);
});

// ── Click permit pin ──────────────────────────────────────────
map.on('click', 'permit-pins', (e) => {
  const p = e.features[0].properties;
  const coords = e.features[0].geometry.coordinates;
  p._lng = coords[0];
  p._lat = coords[1];
  openPermitPanel(p);
});

// ── Permit panel ──────────────────────────────────────────────
// Finds the matching PID in plex_properties by proximity, then runs
// the full feasibility panel — same as any lot click, with permit badge added.
let permitMeta = null; // stores permit info for badge injection after panel renders

function openPermitPanel(p) {
  // Store permit metadata so renderPanel can inject the badge
  permitMeta = p;

  // Use the proximity search endpoint to find matching PID in plex_properties
  // Passes lat/lng and returns the nearest lot within 50m
  const lat = parseFloat(p._lat ?? p.lat ?? 0);
  const lng = parseFloat(p._lng ?? p.lng ?? 0);

  fetch(`/api/nearest_lot.php?lat=${lat}&lng=${lng}`)
    .then(r => r.json())
    .then(result => {
      if (result && result.pid) {
        openPanel(result.pid);
      } else {
        showPermitOnlyPanel(p);
      }
    })
    .catch(() => showPermitOnlyPanel(p));
}

function showPermitOnlyPanel(p) {
  // Fallback when no matching PID found — show project info without pro forma
  permitMeta = null;
  function el(id) { return document.getElementById(id); }
  el('panel')  && el('panel').classList.add('open');
  el('map')    && el('map').classList.add('panel-open');
  el('btn3d')  && el('btn3d').classList.remove('visible');
  if (el('pf-tabs'))       el('pf-tabs').style.display = 'none';
  if (el('ph-address'))    el('ph-address').textContent = p.address || '—';
  if (el('ph-pid'))        el('ph-pid').textContent = p.neighbourhood || p.neighborhood || '';
  if (el('ph-badge'))      el('ph-badge').innerHTML = '<div class="w-elig-badge badge-gold"><i class="fas fa-hard-hat"></i> Active Building Permit</div>';
  if (el('ph-confidence')) el('ph-confidence').innerHTML = '';
  if (el('panel-body'))    el('panel-body').innerHTML = `
    <div class="w-section">
      <div class="w-flag w-flag-gold" style="margin-bottom:0">
        <i class="fas fa-hard-hat"></i>
        <span><strong>Development In Progress</strong> — Feasibility data based on current market conditions.</span>
      </div>
    </div>
    <div class="w-section" style="color:#aaa;font-size:13px">No matching lot found in database.</div>`;
  if (el('panel-actions')) el('panel-actions').style.display = 'none';
  setTimeout(() => map.resize(), 310);
}

// ── Open lot panel ────────────────────────────────────────────
function openPanel(pid) {
  currentLot  = pid;
  currentPath = 'strata';
  currentData = null;

  function el(id) { return document.getElementById(id); }
  el('panel')  && el('panel').classList.add('open');
  el('map')    && el('map').classList.add('panel-open');
  el('btn3d')  && el('btn3d').classList.add('visible');
  const emptyEl = el('panel-empty');
  if (emptyEl) emptyEl.style.display = 'none';
  if (el('pf-tabs'))       el('pf-tabs').style.display = IS_LOGGED_IN ? 'flex' : 'none';
  if (el('ph-address'))    el('ph-address').textContent = 'Loading…';
  if (el('ph-pid'))        el('ph-pid').textContent = '';
  if (el('ph-badge'))      el('ph-badge').innerHTML = '';
  if (el('ph-confidence')) el('ph-confidence').innerHTML = '';
  if (el('panel-body'))    el('panel-body').innerHTML = skeletonHTML();
  if (el('panel-actions')) el('panel-actions').style.display = 'none';

  document.querySelectorAll('.w-tab').forEach(t => t.classList.remove('active'));
  if (el('tab-strata')) el('tab-strata').classList.add('active');

  setTimeout(() => map.resize(), 310);

  if (!IS_LOGGED_IN) {
    fetch('/api/lots.php')
      .then(r => r.json())
      .then(data => {
        const feature = data.features && data.features.find(f => f.properties.pid === pid);
        if (feature) renderGate1(feature.properties);
        else showError('Lot not found');
      })
      .catch(() => showError());
    return;
  }

  const seq = ++fetchSeq;
  fetch(`/api/feasibility.php?pid=${encodeURIComponent(pid)}&path=${currentPath}`)
    .then(r => r.json())
    .then(d => {
      if (seq !== fetchSeq) return;
      currentData = d;
      renderPanel(d, currentPath);
    })
    .catch(() => { if (seq === fetchSeq) showError(); });
}

function closePanel() {
  document.getElementById('panel').classList.remove('open');
  document.getElementById('map').classList.remove('panel-open');
  document.getElementById('btn3d').classList.remove('visible');
  if (is3D) toggle3D();
  currentLot = null; currentData = null;
  setTimeout(() => map.resize(), 310);
}

function switchTab(tab) {
  currentPath = tab;
  document.querySelectorAll('.w-tab').forEach(t => t.classList.remove('active'));
  document.getElementById(`tab-${tab}`).classList.add('active');
  if (!currentData) return;
  renderPanelBody(currentData, tab);
}

// ── Floating menu tool toggle ─────────────────────────────────
function toolToggle(name) {
  // Lot filters are mutually exclusive
  const filters = ['6unit', '4unit', 'duplex'];
  if (filters.includes(name) && !toolState[name]) {
    filters.forEach(f => { if (f !== name) { toolState[f] = false; document.getElementById('tgl-' + f).classList.remove('active'); } });
  }

  toolState[name] = !toolState[name];
  document.getElementById('tgl-' + name).classList.toggle('active', toolState[name]);
  applyToolState();
}

function applyToolState() {
  // Layer visibility
  if (map.getLayer('transit-halos')) map.setLayoutProperty('transit-halos', 'visibility', toolState.halos ? 'visible' : 'none');
  if (map.getLayer('skytrain-stops')) map.setLayoutProperty('skytrain-stops', 'visibility', toolState.skytrain ? 'visible' : 'none');
  if (map.getLayer('skytrain-stops-ring')) map.setLayoutProperty('skytrain-stops-ring', 'visibility', toolState.skytrain ? 'visible' : 'none');
  if (map.getLayer('permit-pins')) map.setLayoutProperty('permit-pins', 'visibility', toolState.permits ? 'visible' : 'none');

  // Lot pin filter
  const f = ['all'];
  if (toolState['6unit'])  { f.push(['>=', ['get', 'lot_width_m'], 15.1]); f.push(['==', ['get', 'transit_proximate'], 1]); f.push(['==', ['get', 'lane_access'], 1]); }
  if (toolState['4unit'])  { f.push(['>=', ['get', 'lot_width_m'], 10.0]); f.push(['<',  ['get', 'lot_width_m'], 15.1]); f.push(['==', ['get', 'lane_access'], 1]); }
  if (toolState['duplex']) { f.push(['>=', ['get', 'lot_width_m'], 7.5]);  f.push(['<',  ['get', 'lot_width_m'], 10.0]); f.push(['==', ['get', 'lane_access'], 1]); }
  if (toolState['buyout']) { f.push(['>=', ['get', 'lot_width_m'], 14.5]); f.push(['<',  ['get', 'lot_width_m'], 15.1]); }
  if (toolState['nopark']) { f.push(['==', ['get', 'transit_proximate'], 1]); }

  if (map.getLayer('lot-pins')) map.setFilter('lot-pins', f.length > 1 ? f : null);
}

// ── Draggable tool menu ───────────────────────────────────────
(function() {
  const menu   = document.getElementById('tool-menu');
  const handle = document.getElementById('tool-drag-handle');
  let dragging = false, startX, startY, origX, origY;

  handle.addEventListener('mousedown', e => {
    dragging = true;
    startX = e.clientX; startY = e.clientY;
    const rect = menu.getBoundingClientRect();
    origX = rect.left; origY = rect.top;
    menu.style.transition = 'none';
    e.preventDefault();
  });

  document.addEventListener('mousemove', e => {
    if (!dragging) return;
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    menu.style.left = Math.max(0, Math.min(window.innerWidth - menu.offsetWidth, origX + dx)) + 'px';
    menu.style.top  = Math.max(56, Math.min(window.innerHeight - menu.offsetHeight, origY + dy)) + 'px';
  });

  document.addEventListener('mouseup', () => { dragging = false; });

  // Touch support
  handle.addEventListener('touchstart', e => {
    const t = e.touches[0];
    dragging = true; startX = t.clientX; startY = t.clientY;
    const rect = menu.getBoundingClientRect();
    origX = rect.left; origY = rect.top;
  }, { passive: true });

  document.addEventListener('touchmove', e => {
    if (!dragging) return;
    const t = e.touches[0];
    const dx = t.clientX - startX, dy = t.clientY - startY;
    menu.style.left = Math.max(0, Math.min(window.innerWidth - menu.offsetWidth, origX + dx)) + 'px';
    menu.style.top  = Math.max(56, Math.min(window.innerHeight - menu.offsetHeight, origY + dy)) + 'px';
  }, { passive: true });

  document.addEventListener('touchend', () => { dragging = false; });
})();

// ── Gate 1 render ─────────────────────────────────────────────
function renderGate1(d) {
  if (d.error) { showError(d.error); return; }
  document.getElementById('ph-address').textContent = d.address || '—';
  document.getElementById('ph-pid').textContent = `PID ${d.pid}`;
  const elig = getEligBadge(d.lot_width_m, d.transit_proximate, d.lane_access);
  document.getElementById('ph-badge').innerHTML = `<div class="w-elig-badge ${elig.cls}">${elig.label}</div>`;
  document.getElementById('panel-body').innerHTML = `
    <div class="w-section">
      <div class="w-section-title">Property Details</div>
      <div class="w-specs">
        <div class="w-spec"><div class="w-spec-label">Width</div><div class="w-spec-val">${(d.lot_width_m/0.3048).toFixed(1)} ft</div><div class="w-spec-sub">${d.lot_width_m}m</div></div>
        <div class="w-spec"><div class="w-spec-label">Area</div><div class="w-spec-val">${d.lot_area_sqm}m²</div><div class="w-spec-sub">${Math.round(d.lot_area_sqm*10.7639)} sqft</div></div>
        <div class="w-spec"><div class="w-spec-label">Lane</div><div class="w-spec-val">${d.lane_access ? '✓ Yes' : '✗ No'}</div></div>
        <div class="w-spec"><div class="w-spec-label">Transit</div><div class="w-spec-val">${d.transit_proximate ? '✓ Yes' : '✗ No'}</div></div>
      </div>
    </div>
    <div style="position:relative">
      <div class="w-section w-gate-blur">
        <div class="w-section-title">Pro Forma</div>
        <div class="w-pf-row"><span>Exit Value</span><span class="w-pf-val">$X,XXX,XXX</span></div>
        <div class="w-pf-row"><span>Build Cost</span><span class="w-pf-val">$X,XXX,XXX</span></div>
        <div class="w-pf-row total"><span>Profit</span><span class="w-pf-val positive">$XXX,XXX</span></div>
        <div class="w-profit-box">
          <div class="w-profit-item"><div class="w-profit-label">ROI</div><div class="w-profit-val">XX%</div></div>
          <div class="w-profit-item"><div class="w-profit-label">Margin/sqft</div><div class="w-profit-val">$XXX</div></div>
        </div>
      </div>
      <div class="w-gate-overlay">
        <h4>Sign in to unlock</h4>
        <p>Full pro forma, comparable sales, Wynston Outlook, and design recommendations.</p>
        <a href="/log-in.php?next=/plex-map/" class="w-gate-btn">Sign In → Free Access</a>
      </div>
    </div>`;
}

// ── Gate 2 render ─────────────────────────────────────────────
function renderPanel(d, tab) {
  if (d.error) { showError(d.error); return; }
  document.getElementById('ph-address').textContent = d.property.address;
  document.getElementById('ph-pid').textContent = `PID ${d.property.pid}`;
  const elig = getEligBadge(d.property.lot_width_m, d.property.transit_proximate, d.property.lane_access);

  // If this was opened via a permit pin, show permit badge instead of eligibility
  if (permitMeta) {
    document.getElementById('ph-badge').innerHTML = `<div class="w-elig-badge badge-gold"><i class="fas fa-hard-hat"></i> Active Building Permit</div>`;
    document.getElementById('ph-confidence').innerHTML = `
      <div class="w-confidence conf-amber">
        <i class="fas fa-hard-hat" style="font-size:10px"></i>
        <span><strong>Development In Progress</strong> — Feasibility data based on current market conditions.</span>
      </div>`;
    permitMeta = null; // reset after use
  } else {
    document.getElementById('ph-badge').innerHTML = `<div class="w-elig-badge ${elig.cls}">${elig.icon} ${elig.label}</div>`;
    const conf = d.confidence;
    document.getElementById('ph-confidence').innerHTML = `
      <div class="w-confidence conf-${conf.colour}">
        <i class="fas fa-chart-bar" style="font-size:10px"></i>
        <span><strong>${conf.label}</strong> — ${conf.description}</span>
      </div>`;
  }
  renderPanelBody(d, tab);
  const actions = document.getElementById('panel-actions');
  actions.style.display = 'flex';
  actions.innerHTML = `
    <button class="w-action-btn w-action-gold" onclick="window.location.href='/generate-report.php?pid=${d.property.pid}'">
      <i class="fas fa-file-pdf"></i> Generate PDF Report
    </button>
    <button class="w-action-btn w-action-primary" onclick="inquireAcquisition('${d.property.pid}','${escHtml(d.property.address)}')">
      <i class="fas fa-handshake"></i> Inquire for Acquisition
    </button>
    <button class="w-action-btn w-action-secondary" onclick="saveLot('${d.property.pid}')">
      <i class="far fa-bookmark"></i> Save Lot
    </button>`;
}

function renderPanelBody(d, tab) {
  const body = document.getElementById('panel-body');
  if      (tab === 'strata')  body.innerHTML = renderStrataTab(d);
  else if (tab === 'rental')  body.innerHTML = renderRentalTab(d);
  else if (tab === 'outlook') body.innerHTML = renderOutlookTab(d);
}

function renderStrataTab(d) {
  const p = d.property, e = d.eligibility, s = d.strata, dom = d.dom;
  const warn149 = e.warning_149m ? `<div class="w-warning-149 w-section"><i class="fas fa-exclamation-triangle"></i><span><strong>0.2m from 6-unit eligibility.</strong> Neighbour buyout may unlock significantly higher exit value.</span></div>` : '';
  return `${warn149}${buildFlags(p)}
    <div class="w-section">
      <div class="w-section-title">Property Details</div>
      <div class="w-specs">
        <div class="w-spec"><div class="w-spec-label">Width</div><div class="w-spec-val">${p.lot_width_ft} ft</div><div class="w-spec-sub">${p.lot_width_m}m</div></div>
        <div class="w-spec"><div class="w-spec-label">Area</div><div class="w-spec-val">${p.lot_area_sqm}m²</div><div class="w-spec-sub">${p.lot_area_sqft} sqft</div></div>
        <div class="w-spec"><div class="w-spec-label">FSR (Strata)</div><div class="w-spec-val">0.70</div></div>
        <div class="w-spec"><div class="w-spec-label">Buildable</div><div class="w-spec-val">${Math.round(s.buildable_sqft).toLocaleString()} sf</div></div>
        <div class="w-spec"><div class="w-spec-label">Saleable</div><div class="w-spec-val">${Math.round(s.saleable_sqft).toLocaleString()} sf</div></div>
        <div class="w-spec"><div class="w-spec-label">Parking</div><div class="w-spec-val">${e.parking_req === 0 ? 'None ✓' : e.parking_req + ' stalls'}</div></div>
      </div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Strata Pro Forma</div>
      <div class="w-pf-row"><span>Land Cost</span><span class="w-pf-val">${fmt(s.land_cost)}</span></div>
      <div class="w-pf-row"><span>Build Cost</span><span class="w-pf-val">${fmt(s.build_cost)}</span></div>
      <div class="w-pf-row"><span>DCL + Permit Fees</span><span class="w-pf-val">${fmt(s.dcl_city_wide + s.dcl_utilities + s.permit_fees)}</span></div>
      ${s.contingency > 0 ? `<div class="w-pf-row"><span>Peat Contingency</span><span class="w-pf-val" style="color:var(--amber)">${fmt(s.contingency)}</span></div>` : ''}
      <div class="w-pf-row total"><span>Total Project Cost</span><span class="w-pf-val">${fmt(s.total_project_cost)}</span></div>
      <div class="w-pf-row" style="margin-top:8px"><span>Exit Value</span><span class="w-pf-val">${fmt(s.exit_value)}</span></div>
      <div class="w-profit-box">
        <div class="w-profit-item"><div class="w-profit-label">Profit</div><div class="w-profit-val ${s.profit<0?'negative':''}">${fmt(s.profit)}</div></div>
        <div class="w-profit-item"><div class="w-profit-label">ROI</div><div class="w-profit-val ${s.roi_pct<0?'negative':''}">${s.roi_pct.toFixed(1)}%</div></div>
      </div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Market Velocity</div>
      <div class="w-dom-row">
        <span class="w-dom-arrow ${dom.colour}">${dom.arrow}</span>
        <span class="w-pf-val">${dom.duplex_current} days</span>
        <span class="w-dom-label">${dom.label}</span>
      </div>
      ${p.neighbourhood ? `<div style="font-size:11px;color:#aaa;margin-top:4px">${p.neighbourhood}</div>` : ''}
    </div>
    ${d.design ? renderDesign(d.design) : ''}`;
}

function renderRentalTab(d) {
  const r = d.rental;
  const rentRows = ['1br','2br','3br'].map(type => {
    const rb = r.rent_breakdown[type];
    if (!rb || rb.unit_count === 0) return '';
    const dotColor = rb.variance_colour==='green'?'#22c55e':rb.variance_colour==='amber'?'#f59e0b':'#94a3b8';
    return `<tr><td>${rb.unit_count}× ${type.toUpperCase()}</td><td>${fmtK(rb.market_rent)}/mo</td><td><span class="w-rent-dot" style="background:${dotColor}"></span>${rb.variance_pct>0?'+':''}${rb.variance_pct.toFixed(0)}% vs CMHC</td></tr>`;
  }).join('');
  return `<div class="w-section">
      <div class="w-section-title">Secured Rental Pro Forma (1.00 FSR)</div>
      <div class="w-pf-row"><span>Land Cost</span><span class="w-pf-val">${fmt(r.land_cost)}</span></div>
      <div class="w-pf-row"><span>Build Cost</span><span class="w-pf-val">${fmt(r.total_build_cost)}</span></div>
      <div class="w-pf-row"><span>DCL + Permit Fees</span><span class="w-pf-val">${fmt(r.total_fees)}</span></div>
      ${r.contingency>0?`<div class="w-pf-row"><span>Peat Contingency</span><span class="w-pf-val" style="color:var(--amber)">${fmt(r.contingency)}</span></div>`:''}
      <div class="w-pf-row total"><span>Total Project Cost</span><span class="w-pf-val">${fmt(r.total_project_cost)}</span></div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Rental Income</div>
      <table class="w-rent-table"><thead><tr><th>Mix</th><th>Market Rent</th><th>vs CMHC</th></tr></thead><tbody>${rentRows}</tbody></table>
      <div style="margin-top:10px">
        <div class="w-pf-row"><span>Gross Monthly</span><span class="w-pf-val">${fmtK(r.gross_monthly)}/mo</span></div>
        <div class="w-pf-row"><span>Annual Gross</span><span class="w-pf-val">${fmt(r.annual_gross)}</span></div>
        <div class="w-pf-row"><span>Vacancy (${(r.vacancy_rate*100).toFixed(0)}%)</span><span class="w-pf-val" style="color:var(--amber)">–${fmt(r.annual_gross-r.effective_gross)}</span></div>
        <div class="w-pf-row"><span>Operating Expenses (${(r.operating_expense_rate*100).toFixed(0)}%)</span><span class="w-pf-val" style="color:var(--amber)">–${fmt(r.effective_gross-r.annual_noi)}</span></div>
        <div class="w-pf-row total"><span>Net Operating Income</span><span class="w-pf-val positive">${fmt(r.annual_noi)}</span></div>
      </div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Hold Analysis</div>
      <div class="w-pf-row"><span>Cap Rate</span><span class="w-pf-val">${r.total_project_cost>0?((r.annual_noi/r.total_project_cost)*100).toFixed(2):'—'}%</span></div>
    </div>`;
}

function renderOutlookTab(d) {
  const o = d.outlook;
  if (!o) return `<div class="w-section" style="color:#888;font-size:13px;text-align:center;padding:32px"><i class="fas fa-chart-line" style="font-size:24px;margin-bottom:12px;display:block;color:#ddd"></i>Wynston Outlook requires at least 4 bank/broker forecasts. Check back after the next quarterly update.</div>`;
  if (o.error) return `<div class="w-section"><div class="w-flag w-flag-yellow"><i class="fas fa-info-circle"></i>${o.message}</div></div>`;
  const pctClass = o.outlook_pct >= 0 ? 'positive' : '';
  return `<div class="w-section">
      <div class="w-section-title">Wynston Outlook — Price Forecast</div>
      <div class="w-outlook-block">
        <div class="w-psf-row"><span>Build cost / sqft</span><span class="w-psf-val">${fmtK(o.current_build_psf)}</span></div>
        <div class="w-psf-row"><span>Current sold / sqft</span><span class="w-psf-val">${fmtK(o.current_finished_psf)}</span></div>
        <div class="w-psf-row" style="border-top:1px solid rgba(0,0,0,.06);margin-top:6px;padding-top:6px">
          <span style="font-weight:800;color:var(--navy)">Outlook / sqft</span>
          <span class="w-psf-val" style="color:var(--green)">${fmtK(o.outlook_psf)}</span>
        </div>
        <div class="w-outlook-pct ${pctClass}">${o.outlook_pct>0?'+':''}${o.outlook_pct.toFixed(1)}%</div>
        <div class="w-conf-range">Range: ${o.confidence_low_pct.toFixed(1)}% to +${o.confidence_high_pct.toFixed(1)}% (${o.forecasts_used} sources)</div>
      </div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Three-Layer Breakdown</div>
      <div class="w-pf-row"><span>Macro (banks/brokers)</span><span class="w-pf-val">${o.macro_signal_pct>0?'+':''}${o.macro_signal_pct.toFixed(1)}% × ${Math.round(o.weights_used.macro*100)}%</span></div>
      <div class="w-pf-row"><span>Local momentum (HPI)</span><span class="w-pf-val">${o.local_momentum_pct>0?'+':''}${o.local_momentum_pct.toFixed(1)}% × ${Math.round(o.weights_used.local*100)}%</span></div>
      <div class="w-pf-row"><span>Pipeline signal</span><span class="w-pf-val">${o.pipeline_signal_pct>0?'+':''}${o.pipeline_signal_pct.toFixed(1)}% × ${Math.round(o.weights_used.pipeline*100)}%</span></div>
    </div>
    <div class="w-section">
      <div class="w-section-title">Margin Story</div>
      <div class="w-pf-row"><span>Current margin / sqft</span><span class="w-pf-val">${fmtK(o.current_margin_psf)}</span></div>
      <div class="w-pf-row"><span>Projected margin / sqft</span><span class="w-pf-val ${o.projected_margin_psf>o.current_margin_psf?'positive':''}">${fmtK(o.projected_margin_psf)}</span></div>
      <div class="w-pf-row total"><span>Margin improvement</span><span class="w-pf-val ${o.margin_improvement_psf>=0?'positive':'negative'}">${o.margin_improvement_psf>=0?'+':''}${fmtK(o.margin_improvement_psf)}/sqft</span></div>
    </div>
    <div class="w-section" style="font-size:11px;color:#aaa;line-height:1.6">Wynston Outlook is an analytical estimate. Not a guarantee. Verify with your financial advisor.</div>`;
}

function renderDesign(design) {
  return `<div class="w-section">
    <div class="w-section-title">Blueprint Match — BC Standardized Design</div>
    <div class="w-design">
      <img class="w-design-thumb" src="${design.thumbnail||'/assets/img/design-placeholder.jpg'}" onerror="this.src='/assets/img/design-placeholder.jpg'">
      <div class="w-design-info">
        <div class="w-design-name">${design.design_name}</div>
        <div style="font-size:11px;color:#888">${design.catalogue} — ${design.design_id}</div>
        ${design.saving_note?`<div class="w-design-saving"><i class="fas fa-check-circle"></i> ${design.saving_note}</div>`:''}
        ${design.external_url?`<a class="w-design-link" href="${design.external_url}" target="_blank">View Plans →</a>`:''}
      </div>
    </div>
  </div>`;
}

function buildFlags(p) {
  let f = '';
  if (p.heritage_category==='A'||p.heritage_category==='B') f+=`<div class="w-flag w-flag-red"><i class="fas fa-landmark"></i><span><strong>Heritage Category ${p.heritage_category}</strong> — Permit delays likely. HRA required.</span></div>`;
  else if (p.heritage_category==='C') f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-landmark"></i><span><strong>Heritage Category C</strong> — Inspection may be required.</span></div>`;
  if (p.peat_zone) f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-exclamation-triangle"></i><span><strong>Peat Zone</strong> — $150,000 contingency added.</span></div>`;
  if (p.covenant_present) f+=`<div class="w-flag w-flag-blue"><i class="fas fa-file-contract"></i><span><strong>Title encumbrance detected</strong> — Obtain a title search before proceeding.</span></div>`;
  if (!p.lane_access) f+=`<div class="w-flag w-flag-yellow"><i class="fas fa-road"></i><span><strong>No lane access detected</strong> — Verify with COV.</span></div>`;
  return f ? `<div class="w-section">${f}</div>` : '';
}

function getEligBadge(width, transit, lane) {
  if (width>=15.1&&transit&&lane) return {cls:'badge-green',icon:'●',label:'6-Unit Eligible'};
  if (width>=10.0&&lane)          return {cls:'badge-teal', icon:'●',label:'4-Unit Eligible'};
  if (width>=7.5&&lane)           return {cls:'badge-amber',icon:'●',label:'Duplex / 3-Unit'};
  return {cls:'badge-gray',icon:'○',label:'Below Minimum'};
}

// ── 3D ────────────────────────────────────────────────────────
function toggle3D() {
  is3D = !is3D;
  const btn = document.getElementById('btn3d');
  if (is3D && currentData) {
    map.easeTo({ pitch: 60, bearing: -20, duration: 800 });
    btn.innerHTML = '<i class="fas fa-map" style="margin-right:5px"></i>Back to Map';
    if (map.getLayer('lot-extrusion')) map.removeLayer('lot-extrusion');
    const w = currentData.property.lot_width_m;
    map.addLayer({ id:'lot-extrusion', type:'fill-extrusion', source:'lots',
      filter:['==',['get','pid'],currentData.property.pid],
      paint:{'fill-extrusion-color':'#002446','fill-extrusion-height':w>=15.1?12.5:w>=10.0?10.5:8.5,'fill-extrusion-base':0,'fill-extrusion-opacity':0.85}});
  } else {
    map.easeTo({ pitch: 0, bearing: 0, duration: 600 });
    btn.innerHTML = '<i class="fas fa-cube" style="margin-right:5px"></i>Visualize Build';
    if (map.getLayer('lot-extrusion')) map.removeLayer('lot-extrusion');
  }
}

// ── Action handlers ───────────────────────────────────────────
function inquireAcquisition(pid, address) {
  if (confirm(`Submit acquisition inquiry for:\n${address}\n\nOur team will contact you within 4 hours.`)) {
    fetch('/api/acquisition.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({pid,address})})
      .then(r=>r.json()).then(d=>alert(d.success?'✓ Inquiry submitted.':'Something went wrong.'));
  }
}
function saveLot(pid) {
  fetch('/api/save_lot.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({pid})})
    .then(r=>r.json()).then(d=>{if(d.success)alert('✓ Lot saved.')});
}

// ── Search ────────────────────────────────────────────────────
let searchTimer = null;
const searchInput   = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
const searchClear   = document.getElementById('search-clear');

searchInput.addEventListener('input', () => {
  const q = searchInput.value.trim();
  searchClear.classList.toggle('visible', q.length > 0);
  if (q.length < 2) { closeSearchDropdown(); return; }
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => runSearch(q), 250);
});
searchInput.addEventListener('keydown', e => {
  if (e.key==='Escape') { clearSearch(); return; }
  if (e.key==='Enter') { const f=searchResults.querySelector('.w-search-result'); if(f)f.click(); }
  if (e.key==='ArrowDown') { e.preventDefault(); const items=searchResults.querySelectorAll('.w-search-result'); if(items.length)items[0].focus(); }
});
searchResults.addEventListener('keydown', e => {
  const items=[...searchResults.querySelectorAll('.w-search-result')];
  const idx=items.indexOf(document.activeElement);
  if(e.key==='ArrowDown'&&idx<items.length-1){e.preventDefault();items[idx+1].focus();}
  if(e.key==='ArrowUp'){e.preventDefault();idx>0?items[idx-1].focus():searchInput.focus();}
  if(e.key==='Escape')clearSearch();
});
document.addEventListener('click', e => { if(!document.getElementById('search-wrap').contains(e.target))closeSearchDropdown(); });

function runSearch(q) {
  fetch(`/api/search.php?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(results=>renderSearchResults(results,q)).catch(()=>{});
}
function renderSearchResults(results, q) {
  if (!results.length) { searchResults.innerHTML=`<div class="w-search-no-results">No lots found for "${escHtml(q)}"</div>`; searchResults.classList.add('open'); return; }
  searchResults.innerHTML = results.map(r => {
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
function selectSearchResult(lat,lng,pid) {
  closeSearchDropdown(); searchInput.value=''; searchClear.classList.remove('visible');
  map.flyTo({center:[lng,lat],zoom:17,duration:900});
  setTimeout(()=>openPanel(pid),300);
}
function clearSearch() { searchInput.value=''; searchClear.classList.remove('visible'); closeSearchDropdown(); searchInput.focus(); }
function closeSearchDropdown() { searchResults.classList.remove('open'); searchResults.innerHTML=''; }
function highlightMatch(address,q) {
  const safe=escHtml(address), safeQ=q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
  try{return safe.replace(new RegExp(`(${safeQ})`,'i'),'<strong style="color:var(--gold)">$1</strong>')}catch(e){return safe;}
}

// ── Tool menu minimize ───────────────────────────────────────
let toolMenuMinimized = false;
function toggleToolMenu() {
  toolMenuMinimized = !toolMenuMinimized;
  const sections = document.getElementById('tool-sections');
  const icon     = document.getElementById('tool-menu-icon');
  if (sections) sections.style.display = toolMenuMinimized ? 'none' : 'block';
  if (icon)     icon.className = toolMenuMinimized ? 'fas fa-plus' : 'fas fa-minus';
}

// ── Header minimize ───────────────────────────────────────────
let headerMinimized = false;
function toggleHeader() {
  headerMinimized = !headerMinimized;
  const header      = document.querySelector('.w-header');
  const restoreBtn  = document.getElementById('header-restore-btn');
  const searchWrap  = document.getElementById('search-wrap');
  const mapEl       = document.getElementById('map');
  const panel       = document.getElementById('panel');
  const toolMenu    = document.getElementById('tool-menu');

  if (headerMinimized) {
    header.style.cssText = 'height:0;overflow:hidden;border:none;padding:0';
    if (restoreBtn)  restoreBtn.style.display = 'block';
    if (searchWrap)  searchWrap.style.top = '10px';
    if (mapEl)       { mapEl.style.top = '0'; mapEl.style.bottom = '0'; mapEl.style.height = '100%'; }
    if (panel)       { panel.style.top = '0'; panel.style.height = '100%'; }
    if (toolMenu)    toolMenu.style.top = '60px';
    setTimeout(() => map.resize(), 50);
  } else {
    header.style.cssText = '';
    if (restoreBtn)  restoreBtn.style.display = 'none';
    if (searchWrap)  searchWrap.style.top = '';
    if (mapEl)       { mapEl.style.top = ''; mapEl.style.bottom = ''; mapEl.style.height = ''; }
    if (panel)       { panel.style.top = ''; panel.style.height = ''; }
    if (toolMenu)    toolMenu.style.top = '';
    setTimeout(() => map.resize(), 50);
  }
}

// ── Map style toggle ─────────────────────────────────────────
let mapIsDark = true;
const STYLE_DARK  = 'mapbox://styles/mapbox/dark-v11';
const STYLE_LIGHT = 'mapbox://styles/mapbox/streets-v12'; // streets-v12 has better contrast than light-v11

function toggleMapStyle() {
  mapIsDark = !mapIsDark;
  const icon = document.getElementById('map-style-icon');
  if (icon) icon.className = mapIsDark ? 'fas fa-moon' : 'fas fa-sun';
  map.setStyle(mapIsDark ? STYLE_DARK : STYLE_LIGHT);
  map.once('style.load', () => {
    addMapSourcesAndLayers();
  });
}

// ── Utilities ─────────────────────────────────────────────────
function fmt(n)  { return '$'+Math.round(n).toLocaleString('en-CA'); }
function fmtK(n) { return '$'+Math.round(n).toLocaleString('en-CA'); }
function escHtml(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function showError(msg) { document.getElementById('panel-body').innerHTML=`<div class="w-section" style="color:#c00;text-align:center;padding:32px"><i class="fas fa-exclamation-circle" style="font-size:24px;margin-bottom:10px;display:block"></i>${msg||'Error loading lot data.'}</div>`; }
function skeletonHTML() { return `<div class="w-section"><div class="w-skeleton" style="width:60%;margin-bottom:12px"></div><div class="w-skeleton"></div><div class="w-skeleton" style="width:80%;margin-top:6px"></div></div><div class="w-section"><div class="w-skeleton" style="width:40%;margin-bottom:10px"></div><div class="w-skeleton"></div><div class="w-skeleton" style="width:75%;margin-top:6px"></div></div>`; }
</script>
</body>
</html>