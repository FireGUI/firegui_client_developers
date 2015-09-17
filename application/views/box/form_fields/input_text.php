<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <input type="text" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $field['fields_draw_placeholder'] ?>" value="<?php echo $value; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
    <?php if (!empty($field['support_data'])): ?>
        <span class="help-block">
            <strong>Valori accettati:</strong>
            <ul>
                <?php foreach ($field['support_data'] as $value): ?>
                    <li>
                        <strong><?php echo $value[$field['fields_name'] . "_id"]; ?></strong>
                        <?php foreach ($field['support_fields'] as $support_field): ?>
                            <?php echo $value[$support_field['fields_name']]; ?>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </span>
    <?php endif; ?>
    <?php echo $help; ?>
</div>