<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Mailer;
use App\Notification\EmailSettingsService;
use DateTimeImmutable;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * Public self-registration. Always creates an Inductee account, with only
     * what authentication needs (email and password). The account starts
     * with an incomplete profile: after verifying their email and logging
     * in, the inductee completes their profile before starting an induction
     * (docs/core/auth.md #5, #12).
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function registerInductee(string $email, string $password, string $passwordConfirmation): array
    {
        $errors = $this->validateNewAccount($email, $password, $passwordConfirmation);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $this->users->create([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'inductee',
            'status' => 'active',
            'profile_completed' => false,
            'email_verification_token' => $token,
            'email_verification_expires_at' => $expiresAt,
        ]);

        $this->sendVerificationEmail($email, $token);

        do_action('inductee_registered', ['email' => $email]);

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
    private function validateNewAccount(string $email, string $password, string $passwordConfirmation): array
    {
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($this->users->emailExists($email)) {
            $errors['email'] = 'An account with this email already exists.';
        }

        return array_merge($errors, $this->validatePassword($password, $passwordConfirmation));
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
        $this->sendSystemEmail($email, Mailer::renderTemplate('verify-email', [
            'verificationUrl' => public_url('/verify-email.php?token=' . $token),
        ]));
    }

    private function sendPasswordResetEmail(string $email, string $token): void
    {
        $this->sendSystemEmail($email, Mailer::renderTemplate('password-reset', [
            'resetUrl' => public_url('/reset-password.php?token=' . $token),
        ]));
    }

    /**
     * Account emails go only to the account holder -- never cc/bcc, since
     * they contain private links.
     *
     * @param array{subject: string, body: string, html: string} $rendered
     */
    private function sendSystemEmail(string $to, array $rendered): void
    {
        $settings = (new EmailSettingsService())->get();
        Mailer::send($to, $rendered['subject'], $rendered['body'], [
            'from_name' => $settings['inductee_sender_name'],
            'from_email' => $settings['inductee_sender_email'],
            'html' => $rendered['html'],
        ]);
    }
}
