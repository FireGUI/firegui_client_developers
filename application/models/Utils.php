<?php

class Utils extends CI_Model
{

    public static $defaultDrops = [
        // Schema
        'meta_data',
        'entity',
        'relations',
        'fields',
        'fields_draw',
        'fields_validation',
        // Form
        'forms',
        'forms_fields',
        // Grid
        'grids',
        'grids_fields',
        'grids_actions',
        // Widgets
        'calendars',
        'calendars_fields',
        'maps',
        'maps_fields',
        'charts',
        'charts_elements',
        // Customization
        'post_process',
        'crons',
        'crons_fields',
        'hooks',
        // ACL
        'permissions',
        'permissions_entities',
        'permissions_modules',
        'unallowed_layouts',
        'limits',
        // Others
        'notifications',
        'modules',
        'mail_queue',
        'user_tokens',
        'emails',
        'log_api',
        'log_crm',
        'ci_sessions',
        'api_manager_tokens',
        'api_manager_permissions',
        'api_manager_fields_permissions',
        'locked_elements',
        'fi_events',
        '_conditions',
        '_queue_pp',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('entities');
    }

    public function set_selected_db($db = null)
    {
        if (!empty($db)) {
            $this->selected_db = $db;
        } else {
            //Tento di prendere i dati da sessione
            if (empty($this->session->userdata(DATABASE_PARAMETERS))) {
                die_error(ERROR_DB_SESSION_DATA, __LINE__, __FILE__);
            } else {
                //Creo l'oggetto db selezionato
                $params = $this->session->userdata(DATABASE_PARAMETERS);
                $this->selected_db = $this->general->get_database_connection($params);
                $this->load->dbforge($this->selected_db);
            }
        }
    }

    /**
     * Ritrova tutte le filter session keys definite nei form
     * @return array
     */
    public function get_filter_session_keys()
    {
        $filter_session_keys = $this->selected_db->where("forms_filter_session_key IS NOT NULL AND forms_filter_session_key <> ''")->get('forms')->result_array();
        return array_map(function ($filter) {
            return $filter['forms_filter_session_key'];
        }, $filter_session_keys);
    }

    /**
     * Droppa tutte le tabelle di sistema
     */
    public function dropAll()
    {

        $drops = self::$defaultDrops;

        if ($this->selected_db->table_exists('entity')) {
            $query = $this->selected_db->get('entity');
            foreach ($query->result() as $table) {
                $drops[] = $table->entity_name;
            }
        }

        foreach ($drops as $tblname) {
            $this->dbforge->drop_table($tblname, true, true);
        }
    }

    /**
     * Overrided from mysql or postgres util
     * @return void
     */
    public function migrationProcess()
    {
        //Demandato all'override dei singoli driver (mysql e postgres)
    }

    /*public function fieldsTypesMysql($fields) {
    if ($this->selected_db->dbdriver != 'postgre') {
    foreach ($fields as $key => $field) {
    switch (strtoupper($field['type'])) {
    case 'SERIAL UNIQUE':
    //                        $fields[$key]['type'] = 'INT';
    //                        $fields[$key]['unsigned'] = true;
    //                        $fields[$key]['constraint'] = 5;
    break;
    case 'VARCHAR':
    $fields[$key]['type'] = "varchar";
    if (empty($field['constraint'])) {
    $fields[$key]['constraint'] = 250;
    }
    break;
    case 'BOOL':
    $fields[$key]['type'] = "varchar(1)";
    if (!empty($fields[$key]['default']) && $fields[$key]['default'] == DB_BOOL_TRUE) {
    $fields[$key]['default'] = DB_BOOL_TRUE;
    } else {
    $fields[$key]['default'] = DB_BOOL_FALSE;
    }
    break;
    case 'INT':
    $fields[$key]['type'] = "bigint";
    $fields[$key]['unsigned'] = true;
    break;
    case 'JSON':
    $fields[$key]['type'] = "TEXT";
    break;
    case 'TEXT':
    $fields[$key]['type'] = "TEXT";
    break;
    case 'TIMESTAMP WITHOUT TIME ZONE':
    if (!empty($field['default']) && ($field['default'] == 'NOW()' || $field['default'] == 'now()')) {
    $fields[$key]['type'] = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    unset($fields[$key]['default']);
    } else {
    $fields[$key]['type'] = "TIMESTAMP";
    }
    break;
    default:
    debug($field,true);
    break;
    }
    }
    }
    return $fields;
    }*/

