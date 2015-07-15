<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t' && !$value): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="input-group">
        <input type="password" name="<?php echo $field['fields_name']; ?>" class="form-control field_<?php echo $field['fields_id']; ?>" placeholder="<?php echo $field['fields_draw_placeholder']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
        <span class="input-group-addon">
            <i class="icon-lock"></i>
        </span>
    </div>
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php elseif($value): ?>
        <span class="help-block">Non compilare se non vuoi modificare la password</span>
    <?php endif; ?>
 </div>
