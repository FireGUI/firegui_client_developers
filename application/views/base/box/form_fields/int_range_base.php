<?php echo $label; ?>
<input type="hidden" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" value="<?php echo $value['range']; ?>" />
<div class="clearfix">
    <div class="input-intrange-wrapper">
        Min: <input type="number" step="1"  class="form-control input-sm <?php echo $class ?>" value="<?php echo $value['from']; ?> js_int_range_<?php echo $field['fields_id']; ?>_from" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
    </div>
    <div class="input-intrange-wrapper">
        Max: <input type="number" step="1"  class="form-control input-sm <?php echo $class ?> js_int_range_<?php echo $field['fields_id']; ?>_to" value="<?php echo $value['to']; ?>" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
    </div>
</div>
<?php echo $help; ?>

<script>
    $(document).ready(function () {
        var range_from = $('.js_int_range_<?php echo $field['fields_id']; ?>_from');
        var range_to = $('.js_int_range_<?php echo $field['fields_id']; ?>_to');
        
        $(range_from).add(range_to).change(function () {
            $('[name=<?php echo $field['fields_name']; ?>]').val('['+range_from.val()+','+range_to.val()+']');
        });
        
    });
</script>
