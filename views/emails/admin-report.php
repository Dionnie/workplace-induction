<?php

declare(strict_types=1);

/**
 * Periodic summary for administrators. A section is null when it is
 * switched off in settings, and an empty array when there was no activity.
 *
 * @var array{from: DateTimeImmutable, to: DateTimeImmutable, sections: array<string, ?array<int, array<string, mixed>>>} $report
 */
$appName = site_settings()['company_name'];
// 'to' is exclusive (the start of the next period), so show the day before.
$lastDay = $report['to']->modify('-1 second');
$period = $report['from']->format('Y-m-d') === $lastDay->format('Y-m-d')
    ? $lastDay->format('j M Y')
    : $report['from']->format('j M Y') . ' – ' . $lastDay->format('j M Y');
$subject = "{$appName} induction report: {$period}";

$maxRows = 50;
$titles = [
    'registrations' => 'New registrations',
    'completions' => 'Completed inductions',
    'expired' => 'Expired compliance',
];

$name = fn (array $row): string => trim($row['first_name'] . ' ' . $row['last_name']);
$lineFor = [
    'registrations' => fn (array $row): string => '- ' . $name($row) . " ({$row['email']})"
        . ((string) ($row['company'] ?? '') !== '' ? ", {$row['company']}" : '')
        . ' – registered ' . date('j M Y', strtotime($row['created_at'])),
    'completions' => fn (array $row): string => '- ' . $name($row) . " – {$row['induction_title']}"
        . ' – completed ' . date('j M Y', strtotime($row['created_at']))
        . " (Certificate {$row['certificate_number']})",
    'expired' => fn (array $row): string => '- ' . $name($row) . " ({$row['email']}) – {$row['induction_title']}"
        . ' – expired ' . date('j M Y', strtotime($row['expiry_date'])),
];

$summary = [];
$sections = '';
foreach ($titles as $key => $title) {
    $rows = $report['sections'][$key] ?? null;
    if ($rows === null) {
        continue;
    }

    $count = count($rows);
    $summary[] = "{$title}: {$count}";

    if ($count === 0) {
        continue;
    }

    $sections .= "\n\n{$title} ({$count})\n";
    foreach (array_slice($rows, 0, $maxRows) as $row) {
        $sections .= $lineFor[$key]($row) . "\n";
    }
    if ($count > $maxRows) {
        $sections .= '…and ' . ($count - $maxRows) . " more.\n";
    }
}

$summaryText = implode("\n", $summary);
$sections = rtrim($sections);
$complianceUrl = public_url('/admin/compliance/index.php');

echo <<<TEXT
Induction activity for {$period}.

{$summaryText}{$sections}

View Compliance Records: {$complianceUrl}

This is an automated report from {$appName}. Change how often it is sent in Settings > Notifications.
TEXT;
