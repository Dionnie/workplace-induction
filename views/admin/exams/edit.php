<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $exam
 * @var array<string, string> $errors
 */
$pageTitle = 'Edit Exam';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';

$values = [
    'title' => old_raw('title', (string) $exam['title']),
    'description' => old_raw('description', (string) $exam['description']),
    'pass_percentage' => old_raw('pass_percentage', (string) $exam['pass_percentage']),
    'status' => old_raw('status', (string) $exam['status']),
];
$formAction = '/admin/exams/edit.php?id=' . (int) $exam['id'];
$submitLabel = 'Save Changes';
$examBlocksJson = old_raw('exam_blocks', (string) $exam['exam_blocks']);
if ($examBlocksJson === '') {
    $examBlocksJson = '[]';
}
?>

<h1 class="fs-4 fw-semibold mb-3">Edit Exam</h1>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body p-4">
        <?php require __DIR__ . '/_form.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
