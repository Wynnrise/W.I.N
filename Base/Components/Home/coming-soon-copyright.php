<?php
/**
 * Wynston Coming Soon Copyright Notice
 * Include at the bottom of: half-map.php, single-property-2.php,
 * concierge-property.php, neighbourhood.php, neighbourhoods.php
 *
 * Usage: include "$base_dir/Components/coming-soon-copyright.php";
 */
$current_year = date('Y');
?>
<div class="wyn-copyright-bar">
    <div class="container">
        <div class="wyn-copyright-inner">
            <div class="wyn-copyright-icon">©</div>
            <div class="wyn-copyright-text">
                <strong>© <?= $current_year ?> Wynston.ca — Proprietary Coming Soon Database.</strong>
                All pre-sale and upcoming development listings on this page are part of Wynston's original compiled database, protected under the <em>Copyright Act</em> (Canada). Reproduction, scraping, redistribution, or commercial use of this data without Wynston's express written consent is strictly prohibited.
                <a href="/terms.php#s7">See Section 7 of our Terms of Service →</a>
            </div>
        </div>
    </div>
</div>

<style>
.wyn-copyright-bar {
    background: #001830;
    border-top: 1px solid rgba(201,168,76,.2);
    padding: 14px 0;
    margin-top: 0;
}
.wyn-copyright-inner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.wyn-copyright-icon {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1.5px solid rgba(201,168,76,.5);
    color: #c9a84c;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
}
.wyn-copyright-text {
    font-size: 11px;
    color: rgba(255,255,255,.4);
    line-height: 1.7;
}
.wyn-copyright-text strong {
    color: rgba(255,255,255,.65);
}
.wyn-copyright-text a {
    color: #c9a84c;
    text-decoration: none;
    margin-left: 4px;
}
.wyn-copyright-text a:hover {
    text-decoration: underline;
}
</style>
