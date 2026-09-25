-- Migration: administrator emails go to a To list chosen in Settings > Email,
-- instead of to every active administrator account. Adds email_settings.admin_to
-- (comma-separated, like admin_cc) and fills it with the emails of the
-- administrators active now, so the same people keep getting them until
-- someone edits the list. A blank list sends to admin@ the site's domain
-- (docs/core/settings.md §3). No other rows or columns change.
-- If those emails don't fit in 500 characters the UPDATE fails and admin_to
-- stays blank: set it in Settings > Email.
-- Back up the database and test on a copy before applying (see
-- docs/rules/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-25-email-admin-to.sql

ALTER TABLE email_settings
    ADD COLUMN admin_to VARCHAR(500) NULL AFTER admin_sender_email;

UPDATE email_settings
SET admin_to = (
        SELECT GROUP_CONCAT(email ORDER BY id SEPARATOR ', ')
        FROM users
        WHERE user_type = 'admin' AND status = 'active'
    ),
    updated_at = updated_at
WHERE id = 1 AND admin_to IS NULL;
