<?php
if (!$this->db->table_exists('currencies')) {
    $this->load->model('entities');
    
    $currencies = $this->entities->new_entity(['entity_name' => 'currencies', 'entity_type' => ENTITY_TYPE_SYSTEM]);
    $this->entities->addFields([
        'entity_id' => $currencies,
        'fields' => [
            ['fields_name' => 'name', 'fields_required' => FIELD_REQUIRED, 'fields_preview' => DB_BOOL_TRUE, 'fields_draw_html_type' => 'input_text'],
            ['fields_name' => 'code', 'fields_required' => FIELD_REQUIRED, 'fields_size' => 250, 'fields_draw_html_type' => 'input_text'],
            ['fields_name' => 'symbol', 'fields_required' => FIELD_REQUIRED, 'fields_size' => 250, 'fields_draw_html_type' => 'input_text'],
            ['fields_name' => 'default', 'fields_required' => FIELD_REQUIRED, 'fields_default' => DB_BOOL_FALSE, 'fields_type' => 'BOOL', 'fields_draw_html_type' => 'radio'],
        ],
    ]);
    
    // add mainly used currencies
    $this->db->insert('currencies', [
        'currencies_name' => 'Euro',
        'currencies_code' => 'EUR',
        'currencies_symbol' => '€',
        'currencies_default' => true,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'US Dollar',
        'currencies_code' => 'USD',
        'currencies_symbol' => '$',
        'currencies_default' => false,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'British Pound',
        'currencies_code' => 'GBP',
        'currencies_symbol' => '£',
        'currencies_default' => false,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'Japanese Yen',
        'currencies_code' => 'JPY',
        'currencies_symbol' => '¥',
        'currencies_default' => false,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'Swiss Franc',
        'currencies_code' => 'CHF',
        'currencies_symbol' => 'CHF',
        'currencies_default' => false,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'Australian Dollar',
        'currencies_code' => 'AUD',
        'currencies_symbol' => '$',
        'currencies_default' => false,
    ]);
    
    $this->db->insert('currencies', [
        'currencies_name' => 'Chinese Yuan',
        'currencies_code' => 'CNY',
        'currencies_symbol' => '¥',
        'currencies_default' => false,
    ]);
    
    $this->db->trans_complete();
}
