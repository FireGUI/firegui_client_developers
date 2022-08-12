<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$grid_id = 'grid_' . $grid['grids']['grids_id'];

$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        $sums = [];
        break;
    }
}
?>

<?php if (empty($grid_data['data'])) : ?>
<p>No records found</p>
<?php else : ?>

<div class="table-scrollable table-scrollable-borderless">
    <table id="<?php echo $grid_id; ?>" class="table table-striped table-condense js_fg_grid_<?php echo $grid['grids']['entity_name']; ?> <?php echo $grid['grids']['grids_append_class']; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                <th>
                    <input type="checkbox" class="js-bulk-select-all" />
                </th>
                <?php endif; ?>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <th><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato) : ?>
            <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                <?php if ($has_bulk) : ?>
                <td>
                    <input type="checkbox" class="js_bulk_check" value="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" />
                </td>
                <?php endif; ?>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php
                            if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
                                if (!empty($this->datab->build_grid_cell($field, $dato))) {
                                    @$sums[$field['grids_fields_id']] += (float) ($this->datab->build_grid_cell($field, $dato));
                                }
                            }
                            ?>
                <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($has_totalable) : ?>
        <tfoot>
            <tr>
                <?php if ($has_bulk) : ?>
                <th data-prevent-order data-name="_foo">
                    <input type="checkbox" class="js-bulk-select-all" />
                </th>
                <?php endif; ?>

                <?php foreach ($grid['grids_fields'] as $key => $field) : ?>
                <?php if ($key == 0) : ?>
                <th><?php e('Totals:'); ?></th>
                <?php else : ?>
                <th>
                    <?php if (!empty($sums[$field['grids_fields_id']])) : ?>
                    <?php echo $sums[$field['grids_fields_id']]; ?>
                    <?php endif; ?>
                </th>
                <?php endif; ?>
                <?php endforeach; ?>



            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <?php if ($has_bulk) : ?>

    <select class="form-control js-bulk-action firegui_widthauto" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>">
        <option value="" class="js-bulk-first-option" selected="selected"></option>

        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
        <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>" disabled="disabled"><?php e('Edit'); ?></option>
        <?php endif; ?>
        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
        <option value="bulk_delete" disabled="disabled"><?php e('Delete'); ?></option>
        <?php endif; ?>
    </select>

    <?php endif; ?>
</div>
<?php endif; ?>