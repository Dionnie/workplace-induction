<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $record
 * @var array<string, mixed> $authUser
 */
$pageTitle = 'Certificate';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/inductee-header.php';

$certificate = [
    'holder' => trim(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? '')),
    'induction_title' => (string) $record['induction_title'],
    'certificate_number' => (string) $record['certificate_number'],
    'issue_date' => (string) $record['issue_date'],
    'expiry_date' => (string) $record['expiry_date'],
    'verification_token' => (string) $record['verification_token'],
];
$verifyUrl = public_url('/certificate.php?token=' . $record['verification_token']);
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/inductee/compliance/index.php">Compliance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Certificate</li>
                </ol>
            </nav>
            <h1 class="page-title">Certificate</h1>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1" aria-hidden="true"></i>Print
        </button>
    </div>

    <?php require __DIR__ . '/../../partials/certificate-card.php'; ?>

    <div class="mt-4">
        <p class="mb-2">Status: <?= status_badge((string) $record['status']) ?></p>
        <p class="text-muted small mb-1">
            Print at 100% scale for a wallet-sized card that fits a standard ID badge holder. Anyone can check that it's
            still valid by scanning its code, or at:
        </p>
        <p class="small text-break mb-0"><a href="<?= e($verifyUrl) ?>"><?= e($verifyUrl) ?></a></p>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
