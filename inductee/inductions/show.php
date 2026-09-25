<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('inductee');

// Inductions need a completed profile (docs/core/users.md §9).
Auth::requireCompletedProfile('/inductee/profile/index.php', 'Complete your profile before starting an induction.');

$id = (int) ($_GET['id'] ?? 0);
$authUser = Auth::user();
$induction = (new InductionService())->findActiveForInductee((int) $authUser['id'], $id);

if (!$induction) {
    http_response_code(404);
    exit('Induction not found.');
}

// Section slides, each with its lecture slides (docs/application/content-blocks.md #3).
$sections = json_decode((string) $induction['content_blocks'], true);
$sections = is_array($sections) ? $sections : [];

require __DIR__ . '/../../views/inductee/inductions/show.php';
