<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/app/Core/helpers.php';
require __DIR__ . '/app/Core/hooks.php';
require __DIR__ . '/app/Notification/listeners.php';

// Before any date is made or the database connects (App\Core\Database uses it too).
if (!date_default_timezone_set((string) (app_config()['timezone'] ?? ''))) {
    throw new RuntimeException('config/app.php: "timezone" must be a PHP timezone name, e.g. Asia/Singapore.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
