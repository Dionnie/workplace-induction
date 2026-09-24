<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $records
 * @var array<int, array<string, mixed>> $inductions
 * @var string $search
 * @var string $status
 * @var int $inductionId
 */
$pageTitle = 'Compliance';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Compliance</h1>
        <p class="page-subtitle">Compliance records of every inductee.</p>
    </div>
</div>

<form method="get" action="/admin/compliance/index.php" class="row g-2 align-items-center mb-3" role="search">
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
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Inductee</th>
                    <th>Induction</th>
                    <th>Certificate</th>
                    <th>Issued</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th><span class="visually-hidden">Actions</span></th>
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
                        <td><code><?= e($record['certificate_number']) ?></code></td>
                        <td><?= e($record['issue_date']) ?></td>
                        <td><?= e($record['expiry_date']) ?></td>
                        <td><?= status_badge((string) $record['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/admin/compliance/show.php?id=<?= (int) $record['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
