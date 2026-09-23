<?php
/** @var bool $verified */
$pageTitle = 'Verify Email';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4 text-center">
        <?php if ($verified): ?>
            <h1 class="fs-4 fw-semibold mb-3">Email Verified</h1>
            <p class="text-muted">Your email address has been verified. You can now log in.</p>
            <a href="/login.php" class="btn btn-primary">Log In</a>
        <?php else: ?>
            <h1 class="fs-4 fw-semibold mb-3">Invalid or Expired Link</h1>
            <p class="text-muted">This verification link is invalid or has expired.</p>
            <a href="/login.php" class="btn btn-outline-secondary">Back to Log In</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
