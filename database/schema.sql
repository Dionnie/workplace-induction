-- Workplace Induction System
-- Database schema
-- Recreate the database structure from empty with:
--   mysql < database/schema.sql

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'inductee') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    -- 1 once the user has completed the profile their user type requires
    -- (docs/core/users.md §9); inductees can't start inductions until then.
    profile_completed TINYINT(1) NOT NULL DEFAULT 0,
    email_verified_at DATETIME NULL,
    email_verification_token VARCHAR(64) NULL,
    email_verification_expires_at DATETIME NULL,
    password_reset_token VARCHAR(64) NULL,
    password_reset_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_email_verification_token (email_verification_token),
    KEY idx_users_password_reset_token (password_reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_profiles (
    user_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_admin_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The inductee's own details. Registration and Add User create only the
-- users row; this row is created when the inductee first saves their
-- profile. What a save requires is decided by
-- InducteeProfileService::REQUIRED_FIELDS (docs/core/users.md §9), not
-- here: the other columns are nullable because job_position is optional
-- and profiles imported from the previous system may lack some details.
CREATE TABLE inductee_profiles (
    user_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(30) NULL,
    job_position VARCHAR(150) NULL,
    company VARCHAR(150) NULL,
    employment_type ENUM('Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other') NULL,
    emergency_contact_name VARCHAR(150) NULL,
    emergency_contact_phone VARCHAR(30) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_inductee_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE exams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    pass_percentage TINYINT UNSIGNED NOT NULL,
    exam_blocks JSON NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inductions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT NULL,
    exam_id INT UNSIGNED NULL,
    validity_months SMALLINT UNSIGNED NOT NULL,
    content_blocks JSON NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inductions_code (code),
    CONSTRAINT fk_inductions_exam FOREIGN KEY (exam_id) REFERENCES exams (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- An exam attempt only exists when the induction has an exam. Never modify
-- score/result after submission -- it is retained as historical evidence.
CREATE TABLE exam_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    induction_id INT UNSIGNED NOT NULL,
    exam_id INT UNSIGNED NOT NULL,
    score SMALLINT UNSIGNED NOT NULL,
    total_score SMALLINT UNSIGNED NOT NULL,
    result ENUM('passed', 'failed') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exam_attempts_user (user_id),
    KEY idx_exam_attempts_induction (induction_id),
    CONSTRAINT fk_exam_attempts_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_exam_attempts_induction FOREIGN KEY (induction_id) REFERENCES inductions (id),
    CONSTRAINT fk_exam_attempts_exam FOREIGN KEY (exam_id) REFERENCES exams (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The authoritative record that a user has successfully completed an induction
-- requirement. Renewal never overwrites a record -- it creates a new one and
-- links back via renewed_from_id, preserving permanent compliance history.
-- legacy_id holds the originating session id from a prior induction system,
-- for records migrated from elsewhere; NULL for records issued by this app.
CREATE TABLE compliance_records (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    induction_id INT UNSIGNED NOT NULL,
    exam_attempt_id INT UNSIGNED NULL,
    verification_token VARCHAR(64) NOT NULL,
    certificate_number VARCHAR(50) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('active', 'expired', 'superseded', 'revoked') NOT NULL DEFAULT 'active',
    renewed_from_id INT UNSIGNED NULL,
    legacy_id VARCHAR(64) NULL,
    expiry_reminder_sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_compliance_verification_token (verification_token),
    UNIQUE KEY uq_compliance_certificate_number (certificate_number),
    UNIQUE KEY uq_compliance_legacy_id (legacy_id),
    KEY idx_compliance_user_induction (user_id, induction_id),
    CONSTRAINT fk_compliance_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_compliance_induction FOREIGN KEY (induction_id) REFERENCES inductions (id),
    CONSTRAINT fk_compliance_exam_attempt FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts (id),
    CONSTRAINT fk_compliance_renewed_from FOREIGN KEY (renewed_from_id) REFERENCES compliance_records (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Single-row settings record (id is always 1) holding the white-label
-- branding shown in page headers and emails. Falls back to config/app.php
-- when no row exists. logo_url is a root-relative media library path.
-- theme_preset is a key of App\Core\Theme::PRESETS, or 'custom' to use
-- theme_primary/theme_accent (#rrggbb).
CREATE TABLE site_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    company_name VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255) NULL,
    primary_email VARCHAR(255) NULL,
    theme_preset VARCHAR(30) NOT NULL DEFAULT 'teal',
    theme_primary CHAR(7) NULL,
    theme_accent CHAR(7) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Single-row settings record (id is always 1) controlling how the
-- application's notification emails are sent and who else receives them.
-- Emails to inductees (including account emails) use the inductee_* sender;
-- emails to administrators use the admin_* sender. A blank sender name or
-- email falls back to site_settings' company name / primary email.
-- *_cc/*_bcc are comma-separated address lists for "other concerned people"
-- who are not necessarily system users.
-- admin_notification_frequency controls whether administrators get one email
-- per event ('instant') or one summary report per period; inductee emails are
-- always sent immediately. admin_report_last_sent_at marks the end of the last
-- reported period.
CREATE TABLE email_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    inductee_sender_name VARCHAR(150) NULL,
    inductee_sender_email VARCHAR(255) NULL,
    inductee_cc VARCHAR(500) NULL,
    inductee_bcc VARCHAR(500) NULL,
    admin_sender_name VARCHAR(150) NULL,
    admin_sender_email VARCHAR(255) NULL,
    admin_cc VARCHAR(500) NULL,
    admin_bcc VARCHAR(500) NULL,
    admin_notification_frequency ENUM('instant', 'daily', 'weekly', 'monthly') NOT NULL DEFAULT 'weekly',
    notify_admin_on_registration TINYINT(1) NOT NULL DEFAULT 1,
    notify_admin_on_completion TINYINT(1) NOT NULL DEFAULT 1,
    notify_inductee_on_completion TINYINT(1) NOT NULL DEFAULT 1,
    notify_inductee_on_expiry TINYINT(1) NOT NULL DEFAULT 1,
    notify_admin_on_expired TINYINT(1) NOT NULL DEFAULT 1,
    expiry_reminder_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    admin_report_last_sent_at DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Flat list of labels used to group media library files. Sorted
-- alphabetically in the UI; a media item belongs to at most one category
-- (or none) -- no nesting.
CREATE TABLE media_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_media_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploaded media library files (images only for now). filename is the
-- randomly generated name stored on disk under assets/uploads/media-library;
-- original_filename is kept only for display. Deleting a category does not
-- delete its files -- they just become uncategorized.
CREATE TABLE media_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_media_items_category (category_id),
    CONSTRAINT fk_media_items_category FOREIGN KEY (category_id) REFERENCES media_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Single-row settings record (id is always 1) controlling media library
-- upload limits. allowed_types is a comma-separated list of file extensions.
CREATE TABLE media_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    max_file_size_mb SMALLINT UNSIGNED NOT NULL DEFAULT 5,
    allowed_types VARCHAR(255) NOT NULL DEFAULT 'jpg,jpeg,png,gif,webp',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
