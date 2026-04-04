<?php
// disclaimer-active-listings.php
// ---------------------------------------------------------------
// HOW TO ADD TO YOUR PAGE (active-listings.php):
//
//   Add this ONE line near the top of active-listings.php,
//   BEFORE the closing </head> tag or right after <?php session_start() ?>:
//
//   <?php include 'disclaimer-active-listings.php'; ?>
//
//   The disclaimer shows automatically on every visit.
//   Once the user clicks "I Understand" it won't show again
//   for the rest of their browser session.
// ---------------------------------------------------------------
?>
<style>
#wyn-disc-overlay-al {
    position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.72);z-index:999999;
    display:flex;align-items:center;justify-content:center;
    padding:16px;
}
#wyn-disc-box-al {
    background:#fff;border-radius:14px;max-width:600px;width:100%;
    padding:36px 40px;box-shadow:0 24px 64px rgba(0,0,0,.35);
    max-height:90vh;overflow-y:auto;
}
#wyn-disc-box-al .wyn-disc-icon-al {
    width:52px;height:52px;border-radius:10px;
    background:#eff6ff;border:1px solid #bfdbfe;
    display:flex;align-items:center;justify-content:center;
    margin-bottom:18px;
}
#wyn-disc-box-al h3 {
    font-size:19px;font-weight:800;color:#002446;margin:0 0 14px;line-height:1.3;
}
#wyn-disc-box-al p { font-size:14px;color:#444;line-height:1.75;margin-bottom:12px; }
.wyn-disc-legal-al {
    background:#f9f6f0;border-radius:8px;padding:16px 18px;
    font-size:12px;color:#555;line-height:1.9;margin:16px 0 24px;
    border-left:3px solid #0065ff;
}
.wyn-disc-legal-al strong { color:#002446; }
#wyn-disc-accept-al {
    background:#002446;color:#fff;border:none;
    padding:14px 28px;border-radius:8px;font-size:14px;
    font-weight:700;cursor:pointer;width:100%;transition:background .2s;
}
#wyn-disc-accept-al:hover { background:#0065ff; }
.wyn-disc-footer-al { font-size:11px;color:#aaa;text-align:center;margin-top:14px; }
</style>

<div id="wyn-disc-overlay-al">
    <div id="wyn-disc-box-al">
        <div class="wyn-disc-icon-al">
            <i class="fa-solid fa-database" style="color:#1d4ed8;font-size:22px;"></i>
        </div>
        <h3>MLS® Listing Data — Important Notice</h3>
        <p>The active listings displayed on this page are sourced from the <strong>MLS® System of the Real Estate Board of Greater Vancouver (REBGV)</strong> and are provided under license to Tam Nguyen, Realtor® with Royal Pacific Realty.</p>
        <p>Please read the following carefully before continuing:</p>
        <div class="wyn-disc-legal-al">
            <strong>MLS® DATA DISCLAIMER.</strong> The trademarks MLS®, Multiple Listing Service® and the associated logos are owned by The Canadian Real Estate Association (CREA) and identify the quality of services provided by real estate professionals who are members of CREA. The trademarks REALTOR®, REALTORS®, and the REALTOR® logo are controlled by CREA and identify real estate professionals who are members of CREA.<br><br>
            <strong>DATA ACCURACY.</strong> Listing data is deemed reliable but is not guaranteed accurate by the Real Estate Board of Greater Vancouver or its member brokerages. All measurements, square footages, and property details should be independently verified. E.&O.E.<br><br>
            <strong>NOT INTENDED TO SOLICIT.</strong> This website is not intended to solicit buyers or sellers currently under contract with another brokerage. If you are currently working with a real estate professional, please continue to work with them.<br><br>
            <strong>REPRESENTATION.</strong> This website is operated by Tam Nguyen, Realtor® with Royal Pacific Realty. By using this site you acknowledge that Tam Nguyen and Royal Pacific Realty represent their clients' interests and are not acting on behalf of the seller unless explicitly stated in a listing.<br><br>
            <!-- TO BE COMPLETED: Insert REBGV member number and any additional board-required disclosures prior to going live on wynston.ca -->
            <strong>By clicking "I Understand" below</strong>, you confirm that you have read this notice and agree to use this listing data for personal, non-commercial research purposes only.
        </div>
        <button id="wyn-disc-accept-al" onclick="wynAcceptDiscAL()">
            <i class="fa-solid fa-check me-2"></i>I Understand — View Active Listings
        </button>
        <p class="wyn-disc-footer-al">
            Tam Nguyen, Realtor® &nbsp;·&nbsp; Royal Pacific Realty &nbsp;·&nbsp; Wynston Real Estate &nbsp;·&nbsp;
            <a href="contact.php" style="color:#c9a84c;">Contact Us</a>
        </p>
    </div>
</div>

<script>
// Runs immediately — hides overlay if already accepted this session
(function() {
    try {
        if (sessionStorage.getItem('wyn_disc_al') === '1') {
            var style = document.createElement('style');
            style.innerHTML = '#wyn-disc-overlay-al{display:none!important;}';
            document.head.appendChild(style);
        }
    } catch(e) {}
})();

function wynAcceptDiscAL() {
    try { sessionStorage.setItem('wyn_disc_al', '1'); } catch(e) {}
    var el = document.getElementById('wyn-disc-overlay-al');
    if (el) {
        el.style.transition = 'opacity .25s';
        el.style.opacity = '0';
        setTimeout(function(){ el.style.display = 'none'; }, 260);
    }
}
</script>