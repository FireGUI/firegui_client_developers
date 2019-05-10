<?php
$choiches = [];
if (empty($field['support_data'])) {
    // Se non ha una join allora è booleana
    $positive_val = $field['fields_type'] == DB_BOOL_IDENTIFIER ? DB_BOOL_TRUE : '1';
    $negative_val = $field['fields_type'] == DB_BOOL_IDENTIFIER ? DB_BOOL_FALSE : '0';

    $choiches[$positive_val] = 'Si';
    $choiches[$negative_val] = 'No';
} else {
    $choiches = $field['support_data'];
}
$inline = true;//count($choiches) < 3;
?>
<?php echo $label; ?>
<!--<div class="col-xs-12">-->
<div>
    <div class="radio-list" style="margin-left: 6px;">
        <?php foreach ($choiches as $id => $name): ?>
            <label class="<?php echo $inline ? 'radio-inline' : 'radio' ?>">
                <input class="<?php echo $class ?>" type="radio" value="<?php echo $id; ?>" <?php echo ($id == $value) ? 'checked' : ''; ?> name="<?php echo $field['fields_name']; ?>" <?php echo $onclick; ?> />
                <?php echo $name; ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>
<?php echo $help; ?>
