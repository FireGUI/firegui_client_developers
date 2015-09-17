<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <div class="input-group">
        <input type="password" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $field['fields_draw_placeholder']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
        <span class="input-group-addon">
            <i class="icon-lock"></i>
        </span>
    </div>
    <?php if($field['fields_draw_help_text']): ?>
        <?php echo $help; ?>
    <?php elseif($value): ?>
        <span class="help-block">Non compilare se non vuoi modificare la password</span>
    <?php endif; ?>
 </div>
