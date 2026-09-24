<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Core\Mailer;

Auth::requireRole('admin');

// Renders an email exactly as it would be sent, using sample data.
$templates = require __DIR__ . '/_email-samples.php';

$template = $_GET['template'] ?? '';
if (!is_string($template) || !isset($templates[$template])) {
    http_response_code(404);
    exit('Unknown email template.');
}

$html = Mailer::renderTemplate($template, $templates[$template]())['html'];

// Links open in a new tab rather than inside the preview frame.
echo str_replace('<head>', '<head><base target="_blank">', $html);
