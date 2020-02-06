<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];

// Recupera info filtri e indicizza i dati per field_id
$_sess_where_data = array_get($sess_data, $form['forms']['forms_filter_session_key'], []);
$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);
?>
<form autocomplete="off" <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax js_filter_form" enctype="multipart/form-data">
        
    <div class="form-body">
        <div class="row">
            <?php foreach($form['forms_fields'] as $k=>$field): ?>
            <?php //debug($field); ?>
                <div class="<?php echo sprintf('col-lg-%d', $field['size'] ? : 6); ?>">
                    <?php
                    //debug($where_data,true);
                    $value = empty($where_data[$field['id']]['value'])? NULL: $where_data[$field['id']]['value'];
                    if ($field['datatype'] == 'INT4RANGE') {
                        $oper  = empty($where_data[$field['id']]['operator'])? 'rangein': $where_data[$field['id']]['operator'];
                    } else {
                        $oper  = empty($where_data[$field['id']]['operator'])? 'eq': $where_data[$field['id']]['operator'];
                    }
                    
                    if (is_array($value)) {
                        // Se per caso ho forzato questo valore ad essere array,
                        // intanto lo implodo, poi vedrò come gestirlo
                        $value = implode(',', $value);
                    }
                    ?>
                    <div class="form-group">
                        <input type="hidden" class="js-filter-field" name="conditions[<?php echo $k; ?>][field_id]" value="<?php echo $field['id']; ?>" />
                        <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="<?php echo $oper; ?>" />

                        <label><?php echo $field['label']; ?></label>
                        <?php if(in_array($field['type'], ['date', 'date_time'])): ?>
                            <div class="input-group js_form_daterangepicker">
                                <input name="conditions[<?php echo $k; ?>][value]" type="text" class="form-control" value="<?php echo $value; ?>" />
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"> <i class="fas fa-calendar-alt"></i> </button>
                                </span>
                            </div>
                        <?php elseif($field['datatype'] == DB_BOOL_IDENTIFIER): ?>
                            <button type="button" class="btn-link" onclick="$('.field_<?php echo $field['id']; ?>', $('#<?php echo "form_{$form['forms']['forms_id']}" ?>')).attr('checked', false)" data-toggle="tooltip" title="Rimuovi selezione" >
                                <small><i class="fas fa-times"></i></small>
                            </button>
                            <div class="col-xs-12">
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_TRUE; ?>" <?php echo (($value==DB_BOOL_TRUE)? 'checked': ''); ?> />
                                    Si
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="<?php echo DB_BOOL_FALSE; ?>" <?php echo (($value==DB_BOOL_FALSE)? 'checked': ''); ?> />
                                    No
                                </label>
                            </div>
                        <?php elseif($field['datatype'] == 'INT4RANGE'): ?>
                            <?php
                            
                            //debug($field,true);
                            
                                //Mi costruisco i parametri basati sui min/max dell'entità.
                                $field_completo = $this->datab->get_field($field['id']);
                                $entity = $this->datab->get_entity($field_completo['fields_entity_id']);
                                //var_dump($field['min']);
                                if ($field['min'] === '') {
                                    $min = $this->db->query("SELECT MIN(LOWER({$field_completo['fields_name']})) as min FROM {$entity['entity_name']}");
                                    $min = ($min->num_rows() == 1)?$min->row()->min:0;
                                } else {
                                    $min = $field['min'];
                                }
                                if ($field['max'] === '') {
                                    $max = $this->db->query("SELECT MAX(UPPER({$field_completo['fields_name']})) as max FROM {$entity['entity_name']}");
                                    $max = ($max->num_rows() == 1)?$max->row()->max:1;
                                } else {
                                    $max = $field['max'];
                                }
                                $step = (int)($max-$min)/20;
                                if ($value) {
                                    $value_expl = explode(',', $value);
                                } else {
                                    $value_expl = [$min,$max];
                                }
                                
                                //debug($value,true);
                            ?>
                            <input id="range_field_<?php echo $field['id']; ?>" type="text" value="<?php echo $value; ?>" />
                            <input type="hidden" class="js-filter-field" id="range_field_value_<?php echo $field['id']; ?>" name="conditions[<?php echo $k; ?>][value]" value="<?php echo $value; ?>" />
                            <script>
                                //alert('<?php echo $value; ?>');
                                $(document).ready(function () {
                                    $("#range_field_<?php echo $field['id']; ?>").ionRangeSlider({
                                        min: <?php echo $min; ?>,
                                        max: <?php echo $max; ?>,
                                        from: <?php echo $value_expl[0]; ?>,
                                        to: <?php echo $value_expl[1]; ?>,
                                        type: 'double',
                                        step: 1,
                                        prefix: "",
                                        postfix: "",
                                        //prettify: false,
                                        hasGrid: true,
                                        onChange: function(obj){
                                            from = obj.fromNumber;
                                            to = obj.toNumber+1;
                                            //alert(from+','+to);
                                            $('#range_field_value_<?php echo $field['id']; ?>').val(from+','+to);
                                        }
                                    });
                                    
                                    
                                });
                            </script>
                        <?php else: ?>
                        	
                        	<?php if ($field['type'] == 'multiselect'): ?>
                            	<input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="in" />
    							<?php if($field['filterref']): ?>
                                    <select multiple class="form-control select2me field_<?php echo $field['id']; ?>"
                                            name="conditions[<?php echo $k; ?>][value][]"
                                            data-val="<?php echo $value; ?>" 
                                            data-ref="<?php echo $field['filterref']; ?>"
                                            data-source-field=""
                                            data-minimum-input-length="0" >
                                        
                                        <?php foreach ($this->crmentity->getEntityPreview($field['filterref']) as $id => $name) : ?>
                                        <option value="<?php echo $id; ?>" <?php echo (in_array($id, explode(',', $value))) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                        
                                    </select>  
                                <?php else: ?>
                                    <?php 
                                    $field_completo = $this->datab->get_field($field['id']);
                                    $entity = $this->datab->get_entity($field_completo['fields_entity_id']); 
                                    ?>
                                    <select multiple class="form-control select2me field_<?php echo $field['id']; ?>"
                                            name="conditions[<?php echo $k; ?>][value][]"
                                            data-val="<?php echo $value; ?>" 
                                            data-ref=""
                                            data-source-field=""
                                            data-minimum-input-length="0" >
                                            
                                        <?php foreach ($this->db->query("SELECT DISTINCT({$field_completo['fields_name']}) as foo FROM {$entity['entity_name']}")->result_array() as $val) : ?>
                                        <option value="<?php echo $val['foo']; ?>" <?php echo ($value && in_array($val['foo'], explode(',', $value))) ? 'selected' : ''; ?>><?php echo $val['foo']; ?></option>
                                        <?php endforeach; ?>
                                        
                                    </select>
                                <?php endif; ?> 
                            <?php else: ?>
                            	<?php if($field['filterref']): ?>
                            	<input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-ref="<?php echo $field['filterref']; ?>" data-referer="<?php echo $field['name']; ?>" class="form-control js_select_ajax field_<?php echo $field['id']; ?>" value="<?php echo $value; ?>" />
                            	<?php  else : ?>
                            	<input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-field-id="<?php echo $field['id']; ?>" class="form-control js_select_ajax_distinct field_<?php echo $field['id']; ?>" value="<?php echo $value; ?>" />
                            	<?php  endif; ?>
                            <?php endif; ?>
                        
                            
                            <?php //debug($value); ?>
                                
                            
                        <?php endif; ?>
                    </div>
                </div>     
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <div class="pull-right">
                
                <?php if ($where_data): //Ho dovuto fare sto accrocchione perchè il new FormData che viene usato nel submit ajax non posta gli input type button... ?>
                <input type="hidden" id="clear-filters-<?php echo $form['forms']['forms_id']; ?>" name="clear-filters" value="" />
                <input type="button" onclick="javascript:$('#clear-filters-<?php echo $form['forms']['forms_id']; ?>').val('1');$('#form_<?php echo $form['forms']['forms_id']; ?>').trigger('submit');" class="btn red-intense"  value="Svuota filtri" />
                <?php endif; ?>
                <button type="submit" class="btn blue"><?php echo $where_data? 'Filtra': 'Aggiorna filtri'; ?></button>
            </div>
        </div>
    </div>
</form>