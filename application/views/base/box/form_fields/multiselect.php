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
if ($field['forms_field_full_data'] == DB_BOOL_TRUE) {
    $rel = $this->crmentity->getRelationByName($field['fields_ref']);
    if ($field['field_support_id'] == $rel['relations_field_1']) {
        $source_table = $rel['relations_table_1'];
    } else {
        $source_table = $rel['relations_table_2'];
    }
}
//debug($options,true);

?>
<?php echo $label; ?>
<select multiple class="form-control js_multiselect <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>[]"
    data-val="<?php echo implode(',', array_keys($value)); ?>" data-ref="<?php echo $field['fields_ref']; ?>"
    data-source-field="<?php echo $field['fields_source']; ?>" <?php echo $field['fields_source'] ? '' : 'data-minimum-input-length="0"'; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>">

    <?php foreach ($options as $id => $name): ?>

        <option value="<?php echo $id; ?>" <?php echo isset($value[$id]) ? 'selected' : ''; ?>     <?php echo $onclick; ?>    <?php if ($field['forms_field_full_data'] == DB_BOOL_TRUE): ?>
                data-full_data="<?php echo base64_encode(json_encode($this->apilib->view($source_table, $id))); ?>" <?php endif; ?>>
            <?php echo $name; ?>
        </option>
    <?php endforeach; ?>
</select>
<?php echo $help; ?>