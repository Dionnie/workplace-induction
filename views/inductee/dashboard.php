<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $inductions
 * @var array<string, mixed> $authUser
 */
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/../partials/inductee-header.php';

$stateBadge = [
    'not_started' => ['secondary', 'Not Started'],
    'compliant' => ['success', 'Compliant'],
    'expired' => ['warning', 'Expired'],
    'failed' => ['danger', 'Not Passed'],
];

$actionLabel = [
    'not_started' => 'Start',
    'compliant' => 'View',
    'expired' => 'Renew',
    'failed' => 'Retry',
];
?>

<h1 class="fs-4 fw-semibold mb-1">Welcome, <?= e($authUser['first_name'] ?? $authUser['email']) ?></h1>
<p class="text-muted mb-4">Complete your assigned induction requirements below.</p>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>Induction</th>
                <th>Status</th>
                <th>Expires</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inductions)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No inductions are currently available.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($inductions as $induction): ?>
                <?php
                $state = $induction['compliance_state'];
                [$badgeClass, $badgeLabel] = $stateBadge[$state];
                $compliance = $induction['latest_compliance'];
                ?>
                <tr>
                    <td><?= e($induction['title']) ?></td>
                    <td><span class="badge text-bg-<?= $badgeClass ?>"><?= e($badgeLabel) ?></span></td>
                    <td><?= $compliance ? e($compliance['expiry_date']) : '&mdash;' ?></td>
                    <td class="text-end">
                        <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>" class="btn btn-sm btn-primary">
                            <?= e($actionLabel[$state]) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../partials/inductee-footer.php'; ?>
