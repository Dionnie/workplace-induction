<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\ContentBlocks\CourseOutlineBuilder;
use App\Induction\InductionService;

Auth::requireRole('inductee');

$id = (int) ($_GET['id'] ?? 0);
$authUser = Auth::user();
$induction = (new InductionService())->findActiveForInductee((int) $authUser['id'], $id);

if (!$induction) {
    http_response_code(404);
    exit('Induction not found.');
}

$blocks = json_decode((string) $induction['content_blocks'], true) ?: [];
$outline = (new CourseOutlineBuilder())->build($blocks);

require __DIR__ . '/../../views/inductee/inductions/show.php';
