<?php

return [
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',

    'host' => getenv('MAIL_HOST') ?: '127.0.0.1',
    'port' => getenv('MAIL_PORT') ?: '587',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',

    'username' => getenv('MAIL_USERNAME') ?: '',
    'password' => getenv('MAIL_PASSWORD') ?: '',
];
