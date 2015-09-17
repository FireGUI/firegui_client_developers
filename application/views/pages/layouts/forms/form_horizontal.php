<?php
$form_id = "form_{$form['forms']['forms_id']}";
$edit = isset($form['forms']['edit_data']['data'])? array_shift($form['forms']['edit_data']['data']): [];
?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <div class="form-body">
        <?php $k=0; foreach ($form['forms_fields'] as $field): ?>
            <?php $view = $this->datab->build_form_input($field, isset($edit[$field['fields_name']]) ? $edit[$field['fields_name']] : $this->input->get($field['fields_name']));
            if($field['fields_draw_html_type'] == 'input_hidden') {
                echo $view;
            } elseif($field['fields_draw_html_type'] == 'wysiwyg') {
                echo '<div class="clearfix"></div><div class="col-md-12">'.$view.'</div><div class="clearfix"></div>';
                $k = 0;
            } else {
                echo '<div class="col-md-6">'.$view.'</div>';
                $k++;
            } ?>
            <?php if( ! ($k%2)): ?>
                <div class="clearfix"></div>
            <?php endif; ?>
            
        <?php endforeach; ?>

        <div class="clearfix"></div>
        <div class="col-md-12">
            <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
        </div>
    </div>

    <div class="form-actions fluid">
        <div class="col-md-12">
            <button type="submit" class="btn blue">Salva</button>
            <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>