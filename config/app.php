<?php

return [
    'name' => 'Induction System',
    'tagline' => 'Induction and compliance management',
    'description' => 'Complete required inductions, review your compliance, and access your certificates in one place.',
    'contact_email' => null,
    // Public base URL (e.g. https://induction.example.com) used for links and
    // images in emails. Required when emails are sent from cron; when null the
    // current request's host is used.
    'url' => getenv('APP_URL') ?: null,
    // The organisation's timezone, as a PHP timezone name. PHP and MySQL
    // both use it, so dates agree whatever the server's own settings
    // (docs/rules/deployment.md §4). Existing times were recorded in it:
    // changing it shifts new times against old ones.
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Singapore',
    'registration_enabled' => true,
];
