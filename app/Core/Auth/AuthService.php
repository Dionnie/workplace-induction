<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Mailer;
use App\Notification\EmailSettingsService;
use DateTimeImmutable;

class AuthService
{
    /** How long an account setup link lasts; a password reset link lasts 1 hour. */
    public const SETUP_LINK_DAYS = 7;

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
     * Emails the user a link to choose their own password, for an account an
     * administrator created (docs/core/auth.md #5). It is a password reset
     * link that lasts SETUP_LINK_DAYS, since the user isn't expecting it. A
     * current password keeps working until the link is used.
     *
     * @return bool Whether the email was handed to the mail system.
     */
    public function sendAccountSetupEmail(int $userId): bool
    {
        $user = $this->users->findById($userId);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+' . self::SETUP_LINK_DAYS . ' days'))->format('Y-m-d H:i:s');

        $this->users->setPasswordResetToken($userId, $token, $expiresAt);

        return $this->sendSystemEmail($user['email'], Mailer::renderTemplate('account-setup', [
            'email' => $user['email'],
            'setupUrl' => public_url('/reset-password.php?token=' . $token),
            'expiryDays' => self::SETUP_LINK_DAYS,
        ]));
    }

    /**
     * Whether a reset or account setup link can still be used, so the page
     * can say so before the user types a password.
     */
    public function isValidResetToken(string $token): bool
    {
        return $this->findByValidResetToken($token) !== null;
    }

    /**
     * Sets the password from a reset or account setup link.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function resetPassword(string $token, string $password, string $passwordConfirmation): array
    {
        $user = $this->findByValidResetToken($token);
        if (!$user) {
            return ['success' => false, 'errors' => ['form' => 'This link is invalid or has expired.']];
        }

        $errors = $this->validatePassword($password, $passwordConfirmation);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));

        // The link was sent to their address, so using it proves they own it (#6).
        if ($user['email_verified_at'] === null) {
            $this->users->markEmailVerified((int) $user['id']);
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * The user a reset token belongs to, while it is unused and unexpired.
     */
    private function findByValidResetToken(string $token): ?array
    {
        $user = $token !== '' ? $this->users->findByResetToken($token) : null;

        if (!$user || $user['password_reset_expires_at'] === null
            || new DateTimeImmutable($user['password_reset_expires_at']) < new DateTimeImmutable()) {
            return null;
        }

        return $user;
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
    private function sendSystemEmail(string $to, array $rendered): bool
    {
        $settings = (new EmailSettingsService())->get();
        return Mailer::send($to, $rendered['subject'], $rendered['body'], [
            'from_name' => $settings['inductee_sender_name'],
            'from_email' => $settings['inductee_sender_email'],
            'html' => $rendered['html'],
        ]);
    }
}
