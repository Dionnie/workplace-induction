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

$statusBadge = [
    'active' => 'success',
    'expired' => 'warning',
    'superseded' => 'secondary',
    'revoked' => 'danger',
];
?>

<h1 class="fs-4 fw-semibold mb-1">Compliance</h1>
<p class="text-muted mb-4">View and manage inductee compliance records.</p>

<form method="get" action="/admin/compliance/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, or certificate"
               value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="induction_id" class="form-select form-select-sm">
            <option value="">All Inductions</option>
            <?php foreach ($inductions as $induction): ?>
                <option value="<?= (int) $induction['id'] ?>" <?= $inductionId === (int) $induction['id'] ? 'selected' : '' ?>>
                    <?= e($induction['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
            <option value="superseded" <?= $status === 'superseded' ? 'selected' : '' ?>>Superseded</option>
            <option value="revoked" <?= $status === 'revoked' ? 'selected' : '' ?>>Revoked</option>
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
                <th>Inductee</th>
                <th>Induction</th>
                <th>Certificate</th>
                <th>Issued</th>
                <th>Expires</th>
                <th>Status</th>
                <th></th>
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
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$record['status']] ?? 'secondary' ?>">
                            <?= e(ucfirst($record['status'])) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="/certificate.php?token=<?= e($record['verification_token']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">View</a>
                        <?php if ($record['status'] === 'active'): ?>
                            <form method="post" action="/admin/compliance/revoke.php" class="d-inline"
                                  onsubmit="return confirm('Revoke this compliance record? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
