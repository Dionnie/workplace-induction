-- Migration: Add discourage_search_engines to site_settings
-- Date: 2026-10-14
--
-- Why: Allows administrators to discourage search engines from indexing the site
-- (Settings > General), matching WordPress's search engine visibility feature.
-- Adds noindex/nofollow meta tags, X-Robots-Tag HTTP headers, and blocks crawlers
-- in robots.txt. In staging/development mode (config.php), search engine indexing
-- can be automatically blocked regardless of database setting.
--
-- Existing rows: Existing site_settings row gets 0 (allow indexing), preserving
-- current public behavior.
--
-- Back up first:
--   mysqldump -uroot workplace-induction > backup-before-discourage-search-engines.sql
--
-- Apply with:
--   mysql -uroot workplace-induction < database/migrations/2026-10-14-discourage-search-engines.sql

ALTER TABLE site_settings
    ADD COLUMN discourage_search_engines TINYINT(1) NOT NULL DEFAULT 0 AFTER primary_email;
