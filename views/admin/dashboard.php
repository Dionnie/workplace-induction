<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $authUser
 * @var array<string, mixed> $metrics
 */
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/../partials/admin-header.php';
?>

<h1 class="fs-4 fw-semibold mb-1">Welcome, <?= e($authUser['first_name'] ?? $authUser['email']) ?></h1>
<p class="text-muted mb-4">Here's what you can do.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-brand"><?= (int) $metrics['total_inductees'] ?></div>
                <div class="text-muted small">Inductees</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-brand"><?= (int) $metrics['active_inductions'] ?></div>
                <div class="text-muted small">Active Inductions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="fs-3 fw-semibold text-warning"><?= (int) $metrics['expiring_soon'] ?></div>
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
                <h2 class="fs-6 fw-semibold mb-3">Compliance by Induction</h2>
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
                <h2 class="fs-6 fw-semibold mb-3">Expiring Soon</h2>
                <?php if (empty($metrics['expiring_soon_list'])): ?>
                    <p class="text-muted small mb-0">Nothing expiring in the next <?= (int) $metrics['expiring_soon_days'] ?> days.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($metrics['expiring_soon_list'] as $item): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div><?= e(trim($item['first_name'] . ' ' . $item['last_name']) ?: $item['email']) ?></div>
                                    <div class="text-muted small"><?= e($item['induction_title']) ?></div>
                                </div>
                                <span class="badge text-bg-warning"><?= e($item['expiry_date']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h2 class="fs-6 fw-semibold mb-3">Manage</h2>

<div class="row g-3">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Inductions</h2>
                <p class="text-muted small mb-3">Create and manage induction requirements and their content.</p>
                <a href="/admin/inductions/index.php" class="btn btn-primary btn-sm">Manage Inductions</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Exams</h2>
                <p class="text-muted small mb-3">Create and manage exams and their questions.</p>
                <a href="/admin/exams/index.php" class="btn btn-primary btn-sm">Manage Exams</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Exam Attempts</h2>
                <p class="text-muted small mb-3">View and manage every exam attempt submitted by inductees.</p>
                <a href="/admin/exam-attempts/index.php" class="btn btn-primary btn-sm">Manage Exam Attempts</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Compliance</h2>
                <p class="text-muted small mb-3">View inductee compliance records and revoke them where necessary.</p>
                <a href="/admin/compliance/index.php" class="btn btn-primary btn-sm">Manage Compliance</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Users</h2>
                <p class="text-muted small mb-3">Create and manage administrator and inductee accounts.</p>
                <a href="/admin/users/index.php" class="btn btn-primary btn-sm">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Settings</h2>
                <p class="text-muted small mb-3">Configure sender details and completion/expiry notification emails.</p>
                <a href="/admin/settings/index.php" class="btn btn-primary btn-sm">Manage Settings</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
