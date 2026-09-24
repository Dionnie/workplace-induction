<?php
/** @var array<string, string> $errors */
$pageTitle = 'Register';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="page-title mb-1">Register</h1>
        <p class="text-muted small mb-3">After you verify your email, you'll complete your profile before starting your inductions.</p>

        <?php if ($error = error_for($errors, 'form')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/register.php" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control <?= error_for($errors, 'email') ? 'is-invalid' : '' ?>"
                       id="email" name="email" value="<?= old('email') ?>" autocomplete="email" required autofocus>
                <?php if ($error = error_for($errors, 'email')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label required">Password</label>
                <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                       id="password" name="password" autocomplete="new-password" required>
                <?php if ($error = error_for($errors, 'password')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php else: ?>
                    <div class="form-text">At least 8 characters.</div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label required">Confirm Password</label>
                <input type="password" class="form-control <?= error_for($errors, 'password_confirmation') ? 'is-invalid' : '' ?>"
                       id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                <?php if ($error = error_for($errors, 'password_confirmation')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </div>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Already have an account? <a href="/login.php">Log In</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
