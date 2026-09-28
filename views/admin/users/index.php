<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $users
 * @var string $search
 * @var string $userType
 * @var string $sort
 * @var string $dir
 */
$pageTitle = 'Users';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';

$sortUrl = function (string $column) use ($sort, $dir, $search, $userType): string {
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
    if ($userType !== '') $params['user_type'] = $userType;

    return '/admin/users/index.php?' . http_build_query($params);
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
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Platform user accounts and permissions.</p>
    </div>
    <a href="/admin/users/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add User
    </a>
</div>

<form method="get" action="/admin/users/index.php" class="row g-2 align-items-center mb-3" role="search">
    <?php if ($sort !== 'created_at'): ?>
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
    <div class="table-responsive table-scrollable">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col" aria-sort="<?= $sort === 'name' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('name')) ?>" class="table-sort-link <?= $sort === 'name' ? 'active' : '' ?>">
                            Name <?= $sortIcon('name') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'email' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('email')) ?>" class="table-sort-link <?= $sort === 'email' ? 'active' : '' ?>">
                            Email <?= $sortIcon('email') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'user_type' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('user_type')) ?>" class="table-sort-link <?= $sort === 'user_type' ? 'active' : '' ?>">
                            Type <?= $sortIcon('user_type') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'status' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('status')) ?>" class="table-sort-link <?= $sort === 'status' ? 'active' : '' ?>">
                            Status <?= $sortIcon('status') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'profile_completed' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('profile_completed')) ?>" class="table-sort-link <?= $sort === 'profile_completed' ? 'active' : '' ?>">
                            Profile <?= $sortIcon('profile_completed') ?>
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
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <?php $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
                        <td>
                            <?php if ($name !== ''): ?>
                                <a href="/admin/users/edit.php?id=<?= (int) $u['id'] ?>" class="text-decoration-none fw-medium">
                                    <?= e($name) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td class="text-nowrap"><?= $u['user_type'] === 'admin' ? 'Administrator' : 'Inductee' ?></td>
                        <td class="text-nowrap"><?= status_badge((string) $u['status']) ?></td>
                        <td class="text-nowrap"><?= status_badge($u['profile_completed'] ? 'complete' : 'incomplete') ?></td>
                        <td class="text-nowrap"><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/users/edit.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-surface-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted small border-top">
        <span>Showing <strong><?= count($users) ?></strong> <?= count($users) === 1 ? 'user' : 'users' ?><?php if ($search !== '' || $userType !== ''): ?> (filtered)<?php endif; ?></span>
        <span>Sorted by <strong><?= e(ucwords(str_replace('_', ' ', $sort === 'created_at' ? 'Created' : ($sort === 'user_type' ? 'Type' : ($sort === 'profile_completed' ? 'Profile' : $sort))))) ?></strong> (<?= $dir === 'asc' ? 'Ascending' : 'Descending' ?>)</span>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
