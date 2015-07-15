<?php 

if ($value && $field['fields_type'] === 'DATERANGE') {
    $dates = dateRange_to_dates($value);
    $value = dateFormat($dates[0]) . ' - ' . dateFormat($dates[1]);
}

?>



<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="row">
        <div class="col-md-12">
            <div class="input-group js_form_daterangepicker field_<?php echo $field['fields_id']; ?>">
                <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> value="<?php echo $value; ?>" />
                <span class="input-group-btn">
                    <button class="btn default" type="button">
                        <i class="icon-calendar"></i>
                        &nbsp;
                        <i class="icon-calendar-empty"></i>
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