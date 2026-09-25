<?php
/**
 * @var array<string, string> $errors
 * @var ?string $redirectTo
 */
$pageTitle = 'Log In';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="page-title mb-3">Log In</h1>

        <?php require __DIR__ . '/../partials/flash.php'; ?>
        <?php if ($error = error_for($errors, 'form')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login.php" novalidate>
            <?= csrf_field() ?>
            <?php if ($redirectTo !== null): ?>
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>"
                       autocomplete="email" required autofocus>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-baseline">
                    <label for="password" class="form-label required">Password</label>
                    <a href="/forgot-password.php" class="small">Forgot your password?</a>
                </div>
                <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary w-100">Log In</button>
            </div>
        </form>

        <?php if (app_config()['registration_enabled']): ?>
            <p class="text-center text-muted small mt-3 mb-0">
                Need an account? <a href="/register.php">Register</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
