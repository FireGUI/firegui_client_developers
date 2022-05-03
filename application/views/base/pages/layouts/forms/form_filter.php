<?php
$sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];

// Recupera info filtri e indicizza i dati per field_id
$_sess_where_data = array_get($sess_data, $form['forms']['forms_filter_session_key'], []);
$where_data = array_combine(array_key_map($_sess_where_data, 'field_id'), $_sess_where_data);
?>


<form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo base_url("db_ajax/save_session_filter/{$form['forms']['forms_id']}"); ?>" class="formAjax js_filter_form <?php echo ($form['forms']['forms_css_extra']) ?? null; ?>" enctype="multipart/form-data">

    <?php add_csrf(); ?>
    <div class="form-body">
        <div class="col-xs-12">
            <p><?php e('Add conditions to filter the table'); ?></p>
        </div>

        <div class="js_filter_form_rows_container">
            <div class="js_filter_form_row row hide">
                <div class="col-xs-4">
                    <select class="form-control input-sm" data-name="field_id">
                        <option></option>

                        <?php foreach ($form['forms_fields'] as $k => $field) : ?>
                            <option value="<?php echo $field['id'] ?>">
                                <?php echo $field['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-4">
                    <select class="form-control input-sm" data-name="operator">
                        <option></option>
                        <?php foreach (unserialize(OPERATORS) as $operator_key => $operator_data) : ?>
                            <option value="<?php echo $operator_key; ?>"><?php echo $operator_data['html']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-4">
                    <input type="text" class="form-control input-sm" data-name="value" placeholder="<?php e('Matching value'); ?>" />
                </div>
            </div>


            <?php if (isset($sess_data[$form['forms']['forms_filter_session_key']])) : ?>
                <?php foreach ($sess_data[$form['forms']['forms_filter_session_key']] as $k => $condition) : ?>
                    <div class="js_filter_form_row row">
                        <div class="col-xs-4">
                            <select class="form-control input-sm" name="conditions[<?php echo $k; ?>][field_id]">
                                <option></option>
                                <?php foreach ($form['forms_fields'] as $k => $field) : ?>
                                    <option value="<?php echo $field['id'] ?>" <?php if ($condition['field_id'] == $field['id']) echo "selected"; ?>>
                                        <?php echo $field['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <select class="form-control input-sm" name="conditions[<?php echo $k; ?>][operator]">
                                <option></option>
                                <?php foreach (unserialize(OPERATORS) as $operator_key => $operator_data) : ?>
                                    <option value="<?php echo $operator_key; ?>" <?php if ($condition['operator'] == $operator_key) echo "selected"; ?>><?php echo $operator_data['html']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control input-sm" name="conditions[<?php echo $k; ?>][value]" placeholder="<?php e('Matching value'); ?>" value="<?php echo $condition['value']; ?>" />
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>


        </div>


        <button type="button" class="btn btn-link btn-sm js_filter_form_add_row">
            <span class="fas fa-plus"></span> <?php e('Add condition'); ?>
        </button>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <div class="pull-right">
                <?php if ($where_data) : ?><input type="submit" class="btn red-intense" name="clear-filters" value="<?php e('Clear filters'); ?>"><?php endif; ?>
                <button type="submit" class="btn btn-primary"><?php echo $where_data ? t('Filter') : t('Update filters'); ?></button>
            </div>
        </div>
    </div>
</form>