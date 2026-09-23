<?php

declare(strict_types=1);

namespace App\Compliance;

use DateTimeImmutable;

class ComplianceService
{
    private ComplianceRepository $records;

    public function __construct()
    {
        $this->records = new ComplianceRepository();
    }

    /**
     * Issues a new compliance record for a completed induction. If the user
     * already holds a record for this induction, the previous one is
     * superseded (never overwritten) and the new record links back to it via
     * renewed_from_id, preserving permanent compliance history.
     *
     * @return array<string, mixed> the newly issued record
     */
    public function issue(int $userId, int $inductionId, int $validityMonths, ?int $examAttemptId): array
    {
        $previous = $this->records->latestForUserAndInduction($userId, $inductionId);

        if ($previous && in_array($previous['status'], ['active', 'expired'], true)) {
            $this->records->markSuperseded((int) $previous['id']);
        }

        $issueDate = new DateTimeImmutable('today');
        $expiryDate = $issueDate->modify("+{$validityMonths} months");

        $id = $this->records->create([
            'user_id' => $userId,
            'induction_id' => $inductionId,
            'exam_attempt_id' => $examAttemptId,
            'verification_token' => bin2hex(random_bytes(32)),
            'certificate_number' => 'PENDING',
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiry_date' => $expiryDate->format('Y-m-d'),
            'status' => 'active',
            'renewed_from_id' => $previous['id'] ?? null,
        ]);

        $certificateNumber = sprintf('CERT-%s-%06d', $issueDate->format('Y'), $id);
        $this->records->updateCertificateNumber($id, $certificateNumber);

        return $this->records->find($id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForUser(int $userId): array
    {
        $this->records->expireLapsed();
        return $this->records->allForUser($userId);
    }

    /**
     * @return array<int, array<string, mixed>> keyed by induction_id
     */
    public function latestPerInductionForUser(int $userId): array
    {
        $this->records->expireLapsed();
        return $this->records->latestPerInductionForUser($userId);
    }

    public function findOwnedByUser(int $id, int $userId): ?array
    {
        $this->records->expireLapsed();
        return $this->records->findOwnedByUser($id, $userId);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $this->records->expireLapsed();
        return $this->records->findByVerificationToken($token);
    }

    /**
     * All compliance records across inductees, for admin management.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listAll(?string $status = null, ?int $inductionId = null, ?string $search = null): array
    {
        $this->records->expireLapsed();
        return $this->records->all($status, $inductionId, $search);
    }

    /**
     * Manually invalidates a compliance record. Only a currently active record
     * can be revoked -- an expired or superseded record is already not
     * current, and a revoked record cannot be revoked again.
     *
     * @return array{success: bool, error: ?string}
     */
    public function revoke(int $id): array
    {
        $this->records->expireLapsed();
        $record = $this->records->find($id);

        if (!$record) {
            return ['success' => false, 'error' => 'Compliance record not found.'];
        }

        if ($record['status'] !== 'active') {
            return ['success' => false, 'error' => 'Only an active compliance record can be revoked.'];
        }

        $this->records->markRevoked($id);

        return ['success' => true, 'error' => null];
    }

    public function countForUser(int $userId): int
    {
        return $this->records->countForUser($userId);
    }

    public function countForInduction(int $inductionId): int
    {
        return $this->records->countForInduction($inductionId);
    }

    /**
     * Permanently deletes every compliance record for a user, as part of a
     * cascade delete of that user. Renewal links within the batch are
     * cleared first so the self-referencing foreign key is satisfied.
     */
    public function deleteAllForUser(int $userId): void
    {
        $this->records->detachRenewalsForUser($userId);
        $this->records->deleteForUser($userId);
    }

    /**
     * Permanently deletes every compliance record for an induction, as part
     * of a cascade delete of that induction.
     */
    public function deleteAllForInduction(int $inductionId): void
    {
        $this->records->detachRenewalsForInduction($inductionId);
        $this->records->deleteForInduction($inductionId);
    }

    /**
     * Clears the exam attempt reference on any compliance record that cites
     * one of this exam's attempts, as part of a cascade delete of the exam.
     * The compliance record itself is kept as permanent history.
     */
    public function detachExamAttempts(int $examId): void
    {
        $this->records->detachExamAttempts($examId);
    }

    /**
     * Clears the exam attempt reference on any compliance record that cites
     * this single attempt, as part of deleting the attempt on its own. The
     * compliance record itself is kept as permanent history.
     */
    public function detachExamAttempt(int $attemptId): void
    {
        $this->records->detachExamAttempt($attemptId);
    }

    /**
     * Permanently deletes a single compliance record. This bypasses the
     * "permanent history" convention that revoke() preserves -- it is an
     * explicit administrative override, not part of the normal compliance
     * lifecycle.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function delete(int $id): array
    {
        $record = $this->records->find($id);
        if (!$record) {
            return ['success' => false, 'errors' => ['form' => 'Compliance record not found.']];
        }

        $this->records->detachRenewalOf($id);
        $this->records->delete($id);

        return ['success' => true, 'errors' => []];
    }
}
