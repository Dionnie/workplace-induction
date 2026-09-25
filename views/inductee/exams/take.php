<?php

declare(strict_types=1);

/**
 * The exam: one card per question in a centred reading column, each answer
 * option a full-width clickable row, and a bar at the bottom with the
 * answered count and Submit Exam (docs/application/exams.md §7).
 *
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $exam
 * @var array<int, array<string, mixed>> $questions Correct-answer flags stripped.
 */
$pageTitle = $induction['title'] . ' — Exam';
$currentPage = 'dashboard';
require __DIR__ . '/../../partials/inductee-header.php';

$total = count($questions);
?>

<div class="page-narrow mx-auto">
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
            <p class="page-subtitle">
                <?= $total ?> <?= $total === 1 ? 'question' : 'questions' ?>, pass mark <?= (int) $exam['pass_percentage'] ?>%.
                Answer every question, then submit.
            </p>
        </div>
    </div>

    <form method="post" action="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>" data-exam-form>
        <?= csrf_field() ?>

        <div class="exam-questions">
            <?php foreach ($questions as $index => $question): ?>
                <div class="exam-question card border-0 shadow-sm">
                    <fieldset class="card-body">
                        <legend class="exam-question-legend">
                            <span class="exam-question-eyebrow required">Question <?= $index + 1 ?> of <?= $total ?></span>
                            <span class="exam-question-text"><?= nl2br(e($question['question'])) ?></span>
                        </legend>

                        <?php if (!empty($question['diagram_img_url'])): ?>
                            <img src="<?= e($question['diagram_img_url']) ?>" class="exam-question-diagram img-fluid rounded" alt="">
                        <?php endif; ?>

                        <div class="exam-choices">
                            <?php foreach ($question['options'] as $option): ?>
                                <label class="exam-choice">
                                    <input class="form-check-input exam-choice-radio" type="radio"
                                           name="answers[<?= e($question['id']) ?>]" value="<?= e($option['id']) ?>" required>
                                    <span class="exam-choice-text"><?= e($option['text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="exam-bar">
            <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            <span class="exam-bar-status" data-exam-progress aria-live="polite">0 of <?= $total ?> answered</span>
            <button type="submit" class="btn btn-primary">Submit Exam</button>
        </div>
    </form>
</div>

<script src="/assets/js/exam-take.js"></script>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
