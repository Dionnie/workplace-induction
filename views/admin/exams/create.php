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

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/exams/index.php">Exams</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Exam</li>
                </ol>
            </nav>
            <h1 class="page-title">Add Exam</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php require __DIR__ . '/_form.php'; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
