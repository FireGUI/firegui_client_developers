<?php

if(!empty($value) && ($timestamp=strtotime(normalize_date($value)))) {
    
    // La data potrebbe essere già in formato dd/mm/yyyy hh:mm
    if(count(explode('/', $value)) !== 3) {
        $value = date('d/m/Y H:i', $timestamp);
    }
}
?>

<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="row">
        <div class="col-md-12">
            <div class="input-group js_form_datetimepicker date field_<?php echo $field['fields_id']; ?>">
                <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" value="<?php echo $value; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                <span class="input-group-btn">
                    <button class="btn default date-set" type="button">
                        <i class="icon-calendar-empty"></i>
                        &nbsp;
                        <i class="icon-time"></i>
                    </button>
                </span>
            </div>
            <?php if($field['fields_draw_help_text']): ?>
                <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
