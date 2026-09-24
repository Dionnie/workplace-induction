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
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteInductionModal"
                                    data-id="<?= (int) $induction['id'] ?>"
                                    data-name="<?= e($induction['title']) ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="deleteInductionModal" tabindex="-1" aria-labelledby="deleteInductionModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/admin/inductions/delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteInductionId" value="">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="deleteInductionModalTitle">Delete Induction</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteInductionName"></strong>? This cannot be undone.</p>
                    <div class="alert alert-warning small mb-3">
                        As an administrator, you can permanently delete this induction even if it has exam attempts
                        or compliance records. If related data exists, deletion is blocked unless cascade delete is
                        enabled below.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cascade" value="1" id="deleteInductionCascade">
                        <label class="form-check-label" for="deleteInductionCascade">
                            Also permanently delete this induction's exam attempts and compliance records (cascade delete)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Induction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteInductionModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('deleteInductionId').value = button.getAttribute('data-id');
    document.getElementById('deleteInductionName').textContent = button.getAttribute('data-name');
});
</script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
