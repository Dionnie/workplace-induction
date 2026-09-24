<?php
/**
 * A Certificate as a pocket-sized pass: ID card size (CR80, 85.6 x 54 mm),
 * with the company's branding and a QR code to the public verification
 * page. Printing the page prints only this card, at its real size.
 * Used by the public verification page and the inductee's Certificate page.
 * See .cert-card in assets/css/app.css and docs/core/design-system.html#certificate.
 *
 * The live status is deliberately not on the card: a printed card can't
 * change, so the page shows the status and the QR code checks it.
 *
 * @var array{holder: string, induction_title: string, certificate_number: string, issue_date: string, expiry_date: string, verification_token: string} $certificate
 */
$certificateSite = site_settings();
$certificateVerifyUrl = public_url('/certificate.php?token=' . $certificate['verification_token']);
?>
<article class="cert-card" aria-label="Certificate <?= e($certificate['certificate_number']) ?>">
    <header class="cert-card-header">
        <span class="cert-card-brand">
            <?php if (!empty($certificateSite['logo_url'])): ?>
                <img src="<?= e($certificateSite['logo_url']) ?>" alt="" class="cert-card-logo">
            <?php endif; ?>
            <span class="cert-card-company"><?= e($certificateSite['company_name']) ?></span>
        </span>
        <span class="cert-card-kind">Induction<br>Certificate</span>
    </header>

    <div class="cert-card-body">
        <div class="cert-card-main">
            <div class="cert-card-holder"><?= e($certificate['holder']) ?></div>
            <div class="cert-card-induction"><?= e($certificate['induction_title']) ?></div>

            <dl class="cert-card-details">
                <div>
                    <dt>Certificate No.</dt>
                    <dd><?= e($certificate['certificate_number']) ?></dd>
                </div>
                <div>
                    <dt>Issued</dt>
                    <dd><?= e($certificate['issue_date']) ?></dd>
                </div>
                <div>
                    <dt>Expires</dt>
                    <dd><?= e($certificate['expiry_date']) ?></dd>
                </div>
            </dl>
        </div>

        <div class="cert-card-verify">
            <div class="cert-card-qr" data-cert-qr="<?= e($certificateVerifyUrl) ?>" role="img"
                 aria-label="QR code that opens this certificate's verification page"></div>
            <div class="cert-card-verify-label">Scan to verify</div>
        </div>
    </div>
</article>

<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    // Draws the QR code (qrcode-generator). If the library can't load, the
    // card still shows every detail; only the scan-to-verify shortcut is missing.
    document.querySelectorAll('[data-cert-qr]').forEach(function (el) {
        if (!window.qrcode) {
            return;
        }
        var qr = window.qrcode(0, 'M');
        qr.addData(el.dataset.certQr);
        qr.make();
        el.innerHTML = qr.createSvgTag({ cellSize: 2, margin: 4, scalable: true });
    });
</script>
