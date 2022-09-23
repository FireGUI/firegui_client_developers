<?php
if (!empty($value) && ($timestamp = strtotime(normalize_date($value)))) {

    // La data potrebbe essere già in formato dd/mm/yyyy hh:mm
    if (count(explode('/', $value)) !== 3) {
        $value = date('d/m/Y H:i', $timestamp);
    }
}

if (empty($value) && $field['fields_required'] != FIELD_NOT_REQUIRED) {
    // $value = date('d/m/Y H:i');
    $value = null; // michael e. - 2022-06-01 - Ho disattivato il value perchè secondo me non è giusto che venga per forza settato il valore... Il campo è required, perciò se si prova a salvare vuoto, darà comunque errore!

}
?>
<?php echo $label; ?>
<div class="row">
    <div class="col-md-12">
        <div class="input-group js_form_datetimepicker date <?php echo $class ?>">
            <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" value="<?php echo $value; ?>" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
            <span class="input-group-btn">
                <button class="btn btn-default date-set" type="button">
                    <i class="far fa-calendar"></i>
                    &nbsp;
                    <i class="far fa-clock"></i>
                </button>
            </span>
        </div>
        <?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>