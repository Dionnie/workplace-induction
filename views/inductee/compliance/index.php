<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $records */
$pageTitle = 'Compliance';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/inductee-header.php';

$statusBadge = [
    'active' => 'success',
    'expired' => 'warning',
    'superseded' => 'secondary',
    'revoked' => 'danger',
];
?>

<h1 class="fs-4 fw-semibold mb-1">Compliance</h1>
<p class="text-muted mb-4">Your current and previous compliance records.</p>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white">
        <thead>
            <tr>
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
                    <td colspan="6" class="text-center text-muted py-4">No compliance records yet.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($records as $record): ?>
                <tr>
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
                        <a href="/inductee/certificates/show.php?id=<?= (int) $record['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
