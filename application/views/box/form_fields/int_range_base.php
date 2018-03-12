<?php echo $label; ?>
<input type="hidden" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" value="<?php echo $value['range']; ?>" />
<div class="clearfix">
    <div class="input-intrange-wrapper">
        Da: <input type="number" step="1" id="int_range_<?php echo $field['fields_id']; ?>_from" class="form-control input-sm <?php echo $class ?>" value="<?php echo $value['from']; ?>" <?php echo $onclick; ?> />
    </div>
    <div class="input-intrange-wrapper">
        A: <input type="number" step="1" id="int_range_<?php echo $field['fields_id']; ?>_to" class="form-control input-sm <?php echo $class ?>" value="<?php echo $value['to']; ?>" <?php echo $onclick; ?> />
    </div>
</div>
<?php echo $help; ?>

<script>
    $(document).ready(function () {
        var range_from = $('#int_range_<?php echo $field['fields_id']; ?>_from');
        var range_to = $('#int_range_<?php echo $field['fields_id']; ?>_to');
        
        $(range_from).add(range_to).change(function () {
            $('[name=<?php echo $field['fields_name']; ?>]').val('['+range_from.val()+','+range_to.val()+']');
        });
        
    });
</script>
