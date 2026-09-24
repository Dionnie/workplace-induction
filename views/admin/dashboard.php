<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $authUser
 * @var array<string, mixed> $metrics
 */
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/../partials/admin-header.php';

$shortcuts = [
    ['Inductions', 'bi-journal-check', '/admin/inductions/index.php', 'Create and edit inductions and their content blocks.'],
    ['Exams', 'bi-ui-checks', '/admin/exams/index.php', 'Create and edit exams and their questions.'],
    ['Exam Attempts', 'bi-clipboard-data', '/admin/exam-attempts/index.php', 'Review every exam attempt submitted by inductees.'],
    ['Compliance', 'bi-patch-check', '/admin/compliance/index.php', 'View compliance records and revoke them where necessary.'],
    ['Users', 'bi-people', '/admin/users/index.php', 'Create and edit administrator and inductee accounts.'],
    ['Settings', 'bi-gear', '/admin/settings/index.php', 'Company details, appearance, email senders and notifications.'],
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Welcome, <?= e($authUser['first_name'] ?? $authUser['email']) ?></h1>
        <p class="page-subtitle">Compliance across all inductions at a glance.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-primary"><?= (int) $metrics['total_inductees'] ?></div>
                <div class="text-muted small">Inductees</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-primary"><?= (int) $metrics['active_inductions'] ?></div>
                <div class="text-muted small">Active Inductions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-warning-emphasis"><?= (int) $metrics['expiring_soon'] ?></div>
                <div class="text-muted small">Expiring in <?= (int) $metrics['expiring_soon_days'] ?> Days</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-danger"><?= (int) $metrics['expired'] ?></div>
                <div class="text-muted small">Expired</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 mb-3">Compliance by Induction</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Induction</th>
                                <th class="text-end">Compliant</th>
                                <th class="text-end">Expired</th>
                                <th class="text-end">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($metrics['induction_breakdown'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No active inductions yet.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($metrics['induction_breakdown'] as $row): ?>
                                <tr>
                                    <td><?= e($row['title']) ?></td>
                                    <td class="text-end"><?= (int) $row['compliant'] ?></td>
                                    <td class="text-end"><?= (int) $row['expired'] ?></td>
                                    <td class="text-end"><?= $row['rate'] !== null ? (int) $row['rate'] . '%' : '&mdash;' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 mb-3">Expiring Soon</h2>
                <?php if (empty($metrics['expiring_soon_list'])): ?>
                    <p class="text-muted small mb-0">Nothing expiring in the next <?= (int) $metrics['expiring_soon_days'] ?> days.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($metrics['expiring_soon_list'] as $item): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <div><?= e(trim($item['first_name'] . ' ' . $item['last_name']) ?: $item['email']) ?></div>
                                    <div class="text-muted small"><?= e($item['induction_title']) ?></div>
                                </div>
                                <?= status_badge('expiring', $item['expiry_date']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h2 class="fs-6 mb-3">Administration</h2>

<div class="row g-3">
    <?php foreach ($shortcuts as [$label, $icon, $url, $description]): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= e($url) ?>" class="card shadow-sm h-100 link-card">
                <div class="card-body">
                    <h3 class="fs-6 mb-1"><i class="bi <?= e($icon) ?> text-primary me-2" aria-hidden="true"></i><?= e($label) ?></h3>
                    <p class="text-muted small mb-0"><?= e($description) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
