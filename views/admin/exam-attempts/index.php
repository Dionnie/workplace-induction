<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $attempts
 * @var array<int, array<string, mixed>> $inductions
 * @var array<int, array<string, mixed>> $exams
 * @var string $search
 * @var string $result
 * @var int $inductionId
 * @var int $examId
 */
$pageTitle = 'Exam Attempts';
$currentPage = 'exam-attempts';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Exam Attempts</h1>
        <p class="page-subtitle">Every exam attempt submitted by inductees.</p>
    </div>
</div>

<form method="get" action="/admin/exam-attempts/index.php" class="row g-2 align-items-center mb-3" role="search">
    <div class="col-12 col-sm-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by name or email"
               aria-label="Search" value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="induction_id" class="form-select form-select-sm" aria-label="Induction">
            <option value="">All Inductions</option>
            <?php foreach ($inductions as $induction): ?>
                <option value="<?= (int) $induction['id'] ?>" <?= $inductionId === (int) $induction['id'] ? 'selected' : '' ?>>
                    <?= e($induction['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="exam_id" class="form-select form-select-sm" aria-label="Exam">
            <option value="">All Exams</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= (int) $exam['id'] ?>" <?= $examId === (int) $exam['id'] ? 'selected' : '' ?>>
                    <?= e($exam['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="result" class="form-select form-select-sm" aria-label="Result">
            <option value="">All Results</option>
            <option value="passed" <?= $result === 'passed' ? 'selected' : '' ?>>Passed</option>
            <option value="failed" <?= $result === 'failed' ? 'selected' : '' ?>>Failed</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $result !== '' || $inductionId || $examId): ?>
            <a href="/admin/exam-attempts/index.php" class="btn btn-link btn-sm">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Inductee</th>
                    <th>Induction</th>
                    <th>Exam</th>
                    <th>Score</th>
                    <th>Result</th>
                    <th>Attempted</th>
                    <th><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attempts)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No exam attempts found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td>
                            <div><?= e(trim($attempt['first_name'] . ' ' . $attempt['last_name'])) ?></div>
                            <div class="text-muted small"><?= e($attempt['email']) ?></div>
                        </td>
                        <td><?= e($attempt['induction_title']) ?></td>
                        <td><?= e($attempt['exam_title']) ?></td>
                        <td><?= (int) $attempt['score'] ?> / <?= (int) $attempt['total_score'] ?></td>
                        <td><?= status_badge((string) $attempt['result']) ?></td>
                        <td><?= e(date('Y-m-d H:i', strtotime((string) $attempt['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <form method="post" action="/admin/exam-attempts/delete.php" class="d-inline"
                                  onsubmit="return confirm('Delete this exam attempt? Any compliance record referencing it keeps its record but loses the reference. This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $attempt['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
