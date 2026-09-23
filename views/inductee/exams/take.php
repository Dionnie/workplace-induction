<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $induction
 * @var array<int, array<string, mixed>> $questions Correct-answer flags stripped.
 */
$pageTitle = $induction['title'] . ' &mdash; Exam';
$currentPage = 'dashboard';
require __DIR__ . '/../../partials/inductee-header.php';
?>

<h1 class="fs-4 fw-semibold mb-1"><?= e($induction['title']) ?> &mdash; Exam</h1>
<p class="text-muted mb-4">Answer all questions, then submit.</p>

<form method="post" action="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>">
    <?= csrf_field() ?>

    <?php foreach ($questions as $index => $question): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold">Question <?= $index + 1 ?></h2>
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
            </div>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary">Submit Exam</button>
</form>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
