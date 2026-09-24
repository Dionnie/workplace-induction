<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $records */
$pageTitle = 'Compliance';
$currentPage = 'compliance';
require __DIR__ . '/../../partials/inductee-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Compliance</h1>
        <p class="page-subtitle">Your current and previous compliance records.</p>
    </div>
</div>

<div class="card shadow-sm card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
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
                        <td colspan="6" class="text-center text-muted py-4">No compliance records yet.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= e($record['induction_title']) ?></td>
                        <td><code><?= e($record['certificate_number']) ?></code></td>
                        <td><?= e($record['issue_date']) ?></td>
                        <td><?= e($record['expiry_date']) ?></td>
                        <td><?= status_badge((string) $record['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/inductee/certificates/show.php?id=<?= (int) $record['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
