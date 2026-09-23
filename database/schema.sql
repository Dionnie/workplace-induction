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

-- contact_number, job_position, and the emergency_contact_* fields are
-- workplace details the inductee manages themselves; all optional since they
-- are collected after registration via the inductee's own profile page.
-- company and employment_type are required at registration but remain
-- nullable here since an admin-created account does not collect them.
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

-- Single-row settings record (id is always 1) controlling how the
-- application's notification emails are sent and who else receives them.
-- cc/bcc are comma-separated address lists for "other concerned people"
-- who are not necessarily system users.
CREATE TABLE email_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    sender_name VARCHAR(150) NOT NULL,
    sender_email VARCHAR(255) NULL,
    cc VARCHAR(500) NULL,
    bcc VARCHAR(500) NULL,
    notify_admin_on_completion TINYINT(1) NOT NULL DEFAULT 1,
    notify_inductee_on_completion TINYINT(1) NOT NULL DEFAULT 1,
    notify_inductee_on_expiry TINYINT(1) NOT NULL DEFAULT 1,
    expiry_reminder_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
