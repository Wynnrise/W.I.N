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
                <h2 class="ipt-title" style="color:#fff;">Privacy Policy</h2>
                <span class="ipn-subtitle" style="color:rgba(255,255,255,.75);">
                    Last Updated: February 28, 2026 &nbsp;·&nbsp; Wynston.ca
                </span>
            </div>
        </div>
    </div>
</div>
<!-- ============================ Page Title End ================================== -->


<!-- ============================ Privacy Body ================================== -->
<section class="pt-0">
<div class="container">
<div class="row">

    <!-- ── Sticky sidebar TOC ───────────────────────────────────────────── -->
    <div class="col-lg-3 col-md-12 d-none d-lg-block">
        <div class="terms-toc">
            <div class="terms-toc-title">Contents</div>
            <ul class="terms-toc-list">
                <li><a href="#p0">Our Commitment</a></li>
                <li><a href="#p1">1. Who We Are</a></li>
                <li><a href="#p2">2. What We Collect</a></li>
                <li><a href="#p3">3. How We Collect It</a></li>
                <li><a href="#p4">4. How We Use Your Data</a></li>
                <li><a href="#p5">5. The Wynston Market Intelligence Advantage</a></li>
                <li><a href="#p6">6. Who We Share Data With</a></li>
                <li><a href="#p7">7. Cookies & Tracking</a></li>
                <li><a href="#p8">8. Data Retention</a></li>
                <li><a href="#p9">9. Your Rights</a></li>
                <li><a href="#p10">10. Data Security</a></li>
                <li><a href="#p11">11. Children's Privacy</a></li>
                <li><a href="#p12">12. Third-Party Links</a></li>
                <li><a href="#p13">13. Changes to This Policy</a></li>
                <li><a href="#p14">14. Contact Us</a></li>
            </ul>
        </div>
    </div>

    <!-- ── Main content ─────────────────────────────────────────────────── -->
    <div class="col-lg-9 col-md-12">
    <div class="terms-body">

        <!-- Preamble -->
        <div class="terms-preamble">
            Wynston.ca is committed to protecting your privacy in accordance with the <strong>Personal Information Protection and Electronic Documents Act (PIPEDA)</strong> and the <strong>Personal Information Protection Act of British Columbia (PIPA BC)</strong>. This Privacy Policy explains what information we collect, how we use it, and your rights regarding your personal data. By using the Wynston Platform, you agree to the practices described in this Policy.
        </div>

        <!-- ── Our Commitment ── -->
        <div class="terms-section" id="p0">
            <h2 class="terms-h2">Our Commitment to You</h2>

            <div class="privacy-commitment-grid">
                <div class="privacy-commitment-card">
                    <div class="pcc-icon">🔒</div>
                    <div class="pcc-title">We Never Sell Your Data</div>
                    <div class="pcc-body">Your personal contact information is never sold or rented to third parties for their independent marketing purposes.</div>
                </div>
                <div class="privacy-commitment-card">
                    <div class="pcc-icon">✉️</div>
                    <div class="pcc-title">Consent-Based Marketing</div>
                    <div class="pcc-body">We only send you marketing emails when you have explicitly opted in, and every email includes a one-click unsubscribe.</div>
                </div>
                <div class="privacy-commitment-card">
                    <div class="pcc-icon">📊</div>
                    <div class="pcc-title">Aggregate Data Only</div>
                    <div class="pcc-body">When we share buyer demand insights with developers, it is always anonymized aggregate data — never individual profiles.</div>
                </div>
                <div class="privacy-commitment-card">
                    <div class="pcc-icon">🛡️</div>
                    <div class="pcc-title">Your Rights Are Real</div>
                    <div class="pcc-body">You can access, correct, or delete your personal information at any time by contacting us at privacy@wynston.ca.</div>
                </div>
            </div>
        </div>

        <!-- ── Section 1 ── -->
        <div class="terms-section" id="p1">
            <h2 class="terms-h2">1. Who We Are</h2>
            <p>Wynston.ca is a real estate research and marketing platform operated by <strong>Tam Nguyen</strong>, a licensed Real Estate Agent registered with the Real Estate Board of Greater Vancouver (REBGV) and operating under <strong>Royal Pacific Realty Corporation</strong>.</p>
            <p>For the purposes of PIPEDA and PIPA BC, Tam Nguyen operating as Wynston is the <strong>"organization"</strong> responsible for personal information under our control. Our designated Privacy Officer can be reached at <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a>.</p>
            <p>The Platform aggregates data from public municipal records, direct Developer submissions, and the MLS® System of the Real Estate Board of Greater Vancouver to help buyers discover pre-sale and active listings across Metro Vancouver.</p>
        </div>

        <!-- ── Section 2 ── -->
        <div class="terms-section" id="p2">
            <h2 class="terms-h2">2. What Personal Information We Collect</h2>
            <p>We collect personal information only to the extent necessary to provide our services. The categories of information we may collect include:</p>

            <h3 class="terms-h3">2.1 From Buyers & General Users</h3>
            <div class="privacy-data-table">
                <div class="pdt-row pdt-header">
                    <div class="pdt-col">Data Type</div>
                    <div class="pdt-col">Examples</div>
                    <div class="pdt-col">When Collected</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Contact Information</strong></div>
                    <div class="pdt-col">Name, email address, phone number</div>
                    <div class="pdt-col">Contact forms, "Notify Me" signups</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Search Behaviour</strong></div>
                    <div class="pdt-col">Neighbourhoods searched, filters applied, property pages viewed, listing saves</div>
                    <div class="pdt-col">Automatically while browsing</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Device & Technical Data</strong></div>
                    <div class="pdt-col">IP address, browser type, operating system, referring URL</div>
                    <div class="pdt-col">Automatically via server logs</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Communication Records</strong></div>
                    <div class="pdt-col">Messages submitted through contact or inquiry forms</div>
                    <div class="pdt-col">When you contact us</div>
                </div>
            </div>

            <h3 class="terms-h3">2.2 From Developers</h3>
            <div class="privacy-data-table">
                <div class="pdt-row pdt-header">
                    <div class="pdt-col">Data Type</div>
                    <div class="pdt-col">Examples</div>
                    <div class="pdt-col">When Collected</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Account Information</strong></div>
                    <div class="pdt-col">Full name, company name, email, phone, website</div>
                    <div class="pdt-col">Developer account registration</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Project Content</strong></div>
                    <div class="pdt-col">Renderings, floor plans, project descriptions, pricing</div>
                    <div class="pdt-col">Listing submission</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Professional Details</strong></div>
                    <div class="pdt-col">Developer bio, awards, previous projects</div>
                    <div class="pdt-col">Profile setup</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Payment Information</strong></div>
                    <div class="pdt-col">Billing details for Concierge/Creative Package services</div>
                    <div class="pdt-col">Service purchase (processed via secure payment processor)</div>
                </div>
            </div>

            <h3 class="terms-h3">2.3 Information We Do Not Collect</h3>
            <p>Wynston does not collect, store, or process:</p>
            <ul class="terms-list">
                <li>Social Insurance Numbers (SIN) or government-issued identification numbers;</li>
                <li>Financial account numbers, credit card numbers (these are handled directly by our payment processor); or</li>
                <li>Sensitive personal information such as health data, racial or ethnic origin, or political opinions.</li>
            </ul>
        </div>

        <!-- ── Section 3 ── -->
        <div class="terms-section" id="p3">
            <h2 class="terms-h2">3. How We Collect Your Information</h2>
            <p>We collect personal information through the following means:</p>

            <h3 class="terms-h3">3.1 Directly From You</h3>
            <ul class="terms-list">
                <li><strong>"Notify Me" feature</strong> — when you submit your email to receive updates on a specific listing or project;</li>
                <li><strong>Contact & inquiry forms</strong> — when you reach out through any form on the Platform;</li>
                <li><strong>Developer account registration</strong> — when a developer creates an account and submits project listings; and</li>
                <li><strong>Service agreements</strong> — when a developer enters into a Concierge or Creative Package arrangement.</li>
            </ul>

            <h3 class="terms-h3">3.2 Automatically</h3>
            <p>When you browse the Platform, we automatically collect certain technical and behavioural data through:</p>
            <ul class="terms-list">
                <li><strong>Server logs</strong> — recording page visits, timestamps, IP addresses, and browser information;</li>
                <li><strong>Cookies and local storage</strong> — storing search preferences and session data (see Section 7); and</li>
                <li><strong>Analytics tools</strong> — if Google Analytics or similar tools are enabled, these collect aggregate usage statistics.</li>
            </ul>

            <h3 class="terms-h3">3.3 From Third Parties</h3>
            <ul class="terms-list">
                <li><strong>MLS® data feed</strong> — listing data sourced under license from the Real Estate Board of Greater Vancouver; and</li>
                <li><strong>Municipal records</strong> — publicly available development permit and zoning data from City of Vancouver and Metro Vancouver municipalities.</li>
            </ul>
        </div>

        <!-- ── Section 4 ── -->
        <div class="terms-section" id="p4">
            <h2 class="terms-h2">4. How We Use Your Personal Information</h2>
            <p>We use personal information only for the purposes for which it was collected, or for consistent purposes that you would reasonably expect. Specifically:</p>

            <h3 class="terms-h3">4.1 To Provide the Platform & Services</h3>
            <ul class="terms-list">
                <li>To display listing information and personalize your search experience;</li>
                <li>to process Developer account registrations, listing submissions, and service agreements;</li>
                <li>to send you property update notifications you have specifically requested via "Notify Me"; and</li>
                <li>to facilitate communication between you and Tam Nguyen as your real estate resource.</li>
            </ul>

            <h3 class="terms-h3">4.2 For Marketing Communications (With Your Consent)</h3>
            <p>With your explicit consent, we may use your contact information to:</p>
            <ul class="terms-list">
                <li>send new listing alerts for areas matching your search history;</li>
                <li>share Metro Vancouver market reports, neighbourhood insights, and pre-sale project updates; and</li>
                <li>send occasional communications from Tam Nguyen regarding real estate services, open houses, or market commentary.</li>
            </ul>
            <div class="terms-callout terms-callout-warning">
                <strong>Your choice matters:</strong> Marketing communications are entirely opt-in. Every marketing email includes a clear unsubscribe link. You can also withdraw consent at any time by emailing <a href="mailto:privacy@wynston.ca" style="color:#7c5a00;">privacy@wynston.ca</a>. Withdrawal of consent does not affect the lawfulness of communications sent before your request.
            </div>

            <h3 class="terms-h3">4.3 For Legal & Regulatory Compliance</h3>
            <ul class="terms-list">
                <li>To comply with BCFSA, REBGV, REDMA, and other applicable regulatory requirements;</li>
                <li>to respond to lawful requests from government authorities or courts; and</li>
                <li>to maintain records as required under the Real Estate Services Act.</li>
            </ul>

            <h3 class="terms-h3">4.4 For Platform Security & Improvement</h3>
            <ul class="terms-list">
                <li>To detect and prevent fraud, unauthorized access, or abuse of the Platform;</li>
                <li>to troubleshoot technical issues and improve Platform performance; and</li>
                <li>to analyze usage patterns and improve the user experience.</li>
            </ul>
        </div>

        <!-- ── Section 5 — THE KEY SECTION ── -->
        <div class="terms-section" id="p5">
            <h2 class="terms-h2">5. The Wynston Market Intelligence Advantage</h2>

            <div class="terms-callout terms-callout-gold">
                <strong>What this means for buyers:</strong> Your individual identity is never shared with developers. What developers see is anonymized market demand data — for example, "312 buyers searched this neighbourhood last month." You remain completely anonymous.
            </div>

            <p>One of Wynston's core value propositions — to both buyers and developers — is our ability to bridge the gap between buyer demand and developer supply. As part of this mission, Wynston uses <strong>anonymized, aggregate search behaviour data</strong> to produce <strong>Market Intelligence Reports</strong> that are shared with Developers as part of our Concierge and Creative Package services.</p>

            <h3 class="terms-h3">5.1 What Market Intelligence Reports Include</h3>
            <p>These reports are based entirely on aggregate, non-identifiable data and may include insights such as:</p>
            <ul class="terms-list">
                <li>the number of unique users who searched a specific neighbourhood, city, or postal code during a given period;</li>
                <li>the most popular property types (e.g., duplex, townhome, condo) searched by buyers on the Platform;</li>
                <li>aggregate price range preferences among Platform users in specific areas;</li>
                <li>overall Platform traffic trends and seasonal buyer activity patterns; and</li>
                <li>the number of "Notify Me" registrations for a specific project or neighbourhood.</li>
            </ul>

            <h3 class="terms-h3">5.2 What Market Intelligence Reports Do NOT Include</h3>
            <ul class="terms-list">
                <li>Any individual user's name, email address, phone number, or contact details;</li>
                <li>any data that could identify a specific individual's search history or behaviour; or</li>
                <li>any information collected from users who have opted out of data collection.</li>
            </ul>

            <h3 class="terms-h3">5.3 Why This Matters for Developers</h3>
            <p>This Market Intelligence data helps Developers make better decisions about project positioning, pricing, and marketing — based on <em>real, current buyer demand</em> rather than broad market assumptions. For example, a developer considering a project in East Vancouver can see exactly how many active buyers are searching that area, what unit types they prefer, and what price sensitivity exists — all before committing to a marketing budget.</p>
            <p>This is a core part of the value Wynston delivers through its Concierge and Creative Package services, and is disclosed here transparently so all Platform users understand how their aggregate, anonymized behaviour contributes to a better-informed market.</p>

            <h3 class="terms-h3">5.4 Your Choice</h3>
            <p>If you prefer that your search behaviour — even in anonymized aggregate form — not be included in Market Intelligence Reports, you may opt out by contacting us at <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a>. We will honour your request within a reasonable timeframe.</p>
        </div>

        <!-- ── Section 6 ── -->
        <div class="terms-section" id="p6">
            <h2 class="terms-h2">6. Who We Share Your Information With</h2>
            <p>Wynston does not sell, rent, or trade your personal information. We share personal information only in the following limited circumstances:</p>

            <h3 class="terms-h3">6.1 Tam Nguyen & Royal Pacific Realty Corporation</h3>
            <p>As the licensed real estate professional operating the Platform, Tam Nguyen and, where required by regulatory obligations, Royal Pacific Realty Corporation may access contact information submitted through the Platform for the purpose of providing real estate services to Users who have requested or consented to such contact.</p>

            <h3 class="terms-h3">6.2 Service Providers</h3>
            <p>We work with trusted third-party service providers who assist in operating the Platform, including:</p>
            <ul class="terms-list">
                <li><strong>Web hosting and infrastructure</strong> (e.g., Hostinger) — to store and serve the Platform;</li>
                <li><strong>Email delivery services</strong> — to send transactional and marketing emails;</li>
                <li><strong>Payment processors</strong> — to securely process Developer service fees (we do not store payment card data); and</li>
                <li><strong>Analytics providers</strong> (e.g., Google Analytics, if enabled) — to analyze Platform usage in aggregate.</li>
            </ul>
            <p>All service providers are contractually required to protect personal information and use it only for the purposes for which it was disclosed.</p>

            <h3 class="terms-h3">6.3 Real Estate Board of Greater Vancouver</h3>
            <p>By registering for a Member Account to access VOW-restricted MLS® data, you enter into a broker-consumer relationship with Tam Nguyen. Certain registration details may be reported to the Real Estate Board of Greater Vancouver as required by their VOW Rules.</p>

            <h3 class="terms-h3">6.4 Legal Requirements</h3>
            <p>We may disclose personal information if required to do so by law, court order, or government authority, or if we believe in good faith that disclosure is necessary to: protect the rights or safety of Wynston, our users, or the public; investigate fraud or security issues; or comply with applicable legal or regulatory obligations.</p>

            <h3 class="terms-h3">6.5 Business Transfers</h3>
            <p>In the event that Wynston or its associated business is sold, merged, or transferred, personal information held by Wynston may be transferred to the successor entity, subject to the same privacy protections described in this Policy. We will notify affected users prior to any such transfer where practicable.</p>
        </div>

        <!-- ── Section 7 ── -->
        <div class="terms-section" id="p7">
            <h2 class="terms-h2">7. Cookies & Tracking Technologies</h2>

            <h3 class="terms-h3">7.1 What Are Cookies?</h3>
            <p>Cookies are small text files stored on your device by your browser when you visit a website. They allow the Platform to remember your preferences, maintain your session, and understand how you use the site. We also use similar technologies such as local storage and session storage.</p>

            <h3 class="terms-h3">7.2 Types of Cookies We Use</h3>
            <div class="privacy-data-table">
                <div class="pdt-row pdt-header">
                    <div class="pdt-col">Cookie Type</div>
                    <div class="pdt-col">Purpose</div>
                    <div class="pdt-col">Can Be Disabled?</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Essential</strong></div>
                    <div class="pdt-col">Login sessions, security tokens, legal disclaimer acknowledgements</div>
                    <div class="pdt-col">No — required for the Platform to function</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Functional</strong></div>
                    <div class="pdt-col">Remembering search filters, neighbourhood preferences, map settings</div>
                    <div class="pdt-col">Yes — disabling may reduce functionality</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Analytics</strong></div>
                    <div class="pdt-col">Aggregate page view counts, traffic sources, popular listings (via Google Analytics if enabled)</div>
                    <div class="pdt-col">Yes — opt out via browser settings or Google Analytics opt-out</div>
                </div>
            </div>

            <h3 class="terms-h3">7.3 Google Maps</h3>
            <p>The Platform uses the Google Maps API to display property locations and neighbourhood maps. Google Maps may set its own cookies and collect location-related data. This is subject to <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a>. We do not store or access the location data collected by Google Maps beyond what is displayed on screen.</p>

            <h3 class="terms-h3">7.4 Managing Cookies</h3>
            <p>You can control cookies through your browser settings. Most browsers allow you to block or delete cookies. Please note that disabling essential cookies will prevent you from logging in or accessing certain Platform features. Instructions for managing cookies can be found in your browser's help documentation.</p>
        </div>

        <!-- ── Section 8 ── -->
        <div class="terms-section" id="p8">
            <h2 class="terms-h2">8. Data Retention</h2>
            <p>We retain personal information only as long as necessary for the purposes outlined in this Policy, or as required by applicable law. Our general retention practices are:</p>

            <div class="privacy-data-table">
                <div class="pdt-row pdt-header">
                    <div class="pdt-col">Data Category</div>
                    <div class="pdt-col">Retention Period</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>"Notify Me" email subscriptions</strong></div>
                    <div class="pdt-col">Until you unsubscribe or the relevant project is completed</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Developer account information</strong></div>
                    <div class="pdt-col">Duration of active account + 7 years (regulatory requirement)</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Contact form submissions</strong></div>
                    <div class="pdt-col">3 years from date of submission</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Server logs & technical data</strong></div>
                    <div class="pdt-col">90 days (rolling)</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Payment & billing records</strong></div>
                    <div class="pdt-col">7 years (CRA requirement)</div>
                </div>
                <div class="pdt-row">
                    <div class="pdt-col"><strong>Aggregate/anonymized analytics</strong></div>
                    <div class="pdt-col">Indefinitely (no personal data retained)</div>
                </div>
            </div>

            <p style="margin-top:16px;">When personal information is no longer required, it is securely deleted or anonymized so that it can no longer be associated with an individual.</p>
        </div>

        <!-- ── Section 9 ── -->
        <div class="terms-section" id="p9">
            <h2 class="terms-h2">9. Your Privacy Rights</h2>
            <p>Under PIPEDA and PIPA BC, you have the following rights with respect to your personal information held by Wynston:</p>

            <div class="privacy-rights-grid">
                <div class="privacy-right-card">
                    <div class="prc-icon">👁️</div>
                    <div class="prc-title">Right of Access</div>
                    <div class="prc-body">You can request a copy of the personal information we hold about you, including what it is used for and who it has been shared with.</div>
                </div>
                <div class="privacy-right-card">
                    <div class="prc-icon">✏️</div>
                    <div class="prc-title">Right to Correction</div>
                    <div class="prc-body">If any personal information we hold about you is inaccurate or incomplete, you can request that we correct it.</div>
                </div>
                <div class="privacy-right-card">
                    <div class="prc-icon">🗑️</div>
                    <div class="prc-title">Right to Deletion</div>
                    <div class="prc-body">You can request deletion of your personal information, subject to legal retention requirements (e.g., billing records we must keep for tax purposes).</div>
                </div>
                <div class="privacy-right-card">
                    <div class="prc-icon">🚫</div>
                    <div class="prc-title">Right to Withdraw Consent</div>
                    <div class="prc-body">You can withdraw consent to marketing communications or aggregate data inclusion at any time, with effect going forward.</div>
                </div>
                <div class="privacy-right-card">
                    <div class="prc-icon">📋</div>
                    <div class="prc-title">Right to Complain</div>
                    <div class="prc-body">If you believe we have not handled your personal information appropriately, you have the right to file a complaint with the Office of the Privacy Commissioner of Canada (OPC).</div>
                </div>
                <div class="privacy-right-card">
                    <div class="prc-icon">📤</div>
                    <div class="prc-title">Right to Portability</div>
                    <div class="prc-body">Where technically feasible, you can request that we provide your personal information in a structured, commonly used format.</div>
                </div>
            </div>

            <h3 class="terms-h3" style="margin-top:28px;">How to Exercise Your Rights</h3>
            <p>To make any privacy request, please contact our Privacy Officer at <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a> with the subject line <strong>"Privacy Request"</strong>. We will respond within <strong>30 days</strong> of receiving your request. We may ask you to verify your identity before processing your request.</p>
            <p>If you are unsatisfied with our response, you may contact the <strong>Office of the Privacy Commissioner of Canada</strong> at <a href="https://www.priv.gc.ca" target="_blank" rel="noopener">www.priv.gc.ca</a> or the <strong>Office of the Information and Privacy Commissioner for BC</strong> at <a href="https://www.oipc.bc.ca" target="_blank" rel="noopener">www.oipc.bc.ca</a>.</p>
        </div>

        <!-- ── Section 10 ── -->
        <div class="terms-section" id="p10">
            <h2 class="terms-h2">10. Data Security</h2>
            <p>Wynston implements commercially reasonable technical and organizational measures to protect personal information against unauthorized access, disclosure, alteration, or destruction. These measures include:</p>
            <ul class="terms-list">
                <li><strong>Password encryption</strong> — Developer account passwords are stored using industry-standard hashing (never in plain text);</li>
                <li><strong>HTTPS encryption</strong> — all data transmitted between your browser and the Platform is encrypted via TLS/SSL;</li>
                <li><strong>Access controls</strong> — personal information is accessible only to authorized personnel with a legitimate need;</li>
                <li><strong>Session management</strong> — Developer sessions automatically expire after a period of inactivity; and</li>
                <li><strong>Secure file storage</strong> — uploaded content (photos, floor plans, renderings) is stored in access-controlled directories.</li>
            </ul>
            <p>While we take these measures seriously, no method of data transmission or storage is 100% secure. In the event of a data breach that poses a real risk of significant harm to you, we will notify you and the Office of the Privacy Commissioner of Canada as required by the <em>Breach of Security Safeguards Regulations</em> under PIPEDA.</p>
        </div>

        <!-- ── Section 11 ── -->
        <div class="terms-section" id="p11">
            <h2 class="terms-h2">11. Children's Privacy</h2>
            <p>The Wynston Platform is not directed at individuals under the age of <strong>19</strong> (the age of majority in British Columbia). We do not knowingly collect personal information from individuals under 19. If you believe we have inadvertently collected information from a minor, please contact us immediately at <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a> and we will promptly delete such information.</p>
        </div>

        <!-- ── Section 12 ── -->
        <div class="terms-section" id="p12">
            <h2 class="terms-h2">12. Third-Party Links</h2>
            <p>The Platform may contain links to third-party websites, including developer project websites, real estate board resources, and municipality pages. These links are provided for your convenience and information only. Wynston is not responsible for the privacy practices or content of any third-party websites. We encourage you to review the privacy policies of any external sites you visit.</p>
        </div>

        <!-- ── Section 13 ── -->
        <div class="terms-section" id="p13">
            <h2 class="terms-h2">13. Changes to This Privacy Policy</h2>
            <p>Wynston may update this Privacy Policy from time to time to reflect changes in our practices, legal requirements, or Platform features. When we make material changes, we will:</p>
            <ul class="terms-list">
                <li>post the revised Policy on this page with an updated "Last Updated" date;</li>
                <li>where the change is significant, notify registered users by email at least 14 days before the change takes effect; and</li>
                <li>obtain fresh consent where required by applicable law.</li>
            </ul>
            <p>Continued use of the Platform after the effective date of any changes constitutes your acceptance of the revised Policy.</p>
        </div>

        <!-- ── Section 14 ── -->
        <div class="terms-section" id="p14">
            <h2 class="terms-h2">14. Contact Us</h2>
            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our handling of your personal information, please contact our Privacy Officer:</p>
            <div class="terms-contact-card">
                <div class="terms-contact-brand">Wynston.ca — Privacy Officer</div>
                <div>Tam Nguyen &nbsp;·&nbsp; Royal Pacific Realty Corporation</div>
                <div><strong style="color:#c9a84c;">Privacy inquiries:</strong> <a href="mailto:privacy@wynston.ca">privacy@wynston.ca</a></div>
                <div><strong style="color:#c9a84c;">General inquiries:</strong> <a href="mailto:info@wynston.ca">info@wynston.ca</a></div>
                <div><a href="https://www.wynston.ca">www.wynston.ca</a></div>
                <div style="margin-top:8px;font-size:11px;color:rgba(255,255,255,.4);">We respond to all privacy requests within 30 days.</div>
            </div>

            <div style="margin-top:24px;">
                <p><strong>Regulatory bodies you can contact if unsatisfied with our response:</strong></p>
                <ul class="terms-list">
                    <li><strong>Office of the Privacy Commissioner of Canada (OPC)</strong> — <a href="https://www.priv.gc.ca" target="_blank" rel="noopener">www.priv.gc.ca</a></li>
                    <li><strong>Office of the Information and Privacy Commissioner for BC (OIPC)</strong> — <a href="https://www.oipc.bc.ca" target="_blank" rel="noopener">www.oipc.bc.ca</a></li>
                </ul>
            </div>
        </div>

        <!-- Closing note -->
        <div class="terms-footer-note">
            By using the Wynston Platform, you acknowledge that you have read and understood this Privacy Policy and consent to the collection, use, and disclosure of your personal information as described herein.
        </div>

    </div><!-- /terms-body -->
    </div><!-- /col-lg-9 -->

