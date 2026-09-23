<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * @var string $pageTitle
 * @var string $currentPage
 * @var bool $wideContainer Set true for pages that need more than
 *     Bootstrap's default .container width (e.g. a page with its own
 *     left sidebar rail, where a 1024px reading canvas plus a 260px
 *     sidebar needs more room than the standard container gives).
 */
$appName = app_config()['name'];
$authUser = Auth::user();
$currentPage = $currentPage ?? '';
$wideContainer = $wideContainer ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand navbar-dark" style="background-color: var(--color-primary-700);">
    <div class="container">
        <a class="navbar-brand" href="/inductee/index.php"><?= e($appName) ?></a>
        <div class="navbar-nav me-auto">
            <a class="nav-link <?= $currentPage === 'dashboard' ? 'fw-semibold text-white' : '' ?>" href="/inductee/index.php">Dashboard</a>
            <a class="nav-link <?= $currentPage === 'compliance' ? 'fw-semibold text-white' : '' ?>" href="/inductee/compliance/index.php">Compliance</a>
        </div>
        <div class="navbar-nav">
            <a class="nav-link <?= $currentPage === 'profile' ? 'fw-semibold text-white' : '' ?>" href="/inductee/profile/index.php">My Profile</a>
            <span class="nav-link text-white-50"><?= e($authUser['email'] ?? '') ?></span>
            <a class="nav-link" href="/logout.php">Log Out</a>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="<?= $wideContainer ? 'container-fluid px-4' : 'container' ?>">
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="alert alert-danger"><?= e($message) ?></div>
        <?php endif; ?>
