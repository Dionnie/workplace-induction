<?php

declare(strict_types=1);

/** @var array<string, mixed> $record */
$pageTitle = 'Compliance Record';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/admin-header.php';

$holder = trim($record['first_name'] . ' ' . $record['last_name']) ?: $record['email'];
$examPercentage = (int) $record['exam_total_score'] > 0 ? round((int) $record['exam_score'] / (int) $record['exam_total_score'] * 100) : 0;
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
            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="inductee" class="form-label">Inductee</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="inductee" value="<?= e($holder) ?>" readonly>
                        <a href="/admin/users/edit.php?id=<?= (int) $record['user_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> user</span></a>
                    </div>
                </div>
                <div class="col-sm">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= e($record['email']) ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-8">
                    <label for="induction" class="form-label">Induction</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="induction" value="<?= e($record['induction_title']) ?>" readonly>
                        <a href="/admin/inductions/edit.php?id=<?= (int) $record['induction_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> induction</span></a>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="induction_code" class="form-label">Induction Code</label>
                    <input type="text" class="form-control" id="induction_code" value="<?= e($record['induction_code']) ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="certificate_number" class="form-label">Certificate Number</label>
                    <input type="text" class="form-control" id="certificate_number" value="<?= e($record['certificate_number']) ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" class="form-control" id="status" value="<?= e(ucfirst((string) $record['status'])) ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="issue_date" class="form-label">Issue Date</label>
                    <input type="text" class="form-control" id="issue_date" value="<?= e($record['issue_date']) ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="text" class="form-control" id="expiry_date" value="<?= e($record['expiry_date']) ?>" readonly>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm">
                    <label for="exam_attempt" class="form-label">Exam Attempt</label>
                    <?php if ($record['exam_attempt_id'] !== null): ?>
                        <div class="input-group">
                            <input type="text" class="form-control" id="exam_attempt" value="<?= (int) $record['exam_score'] ?> / <?= (int) $record['exam_total_score'] ?> (<?= (int) $examPercentage ?>%)" readonly>
                            <a href="/admin/exam-attempts/show.php?id=<?= (int) $record['exam_attempt_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> exam attempt</span></a>
                        </div>
                    <?php else: ?>
                        <input type="text" class="form-control" id="exam_attempt" placeholder="None" readonly>
                    <?php endif; ?>
                </div>
                <div class="col-sm">
                    <label for="renewed_from" class="form-label">Renewal Of</label>
                    <?php if ($record['renewed_from_id'] !== null): ?>
                        <div class="input-group">
                            <input type="text" class="form-control" id="renewed_from" value="<?= e($record['renewed_from_certificate_number']) ?>" readonly>
                            <a href="/admin/compliance/show.php?id=<?= (int) $record['renewed_from_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> previous compliance record</span></a>
                        </div>
                    <?php else: ?>
                        <input type="text" class="form-control" id="renewed_from" placeholder="None" readonly>
                    <?php endif; ?>
                </div>
            </div>
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
