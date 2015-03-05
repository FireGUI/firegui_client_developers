<?php 

if($value) {
    $timeChunks = explode(':', $value);
    if(count($timeChunks) !== 2 OR !is_numeric($timeChunks[0]) OR !is_numeric($timeChunks[1])) {
        $value = null;
    }
}


?>


<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if ($field['fields_required'] == 't'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="row">
        <div class="col-md-12">
            <div class="input-group field_<?php echo $field['fields_id']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?>>
                <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control js_form_timepicker" value="<?php echo $value; ?>" data-default-time="<?php echo $value; ?>" />
                <span class="input-group-btn">
                    <button class="btn default" type="button"><i class="icon-time"></i></button>
                </span>
            </div>
            <?php if($field['fields_draw_help_text']): ?>
                <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>