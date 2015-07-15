<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo ucwords(str_replace('_', ' ', $form['forms']['forms_name'])); ?></h4>
            </div>
            <div class="modal-body">
                <form <?php echo "id='form_{$form['forms']['forms_id']}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
                    <div class="form-body">
                        <?php foreach ($form['forms_fields'] as $k => $field): ?>
                            <?php
                            if(isset($data[$field['fields_name']]) && $data[$field['fields_name']]) {
                                //Potrebbero esserci dati provenienti dal post
                                $value = $data[$field['fields_name']];
                            } else {
                                //Prosegui con la procedura standard
                                $value = (isset($form['forms']['edit_data']['data'][0][$field['fields_name']])) ? $form['forms']['edit_data']['data'][0][$field['fields_name']] : $this->input->get($field['fields_name']);
                                $value = (($value != '' && $value != null)? $value : ($field['forms_fields_default_value'] ? $this->datab->get_default_fields_value($field) : null));
                            }
                            ?>
                            <div class="col-md-12">
                                <?php $this->load->view("box/form_fields/{$field['fields_draw_html_type']}", array('field' => $field, 'value' => $value)); ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div <?php echo "id='msg_form_{$form['forms']['forms_id']}'"; ?> class="alert alert-danger hide"></div>
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