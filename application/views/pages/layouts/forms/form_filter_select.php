<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA);
$_sess_where_data = (isset($sess_data[$form['forms']['forms_filter_session_key']])? $sess_data[$form['forms']['forms_filter_session_key']]: array());
//
// Indicizza i dati per field_id
$where_data = array_combine( array_map(function($row) { return $row['field_id']; }, $_sess_where_data), array_map(function($row) { return $row['value']; }, $_sess_where_data) );
?>

<form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax js_filter_form" enctype="multipart/form-data">
        
    <div class="form-body">
        <?php foreach($form['forms_fields'] as $k=>$field): ?>
            <div class="col-md-12">
                <?php $value = empty($where_data[$field['fields_id']])? NULL: $where_data[$field['fields_id']]; ?>
                <div class="form-group">
                    <input type="hidden" name="conditions[<?php echo $k; ?>][field_id]" value="<?php echo $field['fields_id']; ?>" />
                    <input type="hidden" name="conditions[<?php echo $k; ?>][operator]" value="eq" />
                    
                    <label><?php echo $field['fields_draw_label']; ?></label>
                    <?php if(in_array($field['fields_draw_html_type'], array('date', 'date_time'))): ?>
                        <div class="input-group js_form_daterangepicker">
                            <input name="conditions[<?php echo $k; ?>][value]" type="text" class="form-control" value="<?php echo $value; ?>" />
                            <span class="input-group-btn">
                                <button class="btn default" type="button"> <i class="icon-calendar"></i> </button>
                            </span>
                        </div>
                    <?php elseif($field['fields_type'] == 'BOOL'): ?>
                        <button type="button" class="btn-link" onclick="$('.field_<?php echo $field['fields_id']; ?>', $('#<?php echo "form_{$form['forms']['forms_id']}" ?>')).attr('checked', false)" data-toggle="tooltip" title="Rimuovi selezione" >
                            <small><i class="icon-remove"></i></small>
                        </button>
                        <div class="col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['fields_id']; ?>" value="t" <?php echo (($value=='t')? 'checked': ''); ?> />
                                Si
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['fields_id']; ?>" value="f" <?php echo (($value=='f')? 'checked': ''); ?> />
                                No
                            </label>
                        </div>
                    <?php else: ?>
                        <?php if($field['fields_ref']): ?>
                            <?php
                            // Calcolo il ref da usare, perché per le relazioni voglio cercare nella tabella relazionata, non in quella di relazione
                            if(isset($field['support_fields'][0]['entity_name'])) {
                                $ref = $field['support_fields'][0]['entity_name'];
                            } else {
                                $ref = $field['fields_ref'];
                            }
                            ?>
                            <input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-ref="<?php echo $ref; ?>" class="form-control js_select_ajax field_<?php echo $field['fields_id']; ?>" value="<?php echo $value; ?>" />
                        <?php else: ?>
                            <input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-field-id="<?php echo $field['fields_id']; ?>" class="form-control js_select_ajax_distinct field_<?php echo $field['fields_id']; ?>" value="<?php echo $value; ?>" />
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>     
        <?php endforeach; ?>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <button type="submit" class="btn blue pull-right"><?php echo (empty($grid_data)? 'Filtra': 'Aggiorna filtri'); ?></button>
        </div>
    </div>
</form>