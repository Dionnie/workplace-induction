<?php

declare(strict_types=1);

namespace App\Exam;

use App\Compliance\ComplianceService;

class ExamAttemptService
{
    private ExamAttemptRepository $attempts;

    public function __construct()
    {
        $this->attempts = new ExamAttemptRepository();
    }

    /**
     * All exam attempts across inductees, for admin management.
     *
     * @return array<int, array<string, mixed>>
     */
    public function list(?int $inductionId = null, ?int $examId = null, ?string $result = null, ?string $search = null): array
    {
        return $this->attempts->all($inductionId, $examId, $result, $search);
    }

    public function find(int $id): ?array
    {
        return $this->attempts->find($id);
    }

    /**
     * Permanently deletes a single exam attempt. Attempts are historical
     * evidence and are never edited (see exam_attempts in schema.sql), only
     * removed outright when genuinely necessary. Any compliance record
     * citing this attempt keeps its record but loses the reference,
     * preserving permanent compliance history.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function delete(int $id): array
    {
        if (!$this->attempts->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Exam attempt not found.']];
        }

        (new ComplianceService())->detachExamAttempt($id);
        $this->attempts->delete($id);

        return ['success' => true, 'errors' => []];
    }
}
