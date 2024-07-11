<?php

class Postgre_utils extends Utils
{
    public function __construct()
    {
        //die('test');
        parent::__construct();
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
     * Lancia il processo di aggiornamento
     */
    public function migrationProcess()
    {
        if (empty($this->selected_db)) {
            $this->set_selected_db();
        }

        ////$this->selected_db->trans_start();

        /* ============================
         * Entities / Relationships
         * ============================ */
        $this->morphTable('meta_data', [
            'meta_data_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'meta_data_key' => ['type' => 'VARCHAR', 'constraint' => 250],
            'meta_data_value' => ['type' => 'TEXT'],
        ]);

        $this->morphTable('entity', [
            'entity_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'entity_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'entity_visible' => ['type' => 'BOOL', 'default' => 't'],
            'entity_searchable' => ['type' => 'BOOL', 'default' => 't'],
            'entity_login_entity' => ['type' => 'BOOL', 'default' => 'f'],
            'entity_type' => ['type' => 'INT', 'default' => ENTITY_TYPE_DEFAULT],
            'entity_action_fields' => ['type' => 'JSON', 'null' => true],

            'entity_module' => ['type' => 'VARCHAR', 'constraint' => 250],

            'entity_preview_base' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'entity_preview_custom' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
        ]);

        // Relations
        $this->morphTable('relations', [
            'relations_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'relations_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'relations_table_1' => ['type' => 'VARCHAR', 'constraint' => 250],
            'relations_table_2' => ['type' => 'VARCHAR', 'constraint' => 250],
            'relations_field_1' => ['type' => 'VARCHAR', 'constraint' => 250],
            'relations_field_2' => ['type' => 'VARCHAR', 'constraint' => 250],
            'relations_type' => ['type' => 'VARCHAR', 'constraint' => 250],

            'relations_module' => ['type' => 'VARCHAR', 'constraint' => 250],
        ]);

        /* ============================
         * Fields
         * ============================ */
        $this->morphTable('fields', [
            'fields_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'fields_entity_id' => ['type' => 'INT', 'constraint' => 20, 'unsigned' => true, 'null' => false],
            'fields_default' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'fields_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'fields_type' => ['type' => 'VARCHAR', 'constraint' => 250],
            'fields_size' => ['type' => 'INT', 'constraint' => 12, 'null' => true],
            'fields_required' => ['type' => 'INT', 'default' => FIELD_NOT_REQUIRED],
            'fields_preview' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'fields_visible' => ['type' => 'BOOL', 'default' => 't'],
            'fields_ref' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'fields_ref_auto_left_join' => ['type' => 'BOOL', 'default' => true],
            'fields_ref_auto_right_join' => ['type' => 'BOOL', 'default' => true],
            'fields_source' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'fields_select_where' => ['type' => 'TEXT', 'null' => true],
            'fields_multilingual' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'fields_searchable' => ['type' => 'BOOL', 'default' => 't'],
            'fields_xssclean' => ['type' => 'BOOL', 'default' => DB_BOOL_TRUE, 'null' => false],
            'fields_additional_data' => ['type' => 'TEXT', 'null' => true],
        ]);

        $this->addForeignKey('fields', 'fields_entity_id', 'entity', 'entity_id');

        // Fields validation
        $this->morphTable('fields_validation', [
            'fields_validation_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'fields_validation_fields_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'fields_validation_type' => ['type' => 'VARCHAR', 'constraint' => 250],
            'fields_validation_message' => ['type' => 'TEXT', 'null' => true],
            'fields_validation_extra' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
        ]);

        $this->addForeignKey('fields_validation', 'fields_validation_fields_id', 'fields', 'fields_id');

        // Fields Draw
        $this->morphTable('fields_draw', [
            'fields_draw_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'fields_draw_fields_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'fields_draw_label' => ['type' => 'VARCHAR', 'constraint' => 250],
            'fields_draw_help_text' => ['type' => 'TEXT', 'null' => true],
            'fields_draw_onclick' => ['type' => 'TEXT', 'null' => true],
            'fields_draw_html_type' => ['type' => 'VARCHAR', 'constraint' => 250],
            'fields_draw_placeholder' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'fields_draw_css_extra' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'fields_draw_display_none' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'fields_draw_enabled' => ['type' => 'BOOL', 'default' => 't'],
            'fields_draw_attr' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
        ]);

        $this->addForeignKey('fields_draw', 'fields_draw_fields_id', 'fields', 'fields_id');

        /* ============================
         * Forms
         * ============================ */
        $this->morphTable('forms', [
            'forms_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'forms_entity_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'forms_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'forms_action' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_submit_button_label' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_default' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_layout' => ['type' => 'VARCHAR', 'constraint' => 250],
            'forms_one_record' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_show_delete' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_show_duplicate' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_css_extra' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_display_none' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_filter_session_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'forms_success_status' => ['type' => 'INT', 'default' => 7],
            'forms_success_message' => ['type' => 'VARCHAR', 'null' => true, 'constraint' => 255],
            'forms_success_status_edit' => ['type' => 'INT', 'null' => true],
            'forms_success_message_edit' => ['type' => 'VARCHAR', 'null' => true, 'constraint' => 255],

            'forms_identifier' => ['type' => 'VARCHAR', 'constraint' => 45],

            'forms_public' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'forms_label' => ['type' => 'VARCHAR', 'null' => true, 'constraint' => 255],
            'forms_label_edit' => ['type' => 'VARCHAR', 'null' => true, 'constraint' => 255],
        ]);

        $this->addForeignKey('forms', 'forms_entity_id', 'entity', 'entity_id');

        // Forms Fields
        $this->morphTable('forms_fields', [
            'forms_fields_forms_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'forms_fields_fields_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'forms_fields_order' => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'forms_fields_default_type' => ['type' => 'INT', 'constraint' => 10],
            'forms_fields_default_type' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_fields_default_value' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            // Customizzazioni fields
            'forms_fields_override_type' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_fields_extra_class' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_fields_override_label' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_fields_override_placeholder' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'forms_fields_override_colsize' => ['type' => 'INT', 'null' => true],
            'forms_fields_show_required' => ['type' => 'BOOL', 'null' => true],
            'forms_fields_show_label' => ['type' => 'BOOL', 'default' => true],
            'forms_fields_allow_reverse' => ['type' => 'BOOL', 'default' => false],
            // Possibilità di scegliere un form dal quale creare una nuova entità
            'forms_fields_subform_id' => ['type' => 'INT', 'null' => true],
            'forms_fields_min' => ['type' => 'INT', 'default' => 0],
            'forms_fields_max' => ['type' => 'INT', 'default' => 0],

            'forms_fields_module_key' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'forms_fields_module' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],

            'forms_field_full_data' => ['type' => 'BOOL', 'default' => false],

        ]);

        $this->addForeignKey('forms_fields', 'forms_fields_forms_id', 'forms', 'forms_id');
        $this->addForeignKey('forms_fields', 'forms_fields_fields_id', 'fields', 'fields_id');

        /* ============================
         * Grids
         * ============================ */
        $this->morphTable('grids', [
            'grids_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'grids_entity_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'grids_sub_grid_id' => ['type' => 'INT', 'constraint' => 10, 'null' => true, 'unsigned' => true],
            'grids_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'grids_view_link' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_edit_link' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_view_layout' => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'grids_edit_layout' => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'grids_delete_link' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_where' => ['type' => 'TEXT', 'null' => true],
            'grids_builder_where' => ['type' => 'TEXT', 'null' => true],
            'grids_default' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'grids_layout' => ['type' => 'VARCHAR', 'constraint' => 250],
            'grids_order_by' => ['type' => 'TEXT', 'null' => true],
            'grids_group_by' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'grids_filter_session_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'grids_bulk_mode' => ['type' => 'VARCHAR', 'constraint' => 31, 'null' => true],
            'grids_bulk_edit_form' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'grids_exportable' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_append_class' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'grids_inline_form' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'grids_module_key' => ['type' => 'VARCHAR', 'constraint' => 250],
            'grids_depth' => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'grids_actions_column' => ['type' => 'BOOL', 'default' => 't'],
            'grids_identifier' => ['type' => 'VARCHAR', 'constraint' => 45],
            'grids_datatable' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_ajax' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_searchable' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_pagination' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'grids_inline_edit' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_design' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'grids_limit' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'grids_custom_query' => ['type' => 'TEXT', 'null' => true],
            'grids_json' => ['type' => 'TEXT', 'null' => true],
        ]);

        $this->addForeignKey('grids', 'grids_entity_id', 'entity', 'entity_id');
        $this->addForeignKey('grids', 'grids_bulk_edit_form', 'forms', 'forms_id');

        // Grids fields
        $this->morphTable('grids_fields', [
            'grids_fields_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'grids_fields_grids_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'grids_fields_fields_id' => ['type' => 'INT', 'constraint' => 10, 'null' => true, 'unsigned' => true],
            'grids_fields_order' => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'grids_fields_replace_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'field'],
            'grids_fields_replace' => ['type' => 'TEXT', 'null' => true],
            'grids_fields_column_name' => ['type' => 'VARCHAR', 'constraint' => 75, 'null' => true],
            //20190513 - MP - Aggiunte per sortable e searchable negli eval
            'grids_fields_eval_cache_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'grids_fields_eval_cache_data' => ['type' => 'TEXT', 'null' => true],
            'grids_fields_totalable' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_fields_with_actions' => ['type' => 'BOOL', 'default' => 'f'],
            'grids_fields_switch_inline' => ['type' => 'BOOL', 'default' => 'f', 'null' => true],
            'grids_fields_width' => ['type' => 'INT', 'constraint' => 3, 'null' => true],
            'grids_fields_module_key' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'grids_fields_module' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);

        $this->addForeignKey('grids_fields', 'grids_fields_grids_id', 'grids', 'grids_id');
        $this->addForeignKey('grids_fields', 'grids_fields_fields_id', 'fields', 'fields_id');

        // Grids actions
        $this->morphTable('grids_actions', [
            'grids_actions_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'grids_actions_grids_id' => ['type' => 'INT', 'unsigned' => true],
            'grids_actions_order' => ['type' => 'INT'],
            'grids_actions_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'grids_actions_html' => ['type' => 'TEXT'],

            //Aggiunta per nuovo tool / visual builder:
            //Tutte le action (custom o meno) verranno salvate sempre in questa tabella
            //(così uniformiamo anche).
            //Le "vecchie" view/edit/delete resteranno retrocompatibili,
            //ma il nuovo visual builder non popolerà mai quelle colonne (che sono dentro grids e non dentro grids_actions
            'grids_actions_link' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_actions_layout' => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'grids_actions_form' => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'grids_actions_icon' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_actions_mode' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_actions_type' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_actions_color' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'grids_actions_show' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],

        ]);

        $this->addForeignKey('grids_actions', 'grids_actions_grids_id', 'grids', 'grids_id');

        /* ============================
         * Calendars
         * ============================ */
        $this->morphTable('calendars', [
            'calendars_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'calendars_entity_id' => ['type' => 'INT', 'unsigned' => true, 'constraint' => 10],
            'calendars_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'calendars_where' => ['type' => 'TEXT', 'null' => true],
            'calendars_where_filter' => ['type' => 'TEXT', 'null' => true],
            'calendars_default' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'calendars_layout' => ['type' => 'VARCHAR', 'constraint' => 250],
            'calendars_method' => ['type' => 'VARCHAR', 'constraint' => 250],
            'calendars_method_param' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'calendars_filter_entity_id' => ['type' => 'INT', 'null' => true],
            // <===== Update 16/10/2015 =====>
            'calendars_allow_create' => ['type' => 'BOOL', 'default' => true],
            'calendars_form_create' => ['type' => 'INT', 'null' => true],
            'calendars_allow_edit' => ['type' => 'BOOL', 'default' => true],
            'calendars_form_edit' => ['type' => 'INT', 'null' => true],
            // <===== Update 29/06/2016 - L'ultimo di Alberto =====>
            'calendars_min_time' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'calendars_max_time' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],

            'calendars_default_sidebar_toggle_all_filters' => ['type' => 'BOOL', 'default' => DB_BOOL_TRUE],
            'calendars_default_view' => ['type' => 'VARCHAR', 'constraint' => 250, 'default' => 'timeGridWeek'],

            // <===== Update 21/06/2022 - Michael - Added custom query field =====>
            'calendars_custom_query' => ['type' => 'TEXT', 'null' => true],

            'calendars_module_key' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],

            // <===== Update 21/10/2022 - Michael - Added new fields =====>
            'calendars_layout_modal' => ['type' => 'BOOL', 'default' => DB_BOOL_TRUE, 'null' => false],
            'calendars_layout_id' => ['type' => 'INT', 'unsigned' => true, 'constraint' => 10, 'null' => true],
            'calendars_link' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'calendars_event_click' => ['type' => 'VARCHAR', 'constraint' => 10],
            'calendars_filter_session_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);

        $this->addForeignKey('calendars', 'calendars_entity_id', 'entity', 'entity_id');

        // Calendars fields
        $this->morphTable('calendars_fields', [
            'calendars_fields_calendars_id' => ['type' => 'INT', 'constraint' => 10],
            'calendars_fields_fields_id' => ['type' => 'INT', 'constraint' => 10],
            'calendars_fields_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 0],

        ]);

        $this->addForeignKey('calendars_fields', 'calendars_fields_calendars_id', 'calendars', 'calendars_id');
        $this->addForeignKey('calendars_fields', 'calendars_fields_fields_id', 'fields', 'fields_id');

        /* ============================
         * Maps
         * ============================ */
        $this->morphTable('maps', [
            'maps_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'maps_entity_id' => ['type' => 'INT', 'constraint' => 10],
            'maps_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'maps_where' => ['type' => 'TEXT', 'null' => true],
            'maps_default' => ['type' => 'BOOL', 'default' => false],
            'maps_layout' => ['type' => 'VARCHAR', 'constraint' => 250],
            'maps_engine' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'maps_tile_layer' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],

            'maps_method' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'maps_method_param' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'maps_order_by' => ['type' => 'TEXT', 'null' => true],
            'maps_filter_session_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'maps_init_zoom' => ['type' => 'BIGINT', 'null' => true],
            'maps_init_latlon' => ['type' => 'BIGINT', 'null' => true],
            'maps_cluster' => ['type' => 'BOOL', 'null' => true],
            'maps_sidebar' => ['type' => 'BOOL', 'null' => true],
            'maps_geocoding' => ['type' => 'BOOL', 'null' => true],
            'maps_module' => ['type' => 'VARCHAR', 'constraint' => 250],
            'maps_module_key' => ['type' => 'VARCHAR', 'constraint' => 250],
        ]);

