<?php 
$choiches = [];
$is_bool = ($field['fields_type']==DB_BOOL_IDENTIFIER);
if (empty($field['support_data'])) {
    // Se non ha una join allora è booleana
    $positive_val = $field['fields_type']==DB_BOOL_IDENTIFIER? DB_BOOL_TRUE: '1';
    $choiches[$positive_val] = $field['fields_draw_label'];
} else {
    $choiches = $field['support_data'];
}
$inline = count($choiches) < 4;
?>
<?php echo $label; ?>
<div class="row">
    <div class="col-xs-12">
        <?php if ($is_bool): ?>
            <input type="hidden" value="<?php echo DB_BOOL_FALSE; ?>" name="<?php echo $field['fields_name']; ?>" data-notmodifiable="1" />
        <?php endif; ?>
        <?php foreach($choiches as $id => $name): ?>
            <?php $isSelected = ((is_array($value) && isset($value[$id])) OR (!is_array($value) && $value == $id)); ?>
            <label class="<?php echo $inline ? '': 'checkbox' ?>">
                <input type="checkbox" class="<?php echo $class ?>" value="<?php echo $id; ?>" <?php echo $isSelected ? 'checked': ''; ?> name="<?php echo $field['fields_name'] . ($field['fields_type']==DB_BOOL_IDENTIFIER?'': '[]'); ?>" <?php echo $onclick; ?> />
                <?php echo $name; ?>
            </label>
        <?php endforeach; ?>
        <?php echo $help; ?>
    </div>
</div>