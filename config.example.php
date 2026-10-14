<?php

// Settings that differ per server. config.php is not in the repository:
// each server copies config.example.php to config.php and sets its own
// values there (docs/rules/deployment.md §3.4). Real credentials never go
// in config.example.php.

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
    // The public address, e.g. https://induction.example.com, with no path or
    // trailing slash. Links in emails and on certificates use it; cron-sent
    // emails have no request to take it from, so a live server must set it.
    // Blank uses the address of the current request.
    'url' => getenv('APP_URL') ?: null,
    // The organisation's timezone, as a PHP timezone name. PHP and MySQL
    // both use it, so dates agree whatever the server's own settings
    // (docs/rules/deployment.md §4). Existing times were recorded in it:
    // changing it shifts new times against old ones.
    'timezone' => getenv('APP_TIMEZONE') ?: 'Australia/Melbourne',
    // Deployment environment: 'production', 'staging', or 'development'.
    // Staging and development environments automatically discourage search engines
    // from indexing the site regardless of database settings, protecting staging
    // copies from accidental search engine indexing when databases are cloned.
    'environment' => getenv('APP_ENV') ?: 'production',
    // Optional explicit boolean override for search engine discouragement (null defers
    // to the environment check and the database setting).
    'discourage_search_engines' => getenv('APP_DISCOURAGE_SEARCH_ENGINES') !== false
        ? filter_var(getenv('APP_DISCOURAGE_SEARCH_ENGINES'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
        : null,
];

