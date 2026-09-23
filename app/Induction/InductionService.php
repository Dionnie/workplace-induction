<?php

declare(strict_types=1);

namespace App\Induction;

use App\Compliance\ComplianceService;
use App\ContentBlocks\ContentBlockService;
use App\Core\Auth\UserRepository;
use App\Core\Database;
use App\Exam\ExamAttemptRepository;
use App\Exam\ExamRepository;
use App\Exam\ExamService;

class InductionService
{
    private const STATUSES = ['active', 'inactive'];

    private InductionRepository $inductions;
    private ExamRepository $exams;
    private ContentBlockService $contentBlocks;

    public function __construct()
    {
        $this->inductions = new InductionRepository();
        $this->exams = new ExamRepository();
        $this->contentBlocks = new ContentBlockService();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(?string $status = null, ?string $search = null): array
    {
        return $this->inductions->all($status, $search);
    }

    public function find(int $id): ?array
    {
        return $this->inductions->find($id);
    }

    /**
     * @return array{success: bool, errors: array<string, string>, id?: int}
     */
    public function create(array $data): array
    {
        [$normalized, $errors] = $this->validate($data, null);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->inductions->create($normalized);
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $id, array $data): array
    {
        if (!$this->inductions->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Induction not found.']];
        }

        [$normalized, $errors] = $this->validate($data, $id);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->inductions->update($id, $normalized);
        return ['success' => true, 'errors' => []];
    }

    /**
     * Deletes an induction. An induction with exam attempts or compliance
     * records is blocked unless $cascade is set, since those rows would
     * otherwise be orphaned.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function delete(int $id, bool $cascade = false): array
    {
        if (!$this->inductions->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Induction not found.']];
        }

        $attempts = new ExamAttemptRepository();
        $compliance = new ComplianceService();

        $attemptCount = $attempts->countForInduction($id);
        $complianceCount = $compliance->countForInduction($id);

        if (($attemptCount > 0 || $complianceCount > 0) && !$cascade) {
            return ['success' => false, 'errors' => ['form' => sprintf(
                'This induction has %d exam attempt(s) and %d compliance record(s). Enable cascade delete to remove them together, or they must be removed first.',
                $attemptCount,
                $complianceCount
            )]];
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $compliance->deleteAllForInduction($id);
            $attempts->deleteForInduction($id);
            $this->inductions->delete($id);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Content blocks are saved separately from induction metadata, via the
     * Studio editor.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateContentBlocks(int $id, string $json): array
    {
        if (!$this->inductions->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Induction not found.']];
        }

        $blocks = $this->contentBlocks->validate($json);
        if ($blocks === null) {
            return ['success' => false, 'errors' => ['content_blocks' => 'Content contains invalid blocks.']];
        }

        $this->inductions->updateContentBlocks($id, json_encode($blocks));
        return ['success' => true, 'errors' => []];
    }

    /**
     * Active inductions with each one's latest compliance status for this
     * inductee, for the inductee dashboard.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listActiveForInductee(int $userId): array
    {
        $inductions = $this->inductions->all('active');
        $latestCompliance = (new ComplianceService())->latestPerInductionForUser($userId);
        $attempts = new ExamAttemptRepository();

        foreach ($inductions as &$induction) {
            $induction = $this->withComplianceState($induction, $userId, $latestCompliance, $attempts);
        }
        unset($induction);

        return $inductions;
    }

    /**
     * A single active induction with its compliance state for this inductee,
     * for the induction detail page.
     *
     * @return array<string, mixed>|null
     */
    public function findActiveForInductee(int $userId, int $inductionId): ?array
    {
        $induction = $this->inductions->find($inductionId);
        if (!$induction || $induction['status'] !== 'active') {
            return null;
        }

        $latestCompliance = (new ComplianceService())->latestPerInductionForUser($userId);

        return $this->withComplianceState($induction, $userId, $latestCompliance, new ExamAttemptRepository());
    }

    /**
     * @param array<string, mixed> $induction
     * @param array<int, array<string, mixed>> $latestCompliance keyed by induction_id
     */
    private function withComplianceState(
        array $induction,
        int $userId,
        array $latestCompliance,
        ExamAttemptRepository $attempts
    ): array {
        $inductionId = (int) $induction['id'];
        $compliance = $latestCompliance[$inductionId] ?? null;

        if ($compliance && $compliance['status'] === 'active') {
            $state = 'compliant';
        } elseif ($compliance && in_array($compliance['status'], ['expired', 'superseded'], true)) {
            $state = 'expired';
        } elseif ($induction['exam_id'] !== null) {
            $latestAttempt = $attempts->latestForUserAndInduction($userId, $inductionId);
            $state = ($latestAttempt && $latestAttempt['result'] === 'failed') ? 'failed' : 'not_started';
        } else {
            $state = 'not_started';
        }

        $induction['compliance_state'] = $state;
        $induction['latest_compliance'] = $compliance;

        return $induction;
    }

    /**
     * Completes a non-exam induction and issues compliance immediately.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function completeWithoutExam(int $userId, int $inductionId): array
    {
        $induction = $this->inductions->find($inductionId);

        if (!$induction || $induction['status'] !== 'active') {
            return ['success' => false, 'errors' => ['form' => 'Induction not found.']];
        }

        if ($induction['exam_id'] !== null) {
            return ['success' => false, 'errors' => ['form' => 'This induction requires an exam.']];
        }

        $compliance = (new ComplianceService())->issue($userId, $inductionId, (int) $induction['validity_months'], null);

        $inductee = (new UserRepository())->findById($userId);
        do_action('induction_completed', $inductee, $induction, $compliance);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Scores and records an exam attempt for an induction, issuing compliance
     * when the attempt passes.
     *
     * @param array<string, string> $answers questionId => selected optionId
     * @return array{success: bool, errors: array<string, string>, result?: array<string, mixed>}
     */
    public function submitExam(int $userId, int $inductionId, array $answers): array
    {
        $induction = $this->inductions->find($inductionId);

        if (!$induction || $induction['status'] !== 'active' || $induction['exam_id'] === null) {
            return ['success' => false, 'errors' => ['form' => 'Induction not found.']];
        }

        $examService = new ExamService();
        $exam = $examService->find((int) $induction['exam_id']);

        if (!$exam || $exam['status'] !== 'active') {
            return ['success' => false, 'errors' => ['form' => 'This exam is not currently available.']];
        }

        $scoring = $examService->score($exam, $answers);

        $attemptId = (new ExamAttemptRepository())->create([
            'user_id' => $userId,
            'induction_id' => $inductionId,
            'exam_id' => $exam['id'],
            'score' => $scoring['score'],
            'total_score' => $scoring['total'],
            'result' => $scoring['passed'] ? 'passed' : 'failed',
        ]);

        $compliance = null;
        if ($scoring['passed']) {
            $compliance = (new ComplianceService())->issue(
                $userId,
                $inductionId,
                (int) $induction['validity_months'],
                $attemptId
            );

            $inductee = (new UserRepository())->findById($userId);
            do_action('induction_completed', $inductee, $induction, $compliance);
        }

        return [
            'success' => true,
            'errors' => [],
            'result' => array_merge($scoring, [
                'exam' => $exam,
                'answers' => $answers,
                'compliance' => $compliance,
            ]),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(array $data, ?int $ignoreId): array
    {
        $errors = [];

        $title = trim($data['title'] ?? '');
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }

        $code = strtoupper(trim($data['code'] ?? ''));
        if ($code === '') {
            $errors['code'] = 'Code is required.';
        } elseif (!preg_match('/^[A-Z0-9-]+$/', $code)) {
            $errors['code'] = 'Code may only contain letters, numbers, and dashes.';
        } else {
            $existing = $this->inductions->findByCode($code);
            if ($existing && (int) $existing['id'] !== $ignoreId) {
                $errors['code'] = 'An induction with this code already exists.';
            }
        }

        $description = trim($data['description'] ?? '');

        $examId = trim((string) ($data['exam_id'] ?? ''));
        if ($examId !== '') {
            if (!ctype_digit($examId) || !$this->exams->find((int) $examId)) {
                $errors['exam_id'] = 'Select a valid exam.';
                $examId = null;
            } else {
                $examId = (int) $examId;
            }
        } else {
            $examId = null;
        }

        $validityMonths = $data['validity_months'] ?? '';
        if (!ctype_digit((string) $validityMonths) || (int) $validityMonths < 1) {
            $errors['validity_months'] = 'Enter a valid number of months (1 or more).';
        }

        $status = $data['status'] ?? '';
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Select a valid status.';
        }

        $normalized = [
            'title' => $title,
            'code' => $code,
            'description' => $description,
            'exam_id' => $examId,
            'validity_months' => (int) $validityMonths,
            'status' => $status,
        ];

        return [$normalized, $errors];
    }
}
