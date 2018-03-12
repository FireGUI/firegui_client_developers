<?php $form_id = "form_{$form['forms']['forms_id']}"; ?>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo ucwords(str_replace('_', ' ', $form['forms']['forms_name'])); ?></h4>
            </div>
            <div class="modal-body">
                <form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
                    
                    <!-- FORM HIDDEN DATA -->
                    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>
                    
                    <div class="form-body">
                        <?php foreach ($form['forms_fields'] as $field): ?>
                            <div class="row">
                                <div class="<?php echo sprintf('col-lg-%d', $field['size'] ? : 12); ?>"><?php echo $field['html']; ?></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
                            </div>
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