    /**
     * Crea una tabella. Se non viene passata la primary key allora viene usato
     * l'eventuale campo nometabella_id se passato nei fields
     *
     * @param string $tableName
     * @param array $fields
     * @param string|null $primaryKey
     */
    private function morphTable($tableName, array $fields, $primaryKey = null, $do_remove = true)
    {
        $this->selected_db->cache_delete_all();
        $fields = $this->fieldsTypesMysql($fields);

        $exists = $this->selected_db->table_exists($tableName);
        $primary = (!$primaryKey && isset($fields[$tableName . '_id'])) ? $tableName . '_id' : $primaryKey;

        if (!in_array($tableName, self::$defaultDrops) && $do_remove) {
            show_error(sprintf("Insert table %s into defaultDrops variable of install controller", $tableName));
        }

        // La tabella non esiste?
        // Allora posso creare direttamente i campi senza preoccuparmi di nulla,
        // dei dettagli se ne occupa CI.
        if (!$exists) {

            //debug($primaryKey);

            if ($primary) {
                $this->dbforge->add_key($primary, true);
            }

            $this->dbforge->add_field($fields);
            $this->dbforge->create_table($tableName, true);
            return;
        }

        // Se la tabella esiste
        // Allora devo fare un diff di quello che c'è ed eventualmente apportare
        // le modifiche/drop ecc....
        $actualFields = $this->selected_db->list_fields($tableName);
        $passedFields = array_keys($fields);
        $newFieldsAdded = array_diff($passedFields, $actualFields);

        $create = array_intersect_key($fields, array_flip($newFieldsAdded));
        $remove = array_diff($actualFields, $passedFields);
        $update = array_diff_key($fields, array_keys($create), array_keys($remove));

        foreach ($update as $name => $field) {
            // Rimuovo l'eventuale redefinizione di un campo autoincrement

            if (!empty($field['auto_increment']) && $this->selected_db->dbdriver == 'postgre') {

                unset($update[$name]['type'], $update[$name]['auto_increment']);
            }

            // Se alla fine degli unset ho svuotato l'array allora lo rimuovo
            if (!$update[$name]) {

                unset($update[$name]);
            }
        }

        if ($create) {
            $this->dbforge->add_column($tableName, $create);
        }

        if ($update) {
            //            debug($tableName);
            //debug($update);
            //debug($this->dbforge,true);
            $this->dbforge->modify_column($tableName, $update);
        }
        if ($do_remove) {
            foreach ($remove as $field) {
                // Controllo se esiste ancora perché potrebbe essere stato già
                // rinominato
                if ($this->selected_db->field_exists($field, $tableName)) {
                    $this->dbforge->drop_column($tableName, $field);
                }
            }
        }
    }

    /**
     * Aggiungi chiave esterna
     *
     * @param string $fromTable
     * @param string $fromField
     * @param string $toTable
     * @param string $toField
     */
    private function addForeignKey($fromTable, $fromField, $toTable, $toField)
    {
        $this->selected_db->cache_delete_all();

        if (!$this->selected_db->table_exists($fromTable)) {
            show_error(sprintf("%s table does not exists", $fromTable));
        }

        if (!$this->selected_db->table_exists($toTable)) {
            show_error(sprintf("%s table does not exists", $toTable));
        }

        if (!$this->selected_db->field_exists($fromField, $fromTable)) {
            show_error(sprintf("%s field does not exists in %s table", $fromTable));
        }

        if (!$this->selected_db->field_exists($toField, $toTable)) {
            show_error(sprintf("%s field does not exists in %s table", $toTable));
        }

        // Dai un nome univoco per le chiavi esterne del core

        if ($this->selected_db->dbdriver == 'postgre') {
            $conname = 'core_' . $fromTable . '_' . $fromField . '_fkey';
            $exists = $this->selected_db->query("SELECT * FROM pg_constraint WHERE conname = ?", [$conname])->num_rows();
            if (!$exists) {
                $this->selected_db->query("ALTER TABLE {$fromTable} ADD CONSTRAINT {$conname} FOREIGN KEY ({$fromField}) REFERENCES {$toTable} ({$toField}) ON DELETE CASCADE ON UPDATE CASCADE");
            }
        } else {
            //            $this->selected_db->query("ALTER TABLE $fromTable DROP COLUMN $fromField;");
            //            $this->selected_db->query("ALTER TABLE $fromTable ADD COLUMN $fromField INT NOT NULL;");
            //            $this->selected_db->query("ALTER TABLE $fromTable ADD FOREIGN KEY ($fromField) REFERENCES $toTable($toField) ON DELETE CASCADE ON UPDATE CASCADE");
            $this->dbforge->add_column($fromTable, "FOREIGN KEY ($fromField) REFERENCES $toTable($toField) ON DELETE CASCADE ON UPDATE CASCADE");
        }
    }

