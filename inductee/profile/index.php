<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Inductee\InducteeProfileService;

Auth::requireRole('inductee');

$userId = (int) Auth::id();
$service = new InducteeProfileService();

// The page that needed a completed profile, to return to once it is complete (docs/core/auth.md #12).
$redirectTo = safe_redirect_path($_POST['redirect_to'] ?? $_GET['redirect_to'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'job_position' => trim($_POST['job_position'] ?? ''),
        'company' => trim($_POST['company'] ?? ''),
        'employment_type' => $_POST['employment_type'] ?? '',
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
        'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
    ];

    $result = $service->update($userId, $data);

    if ($result['success']) {
        clear_old();

        // Completing the profile unlocks the inductions: send them back to the
        // one they were stopped at, or to the dashboard.
        if ($result['completed_now']) {
            flash('success', 'Profile complete. You can now start your inductions.');
            redirect(Auth::intendedUrl($redirectTo));
        }

        flash('success', 'Profile updated.');
        redirect('/inductee/profile/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/inductee/profile/index.php' . redirect_to_query($redirectTo));
}

$profile = $service->get($userId);
$errors = get_errors();

require __DIR__ . '/../../views/inductee/profile/index.php';
