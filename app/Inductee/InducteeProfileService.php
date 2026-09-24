<?php

declare(strict_types=1);

namespace App\Inductee;

use App\Core\Auth\UserRepository;

class InducteeProfileService
{
    /**
     * What an inductee must provide before starting an induction
     * (docs/core/auth.md #12): who they are (the name goes on their
     * certificates), who they work for, and how to reach them or their
     * emergency contact on site. Job position is optional.
     */
    public const REQUIRED_FIELDS = [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'company' => 'Company',
        'employment_type' => 'Employment type',
        'contact_number' => 'Contact number',
        'emergency_contact_name' => 'Emergency contact name',
        'emergency_contact_phone' => 'Emergency contact phone number',
    ];

    public const EMPLOYMENT_TYPES = [
        'Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other',
    ];

    private const PHONE_PATTERN = '/^[0-9+\-\s()]{6,30}$/';

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
     * Saves the profile. A save needs every required field, so a successful
     * one also marks the profile complete.
     *
     * @return array{success: bool, errors: array<string, string>, completed_now?: bool}
     */
    public function update(int $userId, array $data): array
    {
        $errors = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $wasCompleted = !empty($this->profiles->find($userId)['profile_completed']);

        $this->profiles->save($userId, $data);
        (new UserRepository())->setProfileCompleted($userId, true);

        return ['success' => true, 'errors' => [], 'completed_now' => !$wasCompleted];
    }

    /**
     * Labels of the required fields the saved profile is missing; empty when
     * the profile has everything it needs.
     *
     * @return array<int, string>
     */
    public function missingFields(int $userId): array
    {
        $profile = $this->profiles->find($userId) ?? [];

        $missing = [];
        foreach (self::REQUIRED_FIELDS as $field => $label) {
            if (trim((string) ($profile[$field] ?? '')) === '') {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field => $label) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $errors[$field] = $field === 'employment_type' ? 'Select your employment type.' : "{$label} is required.";
            }
        }

        if (!isset($errors['employment_type']) && !in_array($data['employment_type'], self::EMPLOYMENT_TYPES, true)) {
            $errors['employment_type'] = 'Select a valid employment type.';
        }

        if (!isset($errors['contact_number']) && !preg_match(self::PHONE_PATTERN, $data['contact_number'])) {
            $errors['contact_number'] = 'Enter a valid contact number.';
        }

        if (!isset($errors['emergency_contact_phone']) && !preg_match(self::PHONE_PATTERN, $data['emergency_contact_phone'])) {
            $errors['emergency_contact_phone'] = 'Enter a valid emergency contact number.';
        }

        return $errors;
    }
}
