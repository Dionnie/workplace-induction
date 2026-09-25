<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Core\Mailer;
use App\Notification\EmailSettingsService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

// Sends a sample with the audience's saved sender, CC and BCC, exactly as a
// real email: an administrator sample to the saved To list, an inductee
// sample to the signed-in administrator (docs/core/settings.md §3).
$samples = [
    'inductee' => 'induction-completed-inductee',
    'admin' => 'induction-completed',
];

$audience = $_POST['audience'] ?? '';
if (!is_string($audience) || !isset($samples[$audience])) {
    http_response_code(400);
    exit('Unknown audience.');
}

$templates = require __DIR__ . '/_email-samples.php';
$rendered = Mailer::renderTemplate($samples[$audience], $templates[$samples[$audience]]());
$settings = (new EmailSettingsService())->get();
$options = EmailSettingsService::senderOptions($settings, $audience);
$to = $audience === 'admin' ? EmailSettingsService::adminTo($settings) : (string) Auth::user()['email'];

$sent = Mailer::send($to, '[Test] ' . $rendered['subject'], $rendered['body'], ['html' => $rendered['html']] + $options);

if ($sent) {
    $copies = '';
    foreach (['cc' => 'CC', 'bcc' => 'BCC'] as $key => $label) {
        if (!empty($options[$key])) {
            $copies .= ", {$label} {$options[$key]}";
        }
    }
    flash('success', "Test email sent to {$to}{$copies}. If it doesn't arrive, check your spam folder and the server's mail setup.");
} else {
    flash('error', 'The test email could not be sent. Check the server\'s mail setup (SMTP settings in php.ini).');
}

redirect('/admin/settings/index.php?tab=email');
