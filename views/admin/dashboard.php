<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $authUser
 * @var array<string, mixed> $metrics
 */
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/../partials/admin-header.php';

// Same sections, groups and order as the sidebar.
$adminMenu = require __DIR__ . '/../partials/admin-menu.php';

$breakdownLabels = ['employment_type' => 'Employment Type', 'company' => 'Company'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Welcome, <?= e($authUser['first_name'] ?? $authUser['email']) ?></h1>
        <p class="page-subtitle">Compliance across all inductions at a glance.</p>
    </div>
</div>

<?php if (empty($authUser['profile_completed'])): ?>
    <!-- Persistent until the profile is complete. -->
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <i class="bi bi-person-exclamation me-1" aria-hidden="true"></i>
            <strong>Complete your profile</strong> by adding your name.
        </div>
        <a href="/admin/profile/index.php" class="btn btn-primary btn-sm">Complete Profile</a>
    </div>
<?php endif; ?>

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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="fs-6 mb-1">Compliance Records Issued</h2>
        <p class="text-muted small mb-3">Quarters with records issued, over the last 4 years. A renewal is a new record.</p>
        <?php if (empty($metrics['issued_per_quarter'])): ?>
            <p class="text-muted small mb-0">No compliance records issued in the last 4 years.</p>
        <?php else: ?>
            <div class="chart-box">
                <canvas id="compliance-issued-chart" aria-hidden="true"></canvas>
            </div>
            <div class="visually-hidden">
                <table>
                    <caption>Compliance records issued per quarter</caption>
                    <thead><tr><th>Quarter</th><th>Issued</th></tr></thead>
                    <tbody>
                        <?php foreach ($metrics['issued_per_quarter'] as $quarter): ?>
                            <tr><td><?= e($quarter['label']) ?></td><td><?= (int) $quarter['count'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="fs-6 mb-1">Expiring by Quarter</h2>
                <p class="text-muted small mb-3">Active records, this quarter and the next 3.</p>
                <?php if (max(array_column($metrics['expiring_per_quarter'], 'count')) === 0): ?>
                    <p class="text-muted small mb-0">No active records expire in the next 4 quarters.</p>
                <?php else: ?>
                    <div class="chart-box">
                        <canvas id="compliance-expiring-chart" aria-hidden="true"></canvas>
                    </div>
                    <div class="visually-hidden">
                        <table>
                            <caption>Active compliance records expiring per quarter</caption>
                            <thead><tr><th>Quarter</th><th>Expiring</th></tr></thead>
                            <tbody>
                                <?php foreach ($metrics['expiring_per_quarter'] as $quarter): ?>
                                    <tr><td><?= e($quarter['label']) ?></td><td><?= (int) $quarter['count'] ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h2 class="fs-6 mb-1">Active Inductees</h2>
                        <p class="text-muted small mb-0">By employment type or company.</p>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Group inductees by">
                        <?php foreach ($breakdownLabels as $field => $label): ?>
                            <button type="button" class="btn btn-outline-secondary <?= $field === 'employment_type' ? 'active' : '' ?>"
                                    aria-pressed="<?= $field === 'employment_type' ? 'true' : 'false' ?>" data-breakdown-switch="<?= e($field) ?>"><?= e($label) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ((int) $metrics['total_inductees'] === 0): ?>
                    <p class="text-muted small mb-0">No active inductees yet.</p>
                <?php else: ?>
                    <?php foreach ($breakdownLabels as $field => $label): ?>
                        <?php $breakdown = $metrics['inductee_breakdowns'][$field]; ?>
                        <div data-breakdown="<?= e($field) ?>" <?= $field === 'employment_type' ? '' : 'hidden' ?>>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th><?= e($label) ?></th>
                                            <th class="text-end">Inductees</th>
                                            <th class="text-end">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($breakdown['rows'])): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">None recorded yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php foreach ($breakdown['rows'] as $i => $row): ?>
                                            <?php $shade = round(1 - 0.75 * $i / max(1, count($breakdown['rows']) - 1), 2); ?>
                                            <tr>
                                                <td><span class="rank-swatch" style="background-color: rgba(var(--color-primary-rgb), <?= $shade ?>)" aria-hidden="true"></span><?= e($row['label']) ?></td>
                                                <td class="text-end"><?= (int) $row['count'] ?></td>
                                                <td class="text-end"><?= (int) $row['share'] ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($breakdown['not_set'] > 0): ?>
                                <p class="text-muted small mt-2 mb-0"><?= e($label) ?> not set for <?= (int) $breakdown['not_set'] ?> active <?= $breakdown['not_set'] === 1 ? 'inductee' : 'inductees' ?>.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
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
    <div class="col-12 col-xl-5">
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

<?php foreach ($adminMenu as $groupLabel => $items): ?>
    <section class="mb-4" aria-labelledby="shortcuts-<?= e(strtolower($groupLabel)) ?>">
        <h2 class="small text-uppercase text-muted fw-semibold mb-2" id="shortcuts-<?= e(strtolower($groupLabel)) ?>"><?= e($groupLabel) ?></h2>
        <div class="row g-3">
            <?php foreach ($items as $item): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="<?= e($item['url']) ?>" class="card shadow-sm h-100 link-card">
                        <div class="card-body">
                            <h3 class="fs-6 mb-1"><i class="bi <?= e($item['icon']) ?> text-primary me-2" aria-hidden="true"></i><?= e($item['label']) ?></h3>
                            <p class="text-muted small mb-0"><?= e($item['description']) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
<script src="/assets/js/dashboard-charts.js"></script>
<script>
    DashboardCharts.init({
        issued: <?= json_encode($metrics['issued_per_quarter'], JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        expiring: <?= json_encode($metrics['expiring_per_quarter'], JSON_HEX_TAG | JSON_HEX_AMP) ?>
    });
</script>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
