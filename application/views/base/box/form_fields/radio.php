<?php
$choiches = [];
if (empty($field['support_data'])) {
    // Se non ha una join allora è booleana
    $positive_val = $field['fields_type'] == DB_BOOL_IDENTIFIER ? DB_BOOL_TRUE : '1';
    $negative_val = $field['fields_type'] == DB_BOOL_IDENTIFIER ? DB_BOOL_FALSE : '0';

    $choiches[$positive_val] = t('Yes');
    $choiches[$negative_val] = t('No');
} else {
    $choiches = $field['support_data'];
}

$inline = true; //count($choiches) < 3;
?>
<?php echo $label; ?>


<div>
    <div class="radio-list firegui_radio-list">
        <?php foreach ($choiches as $id => $name) : ?>
            <?php
            $is_checked = false;

            if ($value !== '' && $value !== null) {
                if ($id == $value) {
                    $is_checked = true;
                }
            } elseif($field['fields_default'] !== '' && $field['fields_default'] !== null) {
                if ($field['fields_default'] == $id) {
                    $is_checked = true;
                }
            } else {
                if ($id == DB_BOOL_FALSE) {
                    $is_checked = true;
                }
            }
            ?>
            <label class="<?php echo $inline ? 'radio-inline' : 'radio' ?>">
                <input class="<?php echo $class ?>" type="radio" value="<?php echo $id; ?>" <?php echo $is_checked ? 'checked="checked"' : ''; ?> name="<?php echo $field['fields_name']; ?>" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
                <?php echo $name; ?>
            </label>
        <?php endforeach; ?>
    </div>
    
</div>

<?php echo $help; ?>
