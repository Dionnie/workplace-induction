<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // NOW(), CURDATE() and CURRENT_TIMESTAMP in the app's timezone
            // (config/app.php), the same as PHP's dates. An offset rather
            // than a zone name: shared hosts often lack MySQL's zone tables.
            self::$connection->exec("SET time_zone = '" . (new \DateTimeImmutable())->format('P') . "'");
        }

        return self::$connection;
    }

    /**
     * The database's current time. It agrees with PHP's (see connection());
     * use it when comparing against DATETIME columns the database filled.
     */
    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string) self::connection()->query('SELECT NOW()')->fetchColumn());
    }
}
