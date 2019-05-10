<?php
// Convert the value to array
if (!is_array($value)) {
    $value = in_array($field['fields_type'], ['VARCHAR', 'TEXT']) ? explode(',', $value) : [];
}

$preselected_values = $value;
?>
<?php echo $label; ?>
<select class="form-control js_multiselect <?php echo $class ?>" multiple name="<?php echo $field['fields_name']; ?>[]" <?php if (!$field['fields_source'] && count($field['support_data']) > 200) echo 'data-minimum-input-length="3"'; ?> data-source-field="<?php echo $field['fields_source']; ?>" data-ref="<?php echo $field['fields_ref']; ?>">
    <?php if (!$field['fields_source']): ?>
        <?php foreach ($field['support_data'] as $id => $name): ?>
            <option value="<?php echo $id; ?>" <?php echo isset($value[$id]) ? 'selected' : ''; ?> <?php echo $onclick; ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
<?php echo $help; ?>
