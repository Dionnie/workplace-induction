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
    'registration_enabled' => true,
];
