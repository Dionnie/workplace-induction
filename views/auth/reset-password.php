<?php
/** @var array<string, string> $errors */
/** @var string $token */
$pageTitle = 'Set Your Password';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="page-title mb-3">Set Your Password</h1>

        <?php if ($error = error_for($errors, 'form')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
            <p class="text-center text-muted small mb-0">
                <a href="/forgot-password.php">Request a new link</a>
            </p>
        <?php else: ?>
            <form method="post" action="/reset-password.php" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="mb-3">
                    <label for="password" class="form-label required">New Password</label>
                    <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                           id="password" name="password" autocomplete="new-password" required autofocus>
                    <?php if ($error = error_for($errors, 'password')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php else: ?>
                        <div class="form-text">At least 8 characters.</div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label required">Confirm New Password</label>
                    <input type="password" class="form-control <?= error_for($errors, 'password_confirmation') ? 'is-invalid' : '' ?>"
                           id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                    <?php if ($error = error_for($errors, 'password_confirmation')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary w-100">Set Password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
