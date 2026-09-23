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
}
