-- Migration: white-label branding settings and admin notification reports.
-- Additive only -- no existing rows or columns are changed or removed.
-- Back up the database and test on a copy before applying (see
-- docs/core/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-24-settings-branding.sql

CREATE TABLE site_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    company_name VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255) NULL,
    primary_email VARCHAR(255) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE email_settings
    ADD COLUMN admin_notification_frequency ENUM('instant', 'daily', 'weekly', 'monthly') NOT NULL DEFAULT 'weekly' AFTER bcc,
    ADD COLUMN notify_admin_on_registration TINYINT(1) NOT NULL DEFAULT 1 AFTER admin_notification_frequency,
    ADD COLUMN notify_admin_on_expired TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_inductee_on_expiry,
    ADD COLUMN admin_report_last_sent_at DATETIME NULL AFTER expiry_reminder_days;

-- Start admin reports from now, so accounts and records that already exist
-- (e.g. the legacy contacts import) are not reported as new activity.
UPDATE email_settings SET admin_report_last_sent_at = NOW() WHERE id = 1;
