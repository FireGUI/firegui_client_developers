<form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <?php $count = count($form['forms_fields']); $half = number_format($count/2); ?>
    <div class="form-body">
        <div class="row">
            <div class="col-md-6" style="border-right: 1px solid #e4e4e4;">
                <?php
                for($i=0; $i<$half; $i++) {
                    $field = $form['forms_fields'][$i];
                    
                    $value = (isset($form['forms']['edit_data']['data'][0][$field['fields_name']])) ? $form['forms']['edit_data']['data'][0][$field['fields_name']] : $this->input->get($field['fields_name']);
                    $value = ($value != '' && $value != null)? $value : (($field['forms_fields_default_value']) ? $this->datab->get_default_fields_value($field) : null);
                    
                    $this->load->view("box/form_fields/{$field['fields_draw_html_type']}", array('field' => $field, 'value' => $value));
                }
                ?>
            </div>
            <div class="col-md-6">
                <?php
                for($i=$half; $i<$count; $i++) {
                    $field = $form['forms_fields'][$i];
                    
                    $value = (isset($form['forms']['edit_data']['data'][0][$field['fields_name']])) ? $form['forms']['edit_data']['data'][0][$field['fields_name']] : $this->input->get($field['fields_name']);
                    $value = ($value) ? $value : (($field['forms_fields_default_value']) ? $this->datab->get_default_fields_value($field) : null);
                    
                    $this->load->view("box/form_fields/{$field['fields_draw_html_type']}", array('field' => $field, 'value' => $value));
                }
                ?>
            </div>
        </div>
        <div class="clearfix"></div>
        
        
        
        <div class="row">
            <div class="col-md-12">
                <div id="msg_form_<?php echo $form['forms']['forms_id']; ?>" class="alert alert-danger hide"></div>
            </div>
        </div>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <button type="submit" class="btn blue">Salva</button>
            <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>