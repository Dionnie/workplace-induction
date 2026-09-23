<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth;

$config = require __DIR__ . '/config/app.php';

$appName = $config['name'];
$tagline = $config['tagline'];
$description = $config['description'];
$registrationEnabled = $config['registration_enabled'];
$year = date('Y');

$authUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>

<header class="border-bottom">
    <div class="container d-flex justify-content-between align-items-center py-3">
        <span class="fs-5 fw-semibold text-brand"><?= e($appName) ?></span>
        <nav>
            <?php if ($authUser): ?>
                <a href="<?= $authUser['user_type'] === 'admin' ? '/admin/index.php' : '/inductee/index.php' ?>" class="btn btn-primary btn-sm">Dashboard</a>
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
    <section class="py-5">
        <div class="container text-center" style="max-width: 640px;">
            <h1 class="fw-semibold"><?= e($appName) ?></h1>
            <p class="text-muted mb-2"><?= e($tagline) ?></p>
            <p class="mb-4"><?= e($description) ?></p>
            <?php if (!$authUser): ?>
                <a href="/login.php" class="btn btn-primary me-2">Log In</a>
                <?php if ($registrationEnabled): ?>
                    <a href="/register.php" class="btn btn-outline-secondary">Register</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-surface-subtle py-5">
        <div class="container" style="max-width: 640px;">
            <h2 class="fs-5 fw-semibold mb-4">What you can do</h2>
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="fs-6 fw-semibold mb-1">Complete Inductions</h3>
                    <p class="text-muted mb-0">Complete assigned induction requirements online.</p>
                </div>
                <div class="col-12">
                    <h3 class="fs-6 fw-semibold mb-1">Review Compliance</h3>
                    <p class="text-muted mb-0">View your current compliance and previous records.</p>
                </div>
                <div class="col-12">
                    <h3 class="fs-6 fw-semibold mb-1">Access Certificates</h3>
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
