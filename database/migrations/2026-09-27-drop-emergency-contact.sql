-- Migration: inductee profiles no longer have an emergency contact. Drops
-- inductee_profiles.emergency_contact_name and emergency_contact_phone; the
-- previous induction system never collected them, so imported profiles had
-- none. No other columns or rows change.
-- DESTRUCTIVE: any emergency contact already saved on the new site is deleted
-- with the columns (approved by the developer, docs/rules/data-protection.md §8).
-- Back up the database and test on a copy before applying (see
-- docs/rules/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-27-drop-emergency-contact.sql

ALTER TABLE inductee_profiles
    DROP COLUMN emergency_contact_name,
    DROP COLUMN emergency_contact_phone;
