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
 * @var string $sort
 * @var string $dir
 */
$pageTitle = 'Exam Attempts';
$currentPage = 'exam-attempts';
require __DIR__ . '/../../partials/admin-header.php';

$sortUrl = function (string $column) use ($sort, $dir, $search, $result, $inductionId, $examId): string {
    $nextDir = 'asc';
    if ($sort === $column) {
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
    } elseif (in_array($column, ['score', 'attempted', 'created_at'], true)) {
        $nextDir = 'desc';
    }

    $params = [
        'sort' => $column,
        'dir' => $nextDir,
    ];
    if ($search !== '') $params['search'] = $search;
    if ($result !== '') $params['result'] = $result;
    if ($inductionId > 0) $params['induction_id'] = $inductionId;
    if ($examId > 0) $params['exam_id'] = $examId;

    return '/admin/exam-attempts/index.php?' . http_build_query($params);
};

$sortIcon = function (string $column) use ($sort, $dir): string {
    if ($sort === $column) {
        $icon = $dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down';
        return '<i class="bi ' . $icon . ' text-primary ms-1" aria-hidden="true"></i>';
    }
    return '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1" aria-hidden="true"></i>';
};
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Exam Attempts</h1>
        <p class="page-subtitle">Every exam attempt submitted by inductees.</p>
    </div>
</div>

<form method="get" action="/admin/exam-attempts/index.php" class="row g-2 align-items-center mb-3" role="search">
    <?php if ($sort !== 'attempted'): ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <?php endif; ?>
    <?php if ($dir !== 'desc'): ?>
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <?php endif; ?>

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
    <div class="table-responsive table-scrollable">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col" aria-sort="<?= $sort === 'inductee' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('inductee')) ?>" class="table-sort-link <?= $sort === 'inductee' ? 'active' : '' ?>">
                            Inductee <?= $sortIcon('inductee') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'induction' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('induction')) ?>" class="table-sort-link <?= $sort === 'induction' ? 'active' : '' ?>">
                            Induction <?= $sortIcon('induction') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'exam' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('exam')) ?>" class="table-sort-link <?= $sort === 'exam' ? 'active' : '' ?>">
                            Exam <?= $sortIcon('exam') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'score' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('score')) ?>" class="table-sort-link <?= $sort === 'score' ? 'active' : '' ?>">
                            Score <?= $sortIcon('score') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'result' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('result')) ?>" class="table-sort-link <?= $sort === 'result' ? 'active' : '' ?>">
                            Result <?= $sortIcon('result') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'attempted' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('attempted')) ?>" class="table-sort-link <?= $sort === 'attempted' ? 'active' : '' ?>">
                            Attempted <?= $sortIcon('attempted') ?>
                        </a>
                    </th>
                    <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
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
                        <td class="text-nowrap"><?= (int) $attempt['score'] ?> / <?= (int) $attempt['total_score'] ?></td>
                        <td class="text-nowrap"><?= status_badge((string) $attempt['result']) ?></td>
                        <td class="text-nowrap"><?= e(date('Y-m-d H:i', strtotime((string) $attempt['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/exam-attempts/show.php?id=<?= (int) $attempt['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-surface-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted small border-top">
        <span>Showing <strong><?= count($attempts) ?></strong> <?= count($attempts) === 1 ? 'attempt' : 'attempts' ?><?php if ($search !== '' || $result !== '' || $inductionId || $examId): ?> (filtered)<?php endif; ?></span>
        <span>Sorted by <strong><?= e(ucwords(str_replace('_', ' ', $sort))) ?></strong> (<?= $dir === 'asc' ? 'Ascending' : 'Descending' ?>)</span>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
