<?php

declare(strict_types=1);

namespace App\Exam;

use App\Compliance\ComplianceService;
use App\Core\Database;
use App\Induction\InductionRepository;

class ExamService
{
    private const STATUSES = ['active', 'inactive'];

    private ExamRepository $exams;

    public function __construct()
    {
        $this->exams = new ExamRepository();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(?string $status = null, ?string $search = null): array
    {
        return $this->exams->all($status, $search);
    }

    public function find(int $id): ?array
    {
        return $this->exams->find($id);
    }

    /**
     * Creates an exam from its details. A new exam has no questions yet, so
     * it starts inactive: questions are added in the Exam Blocks editor, then
     * the exam is made active (see update()).
     *
     * @return array{success: bool, errors: array<string, string>, id?: int}
     */
    public function create(array $data): array
    {
        $data['status'] = 'inactive';
        [$normalized, $errors] = $this->validate($data, 0);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized['exam_blocks'] = '[]';
        $id = $this->exams->create($normalized);
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    /**
     * Updates an exam's details. Its questions are saved separately, by
     * updateExamBlocks().
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $id, array $data): array
    {
        $exam = $this->exams->find($id);
        if (!$exam) {
            return ['success' => false, 'errors' => ['form' => 'Exam not found.']];
        }

        [$normalized, $errors] = $this->validate($data, $this->questionCount($exam));
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->exams->update($id, $normalized);
        return ['success' => true, 'errors' => []];
    }

    /**
     * Saves the questions built in the Exam Blocks editor. Every question
     * must be complete; a problem is reported with the question's number and
     * block id, so the editor can take the admin straight to it.
     *
     * @return array{success: bool, errors: array<string, string>, block_id?: string}
     */
    public function updateExamBlocks(int $id, string $json): array
    {
        $exam = $this->exams->find($id);
        if (!$exam) {
            return ['success' => false, 'errors' => ['form' => 'Exam not found.']];
        }

        [$questions, $error] = $this->parseExamBlocks($json);
        if ($error !== null) {
            return ['success' => false, 'errors' => ['exam_blocks' => $error['message']], 'block_id' => $error['block_id']];
        }

        if (!$questions && $exam['status'] === 'active') {
            return ['success' => false, 'errors' => ['exam_blocks' => 'An active exam needs at least one question. Make the exam inactive first to remove them all.']];
        }

        $this->exams->updateExamBlocks($id, json_encode($questions));
        return ['success' => true, 'errors' => []];
    }

    /**
     * @param array<string, mixed> $exam
     */
    public function questionCount(array $exam): int
    {
        return count(json_decode((string) $exam['exam_blocks'], true) ?: []);
    }

    /**
     * Deletes an exam. An exam used by inductions or with exam attempts is
     * blocked unless $cascade is set: cascading detaches it from any
     * induction (the induction is kept, just without an exam) and deletes
     * its exam attempts, clearing (not deleting) the exam attempt reference
     * on any compliance record that cites one of them.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function delete(int $id, bool $cascade = false): array
    {
        if (!$this->exams->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Exam not found.']];
        }

        $inductions = new InductionRepository();
        $attempts = new ExamAttemptRepository();
        $compliance = new ComplianceService();

        $inductionCount = $inductions->countForExam($id);
        $attemptCount = $attempts->countForExam($id);

        if (($inductionCount > 0 || $attemptCount > 0) && !$cascade) {
            return ['success' => false, 'errors' => ['form' => sprintf(
                'This exam is used by %d induction(s) and has %d exam attempt(s). Enable cascade delete to detach the induction(s) and remove the attempts, or they must be removed first.',
                $inductionCount,
                $attemptCount
            )]];
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $inductions->detachExam($id);
            $compliance->detachExamAttempts($id);
            $attempts->deleteForExam($id);
            $this->exams->delete($id);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Scores submitted answers against an exam's stored questions. The exam
     * row's exam_blocks holds the correct answers; only this server-side
     * check ever sees them alongside the submission.
     *
     * @param array<string, mixed> $exam
     * @param array<string, string> $answers questionId => selected optionId
     * @return array{score: int, total: int, percentage: float, passed: bool}
     */
    public function score(array $exam, array $answers): array
    {
        $questions = json_decode((string) $exam['exam_blocks'], true) ?: [];
        $total = count($questions);
        $score = 0;

        foreach ($questions as $question) {
            $selected = $answers[$question['id']] ?? null;
            foreach ($question['options'] as $option) {
                if ($option['id'] === $selected && !empty($option['correct'])) {
                    $score++;
                    break;
                }
            }
        }

        $percentage = $total > 0 ? ($score / $total) * 100 : 0.0;

        return [
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $percentage >= (float) $exam['pass_percentage'],
        ];
    }

    /**
     * Validates an exam's details. An exam can only be active once it has
     * questions, so inductees are never given an empty exam.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(array $data, int $questionCount): array
    {
        $errors = [];

        $title = trim($data['title'] ?? '');
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }

        $description = trim($data['description'] ?? '');

        $passPercentage = $data['pass_percentage'] ?? '';
        if (!ctype_digit((string) $passPercentage) || (int) $passPercentage < 1 || (int) $passPercentage > 100) {
            $errors['pass_percentage'] = 'Enter a pass percentage between 1 and 100.';
        }

        $status = $data['status'] ?? '';
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Select a valid status.';
        } elseif ($status === 'active' && $questionCount === 0) {
            $errors['status'] = 'Add at least one question in the Exam Blocks editor before making this exam active.';
        }

        $normalized = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'pass_percentage' => (int) $passPercentage,
        ];

        return [$normalized, $errors];
    }

    /**
     * Checks and normalizes the questions from the Exam Blocks editor. The
     * editor (assets/js/exam-editor.js) shows the same problems as warnings
     * while editing; this is the authoritative check.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array{message: string, block_id: ?string}|null}
     */
    private function parseExamBlocks(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !array_is_list($decoded)) {
            return [[], ['message' => 'The exam blocks could not be read. Reload the editor and try again.', 'block_id' => null]];
        }

        $questions = [];

        foreach ($decoded as $index => $question) {
            if (!is_array($question) || !isset($question['id'], $question['options']) || !is_array($question['options'])) {
                return [[], ['message' => 'The exam blocks could not be read. Reload the editor and try again.', 'block_id' => null]];
            }

            $number = $index + 1;
            $blockId = (string) $question['id'];
            $fail = fn (string $problem): array => [[], ['message' => "Question {$number}: {$problem}", 'block_id' => $blockId]];

            $questionText = trim((string) ($question['question'] ?? ''));
            if ($questionText === '') {
                return $fail('enter the question.');
            }

            $options = [];
            $correctCount = 0;

            foreach ($question['options'] as $optionIndex => $option) {
                if (!is_array($option) || !isset($option['id'])) {
                    return [[], ['message' => 'The exam blocks could not be read. Reload the editor and try again.', 'block_id' => null]];
                }

                $optionText = trim((string) ($option['text'] ?? ''));
                if ($optionText === '') {
                    return $fail('option ' . ($optionIndex + 1) . ' is empty. Fill it in or remove it.');
                }

                $correct = !empty($option['correct']);
                if ($correct) {
                    $correctCount++;
                }

                $options[] = [
                    'id' => (string) $option['id'],
                    'text' => $optionText,
                    'correct' => $correct,
                ];
            }

            if (count($options) < 2) {
                return $fail('add at least two options.');
            }

            if ($correctCount !== 1) {
                return $fail('mark the one correct answer.');
            }

            $diagramUrl = trim((string) ($question['diagram_img_url'] ?? ''));
            if ($diagramUrl !== '' && !filter_var($diagramUrl, FILTER_VALIDATE_URL)) {
                return $fail('the diagram image URL is not a valid web address.');
            }

            $questions[] = [
                'id' => $blockId,
                'type' => 'question',
                'question' => $questionText,
                'diagram_img_url' => $diagramUrl,
                'explanation' => trim((string) ($question['explanation'] ?? '')),
                'options' => $options,
            ];
        }

        return [$questions, null];
    }
}
