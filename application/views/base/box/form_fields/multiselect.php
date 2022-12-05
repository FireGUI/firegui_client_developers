<?php
// Convert the value to array
if (!is_array($value) && $value) {
    // Se abbiamo esploso il valore allora facciamo una piccola correzione
    $_v = explode(',', $value);
    $value = array_combine($_v, $_v);
}

$value or $value = [];
$preselected_values = $value;

// Extract options, only if the source is not set
$options = $field['fields_source'] ? [] : array_get($field, 'support_data', []);
//debug($field);
//debug($value);

?>
<?php echo $label; ?>
<select multiple class="form-control js_multiselect <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>[]" data-val="<?php echo implode(',', array_keys($value)); ?>" data-ref="<?php echo $field['fields_ref']; ?>" data-source-field="<?php echo $field['fields_source']; ?>" <?php echo $field['fields_source'] ? '' : 'data-minimum-input-length="0"'; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" >

    <?php foreach ($options as $id => $name): ?>
        <?php //debug('foo');?>
        <option value="<?php echo $id; ?>" <?php echo isset($value[$id]) ? 'selected' : ''; ?> <?php echo $onclick; ?>><?php echo $name; ?></option>
    <?php endforeach;?>
</select>
<?php echo $help; ?>