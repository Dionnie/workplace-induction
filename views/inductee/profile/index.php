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

<h1 class="fs-4 fw-semibold mb-3">My Profile</h1>

<div class="card shadow-sm" style="max-width: 560px;">
    <div class="card-body p-4">
        <form method="post" action="/inductee/profile/index.php" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= e($profile['email']) ?>" disabled>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                           id="first_name" name="first_name" value="<?= old('first_name', (string) $profile['first_name']) ?>" required autofocus>
                    <?php if ($error = error_for($errors, 'first_name')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                           id="last_name" name="last_name" value="<?= old('last_name', (string) $profile['last_name']) ?>" required>
                    <?php if ($error = error_for($errors, 'last_name')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="job_position" class="form-label">Job Position</label>
                <input type="text" class="form-control <?= error_for($errors, 'job_position') ? 'is-invalid' : '' ?>"
                       id="job_position" name="job_position" value="<?= old('job_position', (string) ($profile['job_position'] ?? '')) ?>">
                <?php if ($error = error_for($errors, 'job_position')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="tel" class="form-control <?= error_for($errors, 'contact_number') ? 'is-invalid' : '' ?>"
                       id="contact_number" name="contact_number" value="<?= old('contact_number', (string) ($profile['contact_number'] ?? '')) ?>">
                <?php if ($error = error_for($errors, 'contact_number')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <hr class="my-4">
            <h2 class="fs-6 fw-semibold mb-3">Emergency Contact</h2>

            <div class="mb-3">
                <label for="emergency_contact_name" class="form-label">Name</label>
                <input type="text" class="form-control <?= error_for($errors, 'emergency_contact_name') ? 'is-invalid' : '' ?>"
                       id="emergency_contact_name" name="emergency_contact_name"
                       value="<?= old('emergency_contact_name', (string) ($profile['emergency_contact_name'] ?? '')) ?>">
                <?php if ($error = error_for($errors, 'emergency_contact_name')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="emergency_contact_phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control <?= error_for($errors, 'emergency_contact_phone') ? 'is-invalid' : '' ?>"
                       id="emergency_contact_phone" name="emergency_contact_phone"
                       value="<?= old('emergency_contact_phone', (string) ($profile['emergency_contact_phone'] ?? '')) ?>">
                <?php if ($error = error_for($errors, 'emergency_contact_phone')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
