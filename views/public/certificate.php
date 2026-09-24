<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $record */
$pageTitle = 'Verify Certificate';
require __DIR__ . '/../partials/guest-header.php';
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="page-title mb-3">Certificate Verification</h1>

        <?php if (!$record): ?>
            <div class="alert alert-danger mb-0">This certificate could not be found. Check the link and try again.</div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <h2 class="fs-5 mb-0"><?= e($record['induction_title']) ?></h2>
                <?= status_badge((string) $record['status']) ?>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-5 text-muted fw-normal">Certificate Number</dt>
                <dd class="col-sm-7"><code><?= e($record['certificate_number']) ?></code></dd>

                <dt class="col-sm-5 text-muted fw-normal">Holder</dt>
                <dd class="col-sm-7"><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Induction Code</dt>
                <dd class="col-sm-7"><?= e($record['induction_code']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Issue Date</dt>
                <dd class="col-sm-7"><?= e($record['issue_date']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Expiry Date</dt>
                <dd class="col-sm-7 mb-0"><?= e($record['expiry_date']) ?></dd>
            </dl>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
