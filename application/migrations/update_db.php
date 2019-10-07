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
