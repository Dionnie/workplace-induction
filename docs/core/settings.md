# Settings

**Core.** What an administrator configures for this deployment: branding, colours, email senders and notifications. `/admin/settings/index.php`, one tab per section (`?tab=general|appearance|email|notifications`); each tab saves on its own.

Deployment settings that aren't editable in the app (database, site URL, registration on/off) are in `config/*.php` (`docs/rules/deployment.md` §3.4).

---

## 1. General

Stored in `site_settings` (one row, `id = 1`). Until it is first saved, the company name comes from `config/app.php`.

| Field | Used for |
| --- | --- |
| **Company Name** (required) | Page headers, the landing page, every email, certificates |
| **Company Logo** | Picked from the Media Library (`docs/core/media-library.md` §4); stored as a root-relative path |
| **Primary Email** | Contact address in email footers, and the From Email when none is set under Email. It doesn't receive administrator emails unless it is in their To list. Blank uses `admin@` the site's domain (`default_email()`), which the field shows as its placeholder. |

`site_settings()` returns these values anywhere in the code.

---

## 2. Appearance

The brand colours, stored in `site_settings.theme_*`:

- **Presets** (`App\Core\Theme::PRESETS`): Teal (default), Navy, Forest, Charcoal, Burgundy, Indigo.
- **Custom**: a Primary and an Accent hex colour. Each must have at least 4.5:1 contrast with white text, or the save is refused. Darker primary shades are derived automatically.

How the theme reaches every page, and the rules for using brand colours in CSS, are in `docs/rules/design.md` §3. Emails and printed certificates never use the theme.

---

## 3. Email

Every email goes to one of two **audiences**, and each has its own fields, stored in `email_settings` (`EmailSettingsService::FIELDS`):

| Audience | Gets | Sent to | Fields |
| --- | --- | --- | --- |
| **Inductee** | Account emails (verify email, password reset, account setup, for either user type) and inductee notifications | The account holder | From Name, From Email, CC, BCC |
| **Administrator** | Administrator notifications and reports | The **To** list | From Name, From Email, To, CC, BCC |

- A blank From Name uses the Company Name; a blank From Email uses the Primary Email.
- **To** is who receives administrator emails. It is never taken from user accounts: an administrator gets these emails only if their address is in the list. A blank To sends to `admin@` the site's domain (`default_email()`), which the field shows as its placeholder.
- To, CC and BCC are comma-separated addresses. CC and BCC are for people who should get copies but aren't users. Account emails are never copied.
- **Send Test Email** sends a sample with the audience's saved From, CC and BCC, as a real email is sent: an administrator sample to the To list, an inductee sample to you. It lists who it went to. Save changes first; the test doesn't use unsaved edits.

### How mail is sent

With PHP's `mail()` (`App\Core\Mailer`), through the server's own mail system. There are no SMTP settings; add SMTP only if the server can't deliver mail this way. Locally, Laragon catches every email in Mailpit (`http://localhost:8025`).

The From Email is also the envelope sender (`Return-Path`, passed to sendmail as `-f`): bounces go to it, and SPF checks its domain. Without it the server would use its own `user@hostname`.

Whether mail arrives, rather than landing in spam, depends on the sending domain:

- Set both From Emails to an address on a domain this server may send for, normally the site's own domain.
- On cPanel, check that SPF and DKIM are valid for that domain (cPanel → Email Deliverability).
- If the domain's email is hosted elsewhere (Microsoft 365, Google Workspace), this server isn't in its SPF record. Send from a subdomain the server handles (e.g. `induction.example.com`), or add the server to the SPF record.
- Then use **Send Test Email** for both audiences and check the spam folder.

---

## 4. Notifications

| Email | To | When | Setting |
| --- | --- | --- | --- |
| Induction completed | Inductee | Straight away, with a link to the certificate | Induction completed |
| Compliance expiring soon | Inductee | Once per compliance record, the set number of days before expiry | Compliance expiring soon, days before expiry |
| New registration | Administrator To list | Instant, or in the report | New registrations |
| Induction completed | Administrator To list | Instant, or in the report | Completed inductions |
| Expired compliance | Administrator To list | In the report; with Instant, as a daily list | Expired compliance |
| Verify email, password reset, account setup | The user | Always | None: account emails can't be turned off |

**Administrator delivery** (`admin_notification_frequency`):

- **Instant**: one email per registration or completion as it happens (from the hooks, `docs/rules/architecture.md` §5). Expired compliance has no single moment, so it still comes as a daily list.
- **Daily / Weekly / Monthly report** (default Weekly): one summary per period of the ticked sections. Periods start at midnight, on Monday, and on the 1st. A period with nothing to report is skipped without an email. `admin_report_last_sent_at` records where the last report ended, so nothing is reported twice.

Each email has a **Preview** on the tab, rendered with sample data (`admin/settings/email-preview.php`).

Notifications are side effects: a failed email is logged and never undoes the action that triggered it.

---

## 5. Scheduled Sending

Expiry reminders and administrator reports are sent by one script, run once a day by the server's scheduler:

```text
php /path/to/site/cron/send-notifications.php
```

- It is safe to run more often: each reminder is sent once (`compliance_records.expiry_reminder_sent_at`), and a report only when a new period has started.
- Set `url` in `config/app.php` so links in scheduled emails point at the live site; without a web request the script can't know the address. Setting up the cron job on cPanel: `docs/rules/deployment.md` §3.6.
- **Run Now** on the Notifications tab does the same from the browser.
- `cron/send-expiry-reminders.php` is an old name kept so existing schedules keep working.

---

## 6. Email Templates

`views/emails/<name>.php`, all wrapped by `views/emails/layout.php`. `Mailer::renderTemplate()` returns a subject, a plain-text body and the HTML version, and both are sent.

| Template | Email |
| --- | --- |
| `verify-email`, `password-reset`, `account-setup` | Account emails (`docs/core/users.md` §4, §7, §8) |
| `induction-completed-inductee`, `compliance-expiring` | Inductee notifications |
| `inductee-registered`, `induction-completed`, `admin-report` | Administrator notifications |

The email layout is neutral, never themed; its design rules are in `docs/rules/design.md` §3. A new template also gets sample data in `admin/settings/_email-samples.php` so it can be previewed.

---

## 7. Files

| File | Role |
| --- | --- |
| `app/Core/SiteSettingsService.php`, `SiteSettingsRepository.php`, `Theme.php` | General and Appearance |
| `app/Notification/EmailSettingsService.php`, `EmailSettingsRepository.php` | Email and Notifications settings |
| `app/Notification/NotificationService.php`, `listeners.php` | Sending notifications and reports |
| `app/Core/Mailer.php` | Sending mail, rendering templates |
| `admin/settings/*.php`, `views/admin/settings/index.php` | The page and its actions |
| `cron/send-notifications.php` | Daily scheduled sending |
