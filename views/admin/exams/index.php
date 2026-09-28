<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $exams
 * @var string $search
 * @var string $status
 * @var string $sort
 * @var string $dir
 */
$pageTitle = 'Exams';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';

$sortUrl = function (string $column) use ($sort, $dir, $search, $status): string {
    $nextDir = 'asc';
    if ($sort === $column) {
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
    } elseif ($column === 'created_at') {
        $nextDir = 'desc';
    }

    $params = [
        'sort' => $column,
        'dir' => $nextDir,
    ];
    if ($search !== '') $params['search'] = $search;
    if ($status !== '') $params['status'] = $status;

    return '/admin/exams/index.php?' . http_build_query($params);
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
    <h1 class="page-title">Exams</h1>
    <a href="/admin/exams/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Exam
    </a>
</div>

<form method="get" action="/admin/exams/index.php" class="row g-2 align-items-center mb-3" role="search">
    <?php if ($sort !== 'created_at'): ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <?php endif; ?>
    <?php if ($dir !== 'desc'): ?>
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <?php endif; ?>

    <div class="col-12 col-sm-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by title"
               aria-label="Search" value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $status !== ''): ?>
            <a href="/admin/exams/index.php" class="btn btn-link btn-sm">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm card-table">
    <div class="table-responsive table-scrollable">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col" aria-sort="<?= $sort === 'title' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('title')) ?>" class="table-sort-link <?= $sort === 'title' ? 'active' : '' ?>">
                            Title <?= $sortIcon('title') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'questions' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('questions')) ?>" class="table-sort-link <?= $sort === 'questions' ? 'active' : '' ?>">
                            Questions <?= $sortIcon('questions') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'pass_percentage' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('pass_percentage')) ?>" class="table-sort-link <?= $sort === 'pass_percentage' ? 'active' : '' ?>">
                            Pass % <?= $sortIcon('pass_percentage') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'status' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('status')) ?>" class="table-sort-link <?= $sort === 'status' ? 'active' : '' ?>">
                            Status <?= $sortIcon('status') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'created_at' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('created_at')) ?>" class="table-sort-link <?= $sort === 'created_at' ? 'active' : '' ?>">
                            Created <?= $sortIcon('created_at') ?>
                        </a>
                    </th>
                    <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($exams)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No exams found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($exams as $exam): ?>
                    <?php $questionCount = count(json_decode((string) $exam['exam_blocks'], true) ?: []); ?>
                    <tr>
                        <td>
                            <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="text-decoration-none fw-medium">
                                <?= e($exam['title']) ?>
                            </a>
                        </td>
                        <td class="text-nowrap"><?= $questionCount ?></td>
                        <td class="text-nowrap"><?= (int) $exam['pass_percentage'] ?>%</td>
                        <td class="text-nowrap"><?= status_badge((string) $exam['status']) ?></td>
                        <td class="text-nowrap"><?= e(date('Y-m-d', strtotime((string) $exam['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-surface-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted small border-top">
        <span>Showing <strong><?= count($exams) ?></strong> <?= count($exams) === 1 ? 'exam' : 'exams' ?><?php if ($search !== '' || $status !== ''): ?> (filtered)<?php endif; ?></span>
        <span>Sorted by <strong><?= e(ucwords(str_replace('_', ' ', $sort))) ?></strong> (<?= $dir === 'asc' ? 'Ascending' : 'Descending' ?>)</span>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
