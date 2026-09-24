-- Migration: users.profile_completed. Additive only.
--
-- Whether the user has completed the profile their user type requires
-- (docs/core/auth.md #12). Inductees must complete it before starting an
-- induction; administrators have no application profile requirements.
--
-- New accounts start incomplete (default 0). Every existing account came
-- from the legacy system with its details, so all are marked complete. The
-- column is added with DEFAULT 1 to fill the existing rows, then its default
-- becomes 0 for new accounts. No UPDATE is run, so users.updated_at
-- (ON UPDATE CURRENT_TIMESTAMP) keeps its historical values.
--
-- Back up the database and test on a copy before applying (see
-- docs/core/data-protection.md):
--   mysql workplace-induction < database/migrations/2026-09-25-users-profile-completed.sql

ALTER TABLE users
    ADD COLUMN profile_completed TINYINT(1) NOT NULL DEFAULT 1 AFTER status;

ALTER TABLE users
    ALTER COLUMN profile_completed SET DEFAULT 0;
