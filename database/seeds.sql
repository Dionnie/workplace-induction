-- Default administrator account
-- Email: admin@example.com
-- Password: ChangeMe123!  (change this after first login)

INSERT INTO users (email, password, user_type, status, profile_completed, email_verified_at)
VALUES (
    'admin@example.com',
    '$2y$10$oSF7TVTKg1loGxicO3bCp.f346jcY0Rhd9oK/f8Lt03ehoCnYggV2',
    'admin',
    'active',
    1,
    NOW()
);

INSERT INTO admin_profiles (user_id, first_name, last_name)
VALUES (LAST_INSERT_ID(), 'System', 'Administrator');

-- Default email/notification settings
INSERT INTO email_settings (id, sender_name, sender_email)
VALUES (1, 'Induction System', NULL);
