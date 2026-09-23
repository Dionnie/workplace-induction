<?php
/** @var array<string, string> $errors */
$pageTitle = 'Log In';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="fs-4 fw-semibold mb-3">Log In</h1>

        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error = error_for($errors, 'form')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login.php" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <a href="/forgot-password.php" class="small">Forgot your password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>

        <?php if (app_config()['registration_enabled']): ?>
            <p class="text-center text-muted small mt-3 mb-0">
                Need an account? <a href="/register.php">Register</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
