<?php

$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$grid_id = 'grid_' . $grid['grids']['grids_id'];
$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        break;
    }
}
?>
<?php if (empty($grid_data['data'])) : ?>
<p>No records found</p>
<?php else : ?>

<div class="table-scrollable table-scrollable-borderless">
    <table data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" id="<?php echo $grid_id; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered nowrap table-condensed table-hover js_datatable_slim <?php echo $grid['grids']['grids_append_class']; ?>" <?php // if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' 
                                                                                                                                                                                                                                                                                                                                                                                                                    ?>>
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato) : ?>
            <tr data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                <?php foreach ($grid['grids_fields'] as $field) : ?>
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

                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php $name = ($field['grids_fields_eval_cache_type'] == 'query_equivalent') ? $field['grids_fields_eval_cache_data'] : $field['fields_name']; ?>
                <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>" data-name="<?php echo $name; ?>" <?php if ($field['fields_draw_html_type'] === 'upload_image') echo ' class="firegui_width50"'; ?>>
                </th>
                <?php endforeach; ?>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>
<?php endif; ?>