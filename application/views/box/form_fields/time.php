<?php
if ($value) {
    $timeChunks = explode(':', $value);
    if (count($timeChunks) !== 2 OR ! is_numeric($timeChunks[0]) OR ! is_numeric($timeChunks[1])) {
        $value = null;
    }
}
?>
<?php echo $label; ?>
<div class="row">
    <div class="col-md-12">
        <div class="input-group <?php echo $class ?>" <?php echo $onclick; ?>>
            <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control js_form_timepicker" value="<?php echo $value; ?>" data-default-time="<?php echo $value; ?>" />
            <span class="input-group-btn">
                <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
            </span>
        </div>
<?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>
