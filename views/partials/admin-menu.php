<?php

declare(strict_types=1);

/**
 * Every admin section, grouped like docs/: Application (the induction
 * features) and Core (platform functions any deployment has).
 *
 * The single source for the admin sidebar (admin-header.php) and the
 * dashboard shortcuts: add a new admin page here, under the right group,
 * and it appears in both. Keys are the $currentPage values.
 * See docs/core/design-system.html#navigation.
 *
 * @return array<string, array<string, array{label: string, icon: string, url: string, description: string}>>
 */
return [
    'Application' => [
        'inductions' => [
            'label' => 'Inductions',
            'icon' => 'bi-journal-check',
            'url' => '/admin/inductions/index.php',
            'description' => 'Create and edit inductions and their content blocks.',
        ],
        'exams' => [
            'label' => 'Exams',
            'icon' => 'bi-ui-checks',
            'url' => '/admin/exams/index.php',
            'description' => 'Create and edit exams and their questions.',
        ],
        'exam-attempts' => [
            'label' => 'Exam Attempts',
            'icon' => 'bi-clipboard-data',
            'url' => '/admin/exam-attempts/index.php',
            'description' => 'Review every exam attempt submitted by inductees.',
        ],
        'compliance' => [
            'label' => 'Compliance',
            'icon' => 'bi-patch-check',
            'url' => '/admin/compliance/index.php',
            'description' => 'View compliance records and revoke them where necessary.',
        ],
    ],
    'Core' => [
        'users' => [
            'label' => 'Users',
            'icon' => 'bi-people',
            'url' => '/admin/users/index.php',
            'description' => 'Create and edit administrator and inductee accounts.',
        ],
        'media-library' => [
            'label' => 'Media Library',
            'icon' => 'bi-images',
            'url' => '/admin/media-library/index.php',
            'description' => 'Upload images and group them into categories for content blocks and branding.',
        ],
        'tools' => [
            'label' => 'Tools',
            'icon' => 'bi-tools',
            'url' => '/admin/tools/index.php',
            'description' => 'Maintenance utilities, such as searching and replacing text in the database.',
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'bi-gear',
            'url' => '/admin/settings/index.php',
            'description' => 'Company details, appearance, email senders and notifications.',
        ],
    ],
];
