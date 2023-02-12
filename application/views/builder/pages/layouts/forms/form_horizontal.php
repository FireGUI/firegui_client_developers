<?php
$form_id = "form_{$form['forms']['forms_id']}";

?>

<!-- Builder actions -->
<button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="builder" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form builder">
    <i class="fas fa-hat-wizard"></i>
</button>
<button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="option" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form option">
    <i class="fas fa-edit"></i>
</button>
<button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_init_sortableform hide" data-toggle="tooltip" title="" data-original-title="Start Drag Fields">
    <i class="fas fa-arrows-alt"></i>
</button>
<!-- End Builder actions -->



<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?>" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    <?php add_csrf(); ?>
    <!-- FORM HIDDEN DATA -->
    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>

    <div class="box-body">
        <div class="row sortableForm">
            <?php foreach ($form['forms_fields'] as $k => $field) : ?>

                <div data-form_id="<?php echo $form['forms']['forms_id']; ?>" class="formColumn js_container_field <?php echo sprintf('col-md-%d', $field['size']); ?>" data-id="<?php echo $field['id']; ?>" data-cols="<?php echo $field['size']; ?>">

                    <!-- Builder buttons -->
                    <div class="builder_formcolumns_buttons hide">
                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_minus" data-toggle="tooltip" data-original-title="- columns">
                            <i class="fas fa-caret-left"></i>
                        </a>
                        Size
                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_plus" data-toggle="tooltip" data-original-title="+ columns">
                            <i class="fas fa-caret-right"></i>
                        </a>

                        <a href="javascript:void(0);" class="btn btn-box-tool js_btn_fields_delete btn-space" data-toggle="tooltip" data-original-title="Remove field">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <!-- End Builder buttons -->

                    <?php echo $field['html']; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="row ">
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