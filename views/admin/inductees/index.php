<?php

declare(strict_types=1);

use App\Inductee\InducteeProfileService;

/**
 * @var array<int, array<string, mixed>> $inductees
 * @var string $search
 * @var string $employmentType
 * @var string $status
 * @var string $profileStatus
 * @var string $sort
 * @var string $dir
 */
$pageTitle = 'Inductees';
$currentPage = 'inductees';
require __DIR__ . '/../../partials/admin-header.php';

$sortUrl = function (string $column) use ($sort, $dir, $search, $employmentType, $status, $profileStatus): string {
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
    if ($employmentType !== '') $params['employment_type'] = $employmentType;
    if ($status !== '') $params['status'] = $status;
    if ($profileStatus !== '') $params['profile_completed'] = $profileStatus;

    return '/admin/inductees/index.php?' . http_build_query($params);
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
        <h1 class="page-title">Inductees</h1>
        <p class="page-subtitle">Inductee profiles and account details.</p>
    </div>
    <a href="/admin/users/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Inductee
    </a>
</div>

<form method="get" action="/admin/inductees/index.php" class="row g-2 align-items-center mb-3" role="search">
    <?php if ($sort !== 'created_at'): ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <?php endif; ?>
    <?php if ($dir !== 'desc'): ?>
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <?php endif; ?>

    <div class="col-12 col-md-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by name, email, company, job..."
               aria-label="Search" value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="employment_type" class="form-select form-select-sm" aria-label="Employment type">
            <option value="">All Employment Types</option>
            <?php foreach (InducteeProfileService::EMPLOYMENT_TYPES as $type): ?>
                <option value="<?= e($type) ?>" <?= $employmentType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="profile_completed" class="form-select form-select-sm" aria-label="Profile status">
            <option value="">All Profiles</option>
            <option value="complete" <?= $profileStatus === 'complete' ? 'selected' : '' ?>>Complete</option>
            <option value="incomplete" <?= $profileStatus === 'incomplete' ? 'selected' : '' ?>>Incomplete</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $employmentType !== '' || $status !== '' || $profileStatus !== ''): ?>
            <a href="/admin/inductees/index.php" class="btn btn-link btn-sm">Clear</a>
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
                    <th scope="col" aria-sort="<?= $sort === 'contact_number' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('contact_number')) ?>" class="table-sort-link <?= $sort === 'contact_number' ? 'active' : '' ?>">
                            Contact Number <?= $sortIcon('contact_number') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'job_position' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('job_position')) ?>" class="table-sort-link <?= $sort === 'job_position' ? 'active' : '' ?>">
                            Job Position <?= $sortIcon('job_position') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'company' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('company')) ?>" class="table-sort-link <?= $sort === 'company' ? 'active' : '' ?>">
                            Company <?= $sortIcon('company') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'employment_type' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('employment_type')) ?>" class="table-sort-link <?= $sort === 'employment_type' ? 'active' : '' ?>">
                            Employment Type <?= $sortIcon('employment_type') ?>
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
                <?php if (empty($inductees)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No inductees found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($inductees as $inductee): ?>
                    <tr>
                        <?php $name = trim(($inductee['first_name'] ?? '') . ' ' . ($inductee['last_name'] ?? '')); ?>
                        <td>
                            <?php if ($name !== ''): ?>
                                <a href="/admin/inductees/show.php?id=<?= (int) $inductee['id'] ?>" class="text-decoration-none fw-medium">
                                    <?= e($name) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($inductee['email']) ?></td>
                        <td class="text-nowrap"><?= !empty($inductee['contact_number']) ? e($inductee['contact_number']) : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= !empty($inductee['job_position']) ? e($inductee['job_position']) : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= !empty($inductee['company']) ? e($inductee['company']) : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td class="text-nowrap">
                            <?php if (!empty($inductee['employment_type'])): ?>
                                <span class="badge text-bg-light border"><?= e($inductee['employment_type']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap"><?= status_badge((string) $inductee['status']) ?></td>
                        <td class="text-nowrap"><?= status_badge($inductee['profile_completed'] ? 'complete' : 'incomplete') ?></td>
                        <td class="text-nowrap">
                            <?php
                            $created = $inductee['profile_created_at'] ?? $inductee['user_created_at'] ?? null;
                            echo $created ? e(date('Y-m-d', strtotime((string) $created))) : '<span class="text-muted">&mdash;</span>';
                            ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/inductees/show.php?id=<?= (int) $inductee['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                            <a href="/admin/users/edit.php?id=<?= (int) $inductee['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-surface-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted small border-top">
        <span>Showing <strong><?= count($inductees) ?></strong> <?= count($inductees) === 1 ? 'inductee' : 'inductees' ?><?php if ($search !== '' || $employmentType !== '' || $status !== '' || $profileStatus !== ''): ?> (filtered)<?php endif; ?></span>
        <span>Sorted by <strong><?= e(ucwords(str_replace('_', ' ', $sort === 'created_at' ? 'Created' : ($sort === 'profile_completed' ? 'Profile' : $sort)))) ?></strong> (<?= $dir === 'asc' ? 'Ascending' : 'Descending' ?>)</span>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
