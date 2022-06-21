<?php echo $label; ?>
<div class="row">
    <div class="col-md-6">
        <div class="input-group <?php echo $class ?>">
            <input name="<?php echo $field['fields_name']; ?>" type="color" class="form-control firegui_colorinput <?php echo ($class ?? null) ?>" value="<?php echo $value ?: '#FF0000'; ?>" readonly <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>">
        </div>
        <?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>