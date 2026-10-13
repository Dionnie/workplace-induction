-- Migration: moves the url in config/app.php into site_settings.site_url,
-- set under Settings > General (docs/core/settings.md §1). The rest of
-- config/app.php needs no column: the timezone moved to config.php, the
-- landing page text into index.php, and registration is always open.
--
-- Adds site_url, NULL for existing rows: blank means the address a web
-- request came in on, which is right for pages but not for cron-sent emails,
-- so set it before the next cron run. Nothing copies a live server's old
-- url across: note it before config/app.php is removed, and enter it under
-- Settings > General once this has run (docs/rules/deployment.md §7).
--
-- Back up the database and test on a copy before applying
-- (docs/rules/data-protection.md). Run once, in phpMyAdmin's SQL tab, or:
--   mysql -u USER -p DATABASE < database/migrations/2026-10-13-site-settings-app-config.sql
-- Running it again fails with "Duplicate column name" and changes nothing.

ALTER TABLE site_settings
    ADD COLUMN site_url VARCHAR(255) NULL AFTER primary_email;
