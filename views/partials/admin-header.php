<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * @var string $pageTitle
 * @var string $currentPage
 */
$appName = app_config()['name'];
$authUser = Auth::user();
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand navbar-dark" style="background-color: var(--color-primary-900);">
    <div class="container">
        <a class="navbar-brand" href="/admin/index.php"><?= e($appName) ?></a>
        <div class="navbar-nav me-auto">
            <a class="nav-link <?= $currentPage === 'dashboard' ? 'fw-semibold text-white' : '' ?>" href="/admin/index.php">Dashboard</a>
            <a class="nav-link <?= $currentPage === 'inductions' ? 'fw-semibold text-white' : '' ?>" href="/admin/inductions/index.php">Inductions</a>
            <a class="nav-link <?= $currentPage === 'exams' ? 'fw-semibold text-white' : '' ?>" href="/admin/exams/index.php">Exams</a>
            <a class="nav-link <?= $currentPage === 'exam-attempts' ? 'fw-semibold text-white' : '' ?>" href="/admin/exam-attempts/index.php">Exam Attempts</a>
            <a class="nav-link <?= $currentPage === 'users' ? 'fw-semibold text-white' : '' ?>" href="/admin/users/index.php">Users</a>
            <a class="nav-link <?= $currentPage === 'compliance' ? 'fw-semibold text-white' : '' ?>" href="/admin/compliance/index.php">Compliance</a>
            <a class="nav-link <?= $currentPage === 'settings' ? 'fw-semibold text-white' : '' ?>" href="/admin/settings/index.php">Settings</a>
        </div>
        <div class="navbar-nav">
            <a class="nav-link <?= $currentPage === 'profile' ? 'fw-semibold text-white' : '' ?>" href="/admin/profile/index.php">My Profile</a>
            <a class="nav-link" href="/logout.php">Log Out</a>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="alert alert-danger"><?= e($message) ?></div>
        <?php endif; ?>
