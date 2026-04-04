<?php
// ── Handle contact form submission ────────────────────────────────────────────
$contact_success = false;
$contact_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    $sender_email = filter_var(trim($_POST['inquiry_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $sender_phone = htmlspecialchars(trim($_POST['inquiry_phone'] ?? ''));
    $sender_msg   = htmlspecialchars(trim($_POST['inquiry_message'] ?? ''));
    $prop_address = htmlspecialchars(trim($_POST['prop_address'] ?? 'Unknown Property'));

    if (!$sender_email) {
        $contact_error = 'Please enter a valid email address.';
    } elseif (empty($sender_msg)) {
        $contact_error = 'Please enter a message.';
    } else {
        $to      = 'sold@tamwynn.ca';
        $subject = "Property Inquiry – {$prop_address}";
        $body    = "You have received a new inquiry from your website.\n\n"
                 . "Property: {$prop_address}\n"
                 . "From Email: {$sender_email}\n"
                 . "Phone: " . ($sender_phone ?: 'Not provided') . "\n\n"
                 . "Message:\n{$sender_msg}\n";
        $headers = "From: noreply@tamwynn.ca\r\n"
                 . "Reply-To: {$sender_email}\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($to, $subject, $body, $headers)) {
            $contact_success = true;
        } else {
            $contact_error = 'Sorry, message could not be sent. Please call directly.';
        }
    }
}

// ── Load random paid featured properties ─────────────────────────────────────
$featured = [];
if (isset($pdo)) {
    try {
        $cols = $pdo->query("DESCRIBE multi_2025")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('is_paid', $cols) && in_array('img1', $cols)) {
            $feat_stmt = $pdo->query(
                "SELECT id, address, neighborhood, property_type, img1
                 FROM multi_2025
                 WHERE is_paid = 1 AND img1 IS NOT NULL AND img1 != ''
                 ORDER BY RAND() LIMIT 3"
            );
            $featured = $feat_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // silently fail
    }
}
?>


    <!-- ══ Agent Contact ══════════════════════════════════════════════════ -->
    <div class="sides-widget">
        <div class="sides-widget-header bg-primary">
            <div class="agent-photo">
                <img src="<?php echo $static_url; ?>/img/user-6.jpg" alt="Tam Nguyen">
            </div>
            <div class="sides-widget-details">
                <h4><a href="https://tamwynn.ca" target="_blank" rel="noopener">Tam Nguyen</a></h4>
                </h4>
                <span><i class="lni-phone-handset"></i>(604) 782-4689</span>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="sides-widget-body simple-form">
            <?php if ($contact_success): ?>
                <div style="background:#d4f5e2;color:#1a7a45;border-radius:8px;padding:16px;text-align:center;font-size:13px;font-weight:600;">
                    <i class="fas fa-check-circle" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                    Message sent! Tam will be in touch soon.
                </div>
            <?php else: ?>
                <?php if (!empty($contact_error)): ?>
                    <div style="background:#fee2e2;color:#dc2626;border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:12px;">
                        <i class="fas fa-exclamation-circle me-1"></i><?= $contact_error ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="prop_address" value="<?= isset($p['address']) ? htmlspecialchars($p['address']) : '' ?>">
                    <div class="form-group">
                        <label>Email <span style="color:#dc2626">*</span></label>
                        <input type="email" name="inquiry_email" class="form-control"
                               placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['inquiry_email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone <small style="color:#aaa">(optional)</small></label>
                        <input type="text" name="inquiry_phone" class="form-control"
                               placeholder="(604) 000-0000"
                               value="<?= htmlspecialchars($_POST['inquiry_phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="inquiry_message" class="form-control" rows="4"><?= isset($_POST['inquiry_message']) ? htmlspecialchars($_POST['inquiry_message']) : "I'm interested in this property. Please contact me." ?></textarea>
                    </div>
                    <button type="submit" name="send_inquiry"
                            class="btn btn-light-primary fw-medium rounded full-width">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ Mortgage Calculator ════════════════════════════════════════════ -->
    <div class="sides-widget">
        <div class="sides-widget-header bg-primary">
            <div class="sides-widget-details">
                <h4>Mortgage Calculator</h4>
                <span>Estimate your monthly payment</span>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="sides-widget-body simple-form">

            <div style="background:#f0f4ff;border-radius:7px;padding:9px 12px;font-size:11px;color:#555;margin-bottom:14px;display:flex;gap:8px;align-items:flex-start;">
                <i class="fas fa-info-circle" style="color:#0065ff;margin-top:2px;flex-shrink:0;"></i>
                <span>Check today's rates at
                    <a href="https://www.ratehub.ca" target="_blank" style="color:#0065ff;font-weight:600;">Ratehub</a>,
                    <a href="https://www.rbc.com/mortgages/mortgage-rates.html" target="_blank" style="color:#0065ff;font-weight:600;">RBC</a>, or
                    <a href="https://www.td.com/ca/en/personal-banking/products/mortgages/mortgage-rates" target="_blank" style="color:#0065ff;font-weight:600;">TD</a> and enter below.
                </span>
            </div>

            <div class="form-group">
                <label>Purchase Price ($)</label>
                <div class="input-with-icon">
                    <input type="number" id="mc-price" class="form-control" placeholder="e.g. 850000" min="0">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
            </div>
            <div class="form-group">
                <label>Down Payment ($)</label>
                <div class="input-with-icon">
                    <input type="number" id="mc-down" class="form-control" placeholder="e.g. 170000" min="0">
                    <i class="fa-solid fa-piggy-bank"></i>
                </div>
            </div>
            <div class="form-group">
                <label>Amortization</label>
                <div class="input-with-icon">
                    <select id="mc-term" class="form-control">
                        <option value="10">10 years</option>
                        <option value="15">15 years</option>
                        <option value="20">20 years</option>
                        <option value="25" selected>25 years</option>
                        <option value="30">30 years</option>
                    </select>
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
            </div>
            <div class="form-group">
                <label>Interest Rate (%)</label>
                <div class="input-with-icon">
                    <input type="number" id="mc-rate" class="form-control"
                           placeholder="e.g. 5.49" step="0.01" min="0" max="30">
                    <i class="fa fa-percent"></i>
                </div>
            </div>

            <button onclick="calcMortgage()"
                    class="btn btn-light-primary fw-medium rounded full-width">
                <i class="fas fa-calculator me-2"></i>Calculate
            </button>

            <div id="mc-error" style="display:none;margin-top:10px;background:#fee2e2;color:#dc2626;border-radius:7px;padding:9px 12px;font-size:12px;"></div>

            <div id="mc-result" style="display:none;margin-top:16px;background:#002446;border-radius:10px;padding:18px;text-align:center;color:#fff;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;opacity:0.65;margin-bottom:4px;">Est. Monthly Payment</div>
                <div id="mc-monthly" style="font-size:30px;font-weight:800;"></div>
                <div style="height:1px;background:rgba(255,255,255,0.15);margin:12px 0;"></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;opacity:0.7;">
                    <div><div style="font-weight:700;" id="mc-principal"></div><div>Loan Amount</div></div>
                    <div><div style="font-weight:700;" id="mc-totalint"></div><div>Total Interest</div></div>
                    <div><div style="font-weight:700;" id="mc-total"></div><div>Total Cost</div></div>
                </div>
                <p style="font-size:10px;opacity:0.45;margin:10px 0 0;">*Estimate only. Excludes taxes, insurance &amp; CMHC.</p>
            </div>

        </div>
    </div>

    <!-- ══ Featured Properties ════════════════════════════════════════════ -->
    <div class="sidebar-widgets">
        <h4>Featured Properties</h4>
        <div class="sidebar_featured_property">

            <?php if (!empty($featured)): ?>
                <?php foreach ($featured as $fp): ?>
                <div class="sides_list_property">
                    <div class="sides_list_property_thumb">
                        <a href="single-property-1.php?id=<?= $fp['id'] ?>">
                            <img src="<?= htmlspecialchars($fp['img1']) ?>"
                                 class="img-fluid"
                                 style="width:80px;height:65px;object-fit:cover;border-radius:6px;"
                                 alt="<?= htmlspecialchars($fp['address']) ?>"
                                 loading="lazy">
                        </a>
                    </div>
                    <div class="sides_list_property_detail">
                        <h4><a href="single-property-1.php?id=<?= $fp['id'] ?>">
                            <?= htmlspecialchars(mb_strimwidth($fp['address'], 0, 45, '...')) ?>
                        </a></h4>
                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($fp['neighborhood']) ?></span>
                        <div class="lists_property_price">
                            <div class="lists_property_types">
                                <div class="property_types_vlix sale">Pre-Sale</div>
                            </div>
                            <div class="lists_property_price_value">
                                <h4>T.B.A.</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div style="background:#f8f9fb;border:2px dashed #d0d8e8;border-radius:8px;padding:24px 16px;text-align:center;color:#aab;">
                    <i class="fas fa-building" style="font-size:28px;opacity:0.3;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:12px;margin:0;color:#889;line-height:1.5;">
                        Featured listings will appear here once properties are upgraded to paid.
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Mortgage Calculator JS -->
<script>
function calcMortgage() {
    var price = parseFloat(document.getElementById('mc-price').value);
    var down  = parseFloat(document.getElementById('mc-down').value)  || 0;
    var years = parseInt(document.getElementById('mc-term').value);
    var rate  = parseFloat(document.getElementById('mc-rate').value);
    var err   = document.getElementById('mc-error');
    var res   = document.getElementById('mc-result');

    err.style.display = 'none';
    res.style.display = 'none';

    if (isNaN(price) || price <= 0)  { err.textContent = 'Please enter a valid purchase price.';  err.style.display='block'; return; }
    if (down >= price)               { err.textContent = 'Down payment must be less than price.'; err.style.display='block'; return; }
    if (isNaN(rate)  || rate  <= 0)  { err.textContent = 'Please enter a valid interest rate.';   err.style.display='block'; return; }

    // Canadian mortgage compounding (semi-annual)
    var principal       = price - down;
    var effectiveAnnual = Math.pow(1 + (rate / 100) / 2, 2) - 1;
    var monthlyRate     = Math.pow(1 + effectiveAnnual, 1/12) - 1;
    var n               = years * 12;
    var monthly         = principal * (monthlyRate * Math.pow(1 + monthlyRate, n))
                          / (Math.pow(1 + monthlyRate, n) - 1);
    var totalPaid       = monthly * n;
    var totalInterest   = totalPaid - principal;

    function fmt(v) { return '$' + Math.round(v).toLocaleString('en-CA'); }

    document.getElementById('mc-monthly').textContent   = fmt(monthly) + '/mo';
    document.getElementById('mc-principal').textContent = fmt(principal);
    document.getElementById('mc-totalint').textContent  = fmt(totalInterest);
    document.getElementById('mc-total').textContent     = fmt(totalPaid);
    res.style.display = 'block';
}

// Enter key triggers calculate
['mc-price','mc-down','mc-rate'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') calcMortgage();
    });
});
</script>