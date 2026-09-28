<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee
 */
$fullName = trim(($inductee['first_name'] ?? '') . ' ' . ($inductee['last_name'] ?? ''));
$pageTitle = $fullName !== '' ? $fullName : (string) $inductee['email'];
$currentPage = 'inductees';

$complianceUrl = '/admin/compliance/index.php?' . http_build_query([
    'search' => (string) $inductee['email'],
    'induction_id' => '',
    'status' => '',
]);

require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/inductees/index.php">Inductees</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($fullName ?: $inductee['email']) ?></li>
                </ol>
            </nav>
            <h1 class="page-title"><?= e($fullName ?: 'Inductee Profile') ?></h1>
            <p class="page-subtitle"><?= e($inductee['email']) ?></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= e($complianceUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Compliance Records<span class="visually-hidden"> (opens in a new tab)</span>
            </a>
            <a href="/admin/users/edit.php?id=<?= (int) $inductee['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit User Account
            </a>
            <?php if ($inductee['status'] === 'active'): ?>
                <form method="post" action="/admin/users/switch.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $inductee['id'] ?>">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Switch Account
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Profile Details</h2>
            <p class="text-muted small mb-3">All columns from inductee_profiles and associated account information.</p>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" value="<?= e($inductee['first_name'] ?? '') ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" value="<?= e($inductee['last_name'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= e($inductee['email']) ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text" class="form-control" id="contact_number" value="<?= e($inductee['contact_number'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="company" class="form-label">Company</label>
                    <input type="text" class="form-control" id="company" value="<?= e($inductee['company'] ?? '') ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="job_position" class="form-label">Job Position</label>
                    <input type="text" class="form-control" id="job_position" value="<?= e($inductee['job_position'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="employment_type" class="form-label">Employment Type</label>
                    <input type="text" class="form-control" id="employment_type" value="<?= e($inductee['employment_type'] ?? '') ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="status" class="form-label">Account Status</label>
                    <input type="text" class="form-control" id="status" value="<?= e(ucfirst((string) $inductee['status'])) ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="profile_completed" class="form-label">Profile Completed</label>
                    <input type="text" class="form-control" id="profile_completed" value="<?= $inductee['profile_completed'] ? 'Yes' : 'No' ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="email_verified" class="form-label">Email Verified</label>
                    <input type="text" class="form-control" id="email_verified"
                           value="<?= $inductee['email_verified_at'] ? 'Verified on ' . e(date('Y-m-d H:i', strtotime((string) $inductee['email_verified_at']))) : 'Not verified' ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm">
                    <label for="profile_created_at" class="form-label">Profile Created</label>
                    <input type="text" class="form-control" id="profile_created_at"
                           value="<?= $inductee['profile_created_at'] ? e(date('Y-m-d H:i:s', strtotime((string) $inductee['profile_created_at']))) : 'Not set' ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="profile_updated_at" class="form-label">Profile Last Updated</label>
                    <input type="text" class="form-control" id="profile_updated_at"
                           value="<?= $inductee['profile_updated_at'] ? e(date('Y-m-d H:i:s', strtotime((string) $inductee['profile_updated_at']))) : 'Not set' ?>" readonly>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm">
                    <label for="user_created_at" class="form-label">User Account Created</label>
                    <input type="text" class="form-control" id="user_created_at"
                           value="<?= e(date('Y-m-d H:i:s', strtotime((string) $inductee['user_created_at']))) ?>" readonly>
                </div>
                <div class="col-sm">
                    <label for="user_id" class="form-label">User ID</label>
                    <input type="text" class="form-control" id="user_id" value="<?= (int) $inductee['id'] ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <a href="/admin/inductees/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Inductees
        </a>
        <a href="<?= e($complianceUrl) ?>" class="btn btn-primary" target="_blank" rel="noopener">
            <i class="bi bi-shield-check me-1" aria-hidden="true"></i>View Compliance Records
            <i class="bi bi-box-arrow-up-right ms-1 small" aria-hidden="true"></i><span class="visually-hidden"> (opens in a new tab)</span>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