    /**
     * Crea le entità di sistema
     */
    private function entitiesBaseSetup()
    {
        $this->selected_db->cache_delete_all();

        /* ============================
         * Layouts
         * ============================ */
        $layoutsEntityId = $this->entities->new_entity([
            'entity_name' => 'layouts',
            'entity_visible' => DB_BOOL_FALSE,
            'entity_type' => ENTITY_TYPE_SYSTEM,
        ], true, false);
        $this->entities->addFields([
            'entity_id' => $layoutsEntityId,
            'fields' => [
                ['fields_name' => 'title', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'subtitle', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'is_entity_detail', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'entity_id', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'fullscreen', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'pdf', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'radio'],
                ['fields_name' => 'dashboardable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'radio', 'fields_default' => ($this->selected_db->dbdriver == 'postgres') ? DB_BOOL_FALSE : DB_BOOL_FALSE],
                ['fields_name' => 'cachable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'radio', 'fields_default' => ($this->selected_db->dbdriver == 'postgres') ? DB_BOOL_FALSE : DB_BOOL_FALSE],
            ],
        ]);

        // Layout box
        $layoutsBoxEntityId = $this->entities->new_entity(['entity_name' => 'layouts_boxes', 'entity_visible' => DB_BOOL_FALSE, 'entity_type' => ENTITY_TYPE_SYSTEM]);
        $this->entities->addFields([
            'entity_id' => $layoutsBoxEntityId,
            'fields' => [
                ['fields_name' => 'layout', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'select', 'fields_ref' => 'layouts'],
                ['fields_name' => 'css', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'title', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'content_type', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'select'],
                ['fields_name' => 'content_ref', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'content', 'fields_type' => 'TEXT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'textarea'],
                ['fields_name' => 'position', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'dragable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'collapsible', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'collapsed', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'reloadable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'discardable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'titolable', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'row', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'cols', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'color', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
            ],
        ]);

        // Menù
        $menuEntityId = $this->entities->new_entity(['entity_name' => 'menu', 'entity_visible' => DB_BOOL_FALSE, 'entity_type' => ENTITY_TYPE_SYSTEM]);
        $this->entities->addFields([
            'entity_id' => $menuEntityId,
            'fields' => [
                ['fields_name' => 'label', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'link', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'parent', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'select', 'fields_ref' => 'menu'],
                ['fields_name' => 'icon_class', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'order', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'position', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'layout', 'fields_type' => 'INT', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'select', 'fields_ref' => 'layouts'],
                ['fields_name' => 'css_class', 'fields_type' => 'VARCHAR', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'modal', 'fields_type' => 'BOOL', 'fields_visible' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'radio'],
            ],
        ]);
    }

    /**
     * Pulisce tutti i dati inutili (o refusi del periodo senza foreign keys)
     */
    public function dbClear()
    {
        if (empty($this->selected_db)) {
            $this->set_selected_db();
        }
        // Inizia transazione
        //$this->selected_db->trans_start();

        //  Elimina campi senza entità
        $this->selected_db->query("DELETE FROM fields WHERE fields_entity_id NOT IN (SELECT entity_id FROM entity)");
        $this->selected_db->query("DELETE FROM fields_validation WHERE fields_validation_fields_id NOT IN (SELECT fields_id FROM fields)");

        //  Elimina fields draw doppi o senza campi
        $this->selected_db->query("DELETE FROM fields_draw
                          WHERE fields_draw_fields_id NOT IN (SELECT fields_id FROM fields) OR
                                EXISTS (
                                    SELECT 1
                                    FROM fields_draw AS fd2
                                    WHERE fd2.fields_draw_id < fields_draw_id AND
                                          fd2.fields_draw_fields_id <> fields_draw_fields_id
                                    )
                ");

        // Elimina form/grid/calendar vuoti e form/grid/calendar fields pendenti
        $this->selected_db->query("DELETE FROM forms WHERE NOT EXISTS (SELECT 1 FROM forms_fields WHERE forms_id = forms_fields_forms_id)");
        $this->selected_db->query("DELETE FROM grids WHERE NOT EXISTS (SELECT 1 FROM grids_fields WHERE grids_id = grids_fields_grids_id)");
        $this->selected_db->query("DELETE FROM calendars WHERE NOT EXISTS (SELECT 1 FROM calendars_fields WHERE calendars_id = calendars_fields_calendars_id)");
        $this->selected_db->query("DELETE FROM forms_fields WHERE forms_fields_forms_id NOT IN (SELECT forms_id FROM forms)");
        $this->selected_db->query("DELETE FROM grids_fields WHERE grids_fields_grids_id NOT IN (SELECT grids_id FROM grids)");
        $this->selected_db->query("DELETE FROM calendars_fields WHERE calendars_fields_calendars_id NOT IN (SELECT calendars_id FROM calendars)");

        // Elimina layout vuoti
        $this->selected_db->query("DELETE FROM layouts WHERE NOT EXISTS (SELECT 1 FROM layouts_boxes WHERE layouts_id = layouts_boxes_layout)");
        $this->selected_db->query("DELETE FROM layouts_boxes WHERE layouts_boxes_layout NOT IN (SELECT layouts_id FROM layouts)");

        // Elimina voci di menu pendenti
        $this->selected_db->query("DELETE FROM menu WHERE NOT EXISTS (SELECT 1 FROM layouts_boxes WHERE layouts_id = layouts_boxes_layout)");

        // Completa transazione
        $this->selected_db->trans_complete();
    }
}