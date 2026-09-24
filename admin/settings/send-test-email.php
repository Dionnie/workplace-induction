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

// Sends a sample email to the signed-in administrator only, using the saved
// sender for the chosen audience. CC/BCC are left out so no one else is
// bothered by a test.
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
$options = EmailSettingsService::senderOptions((new EmailSettingsService())->get(), $audience);
$to = (string) Auth::user()['email'];

$sent = Mailer::send($to, '[Test] ' . $rendered['subject'], $rendered['body'], [
    'from_name' => $options['from_name'],
    'from_email' => $options['from_email'],
    'html' => $rendered['html'],
]);

if ($sent) {
    flash('success', "Test email sent to {$to}. If it doesn't arrive, check your spam folder and the server's mail setup.");
} else {
    flash('error', 'The test email could not be sent. Check the server\'s mail setup (SMTP settings in php.ini).');
}

redirect('/admin/settings/index.php?tab=email');
