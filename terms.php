<?php
$base_dir   = __DIR__ . '/Base';
$static_url = '/assets';

ob_start();
include "$base_dir/navbar2.php";
$navlink_content = ob_get_clean();
$page  = 'nav2';
$fpage = 'foot';

ob_start();
?>

<!-- ============================ Page Title ================================== -->
<div class="image-cover page-title" style="background:#002446 url(<?php echo $static_url; ?>/img/new-banner.jpg) no-repeat center center / cover;" data-overlay="7">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <h2 class="ipt-title" style="color:#fff;">Terms of Service</h2>
                <span class="ipn-subtitle" style="color:rgba(255,255,255,.75);">
                    Last Updated: February 28, 2026 &nbsp;·&nbsp; Wynston.ca
                </span>
            </div>
        </div>
    </div>
</div>
<!-- ============================ Page Title End ================================== -->


<!-- ============================ Terms Body ================================== -->
<section class="pt-0">
<div class="container">
<div class="row">

    <!-- ── Sticky sidebar TOC ───────────────────────────────────────────── -->
    <div class="col-lg-3 col-md-12 d-none d-lg-block">
        <div class="terms-toc">
            <div class="terms-toc-title">Contents</div>
            <ul class="terms-toc-list">
                <li><a href="#s1">1. Definitions</a></li>
                <li><a href="#s2">2. Nature of the Platform</a></li>
                <li><a href="#s3">3. Accuracy & Disclaimer</a></li>
                <li><a href="#s4">4. Developer Terms</a></li>
                <li><a href="#s5">5. Buyer & User Terms</a></li>
                <li><a href="#s6">6. VOW & Registration</a></li>
                <li><a href="#s7">7. Intellectual Property & Data</a></li>
                <li><a href="#s8">8. Privacy & Data</a></li>
                <li><a href="#s9">9. Limitation of Liability</a></li>
                <li><a href="#s10">10. Indemnification</a></li>
                <li><a href="#s11">11. Modifications</a></li>
                <li><a href="#s12">12. Dispute Resolution</a></li>
                <li><a href="#s13">13. Professional Disclosure</a></li>
                <li><a href="#s14">14. Governing Law</a></li>
                <li><a href="#s15">15. General Provisions</a></li>
            </ul>
        </div>
    </div>

    <!-- ── Main content ─────────────────────────────────────────────────── -->
    <div class="col-lg-9 col-md-12">
    <div class="terms-body">

        <!-- Preamble -->
        <div class="terms-preamble">
            <strong>IMPORTANT:</strong> Please read these Terms of Service carefully before using the Wynston Platform. By accessing or using Wynston.ca, the Wynston Mobile Application, or any related services, you agree to be bound by these Terms. If you do not agree, you must immediately discontinue use of the Platform.
        </div>

        <!-- ── Section 1 ── -->
        <div class="terms-section" id="s1">
            <h2 class="terms-h2">1. Definitions</h2>
            <p>For the purposes of these Terms of Service, the following definitions apply:</p>
            <ul class="terms-list">
                <li><strong>"Wynston" or "the Platform"</strong> refers collectively to Wynston.ca, the Wynston Mobile Application, and all associated research tools, databases, marketing services, and digital infrastructure operated under the Wynston brand.</li>
                <li><strong>"The Licensee"</strong> refers to Tam Nguyen, a licensed Real Estate Agent registered with the Real Estate Board of Greater Vancouver (REBGV) and operating under Royal Pacific Realty Corporation.</li>
                <li><strong>"The Brokerage"</strong> refers to Royal Pacific Realty Corporation, the supervising brokerage under whose license Tam Nguyen operates.</li>
                <li><strong>"User" or "You"</strong> refers to any individual or entity who accesses or uses the Platform in any capacity, including buyers, sellers, real estate professionals, and developers.</li>
                <li><strong>"Developer"</strong> refers to any real estate developer, builder, or their authorized representative who registers on the Platform to submit, manage, or market pre-sale project listings.</li>
                <li><strong>"Content"</strong> means any architectural renderings, floor plans, MLS® data, photographs, project descriptions, text, images, video, pricing information, or project tracking information displayed on or submitted to the Platform.</li>
                <li><strong>"Concierge Service"</strong> refers to the premium listing package offered by Wynston, pursuant to a signed Listing Agreement, providing a dedicated marketing microsite, rendering production, and buyer outreach services for a Developer's pre-sale project.</li>
                <li><strong>"Creative Package"</strong> refers to the standalone design service including production of floor plans, architectural renderings, and marketing visuals, without a full listing agreement.</li>
                <li><strong>"Coming Soon Listing"</strong> refers to a pre-sale development project listed on the Platform prior to the commencement of formal sales, during the pre-construction or development permit phase.</li>
                <li><strong>"MLS®"</strong> refers to the Multiple Listing Service operated by the Real Estate Board of Greater Vancouver.</li>
            </ul>
        </div>

        <!-- ── Section 2 ── -->
        <div class="terms-section" id="s2">
            <h2 class="terms-h2">2. Nature of the Platform</h2>
            <p>Wynston is a real estate research, information aggregation, and marketing platform serving the Greater Vancouver regional market. The Platform is designed to bridge the <strong>"Invisibility Gap"</strong> — the period during which significant real estate development activity occurs but is not yet visible through traditional channels.</p>

            <h3 class="terms-h3">2.1 Information Aggregation</h3>
            <p>Wynston aggregates Content from multiple sources including: (i) City of Vancouver and Metro Vancouver municipal development permit records; (ii) direct Developer submissions; (iii) the MLS® System of the Real Estate Board of Greater Vancouver; and (iv) publicly available planning and zoning records. This aggregated information is presented as a convenience to Users and Wynston does not guarantee the completeness or accuracy of any such information.</p>

            <h3 class="terms-h3">2.2 No Agency Relationship Created</h3>
            <p>Your access to or use of the Platform does <strong>not</strong> create a real estate agency relationship between you and Tam Nguyen, Royal Pacific Realty Corporation, or any affiliated party. An agency relationship is only formally established upon the execution of a signed, written Buyer Representation Agreement (BRA) or Seller Representation Agreement (LSRA), in accordance with the requirements of the British Columbia Financial Services Authority (BCFSA).</p>

            <h3 class="terms-h3">2.3 Not a Brokerage or Portal</h3>
            <p>Wynston is a marketing and information platform. It is not a licensed real estate brokerage and does not trade in real estate independently. All real estate trading activities facilitated through connections made on this Platform are conducted exclusively through Tam Nguyen and Royal Pacific Realty Corporation, under the regulatory framework of the BCFSA and the Real Estate Services Act, RSBC 1996.</p>
        </div>

        <!-- ── Section 3 ── -->
        <div class="terms-section" id="s3">
            <h2 class="terms-h2">3. Accuracy & The "Invisibility Gap" Disclaimer</h2>
            <p>Wynston specializes in tracking and marketing real estate projects during the critical pre-sale and early construction phase — typically an 18 to 36 month window during which projects are approved, designed, and brought to market. Information during this phase is inherently preliminary and subject to material change.</p>

            <h3 class="terms-h3">3.1 Pre-Sale Project Estimations</h3>
            <p>Project timelines, estimated completion dates, unit specifications, pricing ranges, floor plan configurations, and amenity descriptions for Coming Soon Listings are based on preliminary public data, development permit applications, and information provided directly by Developers. This information:</p>
            <ul class="terms-list">
                <li>is subject to change at any time without notice by the Developer or the applicable municipality;</li>
                <li>may not reflect final approved plans, rezoning outcomes, or revised project scopes;</li>
                <li>does not constitute a binding offer, advertisement of a unit for sale, or a representation of final product; and</li>
                <li>should be independently verified with the Developer, the Developer's disclosure statement (where applicable), and qualified legal counsel prior to any purchase decision.</li>
            </ul>

            <h3 class="terms-h3">3.2 MLS® Listing Accuracy</h3>
            <p>Active real estate listings displayed on the Platform are sourced via a licensed data feed from the Real Estate Board of Greater Vancouver and are updated approximately every two (2) hours. Wynston is not responsible for technical delays, errors, omissions, or interruptions in the Board's data feed. All MLS® data is provided for informational purposes only.</p>

            <h3 class="terms-h3">3.3 No Guarantee of Accuracy</h3>
            <p>While Wynston makes commercially reasonable efforts to ensure the accuracy of information presented on the Platform, all Content is provided on an <strong>"as is"</strong> and <strong>"as available"</strong> basis. Wynston expressly disclaims all representations and warranties, whether express, implied, or statutory, regarding the accuracy, completeness, reliability, suitability, or availability of any Content.</p>
        </div>

        <!-- ── Section 4 ── -->
        <div class="terms-section" id="s4">
            <h2 class="terms-h2">4. Developer Terms & Content Submission</h2>
            <p>These terms apply to all Developers who register on the Platform, submit Content, or engage any paid service offered by Wynston, including the Concierge Service and the Creative Package.</p>

            <h3 class="terms-h3">4.1 Account Registration & Eligibility</h3>
            <p>To submit a project listing or access Developer features, you must register for a Developer Account. By registering, you represent and warrant that:</p>
            <ul class="terms-list">
                <li>you are at least 19 years of age or the age of majority in your jurisdiction;</li>
                <li>you have the legal authority to submit Content on behalf of the Developer entity you represent;</li>
                <li>all information provided during registration and submission is accurate, current, and complete; and</li>
                <li>you will maintain the security of your account credentials and notify Wynston immediately of any unauthorized access.</li>
            </ul>

            <h3 class="terms-h3">4.2 License to Display Content</h3>
            <p>By submitting Content to the Platform, you grant Wynston a non-exclusive, royalty-free, worldwide, sublicensable license to host, store, reproduce, display, distribute, and promote such Content on the Platform and through Wynston's associated marketing channels for the duration of the listing term. This license terminates upon written confirmation of listing removal, subject to applicable archival and legal obligations.</p>

            <h3 class="terms-h3">4.3 Developer Warranties Regarding Content</h3>
            <p>By submitting Content, you represent and warrant that:</p>
            <ul class="terms-list">
                <li>you own or have the lawful right to use all Content submitted, including any architectural renderings, floor plan drawings, photographs, and branding materials;</li>
                <li>the Content does not infringe any third-party intellectual property rights, privacy rights, or any applicable law;</li>
                <li>all marketing materials comply with the Real Estate Development Marketing Act (REDMA), the Real Estate Services Act, and all applicable BCFSA regulations; and</li>
                <li>a valid Disclosure Statement has been filed or is in the process of being filed with the BC Financial Services Authority before any formal advertising or sale of pre-sale units, where required by law.</li>
            </ul>
            <p>Wynston acts solely as a platform for information dissemination and does not prepare, review, file, or take responsibility for disclosure statements, purchase agreements, or any other legal or regulatory filings required of Developers.</p>

            <h3 class="terms-h3">4.4 Concierge Service Terms</h3>
            <p>The Concierge Service is available exclusively to Developers who execute a signed Listing Agreement with Wynston. The service includes a dedicated project microsite, professional Content presentation, and targeted buyer outreach, as specified in the applicable service agreement. Concierge listings receive prominent placement on the Platform's homepage and listing pages.</p>

            <h3 class="terms-h3">4.5 Creative Package Terms</h3>
            <p>The Creative Package is a standalone design service that includes the production of floor plans, 2D/3D renderings, site plan visuals, and related marketing materials. The Creative Package does not include a full listing agreement or Concierge microsite unless separately contracted. Intellectual property in Creative Package deliverables is governed by the applicable service agreement.</p>

            <h3 class="terms-h3">4.6 Payment & Fees</h3>
            <p>Fees for the Concierge Service and Creative Package are as set out in the applicable service agreement or order form. All fees are:</p>
            <ul class="terms-list">
                <li>payable in Canadian dollars (CAD) unless otherwise specified;</li>
                <li>non-refundable upon commencement of services, except as expressly provided in the service agreement; and</li>
                <li>subject to applicable taxes, including GST/HST, as required by Canadian law.</li>
            </ul>
            <p>Wynston reserves the right to suspend or terminate access to paid services upon non-payment of fees.</p>

            <h3 class="terms-h3">4.7 Account Suspension & Termination</h3>
            <p>Wynston reserves the right, in its sole discretion, to suspend or permanently terminate any Developer Account and remove associated Content, with or without notice, in the event that:</p>
            <ul class="terms-list">
                <li>the Developer provides false, misleading, or inaccurate information;</li>
                <li>the Developer submits Content that violates applicable law, these Terms, or the rights of any third party;</li>
                <li>the Developer fails to comply with REDMA, BCFSA regulations, or any other applicable regulatory requirement;</li>
                <li>payment obligations are not met; or</li>
                <li>Wynston determines, in its reasonable judgment, that the Developer's presence on the Platform poses a reputational, legal, or regulatory risk.</li>
            </ul>
        </div>

        <!-- ── Section 5 ── -->
        <div class="terms-section" id="s5">
            <h2 class="terms-h2">5. Buyer & General User Terms</h2>

            <h3 class="terms-h3">5.1 Informational Use Only</h3>
            <p>All Content provided on the Platform is for informational and research purposes only. Nothing on the Platform constitutes financial advice, legal advice, investment advice, or a recommendation to purchase, sell, or lease any specific property. Users should independently verify all information and seek qualified professional advice before making any real estate decision.</p>

            <div class="terms-callout terms-callout-warning">
                <strong>NO PURCHASE OR DEPOSIT ACCEPTANCE:</strong> Wynston does not accept deposits, purchase agreements, reservations, or expressions of interest for any property listed on the Platform. Any purchase of a pre-sale unit must be completed directly with the Developer and their authorized sales team, in accordance with all applicable disclosure and contractual requirements.
            </div>

            <h3 class="terms-h3">5.2 "Notify Me" & Communication Opt-In</h3>
            <p>By submitting your email address or contact information via the Platform's "Notify Me" feature or any registration or contact form, you consent to receive:</p>
            <ul class="terms-list">
                <li>informational updates regarding projects you have expressed interest in;</li>
                <li>market research and new listing alerts relevant to your search criteria; and</li>
                <li>periodic communications from Tam Nguyen and Royal Pacific Realty Corporation regarding real estate services.</li>
            </ul>
            <p>You may unsubscribe from marketing communications at any time by clicking the unsubscribe link in any email or by contacting us directly. Transactional and legally required communications are exempt from opt-out requests.</p>
        </div>

        <!-- ── Section 6 ── -->
        <div class="terms-section" id="s6">
            <h2 class="terms-h2">6. VOW & Member Account Requirements</h2>
            <p>In compliance with the Real Estate Board of Greater Vancouver's Virtual Office Website (VOW) Rules and Regulations, the following conditions apply to Member Account registration:</p>

            <h3 class="terms-h3">6.1 Registration Requirement</h3>
            <p>Certain features of the Platform — including access to detailed sold price data, historical listing records, and specific project documents — may require you to register for a Member Account. Registration constitutes your agreement that:</p>
            <ul class="terms-list">
                <li>you have a bona fide interest in the purchase, sale, or lease of real estate in the Greater Vancouver area;</li>
                <li>you will use the data accessed solely for your own personal, non-commercial research purposes; and</li>
                <li>you acknowledge you are entering into a lawful broker-consumer relationship with Tam Nguyen (for website use purposes only), which does not constitute a representation agreement.</li>
            </ul>

            <h3 class="terms-h3">6.2 Prohibited Uses of MLS® Data</h3>
            <p>As a condition of accessing MLS® data through the Platform, you agree not to:</p>
            <ul class="terms-list">
                <li>copy, redistribute, publish, or commercially exploit any MLS® data obtained through the Platform;</li>
                <li>use automated tools, bots, or scrapers to collect data from the Platform; or</li>
                <li>use MLS® data for any purpose other than personal real estate research.</li>
            </ul>
            <p>Violation of these conditions may result in immediate account termination and may expose you to legal liability under the Real Estate Board of Greater Vancouver's Rules and applicable copyright law.</p>
        </div>

        <!-- ── Section 7 ── -->
        <div class="terms-section" id="s7">
            <h2 class="terms-h2">7. Intellectual Property & Proprietary Data</h2>

            <h3 class="terms-h3">7.1 Wynston Platform IP</h3>
            <p>All intellectual property rights in and to the Platform — including the Wynston brand, logo, design system, software, databases, proprietary algorithms, written content, and the "Wynston Concierge" concept — are the exclusive property of Tam Nguyen operating as Wynston. Nothing in these Terms grants you any right, title, or interest in the Platform's intellectual property.</p>

            <h3 class="terms-h3">7.2 The Wynston Coming Soon Database — Proprietary Compiled Work</h3>
            <p>The Wynston Coming Soon database — comprising all pre-sale and upcoming development listings displayed on this Platform under the "Coming Soon," "Pre-Sale," or equivalent designation — constitutes an <strong>original proprietary compiled work</strong> protected under the <em>Copyright Act</em>, RSC 1985, c. C-42 (Canada). Wynston's copyright subsists in the selection, coordination, arrangement, and original presentation of this data, irrespective of whether individual underlying records (such as municipal development permit applications) are publicly available.</p>

            <p>This compiled database represents significant original research, editorial judgement, and ongoing curatorial effort by Wynston, including:</p>
            <ul class="terms-list">
                <li>systematic review and interpretation of municipal development permit records across Metro Vancouver municipalities;</li>
                <li>original identification, tracking, and categorization of pre-sale projects during the development pipeline phase;</li>
                <li>original neighbourhood classification, property type assignment, and completion timeline estimation; and</li>
                <li>ongoing verification, updating, and quality control of all database records.</li>
            </ul>

            <div class="terms-callout terms-callout-warning">
                <strong>© <?php echo date('Y'); ?> Wynston.ca — All Coming Soon listing data is proprietary.</strong> Reproduction, scraping, redistribution, or commercial use of this data without Wynston's express written consent is strictly prohibited and may give rise to legal liability under the <em>Copyright Act</em> and applicable common law.
            </div>

            <h3 class="terms-h3">7.3 Prohibited Uses of Coming Soon Data</h3>
            <p>Without Wynston's prior written consent, no person, company, brokerage, real estate platform, or automated system may:</p>
            <ul class="terms-list">
                <li><strong>scrape, crawl, or harvest</strong> any Coming Soon listing data from the Platform by automated means, including web scrapers, bots, spiders, or browser automation tools;</li>
                <li><strong>reproduce or republish</strong> any Coming Soon listing data on any other website, application, platform, or marketing material;</li>
                <li><strong>incorporate</strong> any Coming Soon listing data into any competing real estate database, search tool, or aggregation service;</li>
                <li><strong>sell, license, or distribute</strong> any Coming Soon listing data to third parties; or</li>
                <li><strong>use</strong> any Coming Soon listing data for direct marketing, solicitation, or prospecting purposes directed at developers, buyers, or any other person identified through the Platform.</li>
            </ul>
            <p>These prohibitions apply regardless of the technical means used and regardless of whether individual pieces of underlying source data may be obtainable elsewhere. It is the <em>compiled database</em> — Wynston's original selection, arrangement, and presentation of that data — that is protected.</p>
            <p>This section applies exclusively to Wynston's proprietary Coming Soon and pre-sale data. It does not apply to MLS® active listing data, which is subject to its own separate licensing terms described in Section 7.4 below.</p>

            <h3 class="terms-h3">7.4 MLS® Data Rights — Separate Licensing</h3>
            <p>MLS® active listing data displayed on the Platform is sourced under license from the Real Estate Board of Greater Vancouver and remains the property of REBGV and its member brokerages. This data is governed by REBGV Rules and the terms of Wynston's VOW license. Users and Developers have no right to reproduce, redistribute, scrape, or commercially exploit MLS® data. Wynston does not own MLS® data and makes no claim to it.</p>

            <h3 class="terms-h3">7.5 Developer-Submitted Content</h3>
            <p>Developers retain ownership of the intellectual property in Content they submit to the Platform, subject to the display license granted under Section 4. Wynston will not claim copyright ownership of Developer-submitted floor plans, renderings, or project descriptions. However, by submitting Content, Developers grant Wynston the right to include that Content within Wynston's compiled Coming Soon database, which as a whole constitutes Wynston's proprietary work as described in Section 7.2.</p>

            <h3 class="terms-h3">7.6 Enforcement</h3>
            <p>Wynston actively monitors the Platform for unauthorized scraping and data extraction. Wynston reserves the right to pursue all available legal remedies against any person or entity that violates this Section 7, including injunctive relief, damages, and an accounting of profits. Wynston may report unauthorized scraping activity to the applicable real estate regulatory bodies (including BCFSA and REBGV) where the activity involves licensed real estate professionals.</p>
        </div>

        <!-- ── Section 8 ── -->
        <div class="terms-section" id="s8">
            <h2 class="terms-h2">8. Privacy & Personal Information</h2>

            <div class="terms-privacy-crossref">
                <div class="tpc-icon">🔒</div>
                <div class="tpc-content">
                    <div class="tpc-title">This section is a summary. Our full Privacy Policy is a separate document.</div>
                    <div class="tpc-body">Wynston's collection, use, and protection of personal information is governed in full detail by our standalone <strong>Privacy Policy</strong>, which forms part of these Terms by reference. We strongly encourage you to read it — particularly if you are a Developer submitting Content, or a buyer using the "Notify Me" feature.</div>
                    <a href="privacy.php" class="tpc-btn">Read the Full Privacy Policy →</a>
                </div>
            </div>

            <p>In summary, Wynston is committed to protecting your privacy in accordance with the <strong>Personal Information Protection and Electronic Documents Act (PIPEDA)</strong> and the <strong>Personal Information Protection Act of British Columbia (PIPA BC)</strong>. The following is an overview of our key practices:</p>

            <h3 class="terms-h3">8.1 Information We Collect</h3>
            <p>We collect contact information (name, email, phone), account credentials, Developer professional details, search behaviour, device/technical data, and communications submitted through Platform forms. Full details of every data category, how long we keep it, and your rights are set out in our <a href="privacy.php">Privacy Policy</a>.</p>

            <h3 class="terms-h3">8.2 How We Use Your Information</h3>
            <p>Personal information is used to provide Platform services, send listing alerts you have opted into, comply with regulatory obligations, and — using <strong>anonymized aggregate data only</strong> — produce Market Intelligence Reports shared with Developers as part of the Concierge and Creative Package services. No individual is identified in these reports. See <a href="privacy.php#p5">Section 5 of our Privacy Policy</a> for a full explanation of this practice.</p>

            <h3 class="terms-h3">8.3 Cookies & Tracking</h3>
            <p>The Platform uses cookies and similar technologies to remember your preferences and analyze usage. By continuing to use the Platform you consent to this. You may disable non-essential cookies through your browser settings. See <a href="privacy.php#p7">Section 7 of our Privacy Policy</a> for full details.</p>

            <h3 class="terms-h3">8.4 We Do Not Sell Your Data</h3>
            <p>Wynston does not sell, rent, or trade your personal contact information to any third party for their independent marketing purposes.</p>

            <h3 class="terms-h3">8.5 Your Rights</h3>
            <p>You have the right to access, correct, delete, and withdraw consent over your personal information at any time. To exercise these rights, contact our Privacy Officer at <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a>. Full details of your PIPEDA rights are set out in <a href="privacy.php#p9">Section 9 of our Privacy Policy</a>.</p>
        </div>

        <!-- ── Section 9 ── -->
        <div class="terms-section" id="s9">
            <h2 class="terms-h2">9. Limitation of Liability & Disclaimer of Warranties</h2>

            <div class="terms-callout terms-callout-navy">
                <strong>DISCLAIMER OF WARRANTIES:</strong> To the fullest extent permitted by applicable law, the Platform and all Content are provided "as is" and "as available" without warranty of any kind, express or implied, including but not limited to warranties of merchantability, fitness for a particular purpose, accuracy, or non-infringement.
            </div>

            <h3 class="terms-h3">9.1 Limitation of Liability</h3>
            <p>To the fullest extent permitted by applicable law, Wynston, Tam Nguyen, and Royal Pacific Realty Corporation shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or in connection with your use of the Platform, including but not limited to:</p>
            <ul class="terms-list">
                <li>reliance on inaccurate or incomplete listing information;</li>
                <li>decisions to purchase, sell, or lease real estate based on Platform Content;</li>
                <li>loss of data, revenue, or business opportunity; or</li>
                <li>delays, interruptions, or errors in MLS® data delivery.</li>
            </ul>
            <p>In no event shall Wynston's aggregate liability to any User or Developer exceed the greater of: (i) the total fees paid by that party to Wynston in the twelve (12) months preceding the claim; or (ii) CAD $500.00.</p>
        </div>

        <!-- ── Section 10 ── -->
        <div class="terms-section" id="s10">
            <h2 class="terms-h2">10. Indemnification</h2>
            <p>You agree to indemnify, defend, and hold harmless Wynston, Tam Nguyen, Royal Pacific Realty Corporation, and their respective officers, employees, and agents from and against any and all claims, damages, losses, liabilities, costs, and expenses (including reasonable legal fees) arising out of or relating to:</p>
            <ul class="terms-list">
                <li>your use of or access to the Platform;</li>
                <li>any Content you submit, post, or transmit through the Platform;</li>
                <li>your violation of these Terms;</li>
                <li>your violation of any applicable law or regulation, including REDMA or BCFSA requirements; or</li>
                <li>any claim that your Content infringes the intellectual property or other rights of any third party.</li>
            </ul>
        </div>

        <!-- ── Section 11 ── -->
        <div class="terms-section" id="s11">
            <h2 class="terms-h2">11. Modifications to the Platform & Terms</h2>

            <h3 class="terms-h3">11.1 Platform Changes</h3>
            <p>Wynston reserves the right to modify, suspend, or discontinue any aspect of the Platform — including available features, pricing structures, and service tiers — at any time with or without notice. Wynston shall not be liable to any User or Developer for any such modification, suspension, or discontinuation.</p>

            <h3 class="terms-h3">11.2 Updates to These Terms</h3>
            <p>Wynston may update these Terms of Service at any time. The revised Terms will be posted on the Platform with an updated "Last Updated" date. Continued use of the Platform following the posting of revised Terms constitutes your acceptance of those changes. If you do not agree to the revised Terms, you must discontinue use of the Platform immediately.</p>
        </div>

        <!-- ── Section 12 ── -->
        <div class="terms-section" id="s12">
            <h2 class="terms-h2">12. Dispute Resolution</h2>

            <h3 class="terms-h3">12.1 Good Faith Negotiation</h3>
            <p>In the event of any dispute arising out of or relating to these Terms or the Platform, the parties agree to first attempt resolution through good faith negotiation. Either party may initiate this process by providing written notice describing the nature of the dispute and relief sought. The parties shall have thirty (30) days to resolve the dispute before proceeding to formal dispute resolution.</p>

            <h3 class="terms-h3">12.2 Mediation</h3>
            <p>If the dispute cannot be resolved through negotiation within the thirty (30) day period, either party may refer the matter to non-binding mediation administered by a mutually agreed-upon mediator in Vancouver, British Columbia. The costs of mediation shall be shared equally by the parties.</p>

            <h3 class="terms-h3">12.3 Litigation</h3>
            <p>If mediation fails to resolve the dispute, either party may pursue their legal remedies in the courts of the Province of British Columbia. The parties irrevocably submit to the exclusive jurisdiction of the courts located in the City of Vancouver, British Columbia, Canada.</p>
        </div>

        <!-- ── Section 13 ── -->
        <div class="terms-section" id="s13">
            <h2 class="terms-h2">13. Professional Disclosure</h2>
            <p>Tam Nguyen is a licensed real estate professional registered with the Real Estate Board of Greater Vancouver and operating under the supervision of Royal Pacific Realty Corporation, a licensed brokerage in the Province of British Columbia. All real estate trading activities facilitated through this Platform are conducted in accordance with:</p>
            <ul class="terms-list">
                <li>The Real Estate Services Act, RSBC 1996, c. 397;</li>
                <li>The regulations and bylaws of the British Columbia Financial Services Authority (BCFSA);</li>
                <li>The Real Estate Board of Greater Vancouver Rules and Regulations; and</li>
                <li>The Real Estate Development Marketing Act (REDMA), SBC 2004, c. 41, as applicable to pre-sale development projects.</li>
            </ul>
            <p>Wynston is not affiliated with or endorsed by the Real Estate Board of Greater Vancouver, the BCFSA, or any government authority.</p>
        </div>

        <!-- ── Section 14 ── -->
        <div class="terms-section" id="s14">
            <h2 class="terms-h2">14. Governing Law</h2>
            <p>These Terms of Service and any dispute arising hereunder shall be governed by and construed in accordance with the laws of the Province of British Columbia and the applicable federal laws of Canada, without regard to conflicts of law principles. The parties expressly exclude the application of the United Nations Convention on Contracts for the International Sale of Goods.</p>
        </div>

        <!-- ── Section 15 ── -->
        <div class="terms-section" id="s15">
            <h2 class="terms-h2">15. General Provisions</h2>

            <h3 class="terms-h3">15.1 Entire Agreement</h3>
            <p>These Terms of Service, together with any applicable service agreement or order form executed between Wynston and a Developer, constitute the entire agreement between the parties with respect to the subject matter hereof and supersede all prior and contemporaneous agreements, representations, and understandings.</p>

            <h3 class="terms-h3">15.2 Severability</h3>
            <p>If any provision of these Terms is found to be invalid, illegal, or unenforceable by a court of competent jurisdiction, the remaining provisions shall continue in full force and effect.</p>

            <h3 class="terms-h3">15.3 Waiver</h3>
            <p>Wynston's failure to enforce any right or provision of these Terms shall not constitute a waiver of such right or provision. Any waiver must be in writing and signed by an authorized representative of Wynston to be effective.</p>

            <h3 class="terms-h3">15.4 Force Majeure</h3>
            <p>Wynston shall not be liable for any failure or delay in performance resulting from causes beyond its reasonable control, including acts of God, natural disasters, pandemics, government orders, internet or telecommunications failures, or MLS® data feed outages.</p>

            <h3 class="terms-h3">15.5 Contact Information</h3>
            <p>For questions, concerns, or notices regarding these Terms of Service, please contact:</p>
            <div class="terms-contact-card">
                <div class="terms-contact-brand">Wynston.ca</div>
                <div>Tam Nguyen, Licensee &nbsp;·&nbsp; Royal Pacific Realty Corporation</div>
                <div><a href="mailto:info@wynston.ca">info@wynston.ca</a> &nbsp;·&nbsp; <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a></div>
                <div><a href="https://www.wynston.ca">www.wynston.ca</a></div>
            </div>
        </div>

        <!-- Closing acknowledgement -->
        <div class="terms-footer-note">
            By using the Wynston Platform, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
        </div>

    </div><!-- /terms-body -->
    </div><!-- /col-lg-9 -->

