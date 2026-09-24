<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $record
 * @var array<string, mixed> $authUser
 */
$pageTitle = 'Certificate';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/inductee-header.php';

$verifyUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
    . '/certificate.php?token=' . $record['verification_token'];
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb" class="d-print-none">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/inductee/compliance/index.php">Compliance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Certificate</li>
                </ol>
            </nav>
            <h1 class="page-title">Certificate</h1>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm d-print-none" onclick="window.print()">
            <i class="bi bi-printer me-1" aria-hidden="true"></i>Print
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <h2 class="fs-5 mb-0"><?= e($record['induction_title']) ?></h2>
                <?= status_badge((string) $record['status']) ?>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-5 text-muted fw-normal">Certificate Number</dt>
                <dd class="col-sm-7"><code><?= e($record['certificate_number']) ?></code></dd>

                <dt class="col-sm-5 text-muted fw-normal">Holder</dt>
                <dd class="col-sm-7"><?= e(trim(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? ''))) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Induction Code</dt>
                <dd class="col-sm-7"><?= e($record['induction_code']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Issue Date</dt>
                <dd class="col-sm-7"><?= e($record['issue_date']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Expiry Date</dt>
                <dd class="col-sm-7 mb-0"><?= e($record['expiry_date']) ?></dd>
            </dl>

            <hr>

            <p class="text-muted small mb-1">Anyone can verify this certificate at:</p>
            <p class="small text-break mb-0"><a href="<?= e($verifyUrl) ?>"><?= e($verifyUrl) ?></a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
