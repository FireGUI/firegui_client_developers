<?php echo $label; ?>

<?php if ($subform): ?>

    <button <?php echo sprintf('id="%s"', ($randId = uniqid('form-opener'))); ?> type="button"
        class="btn-xs btn-link js-form-opener">
        <?php e('Create new') ?>
    </button>
    <script>
        $(function () {
            'use strict';
            var params = <?php echo json_encode(compact('randId', 'field', 'subform')); ?>;
            // ==============
            var button = $('#' + params.randId);
            var thisField = button.parent().find('[name="' + params.field.fields_name + '"]');
            var subform = params.subform;

            button.on('click', function () {
                openCreationForm(subform, thisField.data('ref'), function (id, name) {
                    thisField.append($('<option/>').attr('value', id).text(name));
                    thisField.val(id).trigger("change");
                });
            });
        });
    </script>
<?php endif; ?>

<select class="form-control select2_standard <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>"
    data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>"
    data-val="<?php echo $value; ?>" <?php echo $onclick; ?> <?php echo $attr; ?>
    data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>">
    <?php if (!$field['fields_source']): ?>
        <?php //if ($field['fields_required'] == FIELD_NOT_REQUIRED): ?>
        <option value=""> --- </option>
        <?php //endif; ?>
        <?php if (isset($field['support_data'])): ?>
            <?php foreach ((array) $field['support_data'] as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'selected' : ''; ?><?php if (!empty($field['forms_field_full_data']) && $field['forms_field_full_data'] == DB_BOOL_TRUE): ?>
                        data-full_data="<?php echo base64_encode(json_encode($this->apilib->view($field['fields_ref'], $id))); ?>" <?php endif; ?>>
                    <?php echo $name; ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</select>
<?php echo $help; ?>