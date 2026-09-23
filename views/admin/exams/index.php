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

$statusBadge = [
    'active' => 'success',
    'inactive' => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Exams</h1>
    <a href="/admin/exams/create.php" class="btn btn-primary btn-sm">Add Exam</a>
</div>

<form method="get" action="/admin/exams/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title"
               value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                <th>Title</th>
                <th>Questions</th>
                <th>Pass %</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
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
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$exam['status']] ?? 'secondary' ?>">
                            <?= e(ucfirst($exam['status'])) ?>
                        </span>
                    </td>
                    <td><?= e(date('Y-m-d', strtotime((string) $exam['created_at']))) ?></td>
                    <td class="text-end">
                        <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
