<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $users
 * @var string $search
 * @var string $userType
 */
$pageTitle = 'Users';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <h1 class="page-title">Users</h1>
    <a href="/admin/users/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add User
    </a>
</div>

<form method="get" action="/admin/users/index.php" class="row g-2 align-items-center mb-3" role="search">
    <div class="col-12 col-sm-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by name or email"
               aria-label="Search" value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="user_type" class="form-select form-select-sm" aria-label="User type">
            <option value="">All Types</option>
            <option value="admin" <?= $userType === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="inductee" <?= $userType === 'inductee' ? 'selected' : '' ?>>Inductee</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $userType !== ''): ?>
            <a href="/admin/users/index.php" class="btn btn-link btn-sm">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= $u['user_type'] === 'admin' ? 'Administrator' : 'Inductee' ?></td>
                        <td><?= status_badge((string) $u['status']) ?></td>
                        <td><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/users/edit.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                    data-id="<?= (int) $u['id'] ?>"
                                    data-name="<?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: $u['email']) ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/admin/users/delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteUserId" value="">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="deleteUserModalTitle">Delete User</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>? This cannot be undone.</p>
                    <div class="alert alert-warning small mb-3">
                        As an administrator, you can permanently delete this user even if they have exam attempts
                        or compliance records. If related data exists, deletion is blocked unless cascade delete
                        is enabled below.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cascade" value="1" id="deleteUserCascade">
                        <label class="form-check-label" for="deleteUserCascade">
                            Also permanently delete this user's exam attempts and compliance records (cascade delete)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteUserModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('deleteUserId').value = button.getAttribute('data-id');
    document.getElementById('deleteUserName').textContent = button.getAttribute('data-name');
});
</script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
