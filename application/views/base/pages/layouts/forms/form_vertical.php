<?php 
$form_id = "form_{$form['forms']['forms_id']}"; 

?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?>" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <?php add_csrf(); ?>
    <!-- FORM HIDDEN DATA -->
    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>


    <div class="form-body">
        <?php foreach ($form['forms_fields'] as $field) : ?>
            <div class="row">
                <div class="<?php echo sprintf('col-md-%d', $field['size'] ?: 12); ?>"><?php echo $field['html']; ?></div>
            </div>
        <?php endforeach; ?>

        <div class="row">
            <div class="col-md-12">
                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
            </div>
        </div>
    </div>

    <div class="form-actions">

        <!-- <button type="button" class="btn btn-default" data-dismiss="modal"><?php e('Cancel'); ?></button> -->
        <button type="submit" class="btn btn-primary">
                <?php if ($value_id) :?>
                    <?php if (array_key_exists('forms_label_edit', $form['forms'])&& !empty($form['forms']['forms_label'])) : ?>
                           <?php echo $form['forms']['forms_label_edit']; ?>
                    <?php else : ?>
                        
                        <?php echo (array_key_exists('forms_label', $form['forms']) && !empty($form['forms']['forms_label'])) ? $form['forms']['forms_label'] : t('Save'); ?>
                    <?php endif; ?>
                <?php else :?>
                        <?php echo (array_key_exists('forms_label', $form['forms']) && !empty($form['forms']['forms_label'])) ? $form['forms']['forms_label'] : t('Save'); ?>
                <?php endif; ?>
                        
               
            </button>
    </div>
</form>