<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $induction
 * @var array<int, array<string, mixed>> $questions Correct-answer flags stripped.
 */
$pageTitle = $induction['title'] . ' — Exam';
$currentPage = 'dashboard';
require __DIR__ . '/../../partials/inductee-header.php';
?>

<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/inductee/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>"><?= e($induction['title']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Exam</li>
            </ol>
        </nav>
        <h1 class="page-title"><?= e($induction['title']) ?> &mdash; Exam</h1>
        <p class="page-subtitle">Answer all questions, then submit.</p>
    </div>
</div>

<form method="post" action="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>">
    <?= csrf_field() ?>

    <?php foreach ($questions as $index => $question): ?>
        <div class="card shadow-sm mb-3">
            <fieldset class="card-body">
                <legend class="fs-6 fw-semibold mb-2 required">Question <?= $index + 1 ?></legend>
                <p><?= nl2br(e($question['question'])) ?></p>

                <?php if (!empty($question['diagram_img_url'])): ?>
                    <img src="<?= e($question['diagram_img_url']) ?>" class="img-fluid rounded mb-3" alt="">
                <?php endif; ?>

                <?php foreach ($question['options'] as $option): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                               name="answers[<?= e($question['id']) ?>]" id="<?= e($option['id']) ?>"
                               value="<?= e($option['id']) ?>" required>
                        <label class="form-check-label" for="<?= e($option['id']) ?>">
                            <?= e($option['text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        </div>
    <?php endforeach; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit Exam</button>
        <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
