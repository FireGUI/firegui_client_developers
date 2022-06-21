<?php

if (is_array(json_decode(html_entity_decode($value), true))) {
    $values = json_decode(html_entity_decode($value), true);
} else {
    $values[] = array("key" => "", "value" => "");
}
$count = 1;
?>
<?php echo $label; ?>

<div class="js_multiple_container">
    <div class="js_multiple_row_container">

        <?php foreach ($values as $single_value) : ?>

            <div class="row js_multiple_key_values_row" style="margin-top:10px">
                <div class="col-xs-5">
                    <input type="text" name="<?php echo $field['fields_name']; ?>[<?php echo $count; ?>][key]" data-name="<?php echo $field['fields_name']; ?>" data-type="key" class="form-control <?php echo $class ?>" placeholder="<?php e('Key'); ?>" value="<?php echo $single_value['key']; ?>" autocomplete="off" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
                </div>
                <div class="col-xs-5">
                    <input type="text" name="<?php echo $field['fields_name']; ?>[<?php echo $count; ?>][value]" data-name="<?php echo $field['fields_name']; ?>" data-type="value" class="form-control <?php echo $class ?>" placeholder="<?php e('Value'); ?>" value="<?php echo $single_value['value']; ?>" autocomplete="off" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
                </div>
                <div class="col-xs-1">
                    <button type="button" class="btn js_remove_row"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            <?php $count++; ?>
        <?php endforeach; ?>
    </div>


    <div class="row">
        <div class="col-xs-12">
            <button type="button" class="btn btn-info col-xs-12 js_add_multiple_key_values" style="margin-top:15px;"><i class="fas fa-plus-square"></i> <?php e('Add'); ?></button>
        </div>
    </div>
</div>
<?php echo $help; ?>