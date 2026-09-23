<?php

declare(strict_types=1);

namespace App\Inductee;

class InducteeProfileService
{
    private const PHONE_PATTERN = '/^[0-9+\-\s()]{6,30}$/';
    private const EMPLOYMENT_TYPES = [
        'Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other',
    ];

    private InducteeProfileRepository $profiles;

    public function __construct()
    {
        $this->profiles = new InducteeProfileRepository();
    }

    public function get(int $userId): ?array
    {
        return $this->profiles->find($userId);
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $userId, array $data): array
    {
        $errors = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->profiles->update($userId, $data);

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (trim($data['first_name'] ?? '') === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (trim($data['last_name'] ?? '') === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        if (($data['contact_number'] ?? '') !== '' && !preg_match(self::PHONE_PATTERN, $data['contact_number'])) {
            $errors['contact_number'] = 'Enter a valid contact number.';
        }

        if (($data['emergency_contact_phone'] ?? '') !== '' && !preg_match(self::PHONE_PATTERN, $data['emergency_contact_phone'])) {
            $errors['emergency_contact_phone'] = 'Enter a valid emergency contact number.';
        }

        if (($data['employment_type'] ?? '') !== '' && !in_array($data['employment_type'], self::EMPLOYMENT_TYPES, true)) {
            $errors['employment_type'] = 'Select a valid employment type.';
        }

        return $errors;
    }
}
