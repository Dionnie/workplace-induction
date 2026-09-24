<?php

declare(strict_types=1);

/**
 * @var array<string, string> $errors
 * @var array<int, array<string, mixed>> $exams
 */
$pageTitle = 'Add Induction';
$currentPage = 'inductions';
require __DIR__ . '/../../partials/admin-header.php';

$values = [
    'title' => old_raw('title'),
    'code' => old_raw('code'),
    'description' => old_raw('description'),
    'exam_id' => old_raw('exam_id'),
    'validity_months' => old_raw('validity_months'),
    'status' => old_raw('status', 'active'),
];
$formAction = '/admin/inductions/create.php';
$submitLabel = 'Create Induction';
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/inductions/index.php">Inductions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Induction</li>
                </ol>
            </nav>
            <h1 class="page-title">Add Induction</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php require __DIR__ . '/_form.php'; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
