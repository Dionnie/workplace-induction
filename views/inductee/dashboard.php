<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $inductions
 * @var array<string, mixed> $authUser
 */
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/../partials/inductee-header.php';

$stateLabel = [
    'not_started' => 'Not Started',
    'compliant' => 'Compliant',
    'expired' => 'Expired',
    'failed' => 'Not Passed',
];

$actionLabel = [
    'not_started' => 'Start',
    'compliant' => 'View',
    'expired' => 'Renew',
    'failed' => 'Retry',
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Welcome, <?= e($authUser['first_name'] ?? $authUser['email']) ?></h1>
        <p class="page-subtitle">Complete your assigned induction requirements below.</p>
    </div>
</div>

<?php if (empty($authUser['profile_completed'])): ?>
    <!-- Persistent until the profile is complete: inductions stay locked until then. -->
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <i class="bi bi-person-exclamation me-1" aria-hidden="true"></i>
            <strong>Complete your profile</strong> before starting your inductions.
        </div>
        <a href="/inductee/profile/index.php" class="btn btn-primary btn-sm">Complete Profile</a>
    </div>
<?php endif; ?>

<div class="card shadow-sm card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Induction</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th><span class="visually-hidden">Actions</span></th>
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
                    $compliance = $induction['latest_compliance'];
                    ?>
                    <tr>
                        <td><?= e($induction['title']) ?></td>
                        <td><?= status_badge($state, $stateLabel[$state]) ?></td>
                        <td><?= $compliance ? e($compliance['expiry_date']) : '&mdash;' ?></td>
                        <td class="text-end text-nowrap">
                            <!-- Only inductions that still need doing get the primary colour. -->
                            <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>"
                               class="btn btn-sm <?= $state === 'compliant' ? 'btn-outline-secondary' : 'btn-primary' ?>">
                                <?= e($actionLabel[$state]) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/inductee-footer.php'; ?>
