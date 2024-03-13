<?php echo $label; ?>


<?php // SUBFORM                ?>
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


<?php // Big buttons                ?>


<input type="hidden"
    class="form-control js_badge_hidden_<?php echo $field['fields_id']; ?> <?php echo (!empty($class)) ? $class : ''; ?>"
    data-source-field="<?php echo (!empty($field['fields_source'])) ? $field['fields_source'] : '' ?>"
    name="<?php echo $field['fields_name']; ?>"
    data-ref="<?php echo (!empty($field['fields_ref'])) ? $field['fields_ref'] : '' ?>" value="<?php echo $value; ?>"
    <?php if ($field['forms_field_full_data'] == DB_BOOL_TRUE): ?>
        data-full_data="<?php echo base64_encode(json_encode($this->apilib->view($field['fields_ref'], $id))); ?>" <?php endif; ?> />

<?php if (!$field['fields_source']): ?>
    <div class="badge_form_field_container <?php echo $class ?>">
        <?php if (isset($field['support_data'])): ?>
            <?php foreach ((array) $field['support_data'] as $id => $name): ?>
                <span
                    class="badge badge_form_field js_badge_form_field <?php echo ($id == $value) ? 'badge_form_field_active' : ''; ?>"
                    data-hidden_field="js_badge_hidden_<?php echo $field['fields_id']; ?>" data-value_id="<?php echo $id; ?>">
                    <?php echo $name; ?>
                </span>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php // Text hellp               ?>
<?php echo $help; ?>