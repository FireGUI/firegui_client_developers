<?php

// Important

$updates = array();

$updates['1.6.6.6'] = [
    'include' => [
        '1.6.6.6_fi_events_migration.php',
        '1.6.6.6_fontawesome_migration.php'
    ],
];

$updates['1.6.9.7'] = [
    'include' => [
        '1.6.9.7_rename_crm_configuration.php',
    ],
];

$updates['1.6.7.5'] = [
    'include' => ['1.6.7.5_custom_views_migration.php'],
];

$updates['1.8.9'] = [
    'include' => ['1.8.9_grids_action_migration.php'],
];
$updates['2.1.9'] = [
    'include' => ['2.1.9_field_user_webauthn_data.php'],
];
$updates['2.2.3'] = [
    'include' => [
        '2.2.3_fields_settings.php',
        '2.2.3_layouts_show_header_field.php'
    ],
];

$updates['2.2.9'] = [
    'include' => ['2.2.9_fields_settings_email.php'],
];

$updates['2.3.0'] = [
    'include' => ['2.3.0_login_background.php'],
];

$updates['2.3.1'] = [
    'include' => [
        '2.3.1_users_deleted.php',
        '2.3.1_users_last_password_change.php',
        '2.3.1_settings_mail_module.php'
    ],
];

$updates['2.3.2'] = [
    'include' => ['2.3.2_settings_template.php'],
];

$updates['2.3.9'] = [
    'include' => ['2.3.9_settings_update_in_progress.php'],
];


$updates['2.4.0'] = [
    'include' => ['2.4.0_settings_last_cron_cli.php'],
];

$updates['2.6.1'] = [
    'include' => ['2.7.0_add_htaccess_module_bridge.php'],
];
$updates['2.6.3'] = [
    'include' => ['2.7.0_add_htaccess_module_bridge.php'],
];
$updates['2.6.4'] = [
    'include' => ['2.7.0_add_htaccess_template_bridge.php'],
];
$updates['2.6.6'] = [
    'include' => ['2.7.0_add_htaccess_template_bridge.php'],
];
$updates['2.7.0'] = [
    'include' => [
        '2.7.0_add_htaccess_module_bridge.php',
        '2.7.0_add_htaccess_template_bridge.php'
    ],
];
$updates['2.7.4'] = [
    'include' => [
        '2.7.4_notifications.php',
    ],
];



$updates['2.8.0'] = [
    'include' => [
        '2.8.0_notifications.php',
    ],
];
$updates['3.2.3'] = [
    'include' => [
        '3.2.3_fields_settings_background_pp.php',
    ],
];

$updates['4.0.2'] = [
    'include' => [
        '4.0.2_remove_macosx_files.php',
    ],
];

$updates['4.1.4'] = [
    'include' => [
        '4.1.4_field_user_verification_code',
        '4.1.4_new_email_templates',
        '4.1.4_add_htaccess_public_rule.php',
    ]
];

$updates['4.1.5'] = [
    'include' => [
        '4.1.5_fix_relations_fields.php',
    ]
];

$updates['4.1.6'] = [
    'include' => [
        '4.1.6_bugfix_relation_fields.php',
    ]
];

$updates['4.1.7'] = [
    'include' => [
        '4.1.4_new_email_templates.php',
        '4.1.4_field_user_verification_code.php',
    ]
];

$updates['4.2.0'] = [
    'include' => [
        '4.2.0_add_currencies_system_entity.php',
    ]
];

$updates['4.2.2'] = [
    'include' => [
        '4.2.2_remove_my_session_file.php',
    ]
];