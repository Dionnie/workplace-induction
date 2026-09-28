<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $records
 * @var array<int, array<string, mixed>> $inductions
 * @var string $search
 * @var string $status
 * @var int $inductionId
 * @var string $sort
 * @var string $dir
 */
$pageTitle = 'Compliance';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/admin-header.php';

$sortUrl = function (string $column) use ($sort, $dir, $search, $status, $inductionId): string {
    $nextDir = 'asc';
    if ($sort === $column) {
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
    } elseif (in_array($column, ['issue_date', 'expiry_date', 'created_at'], true)) {
        $nextDir = 'desc';
    }

    $params = [
        'sort' => $column,
        'dir' => $nextDir,
    ];
    if ($search !== '') $params['search'] = $search;
    if ($status !== '') $params['status'] = $status;
    if ($inductionId > 0) $params['induction_id'] = $inductionId;

    return '/admin/compliance/index.php?' . http_build_query($params);
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
        <h1 class="page-title">Compliance</h1>
        <p class="page-subtitle">Compliance records of every inductee.</p>
    </div>
</div>

<form method="get" action="/admin/compliance/index.php" class="row g-2 align-items-center mb-3" role="search">
    <?php if ($sort !== 'created_at'): ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <?php endif; ?>
    <?php if ($dir !== 'desc'): ?>
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <?php endif; ?>

    <div class="col-12 col-sm-auto">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search by name, email, or certificate"
               aria-label="Search" value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="induction_id" class="form-select form-select-sm" aria-label="Induction">
            <option value="">All Inductions</option>
            <?php foreach ($inductions as $induction): ?>
                <option value="<?= (int) $induction['id'] ?>" <?= $inductionId === (int) $induction['id'] ? 'selected' : '' ?>>
                    <?= e($induction['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
            <option value="superseded" <?= $status === 'superseded' ? 'selected' : '' ?>>Superseded</option>
            <option value="revoked" <?= $status === 'revoked' ? 'selected' : '' ?>>Revoked</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $status !== '' || $inductionId): ?>
            <a href="/admin/compliance/index.php" class="btn btn-link btn-sm">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm card-table">
    <div class="table-responsive table-scrollable">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col" aria-sort="<?= $sort === 'inductee' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('inductee')) ?>" class="table-sort-link <?= $sort === 'inductee' ? 'active' : '' ?>">
                            Inductee <?= $sortIcon('inductee') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'induction' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('induction')) ?>" class="table-sort-link <?= $sort === 'induction' ? 'active' : '' ?>">
                            Induction <?= $sortIcon('induction') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'certificate_number' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('certificate_number')) ?>" class="table-sort-link <?= $sort === 'certificate_number' ? 'active' : '' ?>">
                            Certificate <?= $sortIcon('certificate_number') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'issue_date' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('issue_date')) ?>" class="table-sort-link <?= $sort === 'issue_date' ? 'active' : '' ?>">
                            Issued <?= $sortIcon('issue_date') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'expiry_date' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('expiry_date')) ?>" class="table-sort-link <?= $sort === 'expiry_date' ? 'active' : '' ?>">
                            Expires <?= $sortIcon('expiry_date') ?>
                        </a>
                    </th>
                    <th scope="col" aria-sort="<?= $sort === 'status' ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' ?>">
                        <a href="<?= e($sortUrl('status')) ?>" class="table-sort-link <?= $sort === 'status' ? 'active' : '' ?>">
                            Status <?= $sortIcon('status') ?>
                        </a>
                    </th>
                    <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No compliance records found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td>
                            <div><?= e(trim($record['first_name'] . ' ' . $record['last_name'])) ?></div>
                            <div class="text-muted small"><?= e($record['email']) ?></div>
                        </td>
                        <td><?= e($record['induction_title']) ?></td>
                        <td class="text-nowrap"><code><?= e($record['certificate_number']) ?></code></td>
                        <td class="text-nowrap"><?= e($record['issue_date']) ?></td>
                        <td class="text-nowrap"><?= e($record['expiry_date']) ?></td>
                        <td class="text-nowrap"><?= status_badge((string) $record['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/compliance/show.php?id=<?= (int) $record['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-surface-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between text-muted small border-top">
        <span>Showing <strong><?= count($records) ?></strong> <?= count($records) === 1 ? 'record' : 'records' ?><?php if ($search !== '' || $status !== '' || $inductionId): ?> (filtered)<?php endif; ?></span>
        <span>Sorted by <strong><?= e(ucwords(str_replace('_', ' ', $sort === 'created_at' ? 'Created' : ($sort === 'certificate_number' ? 'Certificate' : ($sort === 'issue_date' ? 'Issued' : ($sort === 'expiry_date' ? 'Expires' : $sort)))))) ?></strong> (<?= $dir === 'asc' ? 'Ascending' : 'Descending' ?>)</span>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
