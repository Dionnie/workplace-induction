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
?>

<div class="page-header">
    <h1 class="page-title">Inductions</h1>
    <a href="/admin/inductions/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Induction
    </a>
</div>

<form method="get" action="/admin/inductions/index.php" class="row g-2 align-items-center mb-3" role="search">
    <div class="col-12 col-sm-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by title or code"
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
            <a href="/admin/inductions/index.php" class="btn btn-link btn-sm">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Code</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th><span class="visually-hidden">Actions</span></th>
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
                        <td><?= status_badge((string) $induction['status']) ?></td>
                        <td><?= e(date('Y-m-d', strtotime((string) $induction['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/inductions/edit.php?id=<?= (int) $induction['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
