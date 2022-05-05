<?php $form_id = "form_{$form['forms']['forms_id']}"; ?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?>" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <?php add_csrf(); ?>
    <!-- FORM HIDDEN DATA -->
    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>
    <?php $count = count($form['forms_fields']);
    $half = number_format($count / 2); ?>

    <div class="form-body">
        <div class="row">
            <div class="col-md-6 form-vertical2col">
                <?php foreach (array_slice($form['forms_fields'], 0, $half) as $field) : ?>
                    <div class="row">
                        <div class="<?php echo sprintf('col-md-%d', $field['size'] ?: 12); ?>"><?php echo $field['html']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <?php foreach (array_slice($form['forms_fields'], $half, $count) as $field) : ?>
                    <div class="row">
                        <div class="<?php echo sprintf('col-lmd-%d', $field['size'] ?: 12); ?>"><?php echo $field['html']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="clearfix"></div>



        <div class="row">
            <div class="col-md-12">
                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
            </div>
        </div>
    </div>

    <div class="form-actions pull-right">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal"><?php e('Cancel'); ?></button> -->
        <button type="submit" class="btn btn-primary"><?php echo (array_key_exists('forms_submit_button_label', $form['forms']) && !empty($form['forms']['forms_submit_button_label'])) ? $form['forms']['forms_submit_button_label'] : t('Save'); ?></button>
    </div>
</form>