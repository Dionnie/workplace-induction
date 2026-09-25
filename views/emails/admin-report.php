<?php

declare(strict_types=1);

/**
 * Periodic summary for administrators. A section is null when it is
 * switched off in settings, and an empty array when there was no activity.
 *
 * Two versions of the same report: $bodyHtml, with a count per section and
 * a table per section, for the HTML layout; and the plain text echoed at
 * the end, for clients without HTML. The HTML is old-school email-safe
 * markup: tables only, inline styles, and the font repeated on every cell
 * because Outlook for Windows doesn't inherit it.
 *
 * @var array{from: DateTimeImmutable, to: DateTimeImmutable, sections: array<string, ?array<int, array<string, mixed>>>} $report
 */
$appName = site_settings()['company_name'];

// The period in words: "Thursday 24 September 2026", "21–27 September 2026",
// "September 2026", "28 September – 4 October 2026". 'to' is exclusive (the
// start of the next period), so the last day is the one before it.
$from = $report['from'];
$lastDay = $report['to']->modify('-1 second');
if ($from->format('Y-m-d') === $lastDay->format('Y-m-d')) {
    $period = $from->format('l j F Y');
} elseif ($from->format('j H:i:s') === '1 00:00:00' && $from->modify('+1 month') == $report['to']) {
    $period = $from->format('F Y');
} elseif ($from->format('Y-m') === $lastDay->format('Y-m')) {
    $period = $from->format('j') . '–' . $lastDay->format('j F Y');
} elseif ($from->format('Y') === $lastDay->format('Y')) {
    $period = $from->format('j F') . ' – ' . $lastDay->format('j F Y');
} else {
    $period = $from->format('j F Y') . ' – ' . $lastDay->format('j F Y');
}
$subject = "{$appName} induction report: {$period}";

$maxRows = 50;
$complianceUrl = public_url('/admin/compliance/index.php');

// A new registration has no name until the inductee completes their profile.
$name = fn (array $row): string => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: (string) ($row['email'] ?? '');
// The inductee cell: name over email, or just the email (flagged, so it may break anywhere) until there is a name.
$who = fn (array $row): array => $name($row) === $row['email']
    ? [(string) $row['email'], '', true]
    : [$name($row), (string) $row['email']];
$nameAndEmail = fn (array $row): string => $name($row) . ($name($row) === $row['email'] ? '' : " ({$row['email']})");
$day = fn (string $value): string => date('j M Y', strtotime($value));
$time = fn (string $value): string => date('g:i a', strtotime($value));

// Each row as three table cells ([main line, second line, main line is an email?]) and as a line of text.
$sectionTypes = [
    'registrations' => [
        'title' => 'New registrations',
        'columns' => ['Inductee', 'Company', 'Registered'],
        'cells' => fn (array $row): array => [
            $who($row),
            [(string) ($row['company'] ?? '') ?: '—', ''],
            [$day($row['created_at']), $time($row['created_at'])],
        ],
        'line' => fn (array $row): string => '- ' . $nameAndEmail($row)
            . ((string) ($row['company'] ?? '') !== '' ? ", {$row['company']}" : '')
            . ' – registered ' . $day($row['created_at']) . ', ' . $time($row['created_at']),
    ],
    'completions' => [
        'title' => 'Completed inductions',
        'columns' => ['Inductee', 'Induction', 'Completed'],
        'cells' => fn (array $row): array => [
            $who($row),
            [(string) $row['induction_title'], 'Certificate ' . $row['certificate_number']],
            [$day($row['created_at']), $time($row['created_at'])],
        ],
        'line' => fn (array $row): string => '- ' . $name($row) . " – {$row['induction_title']}"
            . ' – completed ' . $day($row['created_at']) . ', ' . $time($row['created_at'])
            . " (Certificate {$row['certificate_number']})",
    ],
    'expired' => [
        'title' => 'Expired compliance',
        'columns' => ['Inductee', 'Induction', 'Expired'],
        'cells' => fn (array $row): array => [
            $who($row),
            [(string) $row['induction_title'], ''],
            [$day($row['expiry_date']), ''],
        ],
        'line' => fn (array $row): string => '- ' . $nameAndEmail($row) . " – {$row['induction_title']}"
            . ' – expired ' . $day($row['expiry_date']),
    ],
];

$counts = [];
$tables = [];
$textBlocks = [];
foreach ($sectionTypes as $key => $type) {
    $rows = $report['sections'][$key] ?? null;
    if ($rows === null) {
        continue;
    }

    $count = count($rows);
    $counts[] = [$type['title'], $count];
    if ($count === 0) {
        continue;
    }

    $shown = array_slice($rows, 0, $maxRows);
    $more = max(0, $count - $maxRows);
    $tables[] = ['type' => $type, 'count' => $count, 'rows' => array_map($type['cells'], $shown), 'more' => $more];

    $lines = array_merge(["{$type['title']} ({$count})"], array_map($type['line'], $shown));
    if ($more > 0) {
        $lines[] = "…and {$more} more.";
    }
    $textBlocks[] = implode("\n", $lines);
}

