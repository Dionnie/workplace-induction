<?php
/** @var array<string, string> $errors */
$pageTitle = 'Register';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="fs-4 fw-semibold mb-3">Register</h1>

        <?php if ($error = error_for($errors, 'form')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/register.php" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                       id="first_name" name="first_name" value="<?= old('first_name') ?>" required autofocus>
                <?php if ($error = error_for($errors, 'first_name')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                       id="last_name" name="last_name" value="<?= old('last_name') ?>" required>
                <?php if ($error = error_for($errors, 'last_name')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="company" class="form-label">Company</label>
                <input type="text" class="form-control <?= error_for($errors, 'company') ? 'is-invalid' : '' ?>"
                       id="company" name="company" value="<?= old('company') ?>" required>
                <?php if ($error = error_for($errors, 'company')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="employment_type" class="form-label">Employment Type</label>
                <select class="form-select <?= error_for($errors, 'employment_type') ? 'is-invalid' : '' ?>"
                        id="employment_type" name="employment_type" required>
                    <option value="">Select&hellip;</option>
                    <?php foreach (['Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= old('employment_type') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error = error_for($errors, 'employment_type')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?= error_for($errors, 'email') ? 'is-invalid' : '' ?>"
                       id="email" name="email" value="<?= old('email') ?>" required>
                <?php if ($error = error_for($errors, 'email')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                       id="password" name="password" required>
                <?php if ($error = error_for($errors, 'password')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php else: ?>
                    <div class="form-text">At least 8 characters.</div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control <?= error_for($errors, 'password_confirmation') ? 'is-invalid' : '' ?>"
                       id="password_confirmation" name="password_confirmation" required>
                <?php if ($error = error_for($errors, 'password_confirmation')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Already have an account? <a href="/login.php">Log In</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
