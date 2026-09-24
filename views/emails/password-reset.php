<?php

declare(strict_types=1);

/**
 * @var string $resetUrl
 */
$subject = 'Reset your password';

echo <<<TEXT
A password reset was requested for your account. Visit the link below to set a new password:

{$resetUrl}

If you did not request this, you can ignore this email. This link expires in 1 hour.
TEXT;
