<?php

declare(strict_types=1);

/** @var array<string, mixed> $record */
$pageTitle = 'Compliance Record';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/admin-header.php';

$holder = trim($record['first_name'] . ' ' . $record['last_name']) ?: $record['email'];
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/compliance/index.php">Compliance</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($record['certificate_number']) ?></li>
                </ol>
            </nav>
            <h1 class="page-title">Compliance Record</h1>
        </div>
        <a href="/certificate.php?token=<?= e($record['verification_token']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>View Certificate<span class="visually-hidden"> (opens in a new tab)</span>
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <h2 class="fs-5 mb-0"><?= e($record['induction_title']) ?></h2>
                <?= status_badge((string) $record['status']) ?>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-5 text-muted fw-normal">Inductee</dt>
                <dd class="col-sm-7">
                    <a href="/admin/users/edit.php?id=<?= (int) $record['user_id'] ?>"><?= e($holder) ?></a>
                    <div class="text-muted small"><?= e($record['email']) ?></div>
                </dd>

                <dt class="col-sm-5 text-muted fw-normal">Certificate Number</dt>
                <dd class="col-sm-7"><code><?= e($record['certificate_number']) ?></code></dd>

                <dt class="col-sm-5 text-muted fw-normal">Induction Code</dt>
                <dd class="col-sm-7"><?= e($record['induction_code']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Issue Date</dt>
                <dd class="col-sm-7"><?= e($record['issue_date']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Expiry Date</dt>
                <dd class="col-sm-7"><?= e($record['expiry_date']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Exam Attempt</dt>
                <dd class="col-sm-7">
                    <?php if ($record['exam_attempt_id'] !== null): ?>
                        <a href="/admin/exam-attempts/show.php?id=<?= (int) $record['exam_attempt_id'] ?>">View exam attempt</a>
                    <?php else: ?>
                        <span class="text-muted">None</span>
                    <?php endif; ?>
                </dd>

                <dt class="col-sm-5 text-muted fw-normal">Renewal Of</dt>
                <dd class="col-sm-7 mb-0">
                    <?php if ($record['renewed_from_id'] !== null): ?>
                        <a href="/admin/compliance/show.php?id=<?= (int) $record['renewed_from_id'] ?>">Previous compliance record</a>
                    <?php else: ?>
                        <span class="text-muted">None</span>
                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>

    <div class="card border-danger-subtle shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 text-danger mb-1">Danger Zone</h2>
            <p class="text-muted small mb-2">These actions can't be undone.</p>
            <div class="list-group list-group-flush">
                <?php if ($record['status'] === 'active'): ?>
                    <div class="list-group-item px-0 py-3 d-flex flex-wrap flex-sm-nowrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold">Revoke this compliance record</div>
                            <div class="text-muted small">
                                The certificate stops being valid straight away and shows as revoked when verified.
                                The record stays in the compliance history.
                            </div>
                        </div>
                        <form method="post" action="/admin/compliance/revoke.php" class="flex-shrink-0"
                              onsubmit="return confirm('Revoke this compliance record? The certificate stops being valid immediately. This cannot be undone.');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm text-nowrap">Revoke Compliance Record</button>
                        </form>
                    </div>
                <?php endif; ?>
                <div class="list-group-item px-0 py-3 d-flex flex-wrap flex-sm-nowrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">Delete this compliance record</div>
                        <div class="text-muted small">
                            Permanently removes the record and its certificate, bypassing the compliance history.
                            Use only to correct a record created by mistake.
                        </div>
                    </div>
                    <form method="post" action="/admin/compliance/delete.php" class="flex-shrink-0"
                          onsubmit="return confirm('Delete this compliance record? It bypasses the compliance history and cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm text-nowrap">Delete Compliance Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
