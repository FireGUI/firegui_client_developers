<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA);
$_sess_where_data = (isset($sess_data[$form['forms']['forms_filter_session_key']])? $sess_data[$form['forms']['forms_filter_session_key']]: array());
//
// Indicizza i dati per field_id
$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);
?>

<form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax js_filter_form" enctype="multipart/form-data">
        
    <div class="form-body">
        <div class="row">
            <?php foreach($form['forms_fields'] as $k=>$field): ?>
                <div class="<?php echo sprintf('col-lg-%d', $field['size'] ? : 6); ?>">
                    <?php
                    $value = empty($where_data[$field['id']]['value'])? NULL: $where_data[$field['id']]['value'];
                    $oper  = empty($where_data[$field['id']]['operator'])? 'eq': $where_data[$field['id']]['operator'];
                    ?>
                    <div class="form-group">
                        <input type="hidden" class="js-filter-field" name="conditions[<?php echo $k; ?>][field_id]" value="<?php echo $field['id']; ?>" />
                        <input type="hidden" class="js-filter-operator" name="conditions[<?php echo $k; ?>][operator]" value="<?php echo $oper; ?>" />

                        <label><?php echo $field['label']; ?></label>
                        <?php if(in_array($field['type'], ['date', 'date_time'])): ?>
                            <div class="input-group js_form_daterangepicker">
                                <input name="conditions[<?php echo $k; ?>][value]" type="text" class="form-control" value="<?php echo $value; ?>" />
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"> <i class="fa fa-calendar"></i> </button>
                                </span>
                            </div>
                        <?php elseif($field['datatype'] == 'BOOL'): ?>
                            <button type="button" class="btn-link" onclick="$('.field_<?php echo $field['id']; ?>', $('#<?php echo "form_{$form['forms']['forms_id']}" ?>')).attr('checked', false)" data-toggle="tooltip" title="Rimuovi selezione" >
                                <small><i class="fa fa-remove"></i></small>
                            </button>
                            <div class="col-xs-12">
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="t" <?php echo (($value=='t')? 'checked': ''); ?> />
                                    Si
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="conditions[<?php echo $k; ?>][value]" class="toggle field_<?php echo $field['id']; ?>" value="f" <?php echo (($value=='f')? 'checked': ''); ?> />
                                    No
                                </label>
                            </div>
                        <?php else: ?>
                            <?php if($field['filterref']): ?>
                                <input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-ref="<?php echo $field['filterref']; ?>" data-referer="<?php echo $field['name']; ?>" class="form-control js_select_ajax field_<?php echo $field['id']; ?>" value="<?php echo $value; ?>" />
                            <?php else: ?>
                                <input type="hidden" name="conditions[<?php echo $k; ?>][value]" data-field-id="<?php echo $field['id']; ?>" class="form-control js_select_ajax_distinct field_<?php echo $field['id']; ?>" value="<?php echo $value; ?>" />
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>     
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <button type="submit" class="btn blue pull-right"><?php echo (empty($grid_data)? 'Filtra': 'Aggiorna filtri'); ?></button>
        </div>
    </div>
</form>
