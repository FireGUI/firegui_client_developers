<?php $form_id = "form_{$form['forms']['forms_id']}"; ?>

<?php
$sizeClass = '';
if (isset($size)) {
    switch ($size) {
        case 'small':
            $sizeClass = 'modal-sm';
            break;
        case 'large':
            $sizeClass = 'modal-lg';
            break;
        case 'extra':
            $sizeClass = 'modal-xl';
            break;
    }
}

$show_delete_button = ($form['forms']['forms_show_delete'] == DB_BOOL_TRUE && $value_id);
?>

<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <?php //debug($form); ?>
    <div class="modal-dialog <?php echo $sizeClass; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo ucwords(str_replace('_', ' ', $form['forms']['forms_name'])); ?></h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-xs-12">

                        <form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="form formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">

                            <!-- FORM HIDDEN DATA -->
                            <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>

                            <div class="form-body">
                                <div class="row">
                                <?php foreach ($form['forms_fields'] as $field): ?>
                                    <div class="<?php echo sprintf('col-lg-%d', $field['size'] ?: 12); ?>"><?php echo $field['html']; ?></div>
                                <?php endforeach; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <?php if ($show_delete_button) : ?>
                                    <div class="pull-left">
                                        <a href="<?php echo base_url("db_ajax/generic_delete/{$form['forms']['entity_name']}/$value_id"); ?>" data-confirm-text="are you sure you want to delete this record?" class="btn btn-danger js_confirm_button js_link_ajax " data-toggle="tooltip" title="" data-original-title="Elimina">
                                            <?php e('Delete'); ?>
                                        </a>
                                        
                                    </div>
                                    <?php endif; ?>
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-default " data-dismiss="modal"><?php e('Annulla'); ?></button>
                                        <button type="submit" class="btn btn-primary"><?php e('Salva'); ?></button>
                                    </div>
                                </div>
                            </div>

                        </form> 
                    </div>

                </div>
            </div>
        </div>
    </div>
