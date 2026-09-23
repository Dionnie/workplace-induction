<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $complianceRecord
 * @var string $certificateUrl
 */
$inducteeName = trim($inductee['first_name'] . ' ' . $inductee['last_name']);
$appName = app_config()['name'];
$subject = "You've completed: {$induction['title']}";

echo <<<TEXT
Hi {$inducteeName},

You have successfully completed the induction "{$induction['title']}".

Certificate Number: {$complianceRecord['certificate_number']}
Issue Date: {$complianceRecord['issue_date']}
Expiry Date: {$complianceRecord['expiry_date']}

View your certificate: {$certificateUrl}

This is an automated notification from {$appName}.
TEXT;
