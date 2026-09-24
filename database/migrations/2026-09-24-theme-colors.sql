-- Migration: colour theme for white-label branding. Additive only.
-- Back up the database and test on a copy before applying (see
-- docs/core/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-24-theme-colors.sql

ALTER TABLE site_settings
    ADD COLUMN theme_preset VARCHAR(30) NOT NULL DEFAULT 'teal' AFTER primary_email,
    ADD COLUMN theme_primary CHAR(7) NULL AFTER theme_preset,
    ADD COLUMN theme_accent CHAR(7) NULL AFTER theme_primary;
