<?php
$form_id = "form_{$form['forms']['forms_id']}";
$bulk_mode = (is_array($value_id));

?>

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

$fieldsets = ['__main_fields' => []];
foreach ($form['forms_fields'] as $key => $field) {
    
    if ($field['fieldset'] && $field['required'] != DB_BOOL_TRUE) {
        if (empty($fieldsets[$field['fieldset']])) {
            $fieldsets[$field['fieldset']] = [$field];
        } else {
            $fieldsets[$field['fieldset']][] = $field;
        }
    } else {
        $fieldsets['__main_fields'][] = $field;
    }
    unset($form['forms_fields'][$key]);
}

?>

<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog <?php echo $sizeClass; ?>">
        <div class="modal-content">
            <div class="modal-header">


                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>

                <!-- Builder actions -->
                <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="builder" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form builder">
                    <i class="fas fa-hat-wizard"></i>
                </button>
                <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_builder_toolbar_btn hide" data-action="option" data-element-type="form" data-element-ref="<?php echo $form['forms']['forms_id']; ?>" data-toggle="tooltip" title="" data-original-title="Form option">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" style="float:right" class="btn btn-box-tool builder_toolbar_actions js_init_sortableform hide">
                    <i class="fas fa-arrows-alt"></i>
                </button>
                <!-- End Builder actions -->


                <h4 class="modal-title" id="myModalLabel"><?php echo t(ucwords(str_replace('_', ' ', $form['forms']['forms_name']))); ?></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid" data-form_id="<?php echo $form['forms']['forms_id']; ?>">
                    <div class="row">
                        <div class="col-xs-12">

                            <form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="form formAjax <?php echo ($form['forms']['forms_css_extra']) ?? null; ?>" enctype="multipart/form-data" <?php if (!is_array($value_id)) : ?>data-edit-id="<?php echo $value_id; ?>" <?php else : ?>data-edit-id="" <?php endif; ?>>
                                <?php add_csrf(); ?>
                                <?php if ($bulk_mode) : ?>
                                    <?php foreach ($value_id as $val) : ?>
                                        <input type="hidden" name="value_id[]" value="<?php echo $val; ?>" />
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- FORM HIDDEN DATA -->
                                <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>

                                <div class="form-body">
                                <?php foreach ($fieldsets as $field_set_title => $fields) : ?>
                                            <?php if ($field_set_title != '__main_fields') : ?>
                                                <fieldset class="js_form_fieldset">
                                                    <legend><span><?php e('Show'); ?></span><span style="display:none;"><?php e('Hide'); ?></span> <?php echo $field_set_title; ?> <i class="fa fa-arrow-right"></i></legend>
                                            <?php endif; ?>
                                    <div class="row sortableForm">

                                        
                                            <?php foreach ($fields as $field) : ?>

                                                <?php if ($bulk_mode) : ?>

                                                    <div class="js_field_container">
                                                        <div class="col-lg-3">
                                                            <label>Edit this:</label>
                                                            <input type="checkbox" class="_form-control js_field_check" name="edit_fields[]" value="<?php echo $field['name']; ?>" />
                                                        </div>
                                                        <div class="col-lg-9">

                                                        <?php else : ?>
                                                            <div id="<?php echo $field['id']; ?>" data-form_id="<?php echo $form['forms']['forms_id']; ?>" class=" formColumn js_container_field <?php echo sprintf('col-md-%d', $field['size'] ?: 12); ?>" data-id="<?php echo $field['id']; ?>" data-cols="<?php echo $field['size']; ?>">

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

                                                            <?php endif; ?>

                                                            <?php echo $field['html']; ?>

                                                            </div>
                                                            <?php if ($bulk_mode) : ?>
                                                    </div><?php endif; ?>
                                            <?php endforeach; ?>
                                            
                                                </div>
                                                <?php if ($field_set_title != '__main_fields') : ?>
                                                </fieldset>    
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
                                                    </div>
                                                </div>

                                                <div class="form-actions">
                                                    <?php if ($show_delete_button) : ?>
                                                        <div class="pull-left">
                                                            <a href="<?php echo base_url("db_ajax/generic_delete/{$form['forms']['entity_name']}/$value_id"); ?>" data-confirm-text="<?php e('Are you sure to delete this record?'); ?>" class="btn btn-danger js_confirm_button js_link_ajax " data-toggle="tooltip" title="" data-original-title="Elimina">
                                                                <?php e('Delete'); ?>
                                                            </a>

                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="pull-right">
                                                        <!-- <button type="button" class="btn btn-default " data-dismiss="modal"><?php e('Cancel'); ?></button> -->
                                                        <button type="submit" class="btn btn-primary"><?php echo (array_key_exists('forms_submit_button_label', $form['forms']) && !empty($form['forms']['forms_submit_button_label'])) ? $form['forms']['forms_submit_button_label'] : t('Save'); ?></button>
                                                    </div>
                                                </div>
                                    </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($bulk_mode) : ?>
        <script>
            $(document).ready(function() {
                'use strict';
                $(':input:not(.js_field_check,button,[type="hidden"])', $('#<?php echo $form_id; ?>')).attr('disabled', 'disabled');
                $('.js_field_check').on('click', function() {
                    if ($(this).is(':checked')) {
                        $(':input:not(.js_field_check)', $(this).closest('.js_field_container')).removeAttr('disabled');
                    } else {
                        $(':input:not(.js_field_check,button,[type="hidden"])', $(this).closest('.js_field_container')).attr('disabled', 'disabled');
                    }

                });
            });
        </script>
    <?php endif; ?>