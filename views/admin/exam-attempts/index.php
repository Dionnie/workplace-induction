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

$resultBadge = [
    'passed' => 'success',
    'failed' => 'danger',
];
?>

<h1 class="fs-4 fw-semibold mb-1">Exam Attempts</h1>
<p class="text-muted mb-4">View and manage every exam attempt submitted by inductees.</p>

<form method="get" action="/admin/exam-attempts/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email"
               value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="induction_id" class="form-select form-select-sm">
            <option value="">All Inductions</option>
            <?php foreach ($inductions as $induction): ?>
                <option value="<?= (int) $induction['id'] ?>" <?= $inductionId === (int) $induction['id'] ? 'selected' : '' ?>>
                    <?= e($induction['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="exam_id" class="form-select form-select-sm">
            <option value="">All Exams</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= (int) $exam['id'] ?>" <?= $examId === (int) $exam['id'] ? 'selected' : '' ?>>
                    <?= e($exam['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="result" class="form-select form-select-sm">
            <option value="">All Results</option>
            <option value="passed" <?= $result === 'passed' ? 'selected' : '' ?>>Passed</option>
            <option value="failed" <?= $result === 'failed' ? 'selected' : '' ?>>Failed</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>Inductee</th>
                <th>Induction</th>
                <th>Exam</th>
                <th>Score</th>
                <th>Result</th>
                <th>Attempted</th>
                <th></th>
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
                    <td>
                        <span class="badge text-bg-<?= $resultBadge[$attempt['result']] ?? 'secondary' ?>">
                            <?= e(ucfirst($attempt['result'])) ?>
                        </span>
                    </td>
                    <td><?= e(date('Y-m-d H:i', strtotime((string) $attempt['created_at']))) ?></td>
                    <td class="text-end">
                        <form method="post" action="/admin/exam-attempts/delete.php" class="d-inline"
                              onsubmit="return confirm('Permanently delete this exam attempt? Any compliance record referencing it will keep its record but lose the reference. This cannot be undone.');">
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

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
