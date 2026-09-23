<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Mailer;
use DateTimeImmutable;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * Public self-registration. Always creates an Inductee account.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function registerInductee(string $email, string $password, string $firstName, string $lastName): array
    {
        $errors = $this->validateNewAccount($email, $password, $firstName, $lastName);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $userId = $this->users->create([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'inductee',
            'status' => 'active',
            'email_verification_token' => $token,
            'email_verification_expires_at' => $expiresAt,
        ]);

        $this->users->createInducteeProfile($userId, $firstName, $lastName);
        $this->sendVerificationEmail($email, $token);

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array{success: bool, errors: array<string, string>, user?: array<string, mixed>}
     */
    public function attemptLogin(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'errors' => ['form' => 'Incorrect email or password.']];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'errors' => ['form' => 'Your account is not active. Contact your administrator.']];
        }

        if ($user['user_type'] === 'inductee' && $user['email_verified_at'] === null) {
            return ['success' => false, 'errors' => ['form' => 'Please verify your email address before logging in.']];
        }

        return ['success' => true, 'errors' => [], 'user' => $user];
    }

    public function verifyEmail(string $token): bool
    {
        $user = $this->users->findByVerificationToken($token);

        if (!$user || $user['email_verification_expires_at'] === null) {
            return false;
        }

        if (new DateTimeImmutable($user['email_verification_expires_at']) < new DateTimeImmutable()) {
            return false;
        }

        $this->users->markEmailVerified((int) $user['id']);
        return true;
    }

    public function requestPasswordReset(string $email): void
    {
        $user = $this->users->findByEmail($email);

        // Do not reveal whether the email exists.
        if (!$user) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

        $this->users->setPasswordResetToken((int) $user['id'], $token, $expiresAt);
        $this->sendPasswordResetEmail($user['email'], $token);
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function resetPassword(string $token, string $password, string $passwordConfirmation): array
    {
        $user = $this->users->findByResetToken($token);

        if (!$user || $user['password_reset_expires_at'] === null
            || new DateTimeImmutable($user['password_reset_expires_at']) < new DateTimeImmutable()) {
            return ['success' => false, 'errors' => ['form' => 'This password reset link is invalid or has expired.']];
        }

        $errors = $this->validatePassword($password, $passwordConfirmation);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array<string, string>
     */
    private function validateNewAccount(string $email, string $password, string $firstName, string $lastName): array
    {
        $errors = [];

        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if ($lastName === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($this->users->emailExists($email)) {
            $errors['email'] = 'An account with this email already exists.';
        }

        $errors = array_merge($errors, $this->validatePassword($password, $password));

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function validatePassword(string $password, string $passwordConfirmation): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        return $errors;
    }

    private function sendVerificationEmail(string $email, string $token): void
    {
        $link = $this->publicUrl('/verify-email.php?token=' . $token);
        Mailer::send(
            $email,
            'Verify your email address',
            "Please verify your email address by visiting the link below:\n\n{$link}\n\nThis link expires in 24 hours."
        );
    }

    private function sendPasswordResetEmail(string $email, string $token): void
    {
        $link = $this->publicUrl('/reset-password.php?token=' . $token);
        Mailer::send(
            $email,
            'Reset your password',
            "A password reset was requested for your account. Visit the link below to set a new password:\n\n{$link}\n\nIf you did not request this, you can ignore this email. This link expires in 1 hour."
        );
    }

    private function publicUrl(string $path): string
    {
        $scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }
}
