<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $result score, total, percentage, passed, exam, answers, compliance
 */
$pageTitle = $induction['title'] . ' — Result';
$currentPage = 'dashboard';
require __DIR__ . '/../../partials/inductee-header.php';

$questions = json_decode((string) $result['exam']['exam_blocks'], true) ?: [];
$answers = $result['answers'];
?>

<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/inductee/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>"><?= e($induction['title']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Result</li>
            </ol>
        </nav>
        <h1 class="page-title"><?= e($induction['title']) ?> &mdash; Result</h1>
    </div>
</div>

<?php if ($result['passed']): ?>
    <div class="alert alert-success">
        <strong>You passed.</strong> Score: <?= (int) $result['score'] ?> / <?= (int) $result['total'] ?>
        (<?= round($result['percentage']) ?>%).
        <?php if ($result['compliance']): ?>
            Your compliance record has been issued, valid until <?= e($result['compliance']['expiry_date']) ?>.
            <a href="/inductee/certificates/show.php?id=<?= (int) $result['compliance']['id'] ?>" class="alert-link">View certificate</a>.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        <strong>You did not pass.</strong> Score: <?= (int) $result['score'] ?> / <?= (int) $result['total'] ?>
        (<?= round($result['percentage']) ?>%). Review the explanations below, then
        <a href="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>" class="alert-link">try again</a>.
    </div>
<?php endif; ?>

<?php foreach ($questions as $index => $question): ?>
    <?php
    $selected = $answers[$question['id']] ?? null;
    $correctOption = null;
    $selectedOption = null;
    foreach ($question['options'] as $option) {
        if (!empty($option['correct'])) {
            $correctOption = $option;
        }
        if ($option['id'] === $selected) {
            $selectedOption = $option;
        }
    }
    $wasCorrect = $selectedOption && !empty($selectedOption['correct']);
    ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h2 class="fs-6 mb-0">Question <?= $index + 1 ?></h2>
                <?= status_badge($wasCorrect ? 'correct' : 'incorrect') ?>
            </div>
            <p><?= nl2br(e($question['question'])) ?></p>

            <p class="mb-1">
                <span class="text-muted">Your answer:</span>
                <?= e($selectedOption['text'] ?? 'No answer') ?>
            </p>
            <?php if (!$wasCorrect && $correctOption): ?>
                <p class="mb-1">
                    <span class="text-muted">Correct answer:</span>
                    <?= e($correctOption['text']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($question['explanation'])): ?>
                <p class="text-muted small mb-0 mt-2"><?= nl2br(e($question['explanation'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="form-actions">
    <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Induction
    </a>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
