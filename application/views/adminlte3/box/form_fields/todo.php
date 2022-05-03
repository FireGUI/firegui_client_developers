<?php

if (is_array(json_decode(html_entity_decode($value), true))) {
    $values = json_decode(html_entity_decode($value), true);
} else {
    $values[] = array("checked" => "", "value" => "");
}
$count = 1;
?>
<?php echo $label; ?>

<div class="js_todo_container">
    <div class="js_multiple_row_container js_todo_row_container">

        <?php foreach ($values as $single_value) : ?>

            <div class="row js_multiple_key_values_row" style="margin-top:10px">

                <div class="col-xs-1 text-center" style="padding-top:5px">
                    <label class="container-checkbox ">
                        <input type="checkbox" value="<?php echo DB_BOOL_TRUE; ?>" class="js_container-checkbox" name="<?php echo $field['fields_name']; ?>[<?php echo $count; ?>][checked]" <?php if ($single_value['checked'] == DB_BOOL_TRUE) : ?>checked="checked" <?php endif; ?> />
                        <span class="checkmark"></span>
                    </label>

                </div>

                <div class="col-xs-9" style="margin-right:0px;padding:0px">
                    <?php $textarea_rows = substr_count($single_value['value'], PHP_EOL); ?>
                    <div class="grow-wrap">
                        <textarea rows="<?php echo ($textarea_rows > 0) ? $textarea_rows + 1 : 1; ?>" style="<?php if ($single_value['checked'] == DB_BOOL_TRUE) : ?>text-decoration:line-through<?php endif; ?>" onInput="this.parentNode.dataset.replicatedValue = this.value" name="<?php echo $field['fields_name']; ?>[<?php echo $count; ?>][value]" data-name="<?php echo $field['fields_name']; ?>" data-type="value" class="form-control js_todo_textarea <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" autocomplete="off"><?php echo $single_value['value']; ?></textarea>
                    </div>
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