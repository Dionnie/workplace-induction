<?php
/** @var bool $verified */
$pageTitle = 'Verify Email';
require __DIR__ . '/../partials/guest-header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4 text-center">
        <?php if ($verified): ?>
            <i class="bi bi-check-circle fs-1 text-success d-block mb-2" aria-hidden="true"></i>
            <h1 class="page-title mb-3">Email Verified</h1>
            <p class="text-muted">Your email address has been verified. You can now log in.</p>
            <a href="/login.php" class="btn btn-primary">Log In</a>
        <?php else: ?>
            <i class="bi bi-exclamation-circle fs-1 text-danger d-block mb-2" aria-hidden="true"></i>
            <h1 class="page-title mb-3">Invalid or Expired Link</h1>
            <p class="text-muted">
                This verification link is invalid or has expired. If your email isn't verified yet,
                log in with your email and password and we'll email you a new link.
            </p>
            <a href="/login.php" class="btn btn-primary">Log In</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/guest-footer.php'; ?>
