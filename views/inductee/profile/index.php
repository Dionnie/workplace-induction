<?php

declare(strict_types=1);

use App\Inductee\InducteeProfileService;

/**
 * @var array<string, mixed> $profile
 * @var array<string, string> $errors
 * @var ?string $redirectTo
 */
$pageTitle = 'My Profile';
$currentPage = 'profile';
require __DIR__ . '/../../partials/inductee-header.php';

$isComplete = !empty($profile['profile_completed']);
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Profile</h1>
            <?php if (!$isComplete): ?>
                <p class="page-subtitle">Complete your profile to start your inductions.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Profile</h2>
            <p class="text-muted small mb-3">Your name appears on your certificates. Your contact details are used on site if needed.</p>

            <form method="post" action="/inductee/profile/index.php" novalidate>
                <?= csrf_field() ?>
                <?php if ($redirectTo !== null): ?>
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <?php endif; ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="first_name" class="form-label required">First Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                               id="first_name" name="first_name" value="<?= old('first_name', (string) $profile['first_name']) ?>"
                               autocomplete="given-name" required autofocus>
                        <?php if ($error = error_for($errors, 'first_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="last_name" class="form-label required">Last Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                               id="last_name" name="last_name" value="<?= old('last_name', (string) $profile['last_name']) ?>"
                               autocomplete="family-name" required>
                        <?php if ($error = error_for($errors, 'last_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="company" class="form-label required">Company</label>
                        <input type="text" class="form-control <?= error_for($errors, 'company') ? 'is-invalid' : '' ?>"
                               id="company" name="company" value="<?= old('company', (string) $profile['company']) ?>"
                               autocomplete="organization" required>
                        <?php if ($error = error_for($errors, 'company')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="job_position" class="form-label">Job Position</label>
                        <input type="text" class="form-control <?= error_for($errors, 'job_position') ? 'is-invalid' : '' ?>"
                               id="job_position" name="job_position" value="<?= old('job_position', (string) $profile['job_position']) ?>"
                               autocomplete="organization-title">
                        <?php if ($error = error_for($errors, 'job_position')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="employment_type" class="form-label required">Employment Type</label>
                        <select class="form-select <?= error_for($errors, 'employment_type') ? 'is-invalid' : '' ?>"
                                id="employment_type" name="employment_type" required>
                            <option value="">Select&hellip;</option>
                            <?php foreach (InducteeProfileService::EMPLOYMENT_TYPES as $type): ?>
                                <option value="<?= e($type) ?>" <?= old('employment_type', (string) $profile['employment_type']) === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($error = error_for($errors, 'employment_type')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="contact_number" class="form-label required">Contact Number</label>
                        <input type="tel" class="form-control <?= error_for($errors, 'contact_number') ? 'is-invalid' : '' ?>"
                               id="contact_number" name="contact_number" value="<?= old('contact_number', (string) $profile['contact_number']) ?>"
                               autocomplete="tel" required>
                        <?php if ($error = error_for($errors, 'contact_number')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= $isComplete ? 'Save Profile' : 'Complete Profile' ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Account</h2>
            <p class="text-muted small mb-3">Your login email.</p>

            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" value="<?= e($profile['email']) ?>" disabled>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
