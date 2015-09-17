<?php
$form_id = "form_{$form['forms']['forms_id']}";
$edit = isset($form['forms']['edit_data']['data'])? array_shift($form['forms']['edit_data']['data']): [];
?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <?php $count = count($form['forms_fields']); $half = number_format($count/2); ?>
    <div class="form-body">
        <div class="row">
            <div class="col-md-6" style="border-right: 1px solid #e4e4e4;">
                <?php
                for($i=0; $i<$half; $i++) {
                    $field = $form['forms_fields'][$i];
                    echo $this->datab->build_form_input($field, isset($edit[$field['fields_name']]) ? $edit[$field['fields_name']] : $this->input->get($field['fields_name'])); 
                }
                ?>
            </div>
            <div class="col-md-6">
                <?php
                for($i=$half; $i<$count; $i++) {
                    $field = $form['forms_fields'][$i];
                    echo $this->datab->build_form_input($field, isset($edit[$field['fields_name']]) ? $edit[$field['fields_name']] : $this->input->get($field['fields_name'])); 
                }
                ?>
            </div>
        </div>
        <div class="clearfix"></div>
        
        
        
        <div class="row">
            <div class="col-md-12">
                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
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