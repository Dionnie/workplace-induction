<?php

declare(strict_types=1);

namespace App\Core;

class Mailer
{
    /**
     * @param array{from_name?: ?string, from_email?: ?string, cc?: ?string, bcc?: ?string} $options
     */
    public static function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';

        $fromEmail = $options['from_email'] ?? $config['contact_email'] ?? 'no-reply@localhost';
        $fromName = $options['from_name'] ?? $config['name'] ?? 'Induction System';

        $headers = [
            'From' => sprintf('%s <%s>', $fromName, $fromEmail),
            'Content-Type' => 'text/plain; charset=UTF-8',
        ];

        if (!empty($options['cc'])) {
            $headers['Cc'] = $options['cc'];
        }

        if (!empty($options['bcc'])) {
            $headers['Bcc'] = $options['bcc'];
        }

        $headerString = '';
        foreach ($headers as $name => $value) {
            $headerString .= "{$name}: {$value}\r\n";
        }

        return mail($to, $subject, $body, $headerString);
    }

    /**
     * Renders a plain-text email template from views/emails/. The template
     * sets $subject and echoes the body, the same way a page view sets
     * $pageTitle and renders its content.
     *
     * @param array<string, mixed> $data
     * @return array{subject: string, body: string}
     */
    public static function renderTemplate(string $template, array $data): array
    {
        $path = dirname(__DIR__, 2) . '/views/emails/' . $template . '.php';

        extract($data);
        ob_start();
        require $path;
        $body = trim((string) ob_get_clean());

        return ['subject' => $subject ?? '', 'body' => $body];
    }
}