$font = "font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;";
$th = $font . ' padding:8px 12px 8px 0; border-bottom:1px solid #dee2e6; font-size:12px; font-weight:600; line-height:1.3; letter-spacing:0.04em; text-transform:uppercase; color:#595c5f;';
$td = $font . ' padding:10px 12px 10px 0; border-bottom:1px solid #e9ecef; font-size:14px; line-height:1.45; color:#212529; vertical-align:top;';
// Email addresses are one long word: they may break anywhere, so a table never widens a phone screen.
$email = 'word-break:break-all;';
$second = 'display:block; margin-top:2px; font-size:13px; color:#595c5f;';

ob_start();
?>
<p style="margin:0 0 4px; <?= $font ?> font-size:12px; font-weight:600; line-height:1.3; letter-spacing:0.06em; text-transform:uppercase; color:#595c5f;">Induction report</p>
<h1 style="margin:0 0 20px; <?= $font ?> font-size:22px; font-weight:600; line-height:1.3; color:#212529;"><?= e($period) ?></h1>

<?php if ($counts): ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <?php foreach ($counts as $i => [$title, $count]): ?>
                <?php if ($i > 0): ?>
                    <td width="8" style="width:8px; font-size:0; line-height:0;">&nbsp;</td>
                <?php endif; ?>
                <td width="<?= (int) floor(100 / count($counts)) ?>%" valign="top" bgcolor="#f8f9fa" style="padding:12px 8px; background-color:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; text-align:center; <?= $font ?>">
                    <span style="display:block; font-size:24px; font-weight:600; line-height:1.2; color:<?= $count > 0 ? '#212529' : '#595c5f' ?>;"><?= $count ?></span>
                    <span style="display:block; margin-top:2px; font-size:12px; line-height:1.35; color:#595c5f;"><?= e($title) ?></span>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>
<?php endif; ?>

<?php if (!$tables): ?>
    <p style="margin:24px 0 0; <?= $font ?> font-size:15px; color:#595c5f;">No activity in this period.</p>
<?php endif; ?>

<?php foreach ($tables as $table): ?>
    <h2 style="margin:28px 0 4px; <?= $font ?> font-size:16px; font-weight:600; line-height:1.3; color:#212529;">
        <?= e($table['type']['title']) ?> <span style="font-weight:400; color:#595c5f;">(<?= $table['count'] ?>)</span>
    </h2>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
        <tr>
            <?php foreach ($table['type']['columns'] as $c => $column): ?>
                <th align="<?= $c === 2 ? 'right' : 'left' ?>" style="<?= $th ?> text-align:<?= $c === 2 ? 'right; padding-right:0' : 'left' ?>;"><?= e($column) ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($table['rows'] as $cells): ?>
            <tr>
                <?php foreach ($cells as $c => $cell): ?>
                    <?php [$main, $sub] = $cell; ?>
                    <td align="<?= $c === 2 ? 'right' : 'left' ?>" valign="top" style="<?= $td ?><?= $c === 0 ? ' font-weight:600;' : '' ?><?= $c === 2 ? ' text-align:right; white-space:nowrap; padding-right:0;' : '' ?>">
                        <?php if (!empty($cell[2])): ?>
                            <span style="<?= $email ?>"><?= e($main) ?></span>
                        <?php else: ?>
                            <?= e($main) ?>
                        <?php endif; ?>
                        <?php if ($sub !== ''): ?>
                            <span style="<?= $second ?> font-weight:400;<?= $c === 0 ? ' ' . $email : '' ?>"><?= e($sub) ?></span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ($table['more'] > 0): ?>
            <tr>
                <td colspan="3" style="<?= $td ?> color:#595c5f;">…and <?= $table['more'] ?> more.</td>
            </tr>
        <?php endif; ?>
    </table>
<?php endforeach; ?>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0 0;">
    <tr>
        <td bgcolor="#212529" style="background-color:#212529; border-radius:6px;">
            <a href="<?= e($complianceUrl) ?>" style="display:inline-block; padding:12px 20px; <?= $font ?> font-size:15px; font-weight:600; line-height:1.2; color:#ffffff; text-decoration:none; border-radius:6px;">View Compliance Records</a>
        </td>
    </tr>
</table>

<p style="margin:24px 0 0; <?= $font ?> font-size:13px; line-height:1.5; color:#595c5f;">
    This is an automated report from <?= e($appName) ?>. Change how often it is sent in Settings &gt; Notifications.
</p>
<?php
$bodyHtml = (string) ob_get_clean();

$summaryText = implode("\n", array_map(fn (array $count): string => "{$count[0]}: {$count[1]}", $counts));
$sectionsText = $textBlocks ? "\n\n" . implode("\n\n", $textBlocks) : '';

echo <<<TEXT
Induction activity for {$period}.

{$summaryText}{$sectionsText}

View Compliance Records: {$complianceUrl}

This is an automated report from {$appName}. Change how often it is sent in Settings > Notifications.
TEXT;