</div><!-- /row -->
</div><!-- /container -->
</section>
<!-- ============================ Privacy Body End ================================== -->


<style>
/* ── Shared with terms.php — copy both or include a shared CSS file ── */
section { padding: 60px 0; }

.terms-toc {
    position: sticky; top: 90px;
    background: #fff; border: 1px solid #e8e4dd;
    border-radius: 12px; padding: 24px 20px; margin-top: 40px;
    box-shadow: 0 2px 16px rgba(0,36,70,.06);
}
.terms-toc-title {
    font-size: 11px; font-weight: 800; text-transform: uppercase;
    letter-spacing: 1.2px; color: #c9a84c; margin-bottom: 14px;
    padding-bottom: 10px; border-bottom: 2px solid #f0ece6;
}
.terms-toc-list { list-style: none; padding: 0; margin: 0; }
.terms-toc-list li { margin-bottom: 2px; }
.terms-toc-list a {
    display: block; font-size: 12px; font-weight: 600; color: #555;
    text-decoration: none; padding: 5px 8px; border-radius: 6px;
    transition: background .15s, color .15s; line-height: 1.4;
}
.terms-toc-list a:hover, .terms-toc-list a.active { background: #f0f4ff; color: #002446; }

.terms-body { padding: 40px 0 40px 40px; }

.terms-preamble {
    background: #f0f4ff; border-left: 4px solid #002446;
    border-radius: 0 8px 8px 0; padding: 18px 20px;
    font-size: 13px; color: #002446; line-height: 1.7; margin-bottom: 40px;
}

.terms-section {
    margin-bottom: 48px; padding-bottom: 48px;
    border-bottom: 1px solid #f0ece6;
}
.terms-section:last-of-type { border-bottom: none; }
.terms-section p { font-size: 14px; color: #555; line-height: 1.85; margin-bottom: 14px; }

.terms-h2 {
    font-size: 20px; font-weight: 800; color: #002446;
    margin-bottom: 18px; padding-bottom: 12px;
    border-bottom: 2px solid #c9a84c; display: inline-block;
}
.terms-h3 { font-size: 15px; font-weight: 700; color: #002446; margin: 24px 0 10px; }

.terms-list { padding-left: 20px; margin-bottom: 14px; }
.terms-list li { font-size: 14px; color: #555; line-height: 1.85; margin-bottom: 6px; padding-left: 4px; }
.terms-list li::marker { color: #c9a84c; }

.terms-callout { border-radius: 8px; padding: 18px 20px; font-size: 13px; line-height: 1.7; margin: 20px 0; }
.terms-callout-warning { background: #fffbeb; border-left: 4px solid #c9a84c; color: #7c5a00; }
.terms-callout-navy    { background: #f0f4ff; border-left: 4px solid #002446; color: #002446; }
.terms-callout-gold    { background: linear-gradient(135deg,#002446,#003a70); border-left: 4px solid #c9a84c; color: rgba(255,255,255,.85); }
.terms-callout-gold strong { color: #c9a84c; }

.terms-contact-card {
    background: #002446; border-radius: 10px; padding: 24px 28px; margin-top: 16px;
    display: flex; flex-direction: column; gap: 6px;
    font-size: 13px; color: rgba(255,255,255,.75); line-height: 1.6;
}
.terms-contact-brand { font-size: 18px; font-weight: 800; color: #c9a84c; margin-bottom: 4px; }
.terms-contact-card a { color: rgba(255,255,255,.85); text-decoration: none; transition: color .15s; }
.terms-contact-card a:hover { color: #c9a84c; }

.terms-footer-note {
    background: #002446; color: rgba(255,255,255,.75); text-align: center;
    font-size: 13px; font-style: italic; padding: 20px 28px;
    border-radius: 10px; margin-top: 48px; line-height: 1.7;
}

/* ── Commitment cards ──────────────────────────────────────────────── */
.privacy-commitment-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px;
}
.privacy-commitment-card {
    background: #f8faff; border: 1px solid #e8e4dd;
    border-radius: 10px; padding: 20px; display: flex; flex-direction: column; gap: 8px;
}
.pcc-icon { font-size: 24px; }
.pcc-title { font-size: 13px; font-weight: 800; color: #002446; }
.pcc-body  { font-size: 12px; color: #666; line-height: 1.6; }

/* ── Data table ────────────────────────────────────────────────────── */
.privacy-data-table {
    border: 1px solid #e8e4dd; border-radius: 8px;
    overflow: hidden; margin: 16px 0; font-size: 13px;
}
.pdt-row {
    display: grid; grid-template-columns: 1fr 1.6fr 1fr;
    border-bottom: 1px solid #f0ece6;
}
.pdt-row:last-child { border-bottom: none; }
.pdt-header { background: #002446; }
.pdt-header .pdt-col { color: #c9a84c; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.pdt-col { padding: 12px 14px; color: #555; line-height: 1.5; }
.pdt-row:not(.pdt-header):hover { background: #f8faff; }
.pdt-row:nth-child(even):not(.pdt-header) { background: #fafafa; }
.pdt-row:nth-child(even):not(.pdt-header):hover { background: #f0f4ff; }

/* ── Two-col data table (retention) ───────────────────────────────── */
.pdt-row.two-col { grid-template-columns: 1fr 1fr; }

/* ── Rights grid ───────────────────────────────────────────────────── */
.privacy-rights-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-top: 20px;
}
.privacy-right-card {
    border: 1px solid #e8e4dd; border-radius: 10px; padding: 18px;
    display: flex; flex-direction: column; gap: 8px;
    transition: box-shadow .2s, transform .2s;
}
.privacy-right-card:hover { box-shadow: 0 4px 20px rgba(0,36,70,.1); transform: translateY(-2px); }
.prc-icon  { font-size: 22px; }
.prc-title { font-size: 13px; font-weight: 800; color: #002446; }
.prc-body  { font-size: 12px; color: #666; line-height: 1.6; }

/* ── Mobile ────────────────────────────────────────────────────────── */
@media (max-width: 991px) {
    .terms-body { padding: 24px 0; }
}
@media (max-width: 767px) {
    .privacy-commitment-grid { grid-template-columns: 1fr; }
    .privacy-rights-grid     { grid-template-columns: 1fr 1fr; }
    .pdt-row { grid-template-columns: 1fr 1fr; }
    .pdt-row .pdt-col:last-child { display: none; }
    .pdt-header .pdt-col:last-child { display: none; }
}
@media (max-width: 480px) {
    .privacy-rights-grid { grid-template-columns: 1fr; }
    .pdt-row { grid-template-columns: 1fr; }
    .pdt-row .pdt-col:last-child { display: block; }
    .pdt-header .pdt-col:last-child { display: block; }
}
</style>

<script>
(function() {
    var sections = document.querySelectorAll('.terms-section[id]');
    var links    = document.querySelectorAll('.terms-toc-list a');
    if (!sections.length || !links.length) return;
    function onScroll() {
        var scrollY = window.scrollY + 120;
        var current = '';
        sections.forEach(function(s) { if (s.offsetTop <= scrollY) current = s.id; });
        links.forEach(function(a) { a.classList.toggle('active', a.getAttribute('href') === '#' + current); });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>

<?php
$hero_content = ob_get_clean();
include "$base_dir/style/base.php";
?>