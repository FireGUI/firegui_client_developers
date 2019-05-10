<?php
//debug($grid);
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$grid_id = 'grid_'.$grid['grids']['grids_id'];
$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$cols = ($has_bulk && $has_exportable) ? 6 : 12;
?>

<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>

    <?php //class scrollable eliminata perchè non permetteva l'apertura del dropdown export... ?>
    <div class="___table-scrollable table-scrollable-borderless">
        <table id="<?php echo $grid_id; ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT'))?DEFAULT_GRID_LIMIT:10; ?>" class="table table-striped table-bordered table-hover js_datatable <?php echo $grid['grids']['grids_append_class']; ?>" <?php // if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
            <thead>
                <tr>
                        <?php if ($has_bulk) : ?>
                            <th data-prevent-order>
                                <input type="checkbox" class="js-bulk-select-all" />
                            </th>
                        <?php endif; ?>
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['grids_fields_column_name']; ?></th>
                    <?php endforeach; ?>

                <?php if(grid_has_action($grid['grids'])): ?>
                        <th data-prevent-order><?php e('Action'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                            <?php if ($has_bulk) : ?>
                                <td>
                                    <input type="checkbox" class="js_bulk_check" value="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" />
                                </td>
                            <?php endif; ?>
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                            
                        <?php if (grid_has_action($grid['grids'])): ?>
                            
                            <td><?php $this->load->view('box/grid/actions', array(
                                'links' => $grid['grids']['links'], 
                                'id' => $dato[$grid['grids']['entity_name'] . "_id"], 
                                'row_data' => $dato,
                                'grid' => $grid['grids'],
                                    )); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($has_bulk OR $has_exportable) : ?>
        <div class="row">
            <?php if ($has_bulk) : ?>
                <div class="col-md-<?php echo $cols; ?>">
                    <select class="form-control js-bulk-action" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>" style="width: auto;margin-top:10px;">
                        <option value="" class="js-bulk-first-option" selected="selected"></option>

                        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' OR $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                            <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>" disabled="disabled">Edit</option>
                        <?php endif; ?>
                        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' OR $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
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
<?php endif; ?>
