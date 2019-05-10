<?php echo $label; ?>
<div class="row">
    <div class="col-md-6">
        <div class="input-group color js_form_colorpicker <?php echo $class ?>" data-color="<?php echo $value?:'#000000'; ?>" data-color-format="rgba">
            <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" value="<?php echo $value?:'#000000'; ?>" readonly <?php echo $onclick; ?> >
            <span class="input-group-btn">
                <button class="btn default" type="button"><i></i>&nbsp;</button>
            </span>
        </div>
        <?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>
