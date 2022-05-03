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
    'include' => ['1.6.9.7_rename_crm_configuration.php',],
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
