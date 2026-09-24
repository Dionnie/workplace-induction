<?php

declare(strict_types=1);

/** @var array<string, mixed> $attempt */
$pageTitle = 'Exam Attempt';
$currentPage = 'exam-attempts';
require __DIR__ . '/../../partials/admin-header.php';

$inductee = trim($attempt['first_name'] . ' ' . $attempt['last_name']) ?: $attempt['email'];
$percentage = (int) $attempt['total_score'] > 0 ? round((int) $attempt['score'] / (int) $attempt['total_score'] * 100) : 0;
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/exam-attempts/index.php">Exam Attempts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Exam Attempt</li>
                </ol>
            </nav>
            <h1 class="page-title">Exam Attempt</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <h2 class="fs-5 mb-0"><?= e($attempt['exam_title']) ?></h2>
                <?= status_badge((string) $attempt['result']) ?>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-5 text-muted fw-normal">Inductee</dt>
                <dd class="col-sm-7">
                    <a href="/admin/users/edit.php?id=<?= (int) $attempt['user_id'] ?>"><?= e($inductee) ?></a>
                    <div class="text-muted small"><?= e($attempt['email']) ?></div>
                </dd>

                <dt class="col-sm-5 text-muted fw-normal">Induction</dt>
                <dd class="col-sm-7"><?= e($attempt['induction_title']) ?></dd>

                <dt class="col-sm-5 text-muted fw-normal">Score</dt>
                <dd class="col-sm-7"><?= (int) $attempt['score'] ?> / <?= (int) $attempt['total_score'] ?> (<?= (int) $percentage ?>%)</dd>

                <dt class="col-sm-5 text-muted fw-normal">Attempted</dt>
                <dd class="col-sm-7 mb-0"><?= e(date('Y-m-d H:i', strtotime((string) $attempt['created_at']))) ?></dd>
            </dl>
        </div>
    </div>

    <div class="card border-danger-subtle shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 text-danger mb-1">Danger Zone</h2>
            <p class="text-muted small mb-2">These actions can't be undone.</p>
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3 d-flex flex-wrap flex-sm-nowrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">Delete this exam attempt</div>
                        <div class="text-muted small">
                            Permanently removes the attempt. A compliance record issued from it keeps its record
                            but loses the link to this attempt.
                        </div>
                    </div>
                    <form method="post" action="/admin/exam-attempts/delete.php" class="flex-shrink-0"
                          onsubmit="return confirm('Delete this exam attempt? Any compliance record issued from it keeps its record but loses the link. This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $attempt['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm text-nowrap">Delete Exam Attempt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
