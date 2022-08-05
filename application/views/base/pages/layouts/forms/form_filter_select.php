<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];

// Recupera info filtri e indicizza i dati per field_id
$filterSessionKey = $form['forms']['forms_filter_session_key'];
$_sess_where_data = array_get($sess_data, $filterSessionKey, []);

$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);


?>
<div class="row">
    <!-- Builder actions -->
    <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="builder" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form builder">
        <i class="fas fa-hat-wizard"></i>
    </button>
    <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="option" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form option">
        <i class="fas fa-edit"></i>
    </button>
    <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_init_sortableform hide" data-toggle="tooltip" title="" data-original-title="Start Drag Fields">
        <i class="fas fa-arrows-alt"></i>
    </button>
    <!-- End Builder actions -->
</div>

<form autocomplete="off" <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?> js_filter_form <?php echo ("form_{$form['forms']['forms_id']}"); ?>" enctype="multipart/form-data">
    <?php add_csrf(); ?>
    <div class="form-body">
        <div class="row sortableForm">
            <?php foreach ($form['forms_fields'] as $k => $field) : ?>

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
                    
                    $reverse =   $field['original_field']['forms_fields_allow_reverse']==DB_BOOL_TRUE;

                    $field_completo = $this->datab->get_field($field['id']);

                    $sess_value = !isset($where_data[$field['id']]['value']) ? NULL : $where_data[$field['id']]['value'];
                    $reverse_checked = (!empty($where_data[$field['id']]['reverse']) && $where_data[$field['id']]['reverse'] == DB_BOOL_TRUE) ? true : false;

                    
                    if ($field['datatype'] == 'INT4RANGE') {
                        $oper  = empty($where_data[$field['id']]['operator']) ? 'rangein' : $where_data[$field['id']]['operator'];
                    } else {
                        $oper  = empty($where_data[$field['id']]['operator']) ? 'eq' : $where_data[$field['id']]['operator'];
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
                                'forms_fields_forms_id' => $form['forms']['forms_id']
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

                            $where_data[$filterSessionKey] = array_unique(array_merge(
                                $where_data[$filterSessionKey],
                                [
                                    $field['id'] => [
                                        'field_id' => $field['id'],
                                        'operator' => 'eq',
                                        'value' => $value,
                                    ]
                                ]
                            ), SORT_REGULAR);

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
                            
                            <span><?php e($field['label']); ?></span>
                            <?php if ($reverse) : ?>
                                <span style="float:right;">
                                    <?php e('reverse'); ?> <input type="checkbox" name="conditions[<?php echo $k; ?>][reverse]" value="<?php echo DB_BOOL_TRUE; ?>" <?php if ($reverse_checked) : ?> checked="checked" <?php endif; ?>/> 
                                </span>
                            <?php endif; ?>
                        </label>
                        <?php if (in_array($field['type'], ['date', 'date_time'])) : ?>
                            <div class="input-group js_form_daterangepicker">

                                <input name="conditions[<?php echo $k; ?>][value]" type="text" class="form-control" value="<?php echo $value; ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"> <i class="fas fa-calendar-alt"></i> </button>
                                </span>
                            </div>
                        <?php elseif ($field['datatype'] == DB_BOOL_IDENTIFIER) : ?>


                            <?php //debug($value); 
                            ?>

                            <button type="button" class="btn-link" onclick="$('.field_<?php echo $field['id']; ?>', $('#<?php echo "form_{$form['forms']['forms_id']}" ?>')).attr('checked', false)" data-toggle="tooltip" title="<?php e('Remove selection'); ?>">
                                <small><i class="fas fa-times"></i></small>
                            </button>
                            <div class="col-xs-12">
                                <label class="radio-inline">

                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_TRUE; ?>" <?php echo (($value === DB_BOOL_TRUE) ? 'checked' : ''); ?> />
                                    <?php e('Yes'); ?>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_FALSE; ?>" <?php echo (($value === DB_BOOL_FALSE) ? 'checked' : ''); ?> />
                                    <?php e('No'); ?>
                                </label>
                            </div>
                        <?php elseif ($field['datatype'] == 'INT4RANGE') : ?>
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
                        <?php else : ?>

                            <?php if ($field['filterref']) : ?>
                                <?php if ($field['type'] == 'multiselect') : ?>

                                    <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />

                                    <select multiple class="form-control select2me field_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value][]" data-val="<?php echo $value; ?>" data-ref="<?php echo $field['filterref']; ?>" data-source-field="" data-minimum-input-length="0">
                                        <?php
                                        $filter_ref = $field['filterref'];
                                        $entity = $this->crmentity->getEntity($filter_ref);
                                        if ($entity['entity_type'] == ENTITY_TYPE_RELATION) {
                                            $relation = $this->db->get_where('relations', ['relations_name' => $entity['entity_name']])->row_array();

                                            $support_data = $this->crmentity->getEntityPreview($relation['relations_table_2']);
                                        } else {
                                            $support_data = $this->crmentity->getEntityPreview($field['filterref']);
                                        }
                                        ?>
                                        <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>><?php e('Field empty'); ?></option>
                                        <?php foreach ($support_data as $id => $name) : ?>
                                            <option value="<?php echo $id; ?>" <?php echo (in_array($id, explode(',', $value))) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                <?php else : ?>
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
                                    <select class="form-control select2_standard <?php echo (!empty($class))?$class:''; ?>" data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>" name="conditions[<?php echo $k; ?>][value]" data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : '' ?>" data-val="<?php echo $value; ?>" <?php echo $field['onclick']; ?>>

                                        <option value="-1" <?php echo ('-1' == $value) ? 'selected' : ''; ?>>---</option>
                                        <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>><?php e('Field empty'); ?></option>

                                        <?php foreach ($this->crmentity->getEntityPreview($entity, $where) as $id => $name) : ?>
                                            <option value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>

                            <?php else : ?>
                                <?php $field = array_merge($field, $this->db->where('fields_id', $field['id'])->join('entity', '(fields_entity_id = entity_id)', 'LEFT')->get('fields')->row_array()); ?>
                                <?php if ($field['type'] == 'multiselect') : ?>
                                    <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />

                                    <select multiple class="form-control select2me field_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value][]" data-val="<?php echo $value; ?>" data-ref="<?php echo $field['filterref']; ?>" data-source-field="" data-minimum-input-length="0">
                                    <option value="-2" <?php echo (in_array(-2, explode(',', $value))) ? 'selected' : ''; ?>><?php e('Field empty'); ?></option>
                                        <?php foreach ($this->db->query("SELECT DISTINCT {$field['name']} as valore FROM {$field['entity_name']} ORDER BY {$field['name']}")->result_array() as $row) : ?>
                                            <?php if ($row['valore']) : ?>
                                                <option value="<?php echo $row['valore']; ?>" <?php echo (in_array($row['valore'], explode(',', $value))) ? 'selected' : ''; ?>><?php echo $row['valore']; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>

                                    </select>
                                <?php else : ?>
                                    <select class="form-control select2_standard <?php echo $class ?>" name="conditions[<?php echo $k; ?>][value]" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $value; ?>" <?php echo $field['onclick']; ?>>

                                        <option value="-1" <?php echo ('-1' == $value) ? 'selected' : ''; ?>>---</option>
                                        <option value="-2" <?php echo ('-2' == $value) ? 'selected' : ''; ?>><?php e('Field empty'); ?></option>

                                        <?php foreach ($this->db->query("SELECT DISTINCT {$field['name']} as valore FROM {$field['entity_name']} ORDER BY {$field['name']}")->result_array() as $row) : ?>
                                            <?php if (!empty($row['valore'])) : ?><option value="<?php echo $row['valore']; ?>" <?php if ($value == $row['valore']) : ?> selected="selected" <?php endif; ?>><?php echo $row['valore']; ?></option><?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </select>
                                <?php endif; ?>
                            <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <div class="pull-right">

                <?php if ($where_data) : ?>
                    <input type="hidden" class="clear-filters-<?php echo $form['forms']['forms_id']; ?>" name="clear-filters" value="" />
                    <input type="button" onclick="javascript:$('.clear-filters-<?php echo $form['forms']['forms_id']; ?>').val('1');$('.form_<?php echo $form['forms']['forms_id']; ?>').trigger('submit');" class="btn red-intense" value="<?php e('Clear filters'); ?>" />
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><?php echo $where_data ? t('Filter') : t('Update filters'); ?></button>
            </div>
        </div>
    </div>
</form>