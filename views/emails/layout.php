<?php

declare(strict_types=1);

/**
 * Shared HTML wrapper for every email: a white card with a subtle border and
 * shadow, holding the logo (when one is set) and the company name, then the
 * message; a small footer below the card. Neutral colours only, never the
 * Appearance theme, so any logo and any theme colour stay legible
 * (docs/core/ui-guidelines.md §3).
 *
 * Table layout and inline styles, since many email clients ignore
 * stylesheets. The column is fluid up to 600px; the <style> block only
 * tightens the spacing on phones in clients that support it, and the mso
 * comments give Outlook for Windows, which ignores max-width, a fixed 600px.
 * Where a client drops the shadow, the border still outlines the card.
 *
 * @var string $subject
 * @var string $bodyHtml Already escaped.
 * @var string $companyName
 * @var ?string $logoUrl Absolute URL.
 * @var ?string $primaryEmail
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title><?= e($subject) ?></title>
    <style>
        @media only screen and (max-width: 480px) {
            .email-outer { padding: 16px 8px !important; }
            .email-header, .email-body { padding-left: 16px !important; padding-right: 16px !important; }
            .email-company { font-size: 16px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f6; color:#212529; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; -webkit-text-size-adjust:100%; text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f5f6;">
    <tr>
        <td class="email-outer" align="center" style="padding:32px 16px;">
            <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                <tr>
                    <td>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; background-color:#ffffff; border:1px solid #e1e4e8; border-radius:8px; box-shadow:0 1px 3px rgba(0, 0, 0, 0.06);">
                            <tr>
                                <td class="email-header" style="padding:20px 24px; border-bottom:1px solid #e9ecef;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <?php if ($logoUrl): ?>
                                                <td style="padding:0 12px 0 0; vertical-align:middle;">
                                                    <img src="<?= e($logoUrl) ?>" alt="" height="40" style="display:block; height:40px; width:auto; border:0;">
                                                </td>
                                            <?php endif; ?>
                                            <td class="email-company" style="vertical-align:middle; font-size:18px; font-weight:600; line-height:1.3; color:#212529;">
                                                <?= e($companyName) ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-body" style="padding:24px; font-size:16px; line-height:1.6; color:#212529;">
                                    <?= $bodyHtml ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px 0; font-size:13px; line-height:1.5; color:#595c5f; text-align:center;">
                        Sent by <?= e($companyName) ?>
                        <?php if ($primaryEmail): ?>
                            &middot; <a href="mailto:<?= e($primaryEmail) ?>" style="color:#595c5f; text-decoration:underline;"><?= e($primaryEmail) ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <!--[if mso]></td></tr></table><![endif]-->
        </td>
    </tr>
</table>
</body>
</html>
