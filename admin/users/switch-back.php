<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;

// Requested from inside a switched session, which is an inductee's, so
// this checks for a switch instead of the admin role (docs/core/users.md §10).
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

if (Auth::switchedFrom() === null) {
    redirect(Auth::homeUrl());
}

$switchedUserId = Auth::id();

if (!Auth::switchBack()) {
    flash('error', 'Your administrator account is no longer active, so you have been logged out.');
    redirect('/login.php');
}

flash('success', 'Switched back to your account.');
redirect('/admin/users/edit.php?id=' . $switchedUserId);
