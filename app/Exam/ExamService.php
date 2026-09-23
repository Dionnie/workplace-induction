<?php

declare(strict_types=1);

namespace App\Exam;

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
     * @return array{success: bool, errors: array<string, string>}
     */
    public function create(array $data): array
    {
        [$normalized, $errors] = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->exams->create($normalized);
        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $id, array $data): array
    {
        if (!$this->exams->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Exam not found.']];
        }

        [$normalized, $errors] = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->exams->update($id, $normalized);
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
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(array $data): array
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
        }

        $examBlocks = $this->parseExamBlocks($data['exam_blocks'] ?? '[]');
        if ($examBlocks === null) {
            $errors['exam_blocks'] = 'Exam contains invalid questions.';
        } elseif (empty($examBlocks)) {
            $errors['exam_blocks'] = 'Add at least one question.';
        }

        $normalized = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'pass_percentage' => (int) $passPercentage,
            'exam_blocks' => $examBlocks !== null ? json_encode($examBlocks) : '[]',
        ];

        return [$normalized, $errors];
    }

    /**
     * @return array<int, array<string, mixed>>|null Null when the input is malformed.
     */
    private function parseExamBlocks(string $json): ?array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }

        $questions = [];

        foreach ($decoded as $question) {
            if (!is_array($question) || !isset($question['id'], $question['options']) || !is_array($question['options'])) {
                return null;
            }

            $questionText = trim((string) ($question['question'] ?? ''));
            if ($questionText === '') {
                continue;
            }

            $options = [];
            $correctCount = 0;

            foreach ($question['options'] as $option) {
                if (!is_array($option) || !isset($option['id'])) {
                    return null;
                }

                $optionText = trim((string) ($option['text'] ?? ''));
                if ($optionText === '') {
                    continue;
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

            if (count($options) < 2 || $correctCount !== 1) {
                return null;
            }

            $diagramUrl = trim((string) ($question['diagram_img_url'] ?? ''));
            if ($diagramUrl !== '' && !filter_var($diagramUrl, FILTER_VALIDATE_URL)) {
                return null;
            }

            $questions[] = [
                'id' => (string) $question['id'],
                'type' => 'question',
                'question' => $questionText,
                'diagram_img_url' => $diagramUrl,
                'explanation' => trim((string) ($question['explanation'] ?? '')),
                'options' => $options,
            ];
        }

        return $questions;
    }
}
