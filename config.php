<?php

// Settings that differ per server. A server keeps its own copy with its own
// values (docs/rules/deployment.md §3.4); real credentials never go in the
// repository.

return [
    'database' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'workplace-induction',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    // The organisation's timezone, as a PHP timezone name. PHP and MySQL
    // both use it, so dates agree whatever the server's own settings
    // (docs/rules/deployment.md §4). Existing times were recorded in it:
    // changing it shifts new times against old ones.
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Singapore',
];
