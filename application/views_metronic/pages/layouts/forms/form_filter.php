<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];

// Recupera info filtri e indicizza i dati per field_id
$_sess_where_data = array_get($sess_data, $form['forms']['forms_filter_session_key'], []);
$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);
?>
<form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax js_filter_form" enctype="multipart/form-data">
    
    
    <div class="form-body">
        <div class="col-xs-12">
            <p>Add conditions to filter the table</p>
        </div>

        <div class="js_filter_form_rows_container">
            <div class="js_filter_form_row row hide" style="margin-bottom: 10px;">
                <div class="col-xs-4">
                    <select class="form-control input-sm" data-name="field_id">
                        <option></option>
                        <?php //debug($field); ?>
                        <?php foreach($form['forms_fields'] as $k=>$field): ?>
                        <option value="<?php echo $field['id'] ?>">
                            <?php echo $field['label']; ?>
                        </option>
                        <?php endforeach; ?>
                        <?php /* 
                        <?php foreach($form['forms_fields'] as $field): ?>
                        <option value="<?php echo $field['forms_fields_fields_id'] ?>">
                            <?php echo ($field['fields_draw_label']? $field['fields_draw_label']: $field['fields_name']); ?>
                        </option>
                        <?php endforeach; ?>
                         * 
                         */ ?>
                    </select>
                </div>
                <div class="col-xs-4">
                    <select class="form-control input-sm" data-name="operator">
                        <option></option>
                        <?php foreach(unserialize(OPERATORS) as $operator_key => $operator_data): ?>
                            <option value="<?php echo $operator_key; ?>"><?php echo $operator_data['html']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-4">
                    <input type="text" class="form-control input-sm" data-name="value" placeholder="Matching value" />
                </div>
            </div>
            
            
            <?php if(isset($sess_data[$form['forms']['forms_filter_session_key']])): ?>
                <?php foreach($sess_data[$form['forms']['forms_filter_session_key']] as $k=>$condition): ?>
                    <div class="js_filter_form_row row" style="margin-bottom: 10px;">
                        <div class="col-xs-4">
                            <select class="form-control input-sm" name="conditions[<?php echo $k; ?>][field_id]">
                                <option></option>
                                 <?php foreach($form['forms_fields'] as $k=>$field): ?>
                                    <option value="<?php echo $field['id'] ?>" <?php if($condition['field_id']==$field['id']) echo "selected"; ?>>
                                        <?php echo $field['label']; ?>
                                    </option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <select class="form-control input-sm" name="conditions[<?php echo $k; ?>][operator]">
                                <option></option>
                                <?php foreach(unserialize(OPERATORS) as $operator_key => $operator_data): ?>
                                    <option value="<?php echo $operator_key; ?>" <?php if($condition['operator']==$operator_key) echo "selected"; ?>><?php echo $operator_data['html']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control input-sm" name="conditions[<?php echo $k; ?>][value]" placeholder="Matching value" value="<?php echo $condition['value']; ?>" />
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            
        </div>
        
        
        <button type="button" class="btn btn-link btn-sm js_filter_form_add_row">
            <span class="fas fa-plus"></span> Add condition
        </button>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <div class="pull-right">
                <?php if ($where_data): ?><input type="submit" class="btn red-intense" name="clear-filters" value="Svuota filtri"><?php endif; ?>
                <button type="submit" class="btn blue"><?php echo $where_data? 'Filtra': 'Aggiorna filtri'; ?></button>
            </div>
        </div>
    </div>
</form>