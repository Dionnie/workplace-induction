<?php

declare(strict_types=1);

/**
 * @var string $email
 * @var string $setupUrl
 * @var int $expiryDays
 */
$appName = site_settings()['company_name'];
$subject = 'Set up your account';

echo <<<TEXT
Your {$appName} account is ready. You log in with this email address:

{$email}

To start, choose your password by visiting the link below:

{$setupUrl}

This link expires in {$expiryDays} days. After that, use "Forgot your password?" on the login page to get a new one.
TEXT;