        $this->addForeignKey('maps', 'maps_entity_id', 'entity', 'entity_id');

        // Maps fields
        $this->morphTable('maps_fields', [
            'maps_fields_maps_id' => ['type' => 'INT', 'constraint' => 10],
            'maps_fields_fields_id' => ['type' => 'INT', 'constraint' => 10],
            'maps_fields_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 0],
        ]);

        $this->addForeignKey('maps_fields', 'maps_fields_maps_id', 'maps', 'maps_id');
        $this->addForeignKey('maps_fields', 'maps_fields_fields_id', 'fields', 'fields_id');

        /* ============================
         * Charts
         * ============================ */
        $this->morphTable('charts', [
            'charts_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'charts_name' => ['type' => 'VARCHAR', 'constraint' => 250],
            'charts_title' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'charts_subtitle' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'charts_layout' => ['type' => 'VARCHAR', 'constraint' => 250],

            'charts_module' => ['type' => 'VARCHAR', 'constraint' => 250],
            'charts_module_key' => ['type' => 'VARCHAR', 'constraint' => 250],
        ]);

        // Charts fields
        $this->morphTable('charts_elements', [
            'charts_elements_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'charts_elements_charts_id' => ['type' => 'INT', 'constraint' => 10],
            'charts_elements_function' => ['type' => 'VARCHAR', 'constraint' => 250, 'default' => null, 'null' => true],
            'charts_elements_function_parameter' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'charts_elements_entity_id' => ['type' => 'INT', 'constraint' => 10],
            'charts_elements_fields_id' => ['type' => 'INT', 'constraint' => 10],
            'charts_elements_label' => ['type' => 'VARCHAR', 'constraint' => 150, 'default' => 0],
            'charts_elements_label2' => ['type' => 'VARCHAR', 'constraint' => 150, 'default' => 0],
            'charts_elements_where' => ['type' => 'TEXT'],
            'charts_elements_additional_where' => ['type' => 'TEXT'],
            'charts_elements_groupby' => ['type' => 'TEXT'],
            'charts_elements_use_full_query' => ['type' => 'BOOL', 'null' => true],
            'charts_elements_order' => ['type' => 'varchar', 'constraint' => 250, 'default' => 'ASC'],
            'charts_elements_filter_session_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'charts_elements_full_query' => ['type' => 'TEXT', 'null' => true],
            'charts_elements_mode' => ['type' => 'INT', 'default' => 1],
        ]);

