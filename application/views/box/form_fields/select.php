<?php echo $label; ?>
<select class="form-control select2me <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $value; ?>" <?php echo $onclick; ?>>
    <?php if (!$field['fields_source']): ?>
        <?php if ($field['fields_required'] == 'f'): ?>
            <option></option>
        <?php endif; ?>
        <?php if (isset($field['support_data'])): ?>
            <?php foreach ((array) $field['support_data'] as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
</select>
<?php echo $help; ?>
