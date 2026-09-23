<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $complianceRecord
 */
$inducteeName = trim($inductee['first_name'] . ' ' . $inductee['last_name']);
$appName = app_config()['name'];
$subject = "Induction Completed: {$induction['title']}";

echo <<<TEXT
{$inducteeName} ({$inductee['email']}) has completed the induction "{$induction['title']}".

Certificate Number: {$complianceRecord['certificate_number']}
Issue Date: {$complianceRecord['issue_date']}
Expiry Date: {$complianceRecord['expiry_date']}

This is an automated notification from {$appName}.
TEXT;
