<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('inductee');

// Inductions need a completed profile (docs/core/users.md §9).
Auth::requireCompletedProfile('/inductee/profile/index.php', 'Complete your profile before starting an induction.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$inductionId = (int) ($_POST['induction_id'] ?? 0);
$authUser = Auth::user();

$result = (new InductionService())->completeWithoutExam((int) $authUser['id'], $inductionId);

if ($result['success']) {
    flash('success', 'Induction completed. Your compliance record has been issued.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to complete this induction.');
}

redirect('/inductee/inductions/show.php?id=' . $inductionId);
