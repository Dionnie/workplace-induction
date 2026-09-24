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
                    <th>Profile</th>
                    <th>Created</th>
                    <th><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <?php $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
                        <td><?= $name !== '' ? e($name) : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= $u['user_type'] === 'admin' ? 'Administrator' : 'Inductee' ?></td>
                        <td><?= status_badge((string) $u['status']) ?></td>
                        <td><?= status_badge($u['profile_completed'] ? 'complete' : 'incomplete') ?></td>
                        <td><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/users/edit.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
