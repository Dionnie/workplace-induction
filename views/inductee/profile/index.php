<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $profile
 * @var array<string, string> $errors
 */
$pageTitle = 'My Profile';
$currentPage = 'profile';
require __DIR__ . '/../../partials/inductee-header.php';
?>

<div class="page-narrow">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Profile</h2>
            <p class="text-muted small mb-3">Your name and workplace details.</p>

            <form method="post" action="/inductee/profile/index.php" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                               id="first_name" name="first_name" value="<?= old('first_name', (string) $profile['first_name']) ?>" required autofocus>
                        <?php if ($error = error_for($errors, 'first_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                               id="last_name" name="last_name" value="<?= old('last_name', (string) $profile['last_name']) ?>" required>
                        <?php if ($error = error_for($errors, 'last_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="job_position" class="form-label">Job Position <span class="text-muted small">(optional)</span></label>
                        <input type="text" class="form-control <?= error_for($errors, 'job_position') ? 'is-invalid' : '' ?>"
                               id="job_position" name="job_position" value="<?= old('job_position', (string) ($profile['job_position'] ?? '')) ?>">
                        <?php if ($error = error_for($errors, 'job_position')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="company" class="form-label">Company <span class="text-muted small">(optional)</span></label>
                        <input type="text" class="form-control <?= error_for($errors, 'company') ? 'is-invalid' : '' ?>"
                               id="company" name="company" value="<?= old('company', (string) ($profile['company'] ?? '')) ?>">
                        <?php if ($error = error_for($errors, 'company')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="employment_type" class="form-label">Employment Type <span class="text-muted small">(optional)</span></label>
                        <select class="form-select <?= error_for($errors, 'employment_type') ? 'is-invalid' : '' ?>"
                                id="employment_type" name="employment_type">
                            <option value="">Select&hellip;</option>
                            <?php foreach (['Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other'] as $type): ?>
                                <option value="<?= e($type) ?>" <?= old('employment_type', (string) ($profile['employment_type'] ?? '')) === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($error = error_for($errors, 'employment_type')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="contact_number" class="form-label">Contact Number <span class="text-muted small">(optional)</span></label>
                        <input type="tel" class="form-control <?= error_for($errors, 'contact_number') ? 'is-invalid' : '' ?>"
                               id="contact_number" name="contact_number" value="<?= old('contact_number', (string) ($profile['contact_number'] ?? '')) ?>">
                        <?php if ($error = error_for($errors, 'contact_number')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="small text-uppercase text-muted fw-semibold mt-4 mb-3">Emergency Contact</h3>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="emergency_contact_name" class="form-label">Name <span class="text-muted small">(optional)</span></label>
                        <input type="text" class="form-control <?= error_for($errors, 'emergency_contact_name') ? 'is-invalid' : '' ?>"
                               id="emergency_contact_name" name="emergency_contact_name"
                               value="<?= old('emergency_contact_name', (string) ($profile['emergency_contact_name'] ?? '')) ?>">
                        <?php if ($error = error_for($errors, 'emergency_contact_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="emergency_contact_phone" class="form-label">Phone Number <span class="text-muted small">(optional)</span></label>
                        <input type="tel" class="form-control <?= error_for($errors, 'emergency_contact_phone') ? 'is-invalid' : '' ?>"
                               id="emergency_contact_phone" name="emergency_contact_phone"
                               value="<?= old('emergency_contact_phone', (string) ($profile['emergency_contact_phone'] ?? '')) ?>">
                        <?php if ($error = error_for($errors, 'emergency_contact_phone')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
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
