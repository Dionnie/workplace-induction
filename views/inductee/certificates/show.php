<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $record
 * @var array<string, mixed> $authUser
 */
$pageTitle = 'Certificate';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/inductee-header.php';

$statusBadge = [
    'active' => 'success',
    'expired' => 'warning',
    'superseded' => 'secondary',
    'revoked' => 'danger',
];
$verifyUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
    . '/certificate.php?token=' . $record['verification_token'];
?>

<h1 class="fs-4 fw-semibold mb-3">Certificate</h1>

<div class="card shadow-sm" style="max-width: 560px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h2 class="fs-5 fw-semibold mb-0"><?= e($record['induction_title']) ?></h2>
            <span class="badge text-bg-<?= $statusBadge[$record['status']] ?? 'secondary' ?>">
                <?= e(ucfirst($record['status'])) ?>
            </span>
        </div>

        <dl class="row mb-0">
            <dt class="col-5 text-muted fw-normal">Certificate Number</dt>
            <dd class="col-7"><code><?= e($record['certificate_number']) ?></code></dd>

            <dt class="col-5 text-muted fw-normal">Holder</dt>
            <dd class="col-7"><?= e(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? '')) ?></dd>

            <dt class="col-5 text-muted fw-normal">Induction Code</dt>
            <dd class="col-7"><?= e($record['induction_code']) ?></dd>

            <dt class="col-5 text-muted fw-normal">Issue Date</dt>
            <dd class="col-7"><?= e($record['issue_date']) ?></dd>

            <dt class="col-5 text-muted fw-normal">Expiry Date</dt>
            <dd class="col-7"><?= e($record['expiry_date']) ?></dd>
        </dl>

        <hr>

        <p class="text-muted small mb-1">Anyone can verify this certificate at:</p>
        <p class="small mb-3"><a href="<?= e($verifyUrl) ?>"><?= e($verifyUrl) ?></a></p>

        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
