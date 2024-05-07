<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];

// Recupera info filtri e indicizza i dati per field_id
$filterSessionKey = $form['forms']['forms_filter_session_key'];
$_sess_where_data = array_get($sess_data, $filterSessionKey, []);

$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);

$rowStart = '<div class="row">';
$rowEnd = '</div>';
$rowCol = 0;

?>


<form autocomplete="off" <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?> js_filter_form <?php echo ("form_{$form['forms']['forms_id']}"); ?>" enctype="multipart/form-data">
    <?php add_csrf();?>
    <div class="form-body">
        <!-- <div class="row sortableFormDEPRECATED"> -->
            <div class="box-body">
            <?php foreach ($form['forms_fields'] as $k => $field): ?>
                
                <?php
                    // First row
                    echo $rowCol ? '' : $rowStart;
                    $col = $field['size'] ?: 6;
                    $rowCol += $col;

                    if ($rowCol > 12) {
                        $rowCol = $col;
                        echo $rowEnd, $rowStart;
                    }
                    ?>

                <div data-form_id="<?php echo $form['forms']['forms_id']; ?>" data-id="<?php echo $field['id']; ?>" data-cols="<?php echo $field['size']; ?>" class="formColumn js_container_field <?php echo sprintf('col-md-%d', $field['size'] ?: 6); ?>">
                    
                    <!-- Builder buttons -->
                    <div class="builder_formcolumns_buttons hide">
                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_minus" data-toggle="tooltip" data-original-title="- columns">
                            <i class="fas fa-caret-left"></i>
                        </a>
                        Size
                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_plus" data-toggle="tooltip" data-original-title="+ columns">
                            <i class="fas fa-caret-right"></i>
                        </a>
                        
                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_delete btn-space" data-toggle="tooltip" data-original-title="Remove field">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <!-- End Builder buttons -->
                    
                    <?php
                    
                    $reverse = $field['original_field']['forms_fields_allow_reverse'] == DB_BOOL_TRUE;
                    
                    $field_completo = $this->datab->get_field($field['id']);
                    
                    $sess_value = !isset($where_data[$field['id']]['value']) ? null : $where_data[$field['id']]['value'];
                    $reverse_checked = (!empty($where_data[$field['id']]['reverse']) && $where_data[$field['id']]['reverse'] == DB_BOOL_TRUE) ? true : false;
                    
                    if ($field['datatype'] == 'INT4RANGE') {
                        $oper = empty($where_data[$field['id']]['operator']) ? 'rangein' : $where_data[$field['id']]['operator'];
                    } else {
                        $oper = empty($where_data[$field['id']]['operator']) ? 'eq' : $where_data[$field['id']]['operator'];
                    }
                    
                    if (is_array($sess_value)) {
                        // Se per caso ho forzato questo valore ad essere array,
                        // intanto lo implodo, poi vedrò come gestirlo
                        $sess_value = implode(',', $sess_value);
                    }
                    //debug($sess_value);
                    if ($sess_value === null && $sess_value !== '-1') {
                        //debug('foo');
                        $form_field = $this->db
                            ->join('fields', 'fields_id = forms_fields_fields_id', 'LEFT')
                            ->get_where('forms_fields', [
                                'fields_name' => $field['name'],
                                'forms_fields_forms_id' => $form['forms']['forms_id'],
                            ])
                            ->row_array();
                        
                        $value = $this->datab->get_default_fields_value($form_field, $value_id);
                        //debug($value);
                        if ($value !== null) {
                            //debug('foo');
                            //If it has a default value, save into session
                            if (empty($sess_data[$filterSessionKey])) {
                                $sess_data[$filterSessionKey] = [];
                            }
                            
                            $where_data = $this->session->userdata(SESS_WHERE_DATA);
                            if (empty($where_data[$filterSessionKey])) {
                                $where_data[$filterSessionKey] = [];
                            }
                            
                            // $where_data[$filterSessionKey] = array_unique(array_merge(
                            //     $where_data[$filterSessionKey],
                            //     [
                            //         $field['id'] => [
                            //             'field_id' => $field['id'],
                            //             'operator' => 'eq',
                            //             'value' => $value,
                            //         ],
                            //     ]
                            // ), SORT_REGULAR);
                            
                            $where_data[$filterSessionKey] = array_unique(
                                
                                [
                                    $field['id'] => [
                                        'field_id' => $field['id'],
                                        'operator' => 'eq',
                                        'value' => $value,
                                    ],
                                ]+
                                $where_data[$filterSessionKey], SORT_REGULAR
                            
                            );
                            
                            
                            $this->session->set_userdata(SESS_WHERE_DATA, array_filter($where_data));
                        }
                    } else {
                        $value = $sess_value;
                    }
                    
                    ?>
                    
                    <div class="form-group">
                        <input type="hidden" class="js-filter-field" name="conditions[<?php echo $k; ?>][field_id]" value="<?php echo $field['id']; ?>" />
                        <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="<?php echo $oper; ?>" />
                        
                        <label style="width:100%;">
                            
                            <span><?php e($field['label']);?></span>
                            <?php if ($reverse): ?>
                                <span style="float:right;">
                                    <?php e('reverse');?> <input type="checkbox" name="conditions[<?php echo $k; ?>][reverse]" value="<?php echo DB_BOOL_TRUE; ?>" <?php if ($reverse_checked): ?> checked="checked" <?php endif;?>/>
                                </span>
                            <?php endif;?>
                        </label>
                        <?php if (in_array($field['type'], ['date', 'date_time'])): ?>
                            <div class="input-group js_form_daterangepicker">
                                
                                <input name="conditions[<?php echo $k; ?>][value]" type="text" class="form-control" value="<?php echo $value; ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"> <i class="fas fa-calendar-alt"></i> </button>
                                </span>
                            </div>
                        <?php elseif ($field['datatype'] == DB_BOOL_IDENTIFIER): ?>
                        
                    
                        <?php //debug($field);
                        ?>
                            
                            
                            <div class="col-xs-12">
                                
                                <label class="radio-inline">
                                    
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_TRUE; ?>" <?php echo (($value === DB_BOOL_TRUE) ? 'checked' : ''); ?> />
                                    <?php e('Yes');?>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_FALSE; ?>" <?php echo (($value === DB_BOOL_FALSE) ? 'checked' : ''); ?> />
                                    <?php e('No');?>
                                </label>
                                <button type="button" class="btn-link" onclick="$('.field_<?php echo $field['id']; ?>', $('#<?php echo "form_{$form['forms']['forms_id']}" ?>')).attr('checked', false)" data-toggle="tooltip" title="<?php e('Remove selection');?>">
                                    <small><i class="fas fa-times"></i></small>
                                </button>
                            </div>
                        <?php elseif ($field['datatype'] == 'INT4RANGE'): ?>
                        <?php
                        //Mi costruisco i parametri basati sui min/max dell'entità.
                        
                        $entity = $this->datab->get_entity($field_completo['fields_entity_id']);
                        if ($field['min'] === '') {
                            $min = $this->db->query("SELECT MIN(LOWER({$field_completo['fields_name']})) as min FROM {$entity['entity_name']}");
                            $min = ($min->num_rows() == 1) ? $min->row()->min : 0;
                        } else {
                            $min = $field['min'];
                        }
                        if ($field['max'] === '') {
                            $max = $this->db->query("SELECT MAX(UPPER({$field_completo['fields_name']})) as max FROM {$entity['entity_name']}");
                            $max = ($max->num_rows() == 1) ? $max->row()->max : 1;
                        } else {
                            $max = $field['max'];
                        }
                        $step = (int) ($max - $min) / 20;
                        if ($value) {
                            $value_expl = explode(',', $value);
                        } else {
                            $value_expl = [$min, $max];
                        }
                        ?>
                        <input id="range_field_<?php echo $field['id']; ?>" type="text" value="<?php echo $value; ?>" />
                        <input type="hidden" class="js-filter-field" id="range_field_value_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value]" value="<?php echo $value; ?>" />
                            <script>
                                $(document).ready(function() {
                                    'use strict';
                                    $("#range_field_<?php echo $field['id']; ?>").ionRangeSlider({
                                        min: <?php echo $min; ?>,
                                        max: <?php echo $max; ?>,
                                        from: <?php echo $value_expl[0]; ?>,
                                        to: <?php echo $value_expl[1]; ?>,
                                        type: 'double',
                                        step: 1,
                                        prefix: "",
                                        postfix: "",
                                        hasGrid: true,
                                        onChange: function(obj) {
                                            from = obj.fromNumber;
                                            to = obj.toNumber + 1;
                                            $('#range_field_value_<?php echo $field['id']; ?>').val(from + ',' + to);
                                        }
                                    });
                                    
                                });
                            </script>
                        <?php else: ?>

                    

                        
                        <?php if ($field['filterref']):
                        $preview = $this->datab->get_entity_preview_by_name($field['filterref'], "{$field['filterref']}_id = '{$value}'", 1);
                        $value_preview = array_pop($preview);
                        ?>
                        
                        <?php if ($field['type'] == 'multiselect'): ?>
                        
                            <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />
                                
                                <select multiple class="form-control select2me field_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value][]" data-val="<?php echo $value; ?>"  data-field_name="<?php echo $field['name'] ?>" data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : ($field['filterref'] ?? '') ?>" data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>" data-minimum-input-length="0">
                                    <?php
                                    $filter_ref = $field['filterref'];
                                    $entity = $this->crmentity->getEntity($filter_ref);
                                    if ($entity['entity_type'] == ENTITY_TYPE_RELATION) {
                                        $relation = $this->db->get_where('relations', ['relations_name' => $entity['entity_name']])->row_array();
                                        
                                        $support_data = $this->crmentity->getEntityPreview($relation['relations_table_2'],$field['original_field']['fields_select_where']);
                                    } else {
                                        
                                        $support_data = $this->crmentity->getEntityPreview($field['filterref'],$field['original_field']['fields_select_where']);
                                    }
                                    
                                    
                                    ?>
                                    <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>><?php e('Field empty');?></option>
                                    <?php foreach ($support_data as $id => $name): ?>
                                        <option value="<?php echo $id; ?>" <?php echo (in_array($id, explode(',', $value))) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                    <?php endforeach;?>
                                
                                </select>
                        <?php elseif ($field['type'] == 'select_ajax'): ?>
                                
                            <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />
                                
                                <select class="js_select_ajax_new form-control " name="conditions[<?php echo $k; ?>][value]" data-val="<?php echo $value; ?>" data-ref="<?php echo $field['filterref']; ?>" data-source-field="" data-minimum-input-length="0">
                                    <?php if (isset($field['support_data'])) : ?>
                                        <?php foreach ((array) $field['support_data'] as $id => $name) : ?>
                                            <?php if ($id != $value) {
                                                continue;
                                            } ?>
                                            <option value="<?php echo $id; ?>" selected><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    <?php elseif ($value) : ?>
                                        <option value="<?php echo $value; ?>" selected="selected"><?php echo $value_preview; ?></option>
                                    <?php endif; ?>
                                
                                </select>

                        <?php else: ?>

                                <?php
                                $data = $this->apilib->runDataProcessing($field['filterref'], 'pre-search');
                            $where = '1';
                            foreach ($data as $campo => $valore) {
                                if ($campo) {
                                    $where .= " AND {$campo} = '$valore'";
                                } else {
                                    $where .= " AND $valore";
                                }
                            }
                            if ($field_completo['fields_select_where']) {
                                $field_completo['fields_select_where'] = str_replace('{value_id}', $value_id, $field_completo['fields_select_where']);
                                $where_replaced = $this->datab->replace_superglobal_data(trim($field_completo['fields_select_where']));
                                $where .= " AND {$where_replaced}";
                            }
                            if ($where === '1') {
                                $where = null;
                            }
                            
                            if ($referenced = $this->crmentity->getReferencedEntity($field['name'])) {
                                $entity = $referenced['entity_name'];
                            } else {
                                $entity = $field['filterref'];
                            }
                            
                            ?>
                            
                            <?php // ---------------- NEW SELECT BIG BUTTONS -------------- ?>

                              <?php if ($field['type'] == 'select_big_buttons'):
                                  ?>
                           
                                 <input type="hidden" class="form-control js_badge_hidden_<?php echo $field['id']; ?> <?php echo (!empty($class)) ? $class : ''; ?>" 
                                                data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>" 
                                                name="conditions[<?php echo $k; ?>][value]" 
                                                data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : '' ?>"
                                                value="<?php echo $value; ?>" />
                                
                                <div class="badge_form_field_container">
                                    <?php foreach ($this->crmentity->getEntityPreview($entity, $where) as $id => $name): ?>
                                        <span class="badge badge_form_field js_badge_form_field <?php echo ($id == $value) ? 'badge_form_field_active' : ''; ?>" data-hidden_field="js_badge_hidden_<?php echo $field['id']; ?>" data-value_id="<?php echo $id; ?>" ><?php echo $name; ?></span>
                                    <?php endforeach;?>
                                </div>

                           
                            <?php else:?>

                                <?php // ---------------- STANDARD SELECT --------------   ?>
                                            <select class="form-control select2_standard <?php echo (!empty($class)) ? $class : ''; ?>"
                            data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>"
                            data-field_name="<?php echo $field['name'] ?>"
                            name="conditions[<?php echo $k; ?>][value]"
                            data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : ($field['filterref'] ?? '') ?>" data-val="<?php echo $value; ?>"
                            <?php echo $field['onclick']; ?>>

                            <option value="-1" <?php echo ('-1' == $value) ? 'selected' : ''; ?>>---</option>
                            <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>>
                                <?php e('Field empty'); ?>
                            </option>

                            <?php foreach ($this->crmentity->getEntityPreview($entity, $where) as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                                <?php endif;?>
                        <?php endif;?>
                        
                        <?php else: ?>
                            <?php
                            $field = array_merge($field, $this->db->where('fields_id', $field['id'])->join('entity', '(fields_entity_id = entity_id)', 'LEFT')->get('fields')->row_array());
                            
                            $soft_delete_flag = '';
                            if (!empty($field['entity_action_fields'])) {
                                $action_fields = json_decode($field['entity_action_fields'], true);
                                
                                if (!empty($action_fields['soft_delete_flag'])) {
                                    $soft_delete_field = $action_fields['soft_delete_flag'];
                                    $soft_delete_flag = " WHERE ($soft_delete_field IS NULL OR $soft_delete_field = '' OR $soft_delete_field = '".DB_BOOL_FALSE."')";
                                }
                            }
                            
                            ?>
                            
                            
                            <?php if ($field['type'] == 'multiselect'): ?>
                        <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />
                            
                            <select multiple class="form-control select2me field_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value][]" data-val="<?php echo $value; ?>" data-ref="<?php echo $field['filterref']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-minimum-input-length="0">
                                <option value="-2" <?php echo (in_array(-2, explode(',', $value))) ? 'selected' : ''; ?>><?php e('Field empty');?></option>
                                <?php foreach ($this->db->query("SELECT DISTINCT {$field['name']} as valore FROM {$field['entity_name']} $soft_delete_flag ORDER BY {$field['name']}")->result_array() as $row): ?>
                                    <?php if ($row['valore']): ?>
                                        <option value="<?php echo $row['valore']; ?>" <?php echo (in_array($row['valore'], explode(',', $value))) ? 'selected' : ''; ?>><?php echo $row['valore']; ?></option>
                                    <?php endif;?>
                                <?php endforeach;?>
                            
                            </select>
                        <?php elseif ($field['type'] == 'select_ajax'): ?>
                        <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />
                            
                            <select class="js_select_ajax_new form-control <?php echo $class ?>" name="conditions[<?php echo $k; ?>][value]" data-val="<?php echo $value; ?>" data-ref="<?php echo $field['filterref']; ?>" data-source-field="<?php echo $field['field_ref'] ?>" data-minimum-input-length="0">
                                <?php if (isset($field['support_data'])) : ?>
                                    <?php foreach ((array) $field['support_data'] as $id => $name) : ?>
                                        <?php if ($id != $value) {
                                            continue;
                                        } ?>
                                        <option value="<?php echo $id; ?>" selected><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                <?php elseif ($value) : ?>
                                    <option value="<?php echo $value; ?>" selected="selected"><?php echo $value_preview; ?></option>
                                <?php endif; ?>
                            
                            </select>
                        
                        
                        <?php else: ?>
                            
                                    <?php // ----------------- SELECT BIG BUTTONS YEAR -------------------- ?>
                                    <?php if ($field['type'] == 'select_big_buttons_years'):  ?>
                                        
                                        <input type="hidden"
                                            class="form-control js_badge_hidden_<?php echo $field['id']; ?> <?php echo (!empty($class)) ? $class : ''; ?>"
                                            data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>"
                                            name="conditions[<?php echo $k; ?>][value]"
                                            data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : '' ?>" value="<?php echo $value; ?>" />
                                    
                                        <div class="badge_form_field_container">                                        
                                                <?php for ($i = date('Y') - 2; $i <= date('Y') + 2; $i++): ?>
                                                    <?php $range_value = '01/01/' . $i . ' - 31/12/' . $i; ?>
                                                    <span class="badge badge_form_field js_badge_form_field <?php echo ($range_value == $value) ? 'badge_form_field_active' : ''; ?>"
                                                        data-hidden_field="js_badge_hidden_<?php echo $field['id']; ?>" data-value_id="<?php echo $range_value; ?>">
                                                        <?php echo $i; ?>
                                                    </span>
                                                <?php endfor; ?>
                                                
                                        </div>

                                            <?php // ----------------- SELECT BIG BUTTONS YEAR AND MONTH --------------------  ?>
                                    <?php elseif ($field['type'] == 'select_big_buttons_years_month'):  ?>
                                        
                                        <input type="hidden"
                                            class="form-control js_badge_hidden_<?php echo $field['id']; ?> <?php echo (!empty($class)) ? $class : ''; ?>"
                                            data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>"
                                            name="conditions[<?php echo $k; ?>][value]"
                                            data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : '' ?>" value="<?php echo $value; ?>" />
                                    
                                        <div class="badge_form_field_container">                                        
                                                <?php for ($i = date('Y') - 2; $i <= date('Y') + 2; $i++): ?>
                                                    <?php $range_value = '01/01/' . $i . ' - 31/12/' . $i; ?>
                                                    <span class="badge badge_form_field js_badge_form_field_year js_badge_form_field <?php echo ($range_value == $value) ? 'badge_form_field_active' : ''; ?>"
                                                        data-hidden_field="js_badge_hidden_<?php echo $field['id']; ?>" data-year="<?php echo $i;?>" data-value_id="<?php echo $range_value; ?>">
                                                        <?php echo $i; ?>
                                                    </span>
                                                <?php endfor; ?>
                                                <div class="badge_form_field_container_month">
                                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                                        <?php $month_value = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                                        <span class="badge badge_form_field js_badge_form_field_month js_badge_form_field <?php echo ($month_value == $value) ? 'badge_form_field_active' : ''; ?>"
                                                            data-type_value="month" data-hidden_field="js_badge_hidden_<?php echo $field['id']; ?>" data-month="<?php echo $month_value;?>" data-value_id="<?php echo $month_value; ?>">
                                                            <?php echo t(date('F', mktime(0, 0, 0, $i, 1))); ?>
                                                        </span>
                                                    <?php endfor; ?>
                                                    </div>
                                        </div>

                                        <script>

                                            document.addEventListener('DOMContentLoaded', function() {
                                            // Ottiene il valore dell'input nascosto
                                            const hiddenInputValue = document.querySelector('.js_badge_hidden_<?php echo $field['id']; ?>').value;

                                            // Estrae l'anno, il mese e il giorno dal valore dell'input
                                            const match = hiddenInputValue.match(/(\d{2})\/(\d{2})\/(\d{4}) - (\d{2})\/(\d{2})\/(\d{4})/);
                                            if (match) {
                                                const startDay = match[1];
                                                const startMonth = match[2];
                                                const startYear = match[3];
                                                const endDay = match[4];
                                                const endMonth = match[5];
                                                const endYear = match[6];
                                                // Seleziona e attiva il pulsante dell'anno corrispondente
                                                const yearButton = document.querySelector(`span[data-year="${startYear}"]`);
                                                if (yearButton) {
                                                    yearButton.classList.add('badge_form_field_active');
                                                   
                                                }

                                                if (startMonth == endMonth) {
                                                    // Seleziona e attiva il pulsante del mese corrispondente
                                                    const monthButton = document.querySelector(`span[data-month="${startMonth}"]`);
                                                    if (monthButton) {
                                                        monthButton.classList.add('badge_form_field_active');
                                                    }
                                                }
                                            }
                                        });



                                           document.addEventListener('DOMContentLoaded', function() {
                                                // Funzione per estrarre l'anno dal valore dell'input nascosto
                                                function getSelectedYearFromHiddenInput(hiddenFieldClass) {
                                                    const hiddenInput = document.querySelector('.' + hiddenFieldClass);
                                                    if (hiddenInput && hiddenInput.value) {
                                                        const yearMatch = hiddenInput.value.match(/\d{4}/); // Trova l'anno nel formato 'YYYY'
                                                        return yearMatch ? yearMatch[0] : '';
                                                    }
                                                    return '';
                                                }

                                                // Gestisce il click su un anno
                                                document.querySelectorAll('.js_badge_form_field_year').forEach(function(span) {
                                                    span.addEventListener('click', function() {
                                                        const hiddenFieldClass = this.getAttribute('data-hidden_field');
                                                        const value = this.getAttribute('data-value_id');
                                                        // Aggiorna il campo nascosto
                                                        document.querySelector('.' + hiddenFieldClass).value = value;
                                                    });
                                                });

                                                // Gestisce il click su un mese
                                                document.querySelectorAll('.js_badge_form_field_month').forEach(function(span) {
                                                    span.addEventListener('click', function() {
                                                        const hiddenFieldClass = this.getAttribute('data-hidden_field');
                                                        const hiddenInput = document.querySelector('.' + hiddenFieldClass);
                                                        // Estrai l'anno dal valore corrente dell'input nascosto
                                                        let selectedYear = getSelectedYearFromHiddenInput(hiddenFieldClass);

                                                        if (!selectedYear) {
                                                            selectedYear = "<?php echo date('Y'); ?>";
                                                            const element = document.querySelector('span.js_badge_form_field_year[data-year="'+selectedYear+'"]');
                                                            console.log(element);
                                                            element.classList.add('badge_form_field_active');
                                                        }

                                                        const month = this.getAttribute('data-value_id');
                                                        const lastDayOfMonth = new Date(selectedYear, month, 0).getDate();

                                                        // Formatta il valore per il campo nascosto
                                                        const value = `01/${month}/${selectedYear} - ${lastDayOfMonth}/${month}/${selectedYear}`;
                                                        hiddenInputs.forEach(input => {
                                                            input.value = value; // Aggiorna il valore per tutti gli input trovati
                                                        });
                                                    });
                                                });
                                            });


                                            </script>

                                    <?php else: ?>
                            <select class="form-control select2_standard <?php echo $class ?>" name="conditions[<?php echo $k; ?>][value]" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $value; ?>" <?php echo $field['onclick']; ?>>
                            
                            <option value="-1" <?php echo ('-1' == $value) ? 'selected' : ''; ?>>---</option>
                            <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>><?php e('Field empty');?></option>
                            
                            <?php foreach ($this->db->query("SELECT DISTINCT {$field['name']} as valore FROM {$field['entity_name']} $soft_delete_flag ORDER BY {$field['name']}")->result_array() as $row): ?>
                                <?php if (!empty($row['valore'])): ?><option value="<?php echo $row['valore']; ?>" <?php if ($value == $row['valore']): ?> selected="selected" <?php endif;?>><?php echo $row['valore']; ?></option><?php endif;?>
                            <?php endforeach;?>

<?php endif;?>
                        <?php endif;?>
                        </select>
                        <?php endif;?>
                        <?php endif;?>
                    </div>
                </div>
            <?php endforeach;?>
            <?php echo $rowCol ? $rowEnd : ''; ?>
        </div>
    </div>
    
    <div class="form-actions fluid">
        <div class="col-md-12">
            <div class="pull-right">
                
                <?php if ($where_data): ?>
                    <input type="hidden" class="clear-filters-<?php echo $form['forms']['forms_id']; ?>" name="clear-filters" value="" />
                    <input type="button" onclick="javascript:$('.clear-filters-<?php echo $form['forms']['forms_id']; ?>').val('1');$('.form_<?php echo $form['forms']['forms_id']; ?>').trigger('submit');" class="btn red-intense" value="<?php e('Clear filters');?>" />
                <?php endif;?>
                <button type="submit" class="btn btn-primary"><?php echo $where_data ? t('Filter') : t('Update filters'); ?></button>
            </div>
        </div>
    </div>
</form>