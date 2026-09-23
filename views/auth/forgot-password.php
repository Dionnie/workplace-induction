<?php
$pageTitle = 'Forgot Password';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="fs-4 fw-semibold mb-3">Forgot Password</h1>

        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php else: ?>
            <p class="text-muted">Enter your email address and we will send you a link to reset your password.</p>

            <form method="post" action="/forgot-password.php" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
        <?php endif; ?>

        <p class="text-center text-muted small mt-3 mb-0">
            <a href="/login.php">Back to Log In</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
