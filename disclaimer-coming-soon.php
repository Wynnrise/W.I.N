<?php
// disclaimer-coming-soon.php
// ---------------------------------------------------------------
// HOW TO ADD TO YOUR PAGE (half-map.php):
//
//   Add this ONE line near the top of half-map.php,
//   BEFORE the closing </head> tag or right after <?php session_start() ?>:
//
//   <?php include 'disclaimer-coming-soon.php'; ?>
//
//   The disclaimer shows automatically on every visit.
//   Once the user clicks "I Understand" it won't show again
//   for the rest of their browser session.
// ---------------------------------------------------------------
?>
<style>
#wyn-disc-overlay {
    position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.72);z-index:999999;
    display:flex;align-items:center;justify-content:center;
    padding:16px;
}
#wyn-disc-box {
    background:#fff;border-radius:14px;max-width:600px;width:100%;
    padding:36px 40px;box-shadow:0 24px 64px rgba(0,0,0,.35);
    max-height:90vh;overflow-y:auto;
}
#wyn-disc-box .wyn-disc-icon {
    width:52px;height:52px;border-radius:10px;
    background:#fffbeb;border:1px solid #fde68a;
    display:flex;align-items:center;justify-content:center;
    margin-bottom:18px;
}
#wyn-disc-box h3 {
    font-size:19px;font-weight:800;color:#002446;margin:0 0 14px;line-height:1.3;
}
#wyn-disc-box p { font-size:14px;color:#444;line-height:1.75;margin-bottom:12px; }
.wyn-disc-legal {
    background:#f9f6f0;border-radius:8px;padding:16px 18px;
    font-size:12px;color:#555;line-height:1.9;margin:16px 0 24px;
    border-left:3px solid #c9a84c;
}
.wyn-disc-legal strong { color:#002446; }
#wyn-disc-accept {
    background:#002446;color:#fff;border:none;
    padding:14px 28px;border-radius:8px;font-size:14px;
    font-weight:700;cursor:pointer;width:100%;transition:background .2s;
}
#wyn-disc-accept:hover { background:#0065ff; }
.wyn-disc-footer { font-size:11px;color:#aaa;text-align:center;margin-top:14px; }
</style>

<div id="wyn-disc-overlay">
    <div id="wyn-disc-box">
        <div class="wyn-disc-icon">
            <i class="fa-solid fa-circle-info" style="color:#b45309;font-size:22px;"></i>
        </div>
        <h3>Important Notice — New Construction Information Only</h3>
        <p>The developments shown in this section are <strong>new construction projects currently under development</strong> across Metro Vancouver. This information is published for <strong>research and awareness purposes only.</strong></p>
        <p>Please read the following carefully before continuing:</p>
        <div class="wyn-disc-legal">
            <strong>NOT AN OFFER FOR SALE.</strong> Nothing on this page constitutes an offer for sale, a solicitation to purchase, or a contract of any kind. The properties listed here are not currently available for sale or lease through this platform or through Wynston Real Estate.<br><br>
            <strong>FOR INFORMATION PURPOSES ONLY.</strong> All project details — including addresses, descriptions, renderings, floorplans, and estimated timelines — are provided for general information and research purposes only. All details are subject to change without notice and may not reflect the final completed development.<br><br>
            <strong>NO PURCHASE CAN BE MADE THROUGH THIS PLATFORM.</strong> Wynston Real Estate does not accept deposits, purchase agreements, or reservations for any property listed in this section. Contact the developer directly for information on availability and purchasing when units are ready for sale.<br><br>
            <strong>REGULATORY NOTICE.</strong> Under BC's Real Estate Development Marketing Act, certain multi-unit developments may not be marketed for sale until specific legislative requirements have been met. Listings in this section have not necessarily satisfied those requirements. No representation is made as to the current availability or eligibility for sale of any listed project.<br><br>
            <!-- TO BE COMPLETED: Additional legal language to be reviewed by legal counsel prior to going live on wynston.ca -->
            <strong>By clicking "I Understand" below</strong>, you confirm that you have read this notice and that you are accessing this section for research and information purposes only.
        </div>
        <button id="wyn-disc-accept" onclick="wynAcceptDiscCS()">
            <i class="fa-solid fa-check me-2"></i>I Understand — Continue for Research Purposes Only
        </button>
        <p class="wyn-disc-footer">
            Wynston Real Estate &nbsp;·&nbsp; Tam Nguyen, Realtor® &nbsp;·&nbsp; Royal Pacific Realty &nbsp;·&nbsp;
            <a href="contact.php" style="color:#c9a84c;">Contact Us</a>
        </p>
    </div>
</div>

<script>
// Runs immediately — hides overlay if already accepted this session
(function() {
    try {
        if (sessionStorage.getItem('wyn_disc_cs') === '1') {
            var style = document.createElement('style');
            style.innerHTML = '#wyn-disc-overlay{display:none!important;}';
            document.head.appendChild(style);
        }
    } catch(e) {}
})();

function wynAcceptDiscCS() {
    try { sessionStorage.setItem('wyn_disc_cs', '1'); } catch(e) {}
    var el = document.getElementById('wyn-disc-overlay');
    if (el) {
        el.style.transition = 'opacity .25s';
        el.style.opacity = '0';
        setTimeout(function(){ el.style.display = 'none'; }, 260);
    }
}
</script>