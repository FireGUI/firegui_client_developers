<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);

$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$cols = ($has_bulk && $has_exportable) ? 6 : 12;

$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        break;
    }
}
?>

<div class="___table-scrollable table-scrollable-borderless">
    <table data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" default-limit="<?php echo (defined('OVERRIDE_GRID_LIMIT')) ? OVERRIDE_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover table-condensed nowrap js_ajax_datatable js_fg_grid_<?php echo $grid['grids']['entity_name']; ?> <?php echo $grid['grids']['grids_append_class']; ?>" data-crsf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-value-id="<?php echo $value_id; ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                <th data-prevent-order>
                    <input type="checkbox" class="js-bulk-select-all" />
                </th>
                <?php endif; ?>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php $name = (empty($field['fields_name'])) ? $field['grids_fields_eval_cache_data'] : $field['fields_name'];


                    ?>
                <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>" data-name="<?php echo $name; ?>" <?php echo ($field['grids_fields_replace_type'] !== 'field' && ($field['grids_fields_eval_cache_type'] == '' or $field['grids_fields_eval_cache_type'] == 'no_cache') && empty($field['grids_fields_eval_cache_data'])) ? 'data-prevent-order' : ''; ?> <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>

                <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                <th data-prevent-order><?php e('Actions'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody></tbody>
        <?php if ($has_totalable) : ?>
        <tfoot>
            <tr>
                <?php if ($has_bulk) : ?>
                <th data-prevent-order data-name="_foo">
                    <input type="checkbox" class="js-bulk-select-all" />
                </th>
                <?php endif; ?>

                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php $name = (empty($field['fields_name'])) ? $field['grids_fields_eval_cache_data'] : $field['fields_name'];


                        ?>
                <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>" data-name="<?php echo $name; ?>" <?php if ($field['fields_draw_html_type'] === 'upload_image') echo ' class="firegui_width50"'; ?>>
                </th>
                <?php endforeach; ?>
                <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                <th data-prevent-order>&nbsp;</th>
                <?php endif; ?>

            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <?php if ($has_bulk or $has_exportable) : ?>
    <div class="row">
        <?php if ($has_bulk) : ?>
        <div class="col-md-<?php echo $cols; ?>">
            <select class="form-control js-bulk-action firegui_widthauto" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>">
                <option value="" class="js-bulk-first-option" selected="selected"></option>

                <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>" disabled="disabled">Edit</option>
                <?php endif; ?>
                <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                <option value="bulk_delete" disabled="disabled">Delete</option>
                <?php endif; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if ($has_exportable) : ?>
        <?php $this->load->view('pages/layouts/grids/export_button', ['grid' => $grid, 'cols' => $cols]); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>