</div><!-- /row -->
</div><!-- /container -->
</section>
<!-- ============================ Terms Body End ================================== -->


<style>
/* ── Page layout ───────────────────────────────────────────────────── */
section { padding: 60px 0; }

/* ── Sticky TOC sidebar ────────────────────────────────────────────── */
.terms-toc {
    position: sticky;
    top: 90px;
    background: #fff;
    border: 1px solid #e8e4dd;
    border-radius: 12px;
    padding: 24px 20px;
    margin-top: 40px;
    box-shadow: 0 2px 16px rgba(0,36,70,.06);
}
.terms-toc-title {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #c9a84c;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0ece6;
}
.terms-toc-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.terms-toc-list li {
    margin-bottom: 2px;
}
.terms-toc-list a {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #555;
    text-decoration: none;
    padding: 5px 8px;
    border-radius: 6px;
    transition: background .15s, color .15s;
    line-height: 1.4;
}
.terms-toc-list a:hover,
.terms-toc-list a.active {
    background: #f0f4ff;
    color: #002446;
}

/* ── Main body ─────────────────────────────────────────────────────── */
.terms-body {
    padding: 40px 0 40px 40px;
}

/* ── Preamble banner ───────────────────────────────────────────────── */
.terms-preamble {
    background: #f0f4ff;
    border-left: 4px solid #002446;
    border-radius: 0 8px 8px 0;
    padding: 18px 20px;
    font-size: 13px;
    color: #002446;
    line-height: 1.7;
    margin-bottom: 40px;
}

