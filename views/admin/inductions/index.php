<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $inductions
 * @var string $search
 * @var string $status
 */
$pageTitle = 'Inductions';
$currentPage = 'inductions';
require __DIR__ . '/../../partials/admin-header.php';

$statusBadge = [
    'active' => 'success',
    'inactive' => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Inductions</h1>
    <a href="/admin/inductions/create.php" class="btn btn-primary btn-sm">Add Induction</a>
</div>

<form method="get" action="/admin/inductions/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title or code"
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
                <th>Code</th>
                <th>Validity</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inductions)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No inductions found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($inductions as $induction): ?>
                <tr>
                    <td><?= e($induction['title']) ?></td>
                    <td><code><?= e($induction['code']) ?></code></td>
                    <td><?= (int) $induction['validity_months'] ?> months</td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$induction['status']] ?? 'secondary' ?>">
                            <?= e(ucfirst($induction['status'])) ?>
                        </span>
                    </td>
                    <td><?= e(date('Y-m-d', strtotime((string) $induction['created_at']))) ?></td>
                    <td class="text-end">
                        <a href="/admin/inductions/edit.php?id=<?= (int) $induction['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
