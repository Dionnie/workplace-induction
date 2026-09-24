<?php

declare(strict_types=1);

/**
 * Shared branded HTML wrapper for every email. Table layout and inline
 * styles only, since email clients ignore stylesheets.
 *
 * @var string $subject
 * @var string $bodyHtml Already escaped.
 * @var string $companyName
 * @var ?string $logoUrl Absolute URL.
 * @var ?string $primaryEmail
 * @var string $brandDark Header colour (primary-900).
 * @var string $brandColor Link colour (primary-700).
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($subject) ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f8f6f3; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color:#212529;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f6f3;">
    <tr>
        <td align="center" style="padding:24px 12px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
                <tr>
                    <td style="background-color:<?= e($brandDark) ?>; padding:16px 24px; border-radius:6px 6px 0 0;">
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($companyName) ?>" height="32" style="display:block; height:32px; width:auto; border:0;">
                        <?php else: ?>
                            <span style="color:#ffffff; font-size:18px; font-weight:600;"><?= e($companyName) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#ffffff; padding:24px; font-size:15px; line-height:1.6; border:1px solid #dee2e6; border-top:0; border-radius:0 0 6px 6px;">
                        <?= $bodyHtml ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px; font-size:12px; color:#6c757d; text-align:center;">
                        Sent by <?= e($companyName) ?>
                        <?php if ($primaryEmail): ?>
                            &middot; <a href="mailto:<?= e($primaryEmail) ?>" style="color:#6c757d;"><?= e($primaryEmail) ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
