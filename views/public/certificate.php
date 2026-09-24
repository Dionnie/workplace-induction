<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $record */
$pageTitle = 'Verify Certificate';
$guestContainerClass = 'container-certificate';
require __DIR__ . '/../partials/guest-header.php';

if ($record) {
    // What someone checking the certificate needs to know first.
    [$verdictVariant, $verdictIcon, $verdictTitle, $verdictText] = match ($record['status']) {
        'active' => ['success', 'bi-patch-check', 'Valid certificate.', 'Valid until ' . $record['expiry_date'] . '.'],
        'expired' => ['danger', 'bi-x-octagon', 'Expired.', 'This certificate expired on ' . $record['expiry_date'] . ' and is no longer valid.'],
        'revoked' => ['danger', 'bi-x-octagon', 'Revoked.', 'This certificate has been revoked and is no longer valid.'],
        default => ['info', 'bi-arrow-repeat', 'Replaced.', 'A newer certificate replaced this one when the induction was renewed.'],
    };

    $certificate = [
        'holder' => trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')),
        'induction_title' => (string) $record['induction_title'],
        'certificate_number' => (string) $record['certificate_number'],
        'issue_date' => (string) $record['issue_date'],
        'expiry_date' => (string) $record['expiry_date'],
        'verification_token' => (string) $record['verification_token'],
    ];
}
?>

<h1 class="page-title mb-3">Certificate Verification</h1>

<?php if (!$record): ?>
    <div class="alert alert-danger mb-0">This certificate could not be found. Check the link and try again.</div>
<?php else: ?>
    <div class="alert alert-<?= $verdictVariant ?> d-flex gap-2">
        <i class="bi <?= $verdictIcon ?> flex-shrink-0" aria-hidden="true"></i>
        <div><strong><?= e($verdictTitle) ?></strong> <?= e($verdictText) ?></div>
    </div>

    <?php require __DIR__ . '/../partials/certificate-card.php'; ?>

    <button type="button" class="btn btn-outline-secondary w-100 mt-3" onclick="window.print()">
        <i class="bi bi-printer me-1" aria-hidden="true"></i>Print
    </button>
<?php endif; ?>

<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
