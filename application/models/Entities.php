<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Entities extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Europe/Rome');

        // Fix for compatibility Client-Builder 
        $this->selected_db = $this->db;
        $this->load->model('general');


        $this->alter_text = ($this->selected_db->dbdriver != 'postgre') ? 'MODIFY' : 'ALTER COLUMN';
        $this->type_text = ($this->selected_db->dbdriver != 'postgre') ? '' : 'TYPE';

        if ($this->selected_db) {
            $this->load->dbforge($this->selected_db);
        }

        if (!defined('DB_BOOL_TRUE') && $this->selected_db->dbdriver) {
            if ($this->selected_db->dbdriver == 'postgre') {
                define('DB_BOOL_TRUE', 't');
                define('DB_BOOL_FALSE', 'f');
                define('DB_INTEGER_IDENTIFIER', 'INT');
                define('DB_BOOL_IDENTIFIER', 'BOOL');
            } else {
                define('DB_BOOL_TRUE', '1'); //Mettere 1 per mysql
                define('DB_BOOL_FALSE', '0'); //Mettere 0 per mysql
                define('DB_INTEGER_IDENTIFIER', 'integer');
                define('DB_BOOL_IDENTIFIER', 'BOOLEAN');
            }
        }
        //     }
        // }
    }

    public function layout_detail_exists($dati)
    {
        if (empty($dati['layouts_is_entity_detail']) || $dati['layouts_is_entity_detail'] == DB_BOOL_FALSE) {
            return false;
        }

        $layout_id = $dati['layouts_id'] ?? null;

        if ($layout_id) {
            $this->selected_db->where("layouts_id <> '{$layout_id}'");
        }

        //TODO: 20230127 - MP - consentire da builder di specificare più di un layout detail e dopo qui prendere solo quello a cui l'utente connesso ha accesso (permessi)
        $layout = $this->selected_db->where('layouts_is_entity_detail', true)->where('layouts_entity_id', $dati['layouts_entity_id'])->get('layouts')->row_array();

        if (!empty($layout)) {
            return true;
        } else {
            return false;
        }
    }

    public function entity_exists($entity_name)
    {
        $result = $this->selected_db->query("SELECT * FROM entity WHERE entity_name = '$entity_name'");
        if ($result->num_rows() != 0) {
            $return = $result->row_array();
        } else {
            $return = false;
        }
        return $return;
    }

    public function get_login_entity()
    {
        $result = $this->selected_db->query("SELECT * FROM entity WHERE entity_login_entity = '" . DB_BOOL_TRUE . "'");
        if ($result->num_rows() != 0) {
            $return = $result->row_array();
        } else {
            $result = $this->selected_db->query("SELECT * FROM entity WHERE entity_name = 'users'");
            if ($result->num_rows() != 0) {
                $return = $result->row_array();
            } else {
                $return = false;
            }
        }
        return $return;
    }

    public function new_entity($dati, $create_layout_menu = true, $check_exists = true, $only_layout = false, $layouts_identifier = "")
    {
        $dati['entity_name'] = url_title($dati['entity_name'], '_', true);
        $name = $dati['entity_name'];

        // Check che non esista già
        if ($this->selected_db->table_exists($name)) {
            $query = $this->selected_db->get_where('entity', ['entity_name' => $name]);
            if ($query->num_rows() > 0) {
                //                if (!$check_exists) {
                //                    return $query->row()->entity_id;
                //                } else {
                throw new Exception(sprintf("Entity %s already exists", $name), $query->row()->entity_id);
                //                }
            }

            throw new RuntimeException(sprintf("Table %s already exists in database (not an entity)", $name), 0);
        }

        $dati['entity_type'] = isset($dati['entity_type']) ? $dati['entity_type'] : ENTITY_TYPE_DEFAULT;
        $dati['entity_searchable'] = isset($dati['entity_searchable']) ? $dati['entity_searchable'] : ($dati['entity_type'] == ENTITY_TYPE_DEFAULT); //Le entità di default sono tutte searchable per impostazione di default
        if (isset($dati['entity_action_fields'])) {
            if (is_string($dati['entity_action_fields'])) {
                $dati['entity_action_fields'] = json_decode($dati['entity_action_fields'], true);
            }
            $dati['entity_action_fields'] = $this->processEntityCustomActionFields($dati, $dati['entity_action_fields']);
            if (!$dati['entity_action_fields']) {
                unset($dati['entity_action_fields']);
            }
        }

        // Salvo i dati nella tabella entità
        //$test = $this->selected_db;
        $this->selected_db->insert('entity', $dati);
        $entity_id = $this->selected_db->insert_id();
        if (!$this->dbforge && $this->selected_db) {
            $this->load->dbforge($this->selected_db);
        }

        // Creo la tabella dell'entità
        //        if ($this->selected_db->dbdriver != 'postgre') {
        //            $this->dbforge->add_field([$name . "_id" => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true]]);
        //
        //        } else {
        $this->dbforge->add_field([$name . "_id" => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true]]);
        //        }

        $this->dbforge->add_key($name . "_id", true);
        $this->dbforge->create_table($name, true);

        $this->dbforge->fields = [];

        //CREATE TABLE IF NOT EXISTS `asdasd` ( `asdasd_id` SERIAL UNIQUE NOT NULL, CONSTRAINT `pk_asdasd` PRIMARY KEY(`asdasd_id`) ) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci
        //CREATE TABLE IF NOT EXISTS `interventi_tipologie_di_intervento` (
        //`interventi_id` bigint(20) UNSIGNED NOT NULL NOT NULL,
        //`tipologie_di_intervento_id` bigint(20) UNSIGNED NOT NULL NOT NULL,
        //CONSTRAINT FOREIGN KEY (interventi_id) REFERENCES interventi(interventi_id) ON UPDATE CASCADE ON DELETE CASCADE,
        //CONSTRAINT FOREIGN KEY (tipologie_di_intervento_id) REFERENCES tipologie_di_intervento(tipologie_di_intervento_id) ON UPDATE CASCADE ON DELETE CASCADE,
        //`interventi_tipologie_di_intervento_id` SERIAL UNIQUE NOT NULL, CONSTRAINT `pk_interventi_tipologie_di_intervento` PRIMARY KEY(`interventi_tipologie_di_intervento_id`) ) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci

        $fgName = ucwords(str_replace(array('_', '-'), ' ', $dati['entity_name']));
        $grid_id = $this->new_grid(
            $fgName,
            $entity_id,
            'table',
            null,
            DB_BOOL_TRUE
        );
        $this->selected_db->insert('forms', [
            'forms_entity_id' => $entity_id,
            'forms_name' => $fgName,
            'forms_default' => DB_BOOL_TRUE,
            'forms_layout' => 'horizontal',
        ]);
        $form_id = $this->selected_db->insert_id();

        if (!empty($dati['entity_action_fields'])) {
            $actionFields = json_decode($dati['entity_action_fields'], true);
            $fields = [];

            if (isset($actionFields['create_time'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['create_time'], strlen($name) + 1),
                    'fields_type' => 'TIMESTAMP WITHOUT TIME ZONE',
                    'fields_draw_html_type' => 'date_time',
                    'on_default_form' => false,
                    'on_default_grid' => true,
                ];
            }

            if (isset($actionFields['update_time'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['update_time'], strlen($name) + 1),
                    'fields_type' => 'TIMESTAMP WITHOUT TIME ZONE',
                    'fields_draw_html_type' => 'date_time',
                    'on_default_form' => false,
                    'on_default_grid' => false,
                ];
            }

            if (isset($actionFields['created_by'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['created_by'], strlen($name) + 1),
                    'fields_type' => 'INT',
                    'fields_draw_html_type' => 'select_ajax',
                    'fields_ref' => 'users',
                    'on_default_form' => false,
                    'on_default_grid' => false,
                ];
            }
            if (isset($actionFields['edited_by'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['edited_by'], strlen($name) + 1),
                    'fields_type' => 'INT',
                    'fields_draw_html_type' => 'select_ajax',
                    'fields_ref' => 'users',
                    'on_default_form' => false,
                    'on_default_grid' => false,
                ];
            }
            if (isset($actionFields['insert_scope'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['insert_scope'], strlen($name) + 1),
                    'fields_type' => 'VARCHAR',
                    'fields_draw_html_type' => 'input_text',

                    'on_default_form' => false,
                    'on_default_grid' => false,
                ];
            }

            if (isset($actionFields['edit_scope'])) {
                $fields[] = [
                    'fields_name' => substr($actionFields['edit_scope'], strlen($name) + 1),
                    'fields_type' => 'VARCHAR',
                    'fields_draw_html_type' => 'input_text',

                    'on_default_form' => false,
                    'on_default_grid' => false,
                ];
            }

            if ($fields) {
                $this->addFields([
                    'entity_id' => $entity_id,
                    'fields' => $fields,
                ]);
            }
        }

        //Add internal field to log who created or edit the record (api, apilib or form)
        // $this->addFields([
        //     'entity_id' => $entity_id,
        //     'fields' => [
        //         [
        //             'fields_name' => 'insert_scope',
        //             'fields_type' => 'VARCHAR',
        //             'fields_draw_html_type' => 'input_text',
        //             'on_default_form' => false,
        //             'on_default_grid' => false,
        //         ],
        //         [
        //             'fields_name' => 'edit_scope',
        //             'fields_type' => 'VARCHAR',
        //             'fields_draw_html_type' => 'input_text',
        //             'on_default_form' => false,
        //             'on_default_grid' => false,
        //         ],
        //     ],
        // ]);

        if (($only_layout || $create_layout_menu) && isset($dati['entity_visible']) && $dati['entity_visible'] == DB_BOOL_TRUE) {
            // Poiché abbiamo l'entità visibile, creo le viste base
            $layout_id = $this->newSimpleLayout($name, false, null, $layouts_identifier);

            //20200219 - MP - Add layout box for the menu button new
            $this->new_menu([
                //'menu_link' => "{base_url}get_ajax/modal_form/{$form_id}",
                'menu_label' => "New {$name}",
                'menu_icon_class' => 'fas fa-plus',
                'menu_position' => $name . '_top',
                'menu_order' => 1,
                'menu_css_class' => 'btn-primary',
                'menu_modal' => DB_BOOL_TRUE,
                'menu_type' => 'form',
                'menu_form' => $form_id,
            ]);

            //Aggiungo questo menu a un nuovo layoutbox in posizione 1
            $this->newSimpleLayoutBox('Top menu ' . $name, $layout_id, 'menu_group', $name . '_top', 1, 1, 12, 'box-primary', '', false); //Questo ultimo false è per non far vedere il titolo

            //Creo la action edit che apre il form in modale nella grid
            $this->selected_db->insert('grids_actions', [
                'grids_actions_grids_id' => $grid_id,
                'grids_actions_order' => 1,
                'grids_actions_mode' => 'modal',
                'grids_actions_form' => $form_id,
                'grids_actions_type' => 'edit_form',
                'grids_actions_icon' => 'fas fa-edit',
                'grids_actions_color' => 'rgb(156, 39, 176)',
                'grids_actions_name' => 'Edit',
                'grids_actions_html' => '',
            ]);

            //Creo la action delete
            $this->selected_db->insert('grids_actions', [
                'grids_actions_grids_id' => $grid_id,
                'grids_actions_order' => 99,
                'grids_actions_mode' => null,
                'grids_actions_form' => null,
                'grids_actions_type' => 'delete',
                'grids_actions_icon' => 'fas fa-trash',
                'grids_actions_color' => 'rgb(233, 30, 99)',
                'grids_actions_name' => 'Delete',
                'grids_actions_html' => '',
            ]);

            //Metto la grid in posizione 2, in quanto devo ancora mettere in posizione 1 il menu col pulsante "crea nuovo"
            $this->entities->newSimpleLayoutBox(ucfirst($name), $layout_id, 'grid', $grid_id, 2, 1, 12, 'box-primary', 'box');

            //$formBoxId = $this->newSimpleLayoutBox('New ' . $name, $layout_id, 'form', $form_id, 1, 1, 12, 'red', 'box');
            //$this->newSimpleLayoutBox('List ' . $name, $layout_id, 'grid', $grid_id, 2, 1, 12, 'blue', 'box');

            /*$this->selected_db->update(
            'layouts_boxes',
            [
            'layouts_boxes_collapsible' => true,
            'layouts_boxes_collapsed' => false
            ],
            [
            'layouts_boxes_id' => $formBoxId
            ]
            );*/
            if ($create_layout_menu) {
                $this->newSimpleMenu($name, $layout_id, 'sidebar', 100);
            }
        }

        $this->general->addLog("New entity created", array('entity_name' => $dati['entity_name']));

        return $entity_id;
    }

    /**
     * Qualunque cosa gli passi, lui ti ritorna il json o il null, pronto pronto
     * per essere salvato sul dibbì - tipicamente viene fatto un merge dei dati
     * esistenti, quindi per annullare una chiave basta settarla a null
     *
     * @param int|string|array $entity
     * @param array $data
     */
    public function processEntityCustomActionFields($entity, array $data)
    {
        $actionFields = null;
        if (is_numeric($entity)) {
            $entity = $this->selected_db->get_where('entity', array('entity_id' => $entity))->row_array();
        }

        if (is_array($entity) && array_key_exists('entity_action_fields', $entity)) {
            $actionFields = $entity['entity_action_fields'] ?: [];

            if (is_string($actionFields)) {
                $actionFields = json_decode($actionFields, true);
            }

            if (!is_array($actionFields)) {
                $actionFields = [];
            }
        }

        if (is_string($entity)) {
            // Json decode può tornare null se non è decodificabile, in tal caso
            // non viene inserito nulla
            $actionFields = json_decode($entity, true);
        }

        // Se non è un array allora vuol dire che non può essere inserito..
        // perché ho previsto che qua alla peggio mi arrivi un array vuoto
        if (is_array($actionFields)) {
            $merged = array_filter(array_merge($actionFields, $data));
            return $merged ? json_encode($merged, true) : null;
        }

        return null;
    }

    public function deleteEntityByName($name)
    {
        $entityId = @$this->selected_db->get_where('entity', array('entity_name' => $name))->row()->entity_id;
        if (!$entityId) {
            return false;
        }

        $this->delete_entity($entityId, $name);
        return true;
    }

    public function delete_entity($entity_id, $entity_name)
    {
        if ($entity_id === null && !empty($entity_name)) {
            $entity_id = $this->selected_db->where('entity_name', $entity_name)->get('entity')->row()->entity_id;
        }
        // Cancella grids e layout box contenitore
        $grids = $this->selected_db->get_where('grids', array('grids_entity_id' => $entity_id))->result_array();
        foreach ($grids as $grid) {
            $this->selected_db->delete('grids', array('grids_id' => $grid['grids_id']));
            $this->selected_db->delete(
                'layouts_boxes',
                array(
                    'layouts_boxes_content_type' => 'grid',
                    'layouts_boxes_content_ref' => $grid['grids_id'],
                )
            );
        }

        // Cancella forms e layout box contenitore
        $forms = $this->selected_db->get_where('forms', array('forms_entity_id' => $entity_id))->result_array();
        foreach ($forms as $form) {
            $this->selected_db->delete('forms', array('forms_id' => $form['forms_id']));
            $this->selected_db->delete(
                'layouts_boxes',
                array(
                    'layouts_boxes_content_type' => 'form',
                    'layouts_boxes_content_ref' => $form['forms_id'],
                )
            );
        }

        // Cancella maps e layout box contenitore
        $maps = $this->selected_db->get_where('maps', array('maps_entity_id' => $entity_id))->result_array();
        foreach ($maps as $map) {
            $this->selected_db->delete('maps', array('maps_id' => $map['maps_id']));
            $this->selected_db->delete(
                'layouts_boxes',
                array(
                    'layouts_boxes_content_type' => 'map',
                    'layouts_boxes_content_ref' => $map['maps_id'],
                )
            );
        }

        // Cancella calendars e layout box contenitore
        $calendars = $this->selected_db->get_where('calendars', array('calendars_entity_id' => $entity_id))->result_array();
        foreach ($calendars as $calendar) {
            $this->selected_db->delete('calendars', array('calendars_id' => $calendar['calendars_id']));
            $this->selected_db->delete(
                'layouts_boxes',
                array(
                    'layouts_boxes_content_type' => 'calendar',
                    'layouts_boxes_content_ref' => $calendar['calendars_id'],
                )
            );
        }

        // Cancella i chart elements relativi all'entità
        $this->selected_db->delete('charts_elements', array('charts_elements_entity_id' => $entity_id));

        // Cancella cron e post process
        $this->selected_db->delete('crons', array('crons_entity_id' => $entity_id));
        $this->selected_db->delete('post_process', array('post_process_entity_id' => $entity_id));

        // Cancella fields
        $this->selected_db->delete('fields', array('fields_entity_id' => $entity_id));

        // Infine cancelliamo l'entità
        $this->selected_db->query("DELETE FROM entity WHERE entity_id = '$entity_id'");
        if ($this->selected_db->table_exists($entity_name)) {
            $this->dbforge->drop_table($entity_name);
        }

        $this->general->addLog("Delete entity", array('entity_name' => $entity_name));
    }

    public function addFields($dati, $check_field_exists_in_table = false)
    {
        ////$this->selected_db->trans_start();
        if (empty($dati['entity_id'])) {
            exit();
        }

        $unique_fields = [];
        foreach ($dati['fields'] as $key => $field_data) {
            if (in_array($field_data['fields_name'], $unique_fields)) {
                unset($dati['fields'][$key]);
            } else {
                $unique_fields[] = $field_data['fields_name'];
            }
        }

        if (isset($dati['fields_id'])) {
            $this->general->addLog("Edit entity field");
            $return = $this->editField($dati);
        } else {
            $this->general->addLog("Create entity field");
            $return = $this->createField($dati, $check_field_exists_in_table);
        }

        return $return;
        //$this->selected_db->trans_complete();
    }

    private function normalizeFieldList(array $fields, $entityId)
    {
        $out = [];
        $entityName = $this->selected_db->get_where('entity', ['entity_id' => $entityId])->row()->entity_name;

        foreach ($fields as $field) {
            $name = strtolower(trim($field['fields_name']));

            // Skip se nome non impostato
            if (!$name) {
                continue;
            }

            $field['fields_name'] = "{$entityName}_{$name}";
            $field['fields_visible'] = isset($field['fields_visible']) ? $field['fields_visible'] : DB_BOOL_TRUE;
            $field['fields_preview'] = isset($field['fields_preview']) ? $field['fields_preview'] : DB_BOOL_FALSE;
            $field['fields_required'] = isset($field['fields_required']) ? $field['fields_required'] : FIELD_NOT_REQUIRED;
            $field['fields_multilingual'] = isset($field['fields_multilingual']) ? $field['fields_multilingual'] : DB_BOOL_FALSE;
            $field['fields_xssclean'] = isset($field['fields_xssclean']) ? $field['fields_xssclean'] : DB_BOOL_FALSE;
            $field['fields_ref_auto_left_join'] = isset($field['fields_ref_auto_left_join']) ? $field['fields_ref_auto_left_join'] : DB_BOOL_FALSE;
            $field['fields_ref_auto_right_join'] = isset($field['fields_ref_auto_right_join']) ? $field['fields_ref_auto_right_join'] : DB_BOOL_FALSE;

            $nullables = ['fields_type', 'fields_default', 'fields_size', 'fields_select_where', 'fields_ref', 'fields_source', 'fields_draw_html_type'];
            foreach ($nullables as $k) {
                //if (empty($field[$k])) {
                if (!array_key_exists($k, $field) || $field[$k] === null || $field[$k] === '') { //0 deve essere accettato
                    $field[$k] = null;
                }
            }

            $field['fields_type'] = $field['fields_type'] ?: 'VARCHAR';
            $field['fields_draw_html_type'] = $field['fields_draw_html_type'] ?: 'input_text';

            $field['fields_entity_id'] = $entityId;

            // Se il ref è impostato devo renderlo visibile altrimenti non lo joina
            if ($field['fields_ref']) {
                $field['fields_visible'] = DB_BOOL_TRUE;
            }

            $out[] = $field;
        }
        //debug($out, true);
        return $out;
    }

    private function upsertFieldsDraw(array $field, $fieldId)
    {
        // Prepare data
        $entityName = $this->selected_db->get_where('entity', ['entity_id' => $field['fields_entity_id']])->row()->entity_name;
        $label = ucfirst(str_replace('_', ' ', preg_replace("/{$entityName}_/", '', $field['fields_name'])));

        $htmlType = $field['fields_draw_html_type'];
        $displayNone = isset($field['fields_draw_display_none']) ? $field['fields_draw_display_none'] : (($field['fields_visible'] == DB_BOOL_FALSE) ? DB_BOOL_TRUE : DB_BOOL_FALSE);

        // Check if exists
        $draw = $this->selected_db->get_where('fields_draw', ['fields_draw_fields_id' => $fieldId]);
        if ($draw->num_rows()) {
            $this->selected_db->update('fields_draw', ['fields_draw_html_type' => $htmlType], ['fields_draw_fields_id' => $fieldId]);
            return;
        }

        // If not exists create it
        $this->selected_db->insert('fields_draw', [
            'fields_draw_fields_id' => $fieldId,
            'fields_draw_label' => $label,
            'fields_draw_html_type' => $htmlType,
            'fields_draw_display_none' => $displayNone,
        ]);
    }

    /**
     * Modifica di un campo già esistente
     */
    private function editField($dati)
    {
        foreach ($dati['fields'] as $key => $field) {
            $dati['fields'][$key]['fields_name'] = url_title($field['fields_name'], '_', true);
        }
        $return = [];

        $entity = $this->selected_db->get_where('entity', ['entity_id' => $dati['entity_id']])->row();
        $entity_name = $entity->entity_name;
        if (isset($entity->entity_action_fields) && $entity->entity_action_fields) {
            $entityActionFields = json_decode($entity->entity_action_fields, true);
        }

        $old = $this->selected_db->get_where('fields', ['fields_id' => $dati['fields_id']])->row_array();

        $fields = $this->normalizeFieldList($dati['fields'], $dati['entity_id']);

        //debug($fields,true);
        $fields = $this->changeMySqlTypes($fields);
        foreach ($fields as $field) {
            $return[$field['fields_name']] = $dati['fields_id'];
            $this->upsertFieldsDraw($field, $dati['fields_id']);
            unset($field['fields_draw_html_type'], $field['fields_draw_display_none']);

            $this->selected_db->update('fields', $field, ['fields_id' => $dati['fields_id']]);
        }

        //debug($fields,true);
        foreach ($fields as $field) {
            //            $this->upsertFieldsDraw($field, $dati['fields_id']);
            //            unset($field['fields_draw_html_type'], $field['fields_draw_display_none']);
            //            $this->selected_db->update('fields', $field, ['fields_id' => $dati['fields_id']]);
            // Rinomino la colonna && se questo field ha una action speciale
            // associata allora la rinomino nel json

            if ($field['fields_size']) {
                $add_field_size = "({$field['fields_size']})";
            } else {
                $add_field_size = '';
            }

            if ($old['fields_name'] != $field['fields_name']) {
                if ($this->selected_db->dbdriver == 'postgre') {
                    $this->selected_db->query("ALTER TABLE {$entity_name} RENAME COLUMN {$old['fields_name']} TO {$field['fields_name']}");
                } else {
                    $this->selected_db->query("ALTER TABLE {$entity_name} CHANGE {$old['fields_name']} {$field['fields_name']} {$field['fields_type']}{$add_field_size}");
                }

                // Vedo se è settata la delle $entityActionFields contenente le
                // coppie chiave valore dove la chiave è il tipo di azione e il
                // valore è il nome del campo. Prendo tutte quelle che puntano a
                // questo field e le inserisco nel json
                if (isset($entityActionFields) && ($toOverride = array_keys($entityActionFields, $old['fields_name']))) {
                    $jsonOrNull = $this->processEntityCustomActionFields($entity->entity_action_fields, array_fill_keys($toOverride, $field['fields_name']));
                    $this->selected_db->update('entity', ['entity_action_fields' => $jsonOrNull], ['entity_id' => $dati['entity_id']]);
                }
            }

            // Cambio il tipo

            if (
                $old['fields_type'] !=
                $field['fields_type'] || $old['fields_size'] !=
                $field['fields_size']
            ) {
                if ($this->selected_db->dbdriver == 'postgre') { //Postgres permette l'autocasting in fase di alter
                    if ($field['fields_type'] == 'INT') {
                        $append_using = " USING {$field['fields_name']}::integer";
                    } elseif ($field['fields_type'] == 'FLOAT') {
                        $append_using = " USING {$field['fields_name']}::float";
                    } elseif (in_array(strtolower($field['fields_type']), ['timestamp without time zone', 'datetime'])) {
                        $append_using = " USING {$field['fields_name']}::TIMESTAMP WITHOUT TIME ZONE";
                        $field['fields_type'] = 'TIMESTAMP WITHOUT TIME ZONE';
                    } else {
                        $append_using = '';
                    }
                } else { //Mysql va fatto manualmente prima dell'alter table...
                    if ($field['fields_type'] == 'integer') {
                        $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = NULL WHERE {$field['fields_name']} = ''");
                        $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = CAST({$field['fields_name']} AS integer)");
                        $append_using = '';
                        // } elseif ($field['fields_type'] == 'FLOAT') {
                        //     $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = CAST({$field['fields_name']} AS integer)");
                        // } elseif (in_array(strtolower($field['fields_type']), ['timestamp without time zone', 'datetime'])) {
                        //     $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = CAST({$field['fields_name']} AS integer)");
                    } else {
                        $append_using = '';
                    }
                }

                if ($this->selected_db->dbdriver == 'postgre' && $field['fields_type'] == 'LONGTEXT') {
                    $field['fields_type'] = 'TEXT';
                }

                $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$this->type_text} {$field['fields_type']}{$add_field_size}{$append_using}");
            }

            //            foreach($defaults as $field_name => $default_expression) {
            //                if ($this->selected_db->dbdriver != 'postgre') {
            //                    foreach ($fields as $field) {
            //                        if ($field['fields_name'] == $field_name) {
            //                            $null_text = ($field['fields_required']==DB_BOOL_TRUE)?'NOT NULL':'NULL';
            //                            $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} {$field['fields_type']} {$null_text} DEFAULT '{$default_expression}'");
            //                            break;
            //                        }
            //                    }
            //                } else {
            //                    $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} SET DEFAULT '{$default_expression}'");
            //                }
            //
            //            }
            //
            //            foreach($not_nulls as $field_name) {
            //                // Forse dovrei farlo sempre non solo con i not null...
            //                $this->selected_db->query("UPDATE {$entity_name} SET {$field_name} = DEFAULT WHERE {$field_name} IS NULL");
            //                if ($this->selected_db->dbdriver == 'postgre') { // Se sono su mysql l'ho già fatto prima
            //                    $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} SET NOT NULL");
            //                }
            //            }
            //
            // Imposto il default
            // debug($old);
            // debug($field, true);

            //if (!empty($field['fields_default']) &&
            if (
                !($field['fields_default'] === null or $field['fields_default'] === '') && //0 deve essere considerato come valore e non empty
                $old['fields_default'] !== //Come sopra, anche il vecchio campo, se era vuoto e ora è 0 son due cose diverse
                $field['fields_default']
            ) {
                //$set_drop = ($field['fields_default'] ? 'SET' : 'DROP');
                $set_drop = (($field['fields_default'] !== '' && $field['fields_default'] !== null) ? 'SET' : 'DROP');
                if ($this->selected_db->dbdriver != 'postgre') {
                    $append_required = '';
                    if ($old['fields_required'] != $field['fields_required']) {
                        if ($field['fields_required'] == FIELD_REQUIRED) {
                            $append_required = ' NOT NULL';
                        } else {
                            $append_required = ' NULL';
                        }
                    }
                    if ($set_drop == 'SET') {
                        if (strtoupper($field['fields_type']) == 'VARCHAR') {
                            $size_text = ($field['fields_size']) ? "({$field['fields_size']})" : "(255)";
                        } else {
                            $size_text = '';
                        }
                        //Mi assicuro che inizi e finisca con '...
                        $field['fields_default'] = "'" . trim($field['fields_default'], "'") . "'";

                        if ($field['fields_required'] == FIELD_REQUIRED) { //Prima di modificare la struttura, setto a default tutti i valori
                            $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = {$field['fields_default']} WHERE {$field['fields_name']} IS NULL");
                        }

                        $query = "ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$field['fields_type']}{$size_text} {$append_required} DEFAULT {$field['fields_default']}";
                        //die($query);
                        $this->selected_db->query($query);
                    } else {
                        $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$field['fields_type']}");
                    }
                } else {
                    $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$set_drop} DEFAULT {$field['fields_default']}");
                }
            }

            // Imposto il required
            //debug($old);
            //$old['fields_required'] = str_ireplace(['t', 'f'], ['1', '0'], $old['fields_required']);
            //debug($old);

            if ($old['fields_required'] != $field['fields_required']) {
                if ($this->selected_db->dbdriver != 'postgre') {
                    //20191121 - MP - Spostato sopra perchè altrimenti perdeva il default value.
                    if (strtoupper($field['fields_type']) == 'VARCHAR') {
                        $size_text = ($field['fields_size']) ? "({$field['fields_size']})" : "(255)";
                    } else {
                        $size_text = '';
                    }
                    $field['fields_default'] = "'" . trim($field['fields_default'], "'") . "'";

                    if ($field['fields_required'] == FIELD_REQUIRED) {
                        $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = {$field['fields_default']} WHERE {$field['fields_name']} IS NULL");
                    }

                    $set_drop = ($field['fields_required'] == FIELD_REQUIRED ? 'NOT NULL' : 'NULL');
                    $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$field['fields_type']}{$size_text} {$set_drop} ");
                } else {
                    if ($field['fields_required'] == FIELD_REQUIRED) {
                        $this->selected_db->query("UPDATE {$entity_name} SET {$field['fields_name']} = DEFAULT WHERE {$field['fields_name']} IS NULL");

                        // echo $this->selected_db->last_query();
                    }
                    //debug($field);
                    // if ($entity_name == 'interni_tipo') {
                    //     print_r($old);
                    //     print_r($field);
                    //     die('test');
                    // }

                    $set_drop = (($field['fields_required'] == FIELD_REQUIRED) ? 'SET' : 'DROP');
                    $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field['fields_name']} {$set_drop} NOT NULL");

                    //echo $this->selected_db->last_query();
                }
            }

            // Considero un campo che diventa multilingua
            if ($old['fields_multilingual'] != $field['fields_multilingual']) {
                if ($field['fields_multilingual'] == DB_BOOL_TRUE) {
                    // Il campo è passato da normale a multilingua
                    $this->convertFieldToJson($entity_name, $field);
                } else {
                    /**
                     * @todo
                     */
                }
            }
        }

        return $return;
    }
    //
    private function changeMySqlTypes($fields)
    {
        if ($this->selected_db->dbdriver != 'postgre') {
            foreach ($fields as $key => $field) {
                switch (strtoupper($field['fields_type'])) {
                    case 'SERIAL UNIQUE':
                        $fields[$key]['fields_type'] = 'INT';
                        //                        $fields[$key]['unsigned'] = true;
                        //                        $fields[$key]['constraint'] = 5;

                        break;
                    case 'VARCHAR':
                        $fields[$key]['fields_type'] = "varchar";
                        if (empty($field['fields_size'])) {
                            $fields[$key]['fields_size'] = 250;
                        }

                        break;
                    case 'BOOL':
                        $fields[$key]['fields_type'] = "BOOLEAN";
                        // debug($fields[$key]);
                        // if (!($field['fields_default'] === null or $field['fields_default'] === '') && ($fields[$key]['fields_default'] == DB_BOOL_TRUE || $fields[$key]['fields_default'] == '1')) {
                        //     $fields[$key]['fields_default'] = TRUE;
                        // } else {
                        //     $fields[$key]['fields_default'] = FALSE;
                        // }
                        // debug($fields[$key]);
                        break;
                    case 'INT':
                        $fields[$key]['fields_type'] = "integer";
                        break;
                    case 'FLOAT':
                    case 'DOUBLE':
                        $fields[$key]['fields_type'] = "double";
                        $fields[$key]['fields_size'] = '18,9';
                        break;
                    case 'JSON':
                        $fields[$key]['fields_type'] = "TEXT";

                        break;
                    case 'TEXT': //Su mysql i TEXT li faccio comunque diventare LONGTEXT in quanto postgres TEXT ha molti più caratteri di mysql TEXT
                    case 'LONGTEXT':
                        $fields[$key]['fields_type'] = "LONGTEXT";
                        break;
                    case 'TIMESTAMP WITHOUT TIME ZONE':

                    case 'TIMESTAMP':
                    case 'DATETIME':

                        if (!empty($field['fields_default']) && ($field['fields_default'] == 'NOW()' || $field['fields_default'] == 'now()')) {
                            $fields[$key]['fields_type'] = "DATETIME DEFAULT CURRENT_TIMESTAMP";
                            unset($fields[$key]['fields_default']);
                        } else {
                            $fields[$key]['fields_type'] = "DATETIME";
                        }

                        break;
                    case 'DATETIME DEFAULT CURRENT_TIMESTAMP':

                        break;
                    case 'BOOLEAN':
                    case 'BIGINT':
                        break;
                    case 'GEOGRAPHY':
                        break;
                    case 'POLYGON':
                        break;
                    case 'INTEGER':
                        //Non serve far nulla, va bene così
                        break;
                    default:
                        debug($field, true);
                        break;
                }
            }
        }

        return $fields;
    }

    public function checkFields($dati)
    {
        foreach ($dati['fields'] as $key => $field) {
            if ($field['fields_name'] == 'id') {
                return false;
            } else {
                return true;
            }
        }
    }

    private function createField($dati, $check_field_exists_in_table = false)
    {
        foreach ($dati['fields'] as $key => $field) {
            $dati['fields'][$key]['fields_name'] = url_title($field['fields_name'], '_', true);
        }
        //debug($dati,true);

        $entity_name = $this->selected_db->query("SELECT * FROM entity WHERE entity_id = '{$dati['entity_id']}'")->row()->entity_name;

        //        if ($entity_name == 'layouts') {
        //            debug($dati,true);
        //        }

        $dForm = $this->get_default_form($dati['entity_id']);
        $dGrid = $this->get_default_grid($dati['entity_id']);
        $fields = $this->normalizeFieldList($dati['fields'], $dati['entity_id']);

        $fields = $this->changeMySqlTypes($fields);

        $return = [];

        foreach ($fields as $field) {
            $fieldOrig = $field;
            $addToDgrid = isset($field['on_default_grid']) ? $field['on_default_grid'] : false;
            $addToDform = isset($field['on_default_form']) ? $field['on_default_form'] : false;

            unset($field['fields_draw_html_type'], $field['fields_draw_display_none'], $field['on_default_grid'], $field['on_default_form']);

            $this->selected_db->insert('fields', $field);
            $fieldId = $this->selected_db->insert_id();

            $return[$field['fields_name']] = $fieldId;

            $this->upsertFieldsDraw($fieldOrig, $fieldId);

            if ($dForm && $addToDform) {
                $this->selected_db->insert('forms_fields', ['forms_fields_forms_id' => $dForm['forms_id'], 'forms_fields_fields_id' => $fieldId, 'forms_fields_override_colsize' => '6']);
            }
            if ($dGrid && $addToDgrid) {
                $field_draw = $this->selected_db->where('fields_draw_fields_id', $fieldId)->get('fields_draw')->row();
                if ($field_draw->fields_draw_html_type != 'input_password') {
                    $this->selected_db->insert('grids_fields', ['grids_fields_grids_id' => $dGrid['grids_id'], 'grids_fields_fields_id' => $fieldId]);
                }
            }
        }

        // Altero la tabella con le nuove colonne
        $columns = [];
        $defaults = [];
        $not_nulls = [];

        // Valuto se la tabella è già piena, perché in tal caso non posso
        // mettere il vincolo di NOT NULL se non ho un DEFAULT impostato
        // echo '<pre>';
        // print_r($this->selected_db);
        $this->selected_db->cache_delete_all();
        $this->selected_db->data_cache = [];
        $emptyTable = $this->selected_db->count_all($entity_name) === 0;

        foreach ($fields as $field) {
            $this->selected_db->cache_delete_all();
            $this->selected_db->data_cache = [];
            //Controllo, se il campo esiste già skippo.
            if ($this->selected_db->field_exists($field['fields_name'], $entity_name)) {
                continue;
            }
            //debug($field['fields_name'].' non trovato');

            if (strtolower($field['fields_type']) == 'geography') {
                // Facciamo qua attraverso una query a mano perché CI non è
                // POSTGIS-aware
                if ($this->selected_db->dbdriver != 'postgre') {
                    $this->selected_db->query("ALTER TABLE {$entity_name} ADD COLUMN {$field['fields_name']} POINT;");
                } else {
                    $this->selected_db->query("ALTER TABLE {$entity_name} ADD COLUMN {$field['fields_name']} GEOGRAPHY(POINT,4326);");
                }
                continue;
            }

            if (
                strtolower($field['fields_type']) == 'polygon' || strtolower($field['fields_type']) == 'multipolygon'
                //Tolta la geometry collection. Non serve più da quando il cerchio viene trattato come polygono. potrebbe aver senso rimetterlo per altri tipi di geometry (punto linea, multipunto...). Vedremo...
                //|| strtolower($field['fields_type']) == 'geometrycollection'
            ) {
                if ($this->db->dbdriver == 'postgre') {
                    $this->selected_db->query("ALTER TABLE {$entity_name} ADD COLUMN {$field['fields_name']} GEOGRAPHY(" . strtoupper($field['fields_type']) . ",4326);");
                    //20200408 - Matteo - Perchè continue? Indagare... ha sempre funzionato, ma non son sicuro visto che dopo processa se required, multilingua, null, ecc....
                    continue;
                }
            }

            $isRequired = $field['fields_required'] == FIELD_REQUIRED;

            $col = [
                'type' => ($field['fields_multilingual'] == DB_BOOL_TRUE) ? 'json' : $field['fields_type'],
                'constraint' => $field['fields_size'],
            ];

            // Il campo è nullabile se NON è required
            // OPPURE se la tabella NON è VUOTA
            if (!$isRequired || !$emptyTable) {
                $col['null'] = true;
            } else {
                $col['null'] = false;
            }

            //debug($field, true);
            //if (!empty($field['fields_default'])) {
            if (!($field['fields_default'] === null or $field['fields_default'] === '')) { //Devo accettare anche lo 0 come default value
                $defaults[$field['fields_name']] = $field['fields_default'];

                // La tabella è piena e il campo required, per ora lo
                // setto come nullable e lo metto più tardi come not null
                // dato che ho il campo di default
                if ($isRequired && !$emptyTable) {
                    $not_nulls[] = $field['fields_name'];
                }
            }

            if ($this->selected_db->dbdriver == 'postgre' && $col['type'] == 'DATETIME') {
                $col['type'] = 'TIMESTAMP WITHOUT TIME ZONE';
            }

            if ($this->selected_db->dbdriver != 'postgre' && $col['type'] == 'VARCHAR' && (!array_key_exists('null', $col) || $col['null'] == '1')) {
                if (array_key_exists('constraint', $col) && $col['constraint'] != '') {
                    $col['type'] = 'VARCHAR';
                } else {
                    $col['type'] = 'VARCHAR(255)';
                }
            }

            //debug($col,true);

            //            if ($field['fields_name'] == 'layouts_subtitle') {
            //                debug($col);
            //                debug($field);
            //            }
            $columns[$field['fields_name']] = $col;
        }

        if ($columns) {
            //debug($columns,true);
            $this->dbforge->add_column($entity_name, $columns);
        }
        //        debug($fields);
        //debug($defaults);
        //20230116 - MP - Spostato prima questa query così dopo potrà fare correttamente gli alter table senza avere colonne null e generando l'errore data truncated....
        foreach ($not_nulls as $field_name) {
            // Forse dovrei farlo sempre non solo con i not null...
            $this->selected_db->query("UPDATE {$entity_name} SET {$field_name} = DEFAULT WHERE {$field_name} IS NULL");
            if ($this->selected_db->dbdriver == 'postgre') { // Se sono su mysql l'ho già fatto prima
                $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} SET NOT NULL");
            }
        }
        foreach ($defaults as $field_name => $default_expression) {
            //debug("test: {$default_expression}", true);
            if ($this->selected_db->dbdriver != 'postgre') {
                foreach ($fields as $field) {
                    if ($field['fields_name'] == $field_name) {
                        $null_text = ($field['fields_required'] == FIELD_REQUIRED) ? 'NOT NULL' : 'NULL';
                        if (strtoupper($field['fields_type']) == 'VARCHAR') {
                            $size_text = ($field['fields_size']) ? "({$field['fields_size']})" : "(255)";
                        } else {
                            $size_text = '';
                        }
                        //20230116 - MP - Fix per forzare al valore di default eventuali colonne null che stanno per essere modificate
                        if ($field['fields_required'] == FIELD_REQUIRED) {
                            $this->selected_db->query("UPDATE {$entity_name} SET {$field_name} = '$default_expression' WHERE {$field_name} IS NULL");
                        }
                        $query = "ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} {$field['fields_type']}{$size_text} {$null_text} DEFAULT '{$default_expression}'";
                        //die($query);
                        $this->selected_db->query($query);
                        break;
                    }
                }
            } else {
                $this->selected_db->query("ALTER TABLE {$entity_name} {$this->alter_text} {$field_name} SET DEFAULT '{$default_expression}'");
            }
        }

       

        return $return;
    }

    private function convertFieldToJson($table, $field)
    {
        $column = $field['fields_name'];
        $isRequired = $field['fields_required'] == FIELD_REQUIRED;
        $tmpName = uniqid('tmp_field_' . $column);

        $this->dbforge->add_column($table, [
            $tmpName => ['type' => 'json'],
        ]);

        $this->selected_db->query("UPDATE {$table} SET {$tmpName} = ('{\"1\":' || to_json({$column}) || '}')::JSON");

        // DB Forge è inaffidabile, come CI d'altronde: se io do il
        // modify_column con solo la chiave name, lui comincia a cambiare le
        // impostazioni del NOT NULL e via dicendo,
        // ----
        /* $this->dbforge->modify_column($table, [
        $column => ['name' => '_removed_' . $column],
        $tmpName => ['name' => $column],
        ]); */
        // ----
        // In produzione il sistema dovrà droppare il vecchio field per
        // rimpiazzarlo con quello nuovo, però lascio l'alter table nel caso in
        // cui ci siano problemi
        // ----
        $this->dbforge->drop_column($table, $column);
        //$this->selected_db->query("ALTER TABLE {$table} RENAME COLUMN {$column} TO _{$column}");
        $this->selected_db->query("ALTER TABLE {$table} RENAME COLUMN {$tmpName} TO {$column}");

        if ($isRequired) {
            $this->selected_db->query("ALTER TABLE {$table} {$this->alter_text} {$column} SET NOT NULL");
        }
    }

    public function new_layout($dati)
    {
        $dati['layouts_cachable'] = (empty($dati['layouts_cachable']) || $dati['layouts_cachable'] == DB_BOOL_FALSE) ? DB_BOOL_FALSE : DB_BOOL_TRUE;

        $this->selected_db->insert('layouts', $dati);
        $layout_id = $this->selected_db->insert_id();
        //$login_entity = $this->selected_db->get_where('entity', ['entity_login_entity' => DB_BOOL_TRUE])->row()->entity_name;
        $login_entity = $this->get_login_entity()['entity_name'];
        $users = $this->selected_db->where("{$login_entity}_id NOT IN (SELECT permissions_user_id FROM permissions WHERE permissions_admin = '" . DB_BOOL_TRUE . "' AND permissions_user_id IS NOT NULL)", null, false)->get($login_entity)->result_array();

        foreach ($users as $user) {
            $userID = $user[$login_entity . '_id'];
            $this->selected_db->insert('unallowed_layouts', [
                'unallowed_layouts_layout' => $layout_id,
                'unallowed_layouts_user' => $userID,
            ]);
        }

        return $layout_id;
    }

    public function edit_layout($dati, $id = null)
    {
        if (!$id) {
            if (!isset($dati['layouts_id'])) {
                throw new LogicException("To modify a layout pass its id");
            }

            $id = $dati['layouts_id'];
        }

        unset($dati['layouts_id']);
        $this->selected_db->update('layouts', $dati, ['layouts_id' => $id]);
    }

    public function new_menu($dati)
    {
        $dati['menu_parent'] = empty($dati['menu_parent']) ? null : $dati['menu_parent'];
        $dati['menu_link'] = empty($dati['menu_link']) ? null : $dati['menu_link'];
        $dati['menu_icon_class'] = empty($dati['menu_icon_class']) ? null : $dati['menu_icon_class'];

        $this->selected_db->insert('menu', $dati);
    }

    public function edit_menu($dati)
    {
        $dati['menu_parent'] = empty($dati['menu_parent']) ? null : $dati['menu_parent'];
        $dati['menu_link'] = empty($dati['menu_link']) ? null : $dati['menu_link'];
        $dati['menu_icon_class'] = empty($dati['menu_icon_class']) ? null : $dati['menu_icon_class'];

        if (isset($dati['menu_id'])) {
            $this->selected_db->where('menu_id', $dati['menu_id'])->update('menu', $dati);

            if (isset($dati['menu_position'])) {
                $this->selected_db->update('menu', ['menu_position' => $dati['menu_position']], ['menu_parent' => $dati['menu_id']]);
            }
        }
    }

    public function new_calendar($dati)
    {
        try {
            $this->selected_db->insert('calendars', $dati);
            return $this->db->insert_id();
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            die(json_encode(['status' => 0, 'txt' => "Error while saving calendar."]));
        }
    }

    public function edit_calendar($dati)
    {
        if (isset($dati['calendars_id'])) {
            if (empty($dati['calendars_filter_entity_id'])) {
                $dati['calendars_filter_entity_id'] = null;
            }
            $return = $this->selected_db->where('calendars_id', $dati['calendars_id'])->update('calendars', $dati);
        }
        return $return;
    }

    public function new_map($dati)
    {
        $this->selected_db->insert('maps', $dati);
    }

    public function edit_map($dati)
    {
        if (isset($dati['maps_id'])) {
            $this->selected_db->where('maps_id', $dati['maps_id'])->update('maps', $dati);
        }
    }

    public function support_table($dati, $customizable_grid = true)
    {
        // Creo la tabella di supporto
        $table_name = $dati['table_name'];
        $fields = array(
            $table_name . '_id' => array(
                'type' => ($this->selected_db->dbdriver == 'postgre') ? 'SERIAL UNIQUE' : 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ),
            $table_name . '_value' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            //TODO: creare campo ordinamento
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key($table_name . '_id', true);
        $this->dbforge->create_table($table_name, true);

        // Aggiunto come entità
        $entity = array('entity_name' => $table_name, 'entity_type' => ENTITY_TYPE_SUPPORT_TABLE, 'entity_searchable' => DB_BOOL_FALSE);

        if ($this->selected_db->where('entity_name', $table_name)->get('entity')->num_rows() < 1) {
            $this->selected_db->insert('entity', $entity);
            $entity_id = $this->selected_db->insert_id();
        } else {
            $entity_id = $this->selected_db->where('entity_name', $table_name)->get('entity')->row()->entity_id;
        }

        // Aggiungo i campi
        $id_query = $this->selected_db->where('fields_name', $table_name . '_id')->get('fields');
        if ($id_query->num_rows() < 1) {
            $this->selected_db->insert(
                'fields',
                array(
                    'fields_name' => $table_name . '_id',
                    'fields_preview' => DB_BOOL_FALSE,
                    'fields_entity_id' => $entity_id,
                    'fields_type' => 'INT',
                    'fields_visible' => DB_BOOL_TRUE,
                    //20190322 - Matteo: messo a False altrimenti non funzionavano le support table inline edit
                    'fields_required' => FIELD_NOT_REQUIRED,
                    'fields_default' => '',
                )
            );
            $id_field_id = $this->selected_db->insert_id();
        } else {
            $id_field_id = $id_query->row()->fields_id;
        }

        $value_query = $this->selected_db->where('fields_name', $table_name . '_value')->get('fields');
        if ($value_query->num_rows() < 1) {
            $this->selected_db->insert(
                'fields',
                array(
                    'fields_name' => $table_name . '_value',
                    'fields_preview' => DB_BOOL_TRUE,
                    'fields_entity_id' => $entity_id,
                    'fields_type' => 'VARCHAR',
                    'fields_visible' => DB_BOOL_TRUE,
                    'fields_default' => '',

                )
            );
            $value_field_id = $this->selected_db->insert_id();
        } else {
            $value_field_id = $value_query->row()->fields_id;
        }

        // Assegno il ref al fields originale
        if (array_key_exists('fields_id', $dati)) {
            $original_fields_id = $dati['fields_id'];
            $this->selected_db->query("UPDATE fields SET fields_ref = '$table_name', fields_ref_auto_left_join = '" . DB_BOOL_TRUE . "' WHERE fields_id = '$original_fields_id'");
        }

        // Inserisco i valori
        //debug($dati['support_table'], true);
        foreach ($dati['support_table'] as $value) {
            $val = array($table_name . '_value' => $value['value']);
            $this->selected_db->insert($table_name, $val);
        }

        // Devo creare i fields draw per i campi altrimenti non saranno visibili - creati in ogni caso perché devo avere la label
        $this->selected_db->insert(
            'fields_draw',
            array(
                'fields_draw_fields_id' => $id_field_id,
                'fields_draw_label' => 'ID ' . ucwords(str_replace('_', ' ', $table_name)),
                'fields_draw_html_type' => 'input_text',
            )
        );
        $this->selected_db->insert(
            'fields_draw',
            array(
                'fields_draw_fields_id' => $value_field_id,
                'fields_draw_label' => ucwords(str_replace('_', ' ', $table_name)),
                'fields_draw_html_type' => 'input_text',
            )
        );
        //$this->db->query("SET FOREIGN_KEY_CHECKS=0");
        //Creo il form per la grids_inline_edit_form che creerò dopo
        $this->selected_db->insert('forms', [
            'forms_entity_id' => $entity_id,
            'forms_name' => 'Inline edit ' . str_replace('_', ' ', $table_name),
            'forms_layout' => 'horizontal',
        ]);

        //die($this->db->last_query());

        $form_id = $this->selected_db->insert_id();

        //Creo i forms fields
        // $this->selected_db->insert('forms_fields', [
        //     'forms_fields_forms_id' => $form_id,
        //     'forms_fields_fields_id' => $id_field_id,
        //     'forms_fields_order' => 1,
        // ]);
        $this->selected_db->insert('forms_fields', [
            'forms_fields_forms_id' => $form_id,
            'forms_fields_fields_id' => $value_field_id,
            'forms_fields_order' => 2,
        ]);

        if ($customizable_grid) {
            // Creo una grid per customizzare i dati della support table
            $this->selected_db->insert(
                'grids',
                array(
                    'grids_entity_id' => $entity_id,
                    'grids_name' => "Customize {$table_name}",
                    //'grids_layout' => 'datatable_ajax_inline',
                    'grids_layout' => 'datatable_ajax_inline_form',
                    'grids_inline_form' => $form_id,
                )
            );
            $grid_id = $this->selected_db->insert_id();
            $this->selected_db->insert('grids_fields', array('grids_fields_grids_id' => $grid_id, 'grids_fields_fields_id' => $id_field_id));
            $this->selected_db->insert('grids_fields', array('grids_fields_grids_id' => $grid_id, 'grids_fields_fields_id' => $value_field_id));

            // Michael - 08/2022 - Creating also a grid_actions record containing delete button
            $this->selected_db->insert(
                'grids_actions',
                array(
                    'grids_actions_grids_id' => $grid_id,
                    'grids_actions_order' => 99,
                    'grids_actions_name' => 'Delete',
                    'grids_actions_icon' => 'fas fa-trash',
                    'grids_actions_type' => 'delete',
                    'grids_actions_color' => 'rgb(233, 30, 99)',
                )
            );

            //TODO: anche qui mettere ordinamento
            // Aggiungi la grid al layout delle impostazioni (se esistente)
            /*
            RIMOSSA QUESTA PARTE QUA IN QUANTO ORA LE SUPPORT TABLE SONO DENTGRO IL LAYOUT SPECIFICO support_tables
            $settings_entity = $this->get_by_name('settings');
            //debug($settings_entity, true);
            if (!empty($settings_entity)) {
            $def_form = $this->get_default_form($settings_entity['entity_id']);
            $layout_box = $this->selected_db->get_where('layouts_boxes', array('layouts_boxes_content_type' => 'form', 'layouts_boxes_content_ref' => $def_form['forms_id']));
            //debug($this->selected_db->last_query(), true);
            if ($layout_box->num_rows() > 0) {
            $this->newSimpleLayoutBox('Customize ' . ucwords(str_replace('_', ' ', $table_name)), $layout_box->row()->layouts_boxes_layout, 'grid', $grid_id, 2, 1, 12);
            }
            }*/
        }
        //$this->db->query("SET FOREIGN_KEY_CHECKS=1");
    }
    //    public function relation($relation_name, $table1, $table2, $rel_type=1) {    //Nomi tabelle, 1 = relazione N a M
    //
    //        $table1_id = $table1 . '_id';
    //        $table2_id = $table2 . '_id';
    //        $rel_name = $relation_name;
    //
    //        $this->db->insert('relations', [
    //            'relations_name' => $rel_name,
    //            'relations_table_1' => $table1,
    //            'relations_table_2' => $table2,
    //            'relations_field_1' => $table1_id,
    //            'relations_field_2' => $table2_id,
    //            'relations_type' => $rel_type,
    //        ]);
    //
    //        $this->dbforge->add_field([
    //            $table1_id => ['type' => "int4 NOT NULL REFERENCES $table1($table1_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED"],
    //            $table2_id => ['type' => "int4 NOT NULL REFERENCES $table2($table2_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED"],
    //        ]);
    //
    //        /************ FIX**********/
    //        // La creo anche come entità per motivi tecnici di fields
    //        $this->entities->new_entity(['entity_name' => $rel_name, 'entity_type' => ENTITY_TYPE_RELATION], false);
    //    }
    public function relation($relation_name, $table1, $table2, $rel_type = 1, $rel_module = '', $entity_field_id = null)
    { //Nomi tabelle, 1 = relazione N a M
        $table1_id = $table1 . '_id';
        $table2_id = $table2 . '_id';
        $rel_name = $relation_name; //$table1 . '_' . $table2;

        $this->selected_db->insert('relations', [
            'relations_name' => $rel_name,
            'relations_table_1' => $table1,
            'relations_table_2' => $table2,
            'relations_field_1' => $table1_id,
            'relations_field_2' => $table2_id,
            'relations_type' => $rel_type,
            'relations_module' => $rel_module,
        ]);

        if ($this->selected_db->dbdriver != 'postgre') {
            $this->dbforge->add_field([
                $table1_id => ['type' => "bigint(20) UNSIGNED"],
                $table2_id => ['type' => "bigint(20) UNSIGNED"],
            ]);
            $this->dbforge->add_field("CONSTRAINT FOREIGN KEY ($table1_id) REFERENCES $table1($table1_id) ON UPDATE CASCADE ON DELETE CASCADE");
            $this->dbforge->add_field("CONSTRAINT FOREIGN KEY ($table2_id) REFERENCES $table2($table2_id) ON UPDATE CASCADE ON DELETE CASCADE");
        } else {
            $this->dbforge->add_field([
                $table1_id => ['type' => "integer NOT NULL REFERENCES $table1($table1_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED"],
                $table2_id => ['type' => "integer NOT NULL REFERENCES $table2($table2_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED"],
            ]);
        }

        /*         * ********** FIX********* */
        // La creo anche come entità per motivi tecnici di fields
        $this->entities->new_entity(['entity_name' => $rel_name, 'entity_type' => ENTITY_TYPE_RELATION], false);

        if ($entity_field_id) {
$field = $this->get_field($entity_field_id);
        
        $entity = $this->get_by_name($table1);
        $field_draw = $this->get_field_draw_by_field($entity_field_id);

        $field['fields_ref'] = $rel_name;

        $field["fields_draw_html_type"] = 'multiselect';
        if (!empty($field_draw['fields_draw_html_type'])) {
            $field["fields_draw_html_type"] = $field_draw['fields_draw_html_type'];
        }
        
        $field['fields_name'] = str_ireplace($entity['entity_name'] . '_', '', $field['fields_name']);

        $dati = [
            'entity_id' => $entity['entity_id'],
            'fields_id' => $entity_field_id,
            'fields' => [$field],
        ];

        $this->editField($dati);
        }
        

        return true;
    }

    public function remove_relation($relation_name, $field_id, $delete = false)
    {
        // @todo Michael E. - 2020-03-19 - To be used for delete relation and related stuff instead of generic delete method

        $this->selected_db->delete('relations', array('relations_name' => $relation_name));

        $entity_id = $this->selected_db->get_where('entity', array('entity_name' => $relation_name, 'entity_type' => ENTITY_TYPE_RELATION))->row()->entity_id;

        /** Eseguo un delete_entity per il precedente FIX * */
        $this->delete_entity($entity_id, $relation_name);

        $field = $this->get_field($field_id);
        $entity = $this->get_entity($field['fields_entity_id']);
        $field_draw = $this->get_field_draw_by_field($field_id);

        $field['fields_ref'] = null;

        $field["fields_draw_html_type"] = 'multiselect';
        if (!empty($field_draw['fields_draw_html_type'])) {
            $field["fields_draw_html_type"] = $field_draw['fields_draw_html_type'];
        }

        $field['fields_name'] = str_ireplace($entity['entity_name'] . '_', '', $field['fields_name']);

        $dati = [
            'entity_id' => $entity['entity_id'],
            'fields_id' => $field_id,
            'fields' => [$field],
        ];

        $field_edited = $this->editField($dati);

        if ($field_edited) {
            return true;
        } else {
            return false;
        }
    }

    /** Metodi utili * */
    public function get_by_name($entity_name)
    {
        $entity = $this->selected_db->get_where('entity', array('entity_name' => $entity_name))->row_array();
        return $entity;
    }

    public function get_entity($entity_id)
    {
        $entity = $this->selected_db->get_where('entity', array('entity_id' => $entity_id))->row_array();
        return $entity;
    }

    public function get_field($field_id)
    {
        $field = $this->selected_db->get_where('fields', array('fields_id' => $field_id))->row_array();

        return $field;
    }

    public function get_field_by_name($field_name)
    {
        $field = $this->selected_db->get_where('fields', array('fields_name' => trim($field_name)))->row_array();

        return $field;
    }

    public function get_field_draw_by_field($field_id)
    {
        $field_draw = $this->selected_db->get_where('fields_draw', array('fields_draw_fields_id' => $field_id))->row_array();

        return $field_draw;
    }

    public function get_field_draw($field_draw_id)
    {
        $field_draw = $this->selected_db->get_where('fields_draw', array('fields_draw_id' => $field_draw_id))->row_array();

        return $field_draw;
    }

    public function get_fields($entity_id, $fields_names = array())
    {
        if ($fields_names) {
            $this->selected_db->where_in('fields_name', (array) $fields_names);
        }

        return $this->selected_db->join('fields_draw', 'fields_draw_fields_id = fields_id', 'LEFT')->where('fields_entity_id', $entity_id)->get('fields')->result_array();
    }

    public function get_default_grid($entity_id)
    {
        $query = $this->selected_db->get_where('grids', array('grids_entity_id' => $entity_id, 'grids_default' => DB_BOOL_TRUE));

        //debug($this->selected_db->last_query());

        return $query->row_array();
    }

    public function get_default_form($entity_id)
    {
        $query = $this->selected_db->get_where('forms', array('forms_entity_id' => $entity_id, 'forms_default' => DB_BOOL_TRUE));
        return $query->row_array();
    }

    public function get_layout_boxes($content_type, $content_ref)
    {
        // Ritorno TUTTI i box che contengono un dato elemento
        return $this->selected_db->get_where('layouts_boxes', array('layouts_boxes_content_type' => $content_type, 'layouts_boxes_content_ref' => $content_ref))->result_array();
    }

    /**
     * Fast creation
     */
    public function newSimpleLayout($title, $is_entity_detail = null, $related_entity_id = null, $subtitle = null, $dashboard = DB_BOOL_FALSE, $layouts_identifier = "")
    {
        $success = $this->selected_db->insert(
            'layouts',
            array(
                'layouts_title' => $title,
                'layouts_is_entity_detail' => $is_entity_detail,
                'layouts_entity_id' => $related_entity_id,
                'layouts_subtitle' => $subtitle,
                'layouts_dashboardable' => $dashboard,
                'layouts_cachable' => DB_BOOL_TRUE,
                'layouts_identifier' => $layouts_identifier,
            )
        );

        return $success ? $this->selected_db->insert_id() : false;
    }

    public function newSimpleLayoutBox($title, $layout_id, $content_type, $content_ref, $row = 1, $pos = 1, $col = 12, $color = 'blue', $css_classes = 'box', $titolable = true)
    {
        $success = $this->selected_db->insert(
            'layouts_boxes',
            array(
                'layouts_boxes_title' => $title,
                'layouts_boxes_layout' => $layout_id,
                'layouts_boxes_content_type' => $content_type,
                'layouts_boxes_content_ref' => $content_ref,
                'layouts_boxes_dragable' => false,
                'layouts_boxes_collapsible' => false,
                'layouts_boxes_collapsed' => false,
                'layouts_boxes_reloadable' => false,
                'layouts_boxes_discardable' => false,
                'layouts_boxes_show_container' => false,
                'layouts_boxes_titolable' => $titolable,
                'layouts_boxes_row' => $row,
                'layouts_boxes_position' => $pos,
                'layouts_boxes_cols' => $col,
                'layouts_boxes_color' => $color,
                'layouts_boxes_css' => $css_classes,
                'layouts_boxes_show_container' => DB_BOOL_TRUE,
            )
        );

        return $success ? $this->selected_db->insert_id() : false;
    }

    public function newSimpleMenu($label, $layout = null, $position = 'sidebar', $order = 1, $icon = null, $class = null, $modal = false)
    {
        $success = $this->selected_db->insert(
            'menu',
            array(
                'menu_label' => $label,
                'menu_layout' => $layout,
                'menu_position' => $position,
                'menu_order' => $order,
                'menu_icon_class' => $icon,
                'menu_css_class' => $class,
                'menu_modal' => (bool) $modal,
                'menu_type' => ($layout) ? 'layout' : null,
            )
        );
        return $success ? $this->selected_db->insert_id() : false;
    }

    public function new_grid($grid_name, $entity_id, $layout, $where = null, $default = false, $fields_names = array(), $builder_where = null)
    {
        // Crea grid
        $this->selected_db->insert(
            'grids',
            array(
                'grids_name' => $grid_name,
                'grids_entity_id' => $entity_id,
                'grids_layout' => $layout,
                'grids_where' => $where,
                'grids_builder_where' => $builder_where,
                'grids_default' => ($default === true || $default === DB_BOOL_TRUE || $default === 1) ? DB_BOOL_TRUE : DB_BOOL_FALSE,
            )
        );
        $grid_id = $this->selected_db->insert_id();

        // Aggiungi campi
        if (!empty($fields_names)) {
            $arr_fields_names = (array) $fields_names;

            foreach ($arr_fields_names as $k => $name) {
                $field = $this->selected_db->get_where('fields', array('fields_name' => $name))->row();

                if (empty($field)) {
                    die("Il field $name non esiste");
                }

                $this->selected_db->insert(
                    'grids_fields',
                    array(
                        'grids_fields_grids_id' => $grid_id,
                        'grids_fields_fields_id' => $field->fields_id,
                        'grids_fields_order' => $k,
                    )
                );
            }
        }

        return $grid_id;
    }
    public function setFieldCustomAction($fieldId, $action, $add)
    {
        $query = $this->selected_db->join('entity', 'entity_id = fields_entity_id')->get_where('fields', ['fields_id' => $fieldId]);

        if (!$query->num_rows()) {
            die('Campo non trovato');
        }

        if (!array_key_exists($action, unserialize(CUSTOM_ACTIONS_FIELDS))) {
            die('Azione non valida');
        }

        $field = $query->row();
        $data = json_decode($field->entity_action_fields ?: '{}', true);

        if ($add) {
            $data[$action] = $field->fields_name;
        } else {
            unset($data[$action]);
        }

        $this->processFieldCustomAction($field, $action, $add);

        $this->selected_db->update('entity', ['entity_action_fields' => $data ? json_encode($data) : null], ['entity_id' => $field->entity_id]);
    }
    public function processFieldCustomAction($field, $action, $add)
    {
        switch ($action) {
            case 'add_foreign_key':
                if ($add) {
                    if ($this->selected_db->dbdriver != 'postgre') {
                        $notnull = '';
                        $ondelete = '';
                        //debug($field);
                        if ($field->fields_required == FIELD_REQUIRED) {
                            $notnull = ' NOT NULL ';
                            $ondelete = ' ON DELETE CASCADE ON UPDATE CASCADE ';
                            $this->selected_db->query("DELETE FROM {$field->entity_name} WHERE {$field->fields_name} NOT IN (SELECT {$field->fields_ref}_id FROM {$field->fields_ref});");
                        } else {
                            $this->selected_db->query("UPDATE {$field->entity_name} SET {$field->fields_name} = NULL WHERE {$field->fields_name} NOT IN (SELECT {$field->fields_ref}_id FROM {$field->fields_ref});");
                            $ondelete = ' ON DELETE SET NULL ON UPDATE CASCADE ';
                        }

                        $this->selected_db->query("ALTER TABLE {$field->entity_name} MODIFY {$field->fields_name} bigint(20) unsigned $notnull"); // Lo forzo a bigint(20) unsigned perché così sono gli id in MySQL e devono essere uguali i due campi

                        // Verifica l'esistenza della foreign key
                        $fkExists = $this->selected_db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$field->entity_name}' AND CONSTRAINT_NAME = '{$field->fields_name}_fkey'")->row();
                        if (!$fkExists) {
                            $this->selected_db->query("ALTER TABLE {$field->entity_name} ADD CONSTRAINT {$field->fields_name}_fkey FOREIGN KEY ({$field->fields_name}) REFERENCES {$field->fields_ref} ({$field->fields_ref}_id) $ondelete;");
                        } else {
                            //echo "Foreign key {$field->fields_name}_fkey already exists.";
                        }
                    } else {
                        // PostgreSQL section would need similar check for FK existence
                        $this->selected_db->query("DELETE FROM {$field->entity_name} WHERE {$field->fields_name} NOT IN (SELECT {$field->fields_ref}_id FROM {$field->fields_ref});");
                        $this->selected_db->query("ALTER TABLE {$field->entity_name} ADD CONSTRAINT {$field->fields_name}_fkey FOREIGN KEY ({$field->fields_name}) REFERENCES {$field->fields_ref} ({$field->fields_ref}_id) ON DELETE CASCADE ON UPDATE CASCADE;");
                    }
                } else {
                    if ($this->selected_db->dbdriver != 'postgre') {
                        $this->selected_db->query("ALTER TABLE {$field->entity_name} DROP FOREIGN KEY {$field->fields_name}_fkey;");
                    } else {
                        $this->selected_db->query("ALTER TABLE {$field->entity_name} DROP CONSTRAINT {$field->fields_name}_fkey;");
                    }
                }
                break;

            default:
                break;
        }
    }


    /**
     * @param int|array $field_id
     * @param bool $physical_drop
     * @return bool
     */
    public function deleteField($field_id, bool $physical_drop = true): bool
    {
        if (is_array($field_id)) {
            if (empty($field_id['fields_id'])) return false;

            $field_id = $field_id['fields_id'];
        }

        $field = $this->selected_db->query("SELECT * FROM fields LEFT JOIN entity ON fields.fields_entity_id = entity.entity_id WHERE fields_id = '$field_id'")->row_array();

        if (empty($field)) return false;

        $entityName = $field['entity_name'];
        $fieldName = $field['fields_name'];

        $this->selected_db->trans_begin();

        try {
            // Se il field ha una action speciale, devo aggiornare il dato
            if (isset($field['entity_action_fields']) && $field['entity_action_fields']) {
                $actions = json_decode($field['entity_action_fields'], true);
                $actionsForThisField = array_keys($actions, $fieldName);
                $jsonOrNull = $this->entities->processEntityCustomActionFields($field['entity_action_fields'], array_fill_keys($actionsForThisField, null));
                $this->selected_db->update('entity', ['entity_action_fields' => $jsonOrNull], ['entity_id' => $field['entity_id']]);
            }

            // Elimino dalla tabella
            $this->selected_db->delete('fields', array('fields_id' => $field_id));

            // Elimino da draw
            $this->selected_db->delete('fields_draw', array('fields_draw_fields_id' => $field_id));

            // Elimino da forms fields
            $this->selected_db->delete('forms_fields', array('forms_fields_fields_id' => $field_id));

            // Elimino da grids fields
            $this->selected_db->delete('grids_fields', array('grids_fields_fields_id' => $field_id));

            // Elimino da calendars fields
            $this->selected_db->delete('calendars_fields', array('calendars_fields_fields_id' => $field_id));

            // Elimino da maps fields
            $this->selected_db->delete('maps_fields', array('maps_fields_fields_id' => $field_id));

            // Elimino da charts fields
            $this->selected_db->delete('charts_elements', array('charts_elements_fields_id' => $field_id));

            // Elimino da fields validations
            $this->selected_db->delete('fields_validation', array('fields_validation_fields_id' => $field_id));

            // Elimino la colonna (per qualche motivo potrebbe fallire: magari esiste in fields, ma non esiste nella tabella). Metto quindi in un try/catch
            if ($physical_drop) {
                if ($this->selected_db->table_exists($entityName)) {
                    if ($this->selected_db->field_exists($fieldName, $entityName)) {
                        $this->dbforge->drop_column($entityName, $fieldName);
                    }
                }
            }

            $this->selected_db->trans_complete();

            return true;
        } catch (Exception $e) {
            log_message('error', "ERROR WHILE DELETING FIELD {$fieldName} FROM ENTITY {$entityName} -> " . $e->getMessage());
            $this->selected_db->trans_rollback();
            return false;
        }
    }

    public function createIndex($field_id, $ifNotExists = true)
    {
        if ($ifNotExists) {
            $append = ' IF NOT EXISTS ';
        } else {
            $append = '';
        }
        $field = $this->selected_db->query("SELECT * FROM fields LEFT JOIN entity ON fields.fields_entity_id = entity.entity_id WHERE fields_id = '$field_id'")->row_array();

        $sql = "CREATE INDEX $append {$field['fields_name']} ON {$field['entity_name']}({$field['fields_name']})";

        try {
            $this->selected_db->query($sql);
            return true;
        } catch (Exception $e) {
            log_message('error', "ERROR WHILE CREATING INDEX {$field['fields_name']} ON {$field['entity_name']} -> " . $e->getMessage());
            return false;
        }



    }
}
