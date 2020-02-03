<?php
//debug($grid);
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);

$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$cols = ($has_bulk && $has_exportable) ? 6 : 12;
?>

<div class="___table-scrollable table-scrollable-borderless">
    <?php
    // if($grid['grids']['grids_order_by']) echo 'data-prevent-order' 
    ?>
    <table data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover table-condensed nowrap js_ajax_datatable <?php echo $grid['grids']['grids_append_class']; ?>" data-crsf="<?php echo json_encode(get_csrf()); ?>" data-value-id="<?php echo $value_id; ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                    <th data-prevent-order>
                        <input type="checkbox" class="js-bulk-select-all" />
                    </th>
                <?php endif; ?>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <th <?php echo ($field['grids_fields_replace_type'] !== 'field' && ($field['grids_fields_eval_cache_type'] == '' or $field['grids_fields_eval_cache_type'] == 'no_cache')) ? 'data-prevent-order' : ''; ?> <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['grids_fields_column_name']; ?></th>
                <?php endforeach; ?>

                <?php if (grid_has_action($grid['grids'])) : ?>
                    <th data-prevent-order><?php e('Action'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <?php if ($has_bulk or $has_exportable) : ?>
        <div class="row">
            <?php if ($has_bulk) : ?>
                <div class="col-md-<?php echo $cols; ?>">
                    <select class="form-control js-bulk-action" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>" style="width: auto;">
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