/* ── Sections ──────────────────────────────────────────────────────── */
.terms-section {
    margin-bottom: 48px;
    padding-bottom: 48px;
    border-bottom: 1px solid #f0ece6;
}
.terms-section:last-of-type {
    border-bottom: none;
}
.terms-section p {
    font-size: 14px;
    color: #555;
    line-height: 1.85;
    margin-bottom: 14px;
}

/* ── Headings ──────────────────────────────────────────────────────── */
.terms-h2 {
    font-size: 20px;
    font-weight: 800;
    color: #002446;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 2px solid #c9a84c;
    display: inline-block;
}
.terms-h3 {
    font-size: 15px;
    font-weight: 700;
    color: #002446;
    margin: 24px 0 10px;
}

/* ── Bullet list ───────────────────────────────────────────────────── */
.terms-list {
    padding-left: 20px;
    margin-bottom: 14px;
}
.terms-list li {
    font-size: 14px;
    color: #555;
    line-height: 1.85;
    margin-bottom: 6px;
    padding-left: 4px;
}
.terms-list li::marker {
    color: #c9a84c;
}

/* ── Callout boxes ─────────────────────────────────────────────────── */
.terms-callout {
    border-radius: 8px;
    padding: 18px 20px;
    font-size: 13px;
    line-height: 1.7;
    margin: 20px 0;
}
.terms-callout-warning {
    background: #fffbeb;
    border-left: 4px solid #c9a84c;
    color: #7c5a00;
}
.terms-callout-navy {
    background: #f0f4ff;
    border-left: 4px solid #002446;
    color: #002446;
}

