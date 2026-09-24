<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee email; first_name, last_name and company once their profile is complete
 */
$inducteeName = trim(($inductee['first_name'] ?? '') . ' ' . ($inductee['last_name'] ?? ''));
$identity = $inducteeName !== '' ? $inducteeName : $inductee['email'];
$details = $inducteeName !== ''
    ? "Name: {$inducteeName}\nEmail: {$inductee['email']}\nCompany: " . ((string) ($inductee['company'] ?? '') !== '' ? $inductee['company'] : 'Not provided')
    : "Email: {$inductee['email']}\n\nThey will add their name and workplace details when they complete their profile, after verifying their email.";
$appName = site_settings()['company_name'];
$usersUrl = public_url('/admin/users/index.php');
$subject = "New Inductee Registered: {$identity}";

echo <<<TEXT
A new inductee has registered.

{$details}

View users: {$usersUrl}

This is an automated notification from {$appName}.
TEXT;
