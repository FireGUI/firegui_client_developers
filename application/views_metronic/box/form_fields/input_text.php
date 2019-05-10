<?php echo $label; ?>
<input type="text" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" value="<?php echo $value; ?>" <?php echo $onclick; ?> />
<?php if (!empty($field['support_data'])): ?>
    <div class="help-block">
        <strong>Valori accettati:</strong>
        <ul>
            <?php foreach ($field['support_data'] as $id => $name): ?>
                <li><strong><?php echo $id; ?></strong> <?php echo $name; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php echo $help; ?>
