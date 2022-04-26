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

<select class="form-control select2_standard <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $value; ?>" <?php echo $onclick; ?> <?php echo $attr; ?>>
    <?php if (!$field['fields_source']) : ?>
        <?php if ($field['fields_required'] == DB_BOOL_FALSE) : ?>
            <option></option>
        <?php endif; ?>
        <?php if (isset($field['support_data'])) : ?>
            <?php foreach ((array) $field['support_data'] as $id => $name) : ?>
                <option value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</select>
<?php echo $help; ?>