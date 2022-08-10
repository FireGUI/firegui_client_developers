<?php echo $label; ?>
<input type="text" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" value="<?php echo $value; ?>" <?php echo $onclick; ?> autocomplete="off" <?php echo $attr; ?> data-dependent_on="<?php echo (!empty($field['forms_fields_dependent_on'])) ? $field['forms_fields_dependent_on'] : null; ?>" />
<?php if (!empty($field['support_data'])) : ?>
    <div class="help-block">
        <strong><?php e('Accepted values:') ?></strong>
        <ul>
            <?php foreach ($field['support_data'] as $id => $name) : ?>
                <li><strong><?php echo $id; ?></strong> <?php echo $name; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php echo $help; ?>