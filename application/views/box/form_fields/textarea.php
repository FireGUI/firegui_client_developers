<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <textarea name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" rows="3" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?>><?php echo $value; ?></textarea>
    <?php echo $help; ?>
</div>
