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
    "DELETE FROM fields WHERE fields_name = 'languages_default'",
];
//Updates to 1.9.1
$updates['1.9.1'] = [
    "UPDATE entity SET entity_type = 1 WHERE entity_name = 'users'",
];

//Updates to 1.9.4 - Change custom action style to uniform with native ones
$updates['1.9.4'] = [
    "UPDATE grids_actions SET grids_actions_html = REPLACE(grids_actions_html, 'btn-xs', 'js-action_button btn-grid-action-s')",
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
    "UPDATE fields_draw SET fields_draw_html_type = 'hidden' WHERE fields_draw_label = 'Webauthn data';",
    "UPDATE layouts SET layouts_settings = '" . DB_BOOL_TRUE . "', layouts_title = 'General Settings' WHERE layouts_identifier = 'system_settings'",
    "DELETE FROM menu WHERE menu_label = 'Settings' AND menu_position = 'profile'",
];

$updates['2.3.0'] = [
    "UPDATE fields_draw SET fields_draw_html_type = 'hidden' WHERE fields_draw_label = 'Webauthn data';",
    "UPDATE layouts SET layouts_settings = '" . DB_BOOL_TRUE . "', layouts_title = 'General Settings' WHERE layouts_identifier = 'system_settings'",
    "DELETE FROM menu WHERE menu_label = 'Settings' AND menu_position = 'profile'",

];

$updates['2.3.1'] = [

    "UPDATE layouts SET layouts_identifier = 'users' WHERE layouts_id = 3",
    "UPDATE notifications SET notification_desktop_notified = '" . DB_BOOL_TRUE . "'",
    "ALTER TABLE " . LOGIN_ENTITY . " ADD COLUMN " . LOGIN_ENTITY . "_deleted BOOLEAN DEFAULT 0;",
];

$updates['2.3.2'] = [

    "UPDATE notifications SET notifications_desktop_notified = '" . DB_BOOL_TRUE . "'",
    "ALTER TABLE charts_elements DROP COLUMN IF EXISTS charts_elemets_type;",
    "ALTER TABLE charts_elements DROP COLUMN IF EXISTS charts_elemets_fill_columns;",
];
$updates['2.3.6'] = [
    "UPDATE grids_actions SET grids_actions_color = '#E91E63' WHERE grids_actions_color = '#dd4b39'",
];

$updates['2.3.7'] = [
    "DELETE FROM forms_fields WHERE forms_fields_fields_id IN (SELECT fields_id FROM fields WHERE fields_name = 'users_type') AND forms_fields_forms_id IN (SELECT forms_id FROM forms WHERE forms_default = '" . DB_BOOL_TRUE . "')",
];

$updates['2.5.0'] = [
    "UPDATE grids SET grids_depth = 2 WHERE grids_depth = 1;",
    "DELETE FROM layouts WHERE layouts_id NOT IN (
        SELECT layouts_boxes_layout FROM layouts_boxes WHERE layouts_boxes_layout IS NOT NULL
    ) AND layouts_id NOT IN (
        SELECT menu_layout FROM menu WHERE menu_layout IS NOT NULL
    ) AND layouts_id NOT IN (
        SELECT layouts_boxes_content_ref FROM layouts_boxes WHERE layouts_boxes_content_ref IS NOT NULL AND layouts_boxes_content_type = 'layout'
    );",
    "UPDATE fields SET fields_preview = '1' WHERE fields_name = 'layouts_title';"
];

$updates['4.0.5'] = [
    //Inserisco nella tabella api_manager_permissions un record per ogni entità con accesso completo (per retrocompatibilità)
    "
        INSERT INTO api_manager_permissions (api_manager_permissions_token, api_manager_permissions_entity, api_manager_permissions_chmod)
            SELECT 
                amt.api_manager_tokens_id,
                e.entity_id,
                '5' -- Permesso completo (R/W)
            FROM 
                api_manager_tokens amt
            CROSS JOIN
                entity e
            WHERE 
                e.entity_type = 1
                AND NOT EXISTS (
                    SELECT 1
                    FROM api_manager_permissions amp
                    WHERE amp.api_manager_permissions_token = amt.api_manager_tokens_id
                    AND amp.api_manager_permissions_entity = e.entity_id
                )
    ",
    "
        INSERT INTO api_manager_fields_permissions (
                api_manager_fields_permissions_token, 
                api_manager_fields_permissions_field, 
                api_manager_fields_permissions_chmod
            )
            SELECT 
                amt.api_manager_tokens_id,
                f.fields_id,
                '5' -- Permesso completo (R/W)
            FROM 
                api_manager_tokens amt
            CROSS JOIN
                fields f
            JOIN
                entity e ON f.fields_entity_id = e.entity_id
            WHERE 
                e.entity_type = 1
                AND NOT EXISTS (
                    SELECT 1
                    FROM api_manager_fields_permissions afp
                    WHERE afp.api_manager_fields_permissions_token = amt.api_manager_tokens_id
                    AND afp.api_manager_fields_permissions_field = f.fields_id
                )
    "
];

$updates['4.0.9'] = [
    //Inserisco nella tabella api_manager_permissions un record per ogni entità con accesso completo (per retrocompatibilità)
    "
        INSERT INTO api_manager_permissions (api_manager_permissions_token, api_manager_permissions_entity, api_manager_permissions_chmod)
            SELECT 
                amt.api_manager_tokens_id,
                e.entity_id,
                '5' -- Permesso completo (R/W)
            FROM 
                api_manager_tokens amt
            CROSS JOIN
                entity e
            WHERE 
                e.entity_type IN (1,2)
                AND NOT EXISTS (
                    SELECT 1
                    FROM api_manager_permissions amp
                    WHERE amp.api_manager_permissions_token = amt.api_manager_tokens_id
                    AND amp.api_manager_permissions_entity = e.entity_id
                )
    ",
    "
        INSERT INTO api_manager_fields_permissions (
                api_manager_fields_permissions_token, 
                api_manager_fields_permissions_field, 
                api_manager_fields_permissions_chmod
            )
            SELECT 
                amt.api_manager_tokens_id,
                f.fields_id,
                '5' -- Permesso completo (R/W)
            FROM 
                api_manager_tokens amt
            CROSS JOIN
                fields f
            JOIN
                entity e ON f.fields_entity_id = e.entity_id
            WHERE 
                e.entity_type IN (1,2)
                AND NOT EXISTS (
                    SELECT 1
                    FROM api_manager_fields_permissions afp
                    WHERE afp.api_manager_fields_permissions_token = amt.api_manager_tokens_id
                    AND afp.api_manager_fields_permissions_field = f.fields_id
                )
    "
];
$updates['4.0.10'] = [
    "DELETE FROM api_manager_permissions",
    "DELETE FROM api_manager_fields_permissions",
]; 
