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

$statusBadge = [
    'active' => 'success',
    'inactive' => 'secondary',
    'suspended' => 'danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Users</h1>
    <a href="/admin/users/create.php" class="btn btn-primary btn-sm">Add User</a>
</div>

<form method="get" action="/admin/users/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email"
               value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="user_type" class="form-select form-select-sm">
            <option value="">All Types</option>
            <option value="admin" <?= $userType === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="inductee" <?= $userType === 'inductee' ? 'selected' : '' ?>>Inductee</option>
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
                <th>Name</th>
                <th>Email</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
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
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$u['status']] ?? 'secondary' ?>">
                            <?= e(ucfirst($u['status'])) ?>
                        </span>
                    </td>
                    <td><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
                    <td class="text-end">
                        <a href="/admin/users/edit.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
