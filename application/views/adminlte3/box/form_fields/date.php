<?php
if (!empty($value) && ($timestamp = strtotime(normalize_date($value)))) {

    // La data potrebbe essere già in formato dd/mm/yyyy
    if (count(explode('/', $value)) !== 3) {
        $value = date('d/m/Y', $timestamp);
    }
}

if (empty($value) && $field['fields_required'] == DB_BOOL_TRUE) {
    $value = date('d/m/Y');
}

?>
<?php echo $label; ?>
<div class="row">
    <div class="col-md-12">
        <div class="input-group js_form_datepicker date <?php echo $class ?>">
            <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" <?php echo $onclick; ?> value="<?php echo $value; ?>" />
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><i class="fas fa-calendar-alt"></i></button>
            </span>
        </div>
        <?php echo $help; ?>
    </div>
</div>

<div class="clearfix"></div>