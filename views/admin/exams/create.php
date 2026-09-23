<?php

declare(strict_types=1);

/** @var array<string, string> $errors */
$pageTitle = 'Add Exam';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';

$values = [
    'title' => old_raw('title'),
    'description' => old_raw('description'),
    'pass_percentage' => old_raw('pass_percentage'),
    'status' => old_raw('status', 'active'),
];
$formAction = '/admin/exams/create.php';
$submitLabel = 'Create Exam';
$examBlocksJson = old_raw('exam_blocks', '[]');
if ($examBlocksJson === '') {
    $examBlocksJson = '[]';
}
?>

<h1 class="fs-4 fw-semibold mb-3">Add Exam</h1>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body p-4">
        <?php require __DIR__ . '/_form.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
