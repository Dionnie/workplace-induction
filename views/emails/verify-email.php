<?php

declare(strict_types=1);

/**
 * @var string $verificationUrl
 */
$appName = site_settings()['company_name'];
$subject = 'Verify your email address';

echo <<<TEXT
Welcome to {$appName}.

Please verify your email address by visiting the link below:

{$verificationUrl}

This link expires in 24 hours.
TEXT;
