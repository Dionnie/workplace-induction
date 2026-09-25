<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth;

$config = require __DIR__ . '/config/app.php';

$appName = site_settings()['company_name'];
$appLogo = site_settings()['logo_url'];
$tagline = $config['tagline'];
$description = $config['description'];
$registrationEnabled = $config['registration_enabled'];
$year = date('Y');

$authUser = Auth::user();
$documentTitle = $appName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/views/partials/head.php'; ?>
</head>
<body>

<header class="border-bottom bg-white">
    <div class="container d-flex justify-content-between align-items-center gap-3 py-3">
        <span class="d-inline-flex align-items-center gap-2 fs-5 fw-semibold text-primary">
            <?php if ($appLogo): ?><img src="<?= e($appLogo) ?>" alt="" class="brand-logo"><?php endif; ?>
            <?= e($appName) ?>
        </span>
        <nav class="d-flex gap-2">
            <?php if ($authUser): ?>
                <a href="<?= e(Auth::homeUrl()) ?>" class="btn btn-primary btn-sm">Dashboard</a>
                <a href="/logout.php" class="btn btn-outline-secondary btn-sm">Log Out</a>
            <?php else: ?>
                <a href="/login.php" class="btn btn-outline-secondary btn-sm">Log In</a>
                <?php if ($registrationEnabled): ?>
                    <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <section class="py-5 bg-white border-bottom">
        <div class="container page-narrow text-center">
            <h1><?= e($appName) ?></h1>
            <p class="text-muted mb-2"><?= e($tagline) ?></p>
            <p class="mb-4"><?= e($description) ?></p>
            <?php if (!$authUser): ?>
                <div class="d-flex justify-content-center gap-2">
                    <a href="/login.php" class="btn btn-primary">Log In</a>
                    <?php if ($registrationEnabled): ?>
                        <a href="/register.php" class="btn btn-outline-secondary">Register</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-5">
        <div class="container page-narrow">
            <h2 class="fs-5 mb-4">What you can do</h2>
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="fs-6 mb-1"><i class="bi bi-journal-check text-primary me-2" aria-hidden="true"></i>Complete Inductions</h3>
                    <p class="text-muted mb-0">Complete assigned induction requirements online.</p>
                </div>
                <div class="col-12">
                    <h3 class="fs-6 mb-1"><i class="bi bi-patch-check text-primary me-2" aria-hidden="true"></i>Review Compliance</h3>
                    <p class="text-muted mb-0">View your current compliance and previous records.</p>
                </div>
                <div class="col-12">
                    <h3 class="fs-6 mb-1"><i class="bi bi-award text-primary me-2" aria-hidden="true"></i>Access Certificates</h3>
                    <p class="text-muted mb-0">Download or print your certificate when required.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="border-top py-3">
    <div class="container text-center text-muted small">
        &copy; <?= e($year) ?> <?= e($appName) ?>
    </div>
</footer>

</body>
</html>