        $this->addForeignKey('charts_elements', 'charts_elements_charts_id', 'charts', 'charts_id');
        $this->addForeignKey('charts_elements', 'charts_elements_entity_id', 'entity', 'entity_id');
        $this->addForeignKey('charts_elements', 'charts_elements_fields_id', 'fields', 'fields_id');

        /* ============================
         * Modules
         * ============================ */
        $this->morphTable('modules', [
            'modules_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'modules_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'modules_description' => ['type' => 'TEXT'],
            'modules_installed' => ['type' => 'BOOL', 'default' => 't'],
            'modules_home_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'modules_version' => ['type' => 'VARCHAR', 'constraint' => 255],
            //'modules_label' => ['type' => 'VARCHAR', 'constraint' => 255],
            'modules_identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'modules_base64_zip' => ['type' => 'TEXT'],
            'modules_raw_data_install' => ['type' => 'TEXT'],
            'modules_raw_data_update' => ['type' => 'TEXT'],
            //'modules_raw_data' => ['type' => 'TEXT'],
            //20190510 Matteo - Teniamo ancora per un po' per retrocompatibilità...
            'modules_created_by_user' => ['type' => 'INT'],
            'modules_version_code' => ['type' => 'INT'],
            'modules_thumbnail' => ['type' => 'VARCHAR', 'null' => true],
            //20190711 Michael - Campo per salvare il nome del file dell'icona modulo
            'modules_min_client_version' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'modules_auto_update' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
            'modules_last_update' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'default' => 'NOW()'],
            'modules_core' => ['type' => 'BOOLEAN', 'default' => DB_BOOL_FALSE],
            'modules_notification_message' => ['type' => 'TEXT'],
        ]);

        /* ============================
         * Notifications
         * ============================ */
        // $this->morphTable('notifications', [
        //     'notifications_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
        //     'notifications_type' => ['type' => 'INT'],
        //     'notifications_user_id' => ['type' => 'INT'],
        //     'notifications_title' => ['type' => 'VARCHAR', 'null' => true],
        //     'notifications_message' => ['type' => 'TEXT', 'null' => true],
        //     'notifications_read' => ['type' => 'BOOL', 'default' => false],
        //     'notifications_date_creation' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'default' => 'NOW()'],
        //     'notifications_link' => ['type' => 'VARCHAR', 'null' => true],
        //     'notifications_desktop_notified' => ['type' => 'BOOL', 'default' => false],
        // ]);

        /* ============================
         * PostProcess
         * ============================ */
        $this->morphTable('post_process', [
            'post_process_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'post_process_entity_id' => ['type' => 'INT', 'null' => true],
            'post_process_when' => ['type' => 'VARCHAR', 'constraint' => 300],
            'post_process_what' => ['type' => 'TEXT', 'null' => true],
            'post_process_apilib' => ['type' => 'BOOL', 'default' => 't'],
            'post_process_api' => ['type' => 'BOOL', 'default' => 't'],
            'post_process_crm' => ['type' => 'BOOL', 'default' => 't'],
            'post_process_module' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);

        $this->addForeignKey('post_process', 'post_process_entity_id', 'entity', 'entity_id');

        /* ============================
         * Crons
         * ============================ */
        $this->morphTable('crons', [
            'crons_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'crons_entity_id' => ['type' => 'INT', 'constraint' => 10, 'null' => true],
            'crons_title' => ['type' => 'VARCHAR', 'constraint' => 250],
            'crons_type' => ['type' => 'VARCHAR', 'constraint' => 250],
            'crons_text' => ['type' => 'TEXT', 'null' => true],
            'crons_frequency' => ['type' => 'INT', 'default' => 1],
            'crons_file' => ['type' => 'VARCHAR', 'null' => true],
            'crons_where' => ['type' => 'TEXT', 'null' => true],
            'crons_active' => ['type' => 'BOOL', 'default' => 't'],
            'crons_last_execution' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
            'crons_cli' => ['type' => 'BOOL', 'default' => 'f'],
            'crons_module' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);

        $this->addForeignKey('crons', 'crons_entity_id', 'entity', 'entity_id');

        $this->morphTable('crons_fields', [
            'crons_fields_crons_id' => ['type' => 'INT', 'constraint' => 10],
            'crons_fields_fields_id' => ['type' => 'INT', 'constraint' => 10],
            'crons_fields_type' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);

        $this->addForeignKey('crons_fields', 'crons_fields_crons_id', 'crons', 'crons_id');
        $this->addForeignKey('crons_fields', 'crons_fields_fields_id', 'fields', 'fields_id');

        /* ============================
         * Permessi
         * ============================ */
        $this->morphTable('permissions', [
            'permissions_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'permissions_user_id' => ['type' => 'INT', 'constraint' => 10, 'null' => true],
            'permissions_admin' => ['type' => 'BOOL', 'default' => false],
            'permissions_group' => ['type' => 'VARCHAR', 'null' => true],
        ]);

        // permissions_entities
        $this->morphTable('permissions_entities', [
            'permissions_entities_permissions_id' => ['type' => 'INT', 'constraint' => 10],
            'permissions_entities_entity_id' => ['type' => 'INT', 'constraint' => 10],
            'permissions_entities_value' => ['type' => 'VARCHAR', 'constraint' => 30],
        ]);

        $this->addForeignKey('permissions_entities', 'permissions_entities_permissions_id', 'permissions', 'permissions_id');
        $this->addForeignKey('permissions_entities', 'permissions_entities_entity_id', 'entity', 'entity_id');

        // permissions_modules
        $this->morphTable('permissions_modules', [
            'permissions_modules_permissions_id' => ['type' => 'INT', 'constraint' => 10],
            'permissions_modules_module_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'permissions_modules_value' => ['type' => 'VARCHAR', 'constraint' => 30],
        ]);

        $this->addForeignKey('permissions_modules', 'permissions_modules_permissions_id', 'permissions', 'permissions_id');
        $this->addForeignKey('permissions_modules', 'permissions_modules_module_name', 'modules', 'modules_name');

        // limits
        $this->morphTable('limits', [
            'limits_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'limits_user_id' => ['type' => 'INT', 'constraint' => 10],
            'limits_fields_id' => ['type' => 'INT', 'constraint' => 10],
            'limits_operator' => ['type' => 'VARCHAR', 'constraint' => 10],
            'limits_value' => ['type' => 'TEXT'],
        ]);

        $this->addForeignKey('limits', 'limits_fields_id', 'fields', 'fields_id');

        /* ============================
         * Mail Queue
         * ============================ */
        $this->morphTable('mail_queue', [
            'mail_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'mail_subject' => ['type' => 'VARCHAR', 'constraint' => 300],
            'mail_body' => ['type' => 'TEXT', 'null' => true],
            'mail_to' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mail_boundary' => ['type' => 'TEXT', 'null' => true],
            'mail_headers' => ['type' => 'TEXT', 'null' => true],
            'mail_is_html' => ['type' => 'BOOL', 'default' => false],
            'mail_log' => ['type' => 'TEXT', 'null' => true],
            'mail_date_sent' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
            'mail_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'default' => 'now()'],
            'mail_user' => ['type' => 'INT', 'constraint' => 10],
            'mail_attachments' => ['type' => 'TEXT', 'null' => true],

        ], 'mail_id');

        /* ============================
         * User token
         * ============================ */
        $this->morphTable('user_tokens', [
            'user_token_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT'],
            'token_string' => ['type' => 'VARCHAR', 'constraint' => 255],
            'token_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'default' => 'now()'],
        ], 'user_token_id');

        /* ============================
         * Email templates db
         * ============================ */
        $this->morphTable('emails', [
            'emails_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'emails_key' => ['type' => 'VARCHAR', 'constraint' => 150],
            'emails_language' => ['type' => 'VARCHAR', 'constraint' => 50],
            'emails_subject' => ['type' => 'VARCHAR', 'constraint' => 150],
            'emails_template' => ['type' => 'TEXT'],
            'emails_headers' => ['type' => 'TEXT'],

            'emails_module' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);

        try {
            $this->entitiesBaseSetup();
        } catch (Exception $ex) {
            // Se fallisce è perché le entità sono già state create
            //echo($ex->getMessage());
        }

        /* ============================
         * Unallowed layouts
         * ============================ */
        $this->morphTable('unallowed_layouts', [
            'unallowed_layouts_user' => ['type' => 'INT', 'null' => false],
            'unallowed_layouts_layout' => ['type' => 'INT', 'null' => false, 'unsigned' => true],
        ]);

        //TODO: creare indice se non esiste su unallowed_layouts_user
        //$this->addForeignKey('unallowed_layouts', 'unallowed_layouts_user', 'users', 'users_id');
        $this->addForeignKey('unallowed_layouts', 'unallowed_layouts_layout', 'layouts', 'layouts_id');

        /* ============================
         * Tabelle di log
         * ============================ */
        $this->morphTable('log_api', [
            'log_api_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            'log_api_method' => ['type' => 'VARCHAR', 'constraint' => 50],
            'log_api_params' => ['type' => 'TEXT'],
            'log_api_get' => ['type' => 'TEXT'],
            'log_api_post' => ['type' => 'TEXT'],
            'log_api_files' => ['type' => 'TEXT'],
            'log_api_output' => ['type' => 'TEXT'],
            'log_api_ip_addr' => ['type' => 'VARCHAR', 'constraint' => 50],
            'log_api_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
        ]);

        $this->morphTable('log_crm', [
            'log_crm_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
            // === INFO UTENTE === Questi parametri possono essere nulli in quanto non è detto che abbia una sessione quando eseguo l'azione (ad esempio su cron)
            'log_crm_user_id' => ['type' => 'INT', 'null' => true],
            'log_crm_user_name' => ['type' => 'VARCHAR', 'null' => true, 'constraint' => 300],
            // === INFO CLIENT === Informazioni sul browser/client
            'log_crm_ip_addr' => ['type' => 'VARCHAR', 'constraint' => 50],
            'log_crm_user_agent' => ['type' => 'TEXT', 'null' => true],
            'log_crm_referer' => ['type' => 'TEXT', 'null' => true],
            // === INFO ACTION === Informazioni sul browser/client
            'log_crm_time' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE'],
            'log_crm_type' => ['type' => 'INT', 'unsigned' => true],
            'log_crm_title' => ['type' => 'VARCHAR', 'constraint' => 150],
            'log_crm_system' => ['type' => 'BOOL', 'default' => false],
            'log_crm_extra' => ['type' => 'JSON', 'null' => true],
        ]);

        /* ============================
         * Tabella hooks
         * ============================ */
        $this->morphTable('hooks', [
            'hooks_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'hooks_title' => ['type' => 'varchar', 'constraint' => 250],
            'hooks_type' => ['type' => 'varchar', 'constraint' => 250],
            'hooks_ref' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'hooks_order' => ['type' => 'int', 'unsigned' => true],
            'hooks_content' => ['type' => 'text'],

            'hooks_module' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);

        /* ============================
         * CI_Sessions
         * ============================ */

        $this->morphTable('ci_sessions', [
            'session_id' => ['type' => 'varchar', 'constraint' => 40, 'default' => "'0'"],
            'ip_address' => ['type' => 'varchar', 'constraint' => 45, 'default' => "'0'"],
            'user_agent' => ['type' => 'varchar', 'constraint' => 120, 'default' => "'0'"],
            'last_activity' => ['type' => 'int', 'unsigned' => true, 'constraint' => 10, 'default' => '0'],
            'user_data' => ['type' => 'text'],

            'id' => ['type' => 'varchar', 'constraint' => 128, 'default' => '', 'null' => false],
            'ip_address' => ['type' => 'varchar', 'constraint' => 45, 'default' => "'0'"],
            'timestamp' => ['type' => 'bigint', 'default' => '0', 'null' => false],
            'data' => ['type' => 'text', 'default' => '', 'null' => false],

        ], 'id');

        //die('test');
        /* ============================
         * Tabelle api manager
         * ============================ */
        $this->morphTable('api_manager_tokens', [
            'api_manager_tokens_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'api_manager_tokens_user' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'api_manager_tokens_token' => ['type' => 'varchar', 'constraint' => 250],
            //'api_manager_tokens_private_token' => ['type' => 'varchar', 'constraint' => 250],
            'api_manager_tokens_creation_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'default' => 'now()'],
            'api_manager_tokens_expire_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
            'api_manager_tokens_last_use_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
            'api_manager_tokens_ms_between_requests' => ['type' => 'int', 'default' => '1000'],
            'api_manager_tokens_limit_per_minute' => ['type' => 'int', 'default' => '50'],
            'api_manager_tokens_requests' => ['type' => 'int', 'default' => '0'],
            'api_manager_tokens_errors' => ['type' => 'int', 'default' => '0'],
            'api_manager_tokens_active' => ['type' => 'BOOL', 'default' => true],
        ]);
        $this->morphTable('api_manager_permissions', [
            'api_manager_permissions_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'api_manager_permissions_entity' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'api_manager_permissions_token' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'api_manager_permissions_chmod' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'api_manager_permissions_where' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->addForeignKey('api_manager_permissions', 'api_manager_permissions_token', 'api_manager_tokens', 'api_manager_tokens_id');
        $this->addForeignKey('api_manager_permissions', 'api_manager_permissions_entity', 'entity', 'entity_id');

        $this->morphTable('api_manager_fields_permissions', [
            'api_manager_fields_permissions_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'api_manager_fields_permissions_field' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'api_manager_fields_permissions_token' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'api_manager_fields_permissions_chmod' => ['type' => 'int', 'unsigned' => true, 'null' => false],
        ]);
        $this->addForeignKey('api_manager_fields_permissions', 'api_manager_fields_permissions_token', 'api_manager_tokens', 'api_manager_tokens_id');
        $this->addForeignKey('api_manager_fields_permissions', 'api_manager_fields_permissions_field', 'fields', 'fields_id');

        /*$this->morphTable('layouts', [
        'layouts_id' => ['type' => 'SERIAL UNIQUE', 'unsigned' => true, 'auto_increment' => true],
        'layouts_title' => ['type' => 'varchar', 'null' => false,'constraint' => 150],
        'layouts_subtitle' => ['type' => 'varchar','constraint' => 150],
        'layouts_is_entity_detail' => ['type' => 'BOOL', 'null' => true],
        'layouts_entity_id' => ['type' => 'int'],
        'layouts_fullscreen' => ['type' => 'BOOL'],
        'layouts_pdf' => ['type' => 'BOOL'],
        'layouts_dashboardable' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE],
        'layouts_cachable' => ['type' => 'BOOL', 'default' => DB_BOOL_FALSE]
        ]);*/

        //Tabella per indicare gli elementi bloccati, ovvero che non possono essere modificati nè da tool, nè da eventuali update dei moduli...
        $this->morphTable('locked_elements', [
            'locked_elements_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'locked_elements_type' => ['type' => 'varchar', 'constraint' => 45],
            'locked_elements_ref_id' => ['type' => 'int', 'unsigned' => true, 'null' => false],
        ], 'locked_elements_id');

        $this->morphTable('fi_events', [
            'fi_events_id' => ['type' => 'serial unique', 'unsigned' => true, 'auto_increment' => true],
            'fi_events_title' => ['type' => 'varchar', 'constraint' => 255],
            'fi_events_json_data' => ['type' => 'JSON', 'null' => true],
            //Dati grezzi (non si sa mai che torni utile recuperare tutto il $_POST fatto)

            'fi_events_type' => ['type' => 'varchar', 'constraint' => 45],
            //Database, layout, grid, form, cron
            'fi_events_when' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            //pre-insert, post-edit, pre-validation, pre-layout...
            'fi_events_ref_id' => ['type' => 'int', 'constraint' => 45, 'null' => true],
            //Id del layout, piuttosto che dell'entity, piuttosto che della grid...
            'fi_events_action' => ['type' => 'varchar', 'constraint' => 255],
            //Custom code, send email, send a curl...
            'fi_events_actiondata' => ['type' => 'JSON'],
            //Tutto quello che serve all'action per essere eseguita: codice custom, parametri della mail, parametri della curl, ecc...

            'fi_events_active' => ['type' => 'BOOL', 'default' => 't'],

            'fi_events_apilib' => ['type' => 'BOOL', 'default' => 't', 'null' => true],
            'fi_events_api' => ['type' => 'BOOL', 'default' => 't', 'null' => true],
            'fi_events_crm' => ['type' => 'BOOL', 'default' => 't', 'null' => true],
            'fi_events_module' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            'fi_events_order' => ['type' => 'int', 'unsigned' => true, 'null' => true],

            'fi_events_cron_frequency' => ['type' => 'INT', 'default' => 1, 'null' => true],
            //Campo specifico per i cron che mi indica la frequenza di esecuzione
            'fi_events_cron_last_execution' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
            //Campo specifico per i cron che mi indica la frequenza di esecuzione

            'fi_events_hook_order' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'fi_events_post_process_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'fi_events_cron_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'fi_events_hook_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'fi_events_cli' => ['type' => 'BOOL', 'default' => 'f', 'null' => false],
            'fi_events_creation_date' => ['type' => 'TIMESTAMP WITHOUT TIME ZONE', 'null' => true],
        ], 'fi_events_id');

        try {
            $this->entitiesBaseSetup();
        } catch (Exception $ex) {
            // Se fallisce è perché le entità sono già state create
            //echo($ex->getMessage());
        }

        $this->morphTable('layouts', [
            'layouts_cachable' => ['type' => 'BOOL', 'default' => true, 'null' => false],
            'layouts_settings' => ['type' => 'BOOL', 'default' => false, 'null' => false],
            'layouts_identifier' => ['type' => 'varchar', 'constraint' => 45],
            'layouts_module' => ['type' => 'VARCHAR', 'constraint' => 255],
            'layouts_module_key' => ['type' => 'VARCHAR', 'constraint' => 250],

            'layouts_show_header' => ['type' => 'BOOL', 'default' => true, 'null' => false],
            'layouts_is_public' => ['type' => 'BOOL', 'default' => false, 'null' => false],
            'layouts_ajax_allowed' => ['type' => 'BOOL', 'default' => true, 'null' => false],
        ], null, false);

        $this->morphTable('layouts_boxes', [
            'layouts_boxes_show_container' => ['type' => 'BOOL', 'default' => 't', 'null' => false],
            'layouts_boxes_module_key' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ], null, false);

        //        $this->morphTable('settings', [
        //            'settings_smtp_host' => ['type' => 'VARCHAR', 'constraint' => 255],
        //
        //        ], null, false);

        //die('test');

        //$this->selected_db->trans_complete();
    }

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

        $exists = $this->selected_db->table_exists($tableName);
        $primary = (!$primaryKey && isset($fields[$tableName . '_id'])) ? $tableName . '_id' : $primaryKey;

        if (!in_array($tableName, parent::$defaultDrops) && $do_remove) {
            show_error(sprintf("Inserire la tabella %s dentro alla variabile defaultDrops del controller install", $tableName));
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

            if (!empty($field['auto_increment'])) {
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
            show_error(sprintf("La tabella %s non esiste", $fromTable));
        }

        if (!$this->selected_db->table_exists($toTable)) {
            show_error(sprintf("La tabella %s non esiste", $toTable));
        }

        if (!$this->selected_db->field_exists($fromField, $fromTable)) {
            show_error(sprintf("Il campo %s non esiste nella tabella %s", $fromField, $fromTable));
        }

        if (!$this->selected_db->field_exists($toField, $toTable)) {
            show_error(sprintf("Il campo %s non esiste nella tabella %s", $toTable));
        }

        // Dai un nome univoco per le chiavi esterne del core

        $conname = 'core_' . $fromTable . '_' . $fromField . '_fkey';
        $exists = $this->selected_db->query("SELECT * FROM pg_constraint WHERE conname = ?", [$conname])->num_rows();
        if (!$exists) {
            $this->selected_db->query("ALTER TABLE {$fromTable} ADD CONSTRAINT {$conname} FOREIGN KEY ({$fromField}) REFERENCES {$toTable} ({$toField}) ON DELETE CASCADE ON UPDATE CASCADE");
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
                ['fields_name' => 'title', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text', 'fields_preview' => DB_BOOL_TRUE],
                ['fields_name' => 'subtitle', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'is_entity_detail', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'entity_id', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'fullscreen', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'pdf', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio'],
                ['fields_name' => 'dashboardable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio', 'fields_default' => DB_BOOL_FALSE],
                ['fields_name' => 'cachable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio', 'fields_default' => DB_BOOL_FALSE],
                ['fields_name' => 'settings', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio', 'fields_default' => DB_BOOL_FALSE],

                ['fields_name' => 'show_header', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio', 'fields_default' => DB_BOOL_TRUE],
                ['fields_name' => 'ajax_allowed', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio', 'fields_default' => DB_BOOL_TRUE],
                ['fields_name' => 'identifier', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'module', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'module_key', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
            ],
        ]);

        // Layout box
        $layoutsBoxEntityId = $this->entities->new_entity(['entity_name' => 'layouts_boxes', 'entity_visible' => DB_BOOL_FALSE, 'entity_type' => ENTITY_TYPE_SYSTEM]);
        $this->entities->addFields([
            'entity_id' => $layoutsBoxEntityId,
            'fields' => [
                ['fields_name' => 'layout', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'select', 'fields_ref' => 'layouts'],
                ['fields_name' => 'css', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'title', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'content_type', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'select'],
                ['fields_name' => 'content_ref', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'content', 'fields_type' => 'TEXT', 'fields_visible' => 't', 'fields_draw_html_type' => 'textarea'],
                ['fields_name' => 'position', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'dragable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'collapsible', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'collapsed', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'reloadable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'discardable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'titolable', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'show_container', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'checkbox'],
                ['fields_name' => 'row', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'cols', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'color', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
            ],
        ]);

        // Menù
        $menuEntityId = $this->entities->new_entity(['entity_name' => 'menu', 'entity_visible' => DB_BOOL_FALSE, 'entity_type' => ENTITY_TYPE_SYSTEM]);
        $this->entities->addFields([
            'entity_id' => $menuEntityId,
            'fields' => [
                ['fields_name' => 'label', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_required' => FIELD_REQUIRED, 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'link', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'parent', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'select', 'fields_ref' => 'menu'],
                ['fields_name' => 'icon_class', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'order', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'position', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'layout', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'select', 'fields_ref' => 'layouts'],
                ['fields_name' => 'css_class', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'html_attr', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'modal', 'fields_type' => 'BOOL', 'fields_visible' => 't', 'fields_draw_html_type' => 'radio'],
                ['fields_name' => 'module', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'type', 'fields_type' => 'VARCHAR', 'fields_visible' => 't', 'fields_draw_html_type' => 'input_text'],
                ['fields_name' => 'form', 'fields_type' => 'INT', 'fields_visible' => 't', 'fields_draw_html_type' => 'select', 'fields_ref' => 'forms'],
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