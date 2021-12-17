<?php

// Important

$updates = array();

// Updates to 1.6.6.6
if ($this->db->dbdriver != 'postgres') {
    //mysql
    $updates['1.6.6.6'] = ["ALTER TABLE menu ADD COLUMN menu_type VARCHAR(250);", "ALTER TABLE menu ADD COLUMN menu_form BIGINT;"];
} else {
    //Pg
    $updates['1.6.6.6'] = ["ALTER TABLE menu ADD COLUMN menu_type VARCHAR;", "ALTER TABLE menu ADD COLUMN menu_form INT;"];
}

// Updates to 1.5.5
$updates['1.5.5'] = ["UPDATE entity SET entity_login_entity = '" . DB_BOOL_TRUE . "' WHERE entity_name = '" . LOGIN_ENTITY . "'"];

//Updates to 1.7.3
$updates['1.7.3'] = [
    "ALTER TABLE settings ADD COLUMN settings_default_language INT DEFAULT 1;",
    "ALTER TABLE languages DROP COLUMN languages_default;",
    "DELETE FROM fields WHERE fields_name = 'languages_default'"
];
//Updates to 1.9.1
$updates['1.9.1'] = [
    "UPDATE entity SET entity_type = 1 WHERE entity_name = 'users'"
];

//Updates to 1.9.4 - Change custom action style to uniform with native ones
$updates['1.9.4'] = [
    "UPDATE grids_actions SET grids_actions_html = REPLACE(grids_actions_html, 'btn-xs', 'js-action_button btn-grid-action-s')"
];

// Updates to 2.0.0
if ($this->db->dbdriver != 'postgres') {
    //mysql
    $updates['2.0.0'] = ["ALTER TABLE menu ADD COLUMN menu_html_attr VARCHAR(250) NULL;"];
} else {
    //Pg
    $updates['2.0.0'] = ["ALTER TABLE menu ADD COLUMN menu_html_attr VARCHAR;"];
}
$updates['2.2.7'] = [
    "UPDATE layouts SET layouts_show_header = '" . DB_BOOL_FALSE . "' WHERE layouts_show_header IS NULL",
];



$updates['2.2.8'] = [
    "UPDATE layouts SET layouts_settings = '" . DB_BOOL_FALSE . "', layouts_title = 'General Settings' WHERE layouts_identifier = 'system_settings'",
    "DELETE FROM menu WHERE menu_label = 'Settings' AND menu_position = 'profile'",
];


$updates['2.2.9'] = [
    "ALTER TABLE settings ADD COLUMN settings_company_email_update_notifications VARCHAR(250);",
    "UPDATE settings SET settings_company_email_update_notifications = settings_company_email;",
    "UPDATE fields SET fields_draw_html_type = 'hidden' WHERE fields_name = 'users_webauthn_data';",
    "UPDATE layouts SET layouts_settings = '" . DB_BOOL_TRUE . "', layouts_title = 'General Settings' WHERE layouts_identifier = 'system_settings'",
    "DELETE FROM menu WHERE menu_label = 'Settings' AND menu_position = 'profile'",
];
