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
}
