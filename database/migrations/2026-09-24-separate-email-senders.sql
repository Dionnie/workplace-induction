-- Migration: separate sender details for inductee emails and administrator
-- emails. The existing sender/cc/bcc columns are renamed (values kept) to
-- the inductee_* set, and copied into the new admin_* set so nothing changes
-- until an administrator edits them. Sender names become optional -- blank
-- falls back to the company name.
-- Back up the database and test on a copy before applying (see
-- docs/core/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-24-separate-email-senders.sql

ALTER TABLE email_settings
    CHANGE COLUMN sender_name inductee_sender_name VARCHAR(150) NULL,
    CHANGE COLUMN sender_email inductee_sender_email VARCHAR(255) NULL,
    CHANGE COLUMN cc inductee_cc VARCHAR(500) NULL,
    CHANGE COLUMN bcc inductee_bcc VARCHAR(500) NULL,
    ADD COLUMN admin_sender_name VARCHAR(150) NULL AFTER inductee_bcc,
    ADD COLUMN admin_sender_email VARCHAR(255) NULL AFTER admin_sender_name,
    ADD COLUMN admin_cc VARCHAR(500) NULL AFTER admin_sender_email,
    ADD COLUMN admin_bcc VARCHAR(500) NULL AFTER admin_cc;

UPDATE email_settings
SET admin_sender_name = inductee_sender_name,
    admin_sender_email = inductee_sender_email,
    admin_cc = inductee_cc,
    admin_bcc = inductee_bcc;
