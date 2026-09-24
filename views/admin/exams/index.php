<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $exams
 * @var string $search
 * @var string $status
 */
$pageTitle = 'Exams';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <h1 class="page-title">Exams</h1>
    <a href="/admin/exams/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Exam
    </a>
</div>

<form method="get" action="/admin/exams/index.php" class="row g-2 align-items-center mb-3" role="search">
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
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Questions</th>
                    <th>Pass %</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th><span class="visually-hidden">Actions</span></th>
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
                        <td><?= e($exam['title']) ?></td>
                        <td><?= $questionCount ?></td>
                        <td><?= (int) $exam['pass_percentage'] ?>%</td>
                        <td><?= status_badge((string) $exam['status']) ?></td>
                        <td><?= e(date('Y-m-d', strtotime((string) $exam['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
