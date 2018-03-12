<?php

/*
 * Install constants
 */
defined('ENTITY_SETTINGS') OR define('ENTITY_SETTINGS',   'settings');

/*
 * Abilita scrittura mail
 */
defined('MAILBOX_COMPOSE') OR define('MAILBOX_COMPOSE',   false);

/*
 * Flag filters è un array serializzato tale che:
 *  - la chiave è la label del filtro
 *  - il valore è il filtro da applicare (in AND con l'eventuale ricerca mailbox)
 */
defined('MAILBOX_FLAG_FILTERS') OR define('MAILBOX_FLAG_FILTERS', serialize([ /* ... */ ]));