/* ── Contact card ──────────────────────────────────────────────────── */
.terms-contact-card {
    background: #002446;
    border-radius: 10px;
    padding: 24px 28px;
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
    color: rgba(255,255,255,.75);
    line-height: 1.6;
}
.terms-contact-brand {
    font-size: 18px;
    font-weight: 800;
    color: #c9a84c;
    margin-bottom: 4px;
}
.terms-contact-card a {
    color: rgba(255,255,255,.85);
    text-decoration: none;
    transition: color .15s;
}
.terms-contact-card a:hover {
    color: #c9a84c;
}

/* ── Footer acknowledgement ────────────────────────────────────────── */
.terms-footer-note {
    background: #002446;
    color: rgba(255,255,255,.75);
    text-align: center;
    font-size: 13px;
    font-style: italic;
    padding: 20px 28px;
    border-radius: 10px;
    margin-top: 48px;
    line-height: 1.7;
}

/* ── Privacy cross-reference box ──────────────────────────────────── */
.terms-privacy-crossref {
    display: flex; gap: 16px; align-items: flex-start;
    background: linear-gradient(135deg, #002446, #003a70);
    border-radius: 12px; padding: 24px; margin: 0 0 24px;
    border: 1px solid rgba(201,168,76,.25);
}
.tpc-icon { font-size: 28px; flex-shrink: 0; margin-top: 2px; }
.tpc-content { display: flex; flex-direction: column; gap: 10px; }
.tpc-title { font-size: 13px; font-weight: 800; color: #c9a84c; text-transform: uppercase; letter-spacing: .5px; }
.tpc-body  { font-size: 13px; color: rgba(255,255,255,.8); line-height: 1.7; }
.tpc-body strong { color: #fff; }
.tpc-btn {
    display: inline-block; align-self: flex-start;
    background: linear-gradient(135deg, #c9a84c, #e8d84b);
    color: #002446; font-size: 12px; font-weight: 800;
    padding: 10px 20px; border-radius: 24px; text-decoration: none;
    text-transform: uppercase; letter-spacing: .5px;
    transition: opacity .2s, transform .2s; margin-top: 4px;
}
.tpc-btn:hover { opacity: .85; transform: translateY(-1px); color: #002446; }

@media (max-width: 991px) {
    .terms-body { padding: 24px 0; }
}
</style>

<script>
// Highlight active TOC link on scroll
(function() {
    var sections = document.querySelectorAll('.terms-section[id]');
    var links    = document.querySelectorAll('.terms-toc-list a');
    if (!sections.length || !links.length) return;

    function onScroll() {
        var scrollY = window.scrollY + 120;
        var current = '';
        sections.forEach(function(s) {
            if (s.offsetTop <= scrollY) current = s.id;
        });
        links.forEach(function(a) {
            a.classList.toggle('active', a.getAttribute('href') === '#' + current);
        });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>