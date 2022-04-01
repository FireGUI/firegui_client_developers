<?php echo $label; ?>

<?php if ($subform) : ?>
    <button <?php echo sprintf('id="%s"', ($randId = uniqid('form-opener'))); ?> type="button" class="btn-xs btn-link js-form-opener"><?php e('Create new') ?></button>
    <script>
        $(function() {
            'use strict';
            var params = <?php echo json_encode(compact('randId', 'field', 'subform')); ?>;
            // ==============
            var button = $('#' + params.randId);
            var thisField = button.parent().find('[name="' + params.field.fields_name + '"]');
            var subform = params.subform;

            button.on('click', function() {
                openCreationForm(subform, thisField.data('ref'), function(id, name) {
                    thisField.append($('<option/>').attr('value', id).text(name));
                    thisField.val(id).trigger("change");

                });
            });
        });
    </script>
<?php endif; ?>

<select class="js_select_ajax_new form-control <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" data-required="<?php echo $field['fields_required'] ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $value; ?>" <?php echo $attr; ?>>
    <?php if (isset($field['support_data'])) : ?>
        <?php foreach ((array) $field['support_data'] as $id => $name) : ?>
            <?php if ($id != $value) {
                continue;
            } ?>
            <option value="<?php echo $id; ?>" selected><?php echo $name; ?></option>
        <?php endforeach; ?>
    <?php elseif ($value) : ?>
        <option value="<?php echo $value; ?>" selected="selected"><?php echo $value_preview; ?></option>
    <?php endif; ?>

</select>

<?php echo $help; ?>