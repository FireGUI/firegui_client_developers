<?php echo $label; ?>
<div class="row">
    <div class="col-md-6">
        <div class="input-group <?php echo $class ?>">
            <input name="<?php echo $field['fields_name']; ?>" type="color" class="form-control" value="<?php echo $value?:'#FF0000'; ?>" readonly <?php echo $onclick; ?> style="min-width:95px">
        </div>
        <?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>
