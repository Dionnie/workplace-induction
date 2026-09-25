<?php

declare(strict_types=1);

/**
 * The exam result: the score, then every question as it was on the exam
 * page, with the inductee's answer and the correct answer marked
 * (docs/application/exam_blocks_editor.md §7).
 *
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $result score, total, percentage, passed, exam, answers, compliance
 */
$pageTitle = $induction['title'] . ' — Result';
$currentPage = 'dashboard';
require __DIR__ . '/../../partials/inductee-header.php';

$questions = json_decode((string) $result['exam']['exam_blocks'], true) ?: [];
$answers = $result['answers'];
$total = count($questions);
?>

<div class="page-narrow mx-auto">
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

    <div class="exam-questions">
        <?php foreach ($questions as $index => $question): ?>
            <?php
            $selected = $answers[$question['id']] ?? null;
            $wasCorrect = false;
            foreach ($question['options'] as $option) {
                if ($option['id'] === $selected && !empty($option['correct'])) {
                    $wasCorrect = true;
                }
            }
            ?>
            <div class="exam-question card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <span class="exam-question-eyebrow">Question <?= $index + 1 ?> of <?= $total ?></span>
                        <?= status_badge($wasCorrect ? 'correct' : 'incorrect') ?>
                    </div>
                    <h2 class="exam-question-text"><?= nl2br(e($question['question'])) ?></h2>

                    <?php if (!empty($question['diagram_img_url'])): ?>
                        <img src="<?= e($question['diagram_img_url']) ?>" class="exam-question-diagram img-fluid rounded" alt="">
                    <?php endif; ?>

                    <div class="exam-choices">
                        <?php foreach ($question['options'] as $option): ?>
                            <?php
                            $isSelected = $option['id'] === $selected;
                            $isCorrect = !empty($option['correct']);
                            if ($isCorrect) {
                                $state = 'is-correct';
                                $icon = 'bi-check-circle-fill';
                                $note = $isSelected ? 'Your answer' : 'Correct answer';
                            } elseif ($isSelected) {
                                $state = 'is-incorrect';
                                $icon = 'bi-x-circle-fill';
                                $note = 'Your answer';
                            } else {
                                $state = '';
                                $icon = 'bi-circle';
                                $note = '';
                            }
                            ?>
                            <div class="exam-choice <?= $state ?>">
                                <i class="exam-choice-icon bi <?= $icon ?>" aria-hidden="true"></i>
                                <span class="exam-choice-text"><?= e($option['text']) ?></span>
                                <?php if ($note !== ''): ?>
                                    <span class="exam-choice-note"><?= $note ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($selected === null): ?>
                        <p class="text-muted small mt-2 mb-0">Not answered.</p>
                    <?php endif; ?>

                    <?php if (!empty($question['explanation'])): ?>
                        <p class="exam-explanation">
                            <i class="bi bi-lightbulb me-1" aria-hidden="true"></i><?= nl2br(e($question['explanation'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-actions mt-4">
        <a href="/inductee/inductions/show.php?id=<?= (int) $induction['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Induction
        </a>
    </div>
</div>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
