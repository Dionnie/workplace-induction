<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee
 */
$inducteeName = trim($inductee['first_name'] . ' ' . $inductee['last_name']);
$company = (string) ($inductee['company'] ?? '') !== '' ? $inductee['company'] : 'Not provided';
$appName = site_settings()['company_name'];
$usersUrl = public_url('/admin/users/index.php');
$subject = "New Inductee Registered: {$inducteeName}";

echo <<<TEXT
A new inductee has registered.

Name: {$inducteeName}
Email: {$inductee['email']}
Company: {$company}

View users: {$usersUrl}

This is an automated notification from {$appName}.
TEXT;
