<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Auth\UserRepository;

class Auth
{
    private static ?array $userCache = null;

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }

        if (self::$userCache === null || (int) self::$userCache['id'] !== $id) {
            self::$userCache = (new UserRepository())->findById($id);
        }

        return self::$userCache;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        self::$userCache = null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        self::$userCache = null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login.php');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        $user = self::user();

        if (!$user || $user['user_type'] !== $role) {
            http_response_code(403);
            exit('Forbidden.');
        }
    }

    /**
     * Whether the logged-in user has completed the profile their user type
     * requires (users.profile_completed; docs/core/auth.md #12).
     */
    public static function profileCompleted(): bool
    {
        return !empty(self::user()['profile_completed']);
    }

    /**
     * Sends a user with an incomplete profile to their profile page, for
     * actions that need a completed profile (e.g. starting an induction).
     */
    public static function requireCompletedProfile(string $profileUrl, string $message): void
    {
        self::requireLogin();

        if (!self::profileCompleted()) {
            flash('error', $message);
            redirect($profileUrl);
        }
    }
}
