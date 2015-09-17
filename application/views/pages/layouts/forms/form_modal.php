<?php
$form_id = "form_{$form['forms']['forms_id']}";
$edit = isset($form['forms']['edit_data']['data'])? array_shift($form['forms']['edit_data']['data']): [];
?>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo ucwords(str_replace('_', ' ', $form['forms']['forms_name'])); ?></h4>
            </div>
            <div class="modal-body">
                <form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
                    <div class="form-body">
                        <?php foreach ($form['forms_fields'] as $k => $field): ?>
                            <div class="col-md-12">
                                <?php
                                if(isset($data[$field['fields_name']]) && $data[$field['fields_name']]) {
                                    //Potrebbero esserci dati provenienti dal post
                                    $value = $data[$field['fields_name']];
                                } else {
                                    //Prosegui con la procedura standard
                                    $value = isset($edit[$field['fields_name']]) ? $edit[$field['fields_name']] : $this->input->get($field['fields_name']);
                                }
                                echo $this->datab->build_form_input($field, $value);
                                ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#form_<?php echo $form['forms']['forms_id']; ?>').submit();">Salva</button>
            </div>
        </div>
    </div>
</div>