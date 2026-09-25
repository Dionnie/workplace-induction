<?php

declare(strict_types=1);

namespace App\Core;

class Mailer
{
    /**
     * Sends plain text, or multipart/alternative (text + HTML) when an
     * 'html' option is given so clients without HTML support still get the
     * readable text version.
     *
     * @param array{from_name?: ?string, from_email?: ?string, cc?: ?string, bcc?: ?string, html?: ?string} $options
     */
    public static function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $site = site_settings();

        $fromEmail = ($options['from_email'] ?? null) ?: ($site['primary_email'] ?: default_email());
        $fromName = ($options['from_name'] ?? null) ?: $site['company_name'];

        $headers = [
            'From' => self::address((string) $fromName, (string) $fromEmail),
            'MIME-Version' => '1.0',
        ];

        if (!empty($options['cc'])) {
            $headers['Cc'] = $options['cc'];
        }

        if (!empty($options['bcc'])) {
            $headers['Bcc'] = $options['bcc'];
        }

        if (!empty($options['html'])) {
            // Line breaks become CRLF first; quoted-printable would encode a
            // bare \n as "=0A", running the text part into one long line.
            $encode = fn (string $text): string => quoted_printable_encode(preg_replace('/\r?\n/', "\r\n", $text));
            $boundary = 'b' . bin2hex(random_bytes(12));
            $headers['Content-Type'] = "multipart/alternative; boundary=\"{$boundary}\"";
            $body = "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
                . $encode($body) . "\r\n\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
                . $encode($options['html']) . "\r\n\r\n"
                . "--{$boundary}--";
        } else {
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
        }

        $headerString = '';
        foreach ($headers as $name => $value) {
            $headerString .= "{$name}: {$value}\r\n";
        }

        // The envelope sender (Return-Path): where bounces go and the domain
        // SPF checks. Without -f the server uses its own user@hostname
        // (docs/core/settings.md §3). mail() puts it on the sendmail command
        // line, so only a plain address is passed.
        $envelope = preg_match('/^[A-Za-z0-9._+-]+@[A-Za-z0-9.-]+$/', (string) $fromEmail) ? '-f' . $fromEmail : '';

        return mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, $headerString, $envelope);
    }

    /**
     * A From address: "Name" <email>. The name is quoted, so a comma
     * ("Stark Food Systems, HSE") doesn't split it into two addresses;
     * MIME-encoded when it isn't plain ASCII; and stripped of line breaks,
     * so it can never add a header.
     */
    private static function address(string $name, string $email): string
    {
        $name = trim(str_replace(["\r", "\n"], ' ', $name));
        if ($name === '') {
            return $email;
        }

        $name = preg_match('/[^\x20-\x7E]/', $name)
            ? mb_encode_mimeheader($name, 'UTF-8', 'Q')
            : '"' . addcslashes($name, '"\\') . '"';

        return "{$name} <{$email}>";
    }

    /**
     * Renders a plain-text email template from views/emails/. The template
     * sets $subject and echoes the body, the same way a page view sets
     * $pageTitle and renders its content. The text is also wrapped in the
     * HTML layout (views/emails/layout.php): logo and company name, neutral
     * white, never the Appearance theme. A template that needs more than
     * text (tables) also sets $bodyHtml, escaped, for the layout to use
     * instead; the text stays the plain-text part.
     *
     * @param array<string, mixed> $data
     * @return array{subject: string, body: string, html: string}
     */
    public static function renderTemplate(string $template, array $data): array
    {
        $path = dirname(__DIR__, 2) . '/views/emails/' . $template . '.php';

        extract($data);
        $bodyHtml = null;
        ob_start();
        require $path;
        $body = trim((string) ob_get_clean());
        $subject = $subject ?? '';

        return ['subject' => $subject, 'body' => $body, 'html' => self::renderLayout($subject, $body, $bodyHtml)];
    }

    private static function renderLayout(string $subject, string $body, ?string $bodyHtml = null): string
    {
        $site = site_settings();
        $companyName = $site['company_name'];
        $logoUrl = $site['logo_url'] ? public_url($site['logo_url']) : null;
        $primaryEmail = $site['primary_email'] ?: default_email();

        // Escape first, then turn bare URLs into links and keep line breaks.
        // Long links (verification tokens) wrap instead of widening a phone screen.
        $bodyHtml ??= nl2br(preg_replace(
            '~(https?://[^\s<]+)~',
            '<a href="$1" style="color:#212529; text-decoration:underline; word-break:break-all;">$1</a>',
            e($body)
        ));

        ob_start();
        require dirname(__DIR__, 2) . '/views/emails/layout.php';
        return (string) ob_get_clean();
    }
}
