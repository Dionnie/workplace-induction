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
            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="inductee" class="form-label">Inductee</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="inductee" value="<?= e($inductee) ?>" readonly>
                        <a href="/admin/users/edit.php?id=<?= (int) $attempt['user_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> user</span></a>
                    </div>
                </div>
                <div class="col-sm">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= e($attempt['email']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label for="induction" class="form-label">Induction</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="induction" value="<?= e($attempt['induction_title']) ?>" readonly>
                    <a href="/admin/inductions/edit.php?id=<?= (int) $attempt['induction_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> induction</span></a>
                </div>
            </div>

            <div class="mb-3">
                <label for="exam" class="form-label">Exam</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="exam" value="<?= e($attempt['exam_title']) ?>" readonly>
                    <a href="/admin/exams/edit.php?id=<?= (int) $attempt['exam_id'] ?>" class="btn btn-outline-secondary">View<span class="visually-hidden"> exam</span></a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm">
                    <label for="result" class="form-label">Result</label>
                    <input type="text" class="form-control" id="result" value="<?= e(ucfirst((string) $attempt['result'])) ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="score" class="form-label">Score</label>
                    <input type="text" class="form-control" id="score" value="<?= (int) $attempt['score'] ?> / <?= (int) $attempt['total_score'] ?> (<?= (int) $percentage ?>%)" readonly>
                </div>
                <div class="col-sm">
                    <label for="attempted" class="form-label">Attempted</label>
                    <input type="text" class="form-control" id="attempted" value="<?= e(date('Y-m-d H:i', strtotime((string) $attempt['created_at']))) ?>" readonly>
                </div>
            </div>
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
