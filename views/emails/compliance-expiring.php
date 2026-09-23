<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $inductee
 * @var array<string, mixed> $induction
 * @var array<string, mixed> $complianceRecord
 */
$inducteeName = trim($inductee['first_name'] . ' ' . $inductee['last_name']);
$appName = app_config()['name'];
$subject = "Your compliance for \"{$induction['title']}\" is expiring soon";

echo <<<TEXT
Hi {$inducteeName},

Your compliance for "{$induction['title']}" is due to expire on {$complianceRecord['expiry_date']}.

Please renew before this date to remain compliant.

This is an automated notification from {$appName}.
TEXT;
