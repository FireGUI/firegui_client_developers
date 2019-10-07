<?php

// Important

$updates = array();


// Updates to 1.5.5
$updates['1.5.5'] = ["UPDATE entity SET entity_login_entity = '".DB_BOOL_TRUE."' WHERE entity_name = '".LOGIN_ENTITY."'"];