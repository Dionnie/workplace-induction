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
        return self::user() !== null;
    }

    /**
     * The logged-in user, loaded once per request. If the account has been
     * deleted or is no longer active, the session ends here, so a suspended
     * user is logged out on their next page (docs/core/users.md §2).
     */
    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }

        if (self::$userCache === null || (int) self::$userCache['id'] !== $id) {
            $user = (new UserRepository())->findById($id);

            if (!$user || $user['status'] !== 'active') {
                self::logout();
                return null;
            }

            self::$userCache = $user;
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

    /**
     * Sends a guest to the login page, which returns them to this page after
     * logging in (redirect_to, docs/core/users.md §6).
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login.php' . redirect_to_query(self::currentPage()));
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
     * requires (users.profile_completed; docs/core/users.md §9).
     */
    public static function profileCompleted(): bool
    {
        return !empty(self::user()['profile_completed']);
    }

    /**
     * Sends a user with an incomplete profile to their profile page, for
     * actions that need a completed profile (e.g. starting an induction).
     * Completing the profile returns them to this page (redirect_to).
     */
    public static function requireCompletedProfile(string $profileUrl, string $message): void
    {
        self::requireLogin();

        if (!self::profileCompleted()) {
            flash('error', $message);
            redirect($profileUrl . redirect_to_query(self::currentPage()));
        }
    }

    /**
     * The logged-in user's entry point for their user type (docs/core/users.md §6).
     */
    public static function homeUrl(): string
    {
        return self::area() . 'index.php';
    }

    /**
     * Where to send the logged-in user after login or profile completion:
     * $redirectTo when it is a safe path inside their own area, otherwise
     * their home page. Another area's page would only answer "Forbidden".
     */
    public static function intendedUrl(mixed $redirectTo): string
    {
        $path = safe_redirect_path($redirectTo);
        return $path !== null && str_starts_with($path, self::area()) ? $path : self::homeUrl();
    }

    private static function area(): string
    {
        return (self::user()['user_type'] ?? null) === 'admin' ? '/admin/' : '/inductee/';
    }

    /**
     * The requested page, to return to later. GET only: a form submission
     * can't be repeated by a redirect.
     */
    private static function currentPage(): ?string
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' ? ($_SERVER['REQUEST_URI'] ?? null) : null;
    }
}
