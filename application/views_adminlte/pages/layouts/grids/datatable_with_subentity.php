<?php

$all_sub_data = [];
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);
if (!empty($sub_grid) && !empty($grid_data['sub_grid_data'])) {
    $relation_field = $sub_grid['grid_relation_field'];
    foreach ($grid_data['sub_grid_data'] as $sub_record) {
        $all_sub_data[$sub_record[$relation_field]][] = $sub_record;
    }

    unset($grid_data['sub_grid_data']); // Free memory
}
$cols = ($has_bulk && $has_exportable) ? 6 : 12;
//debug($sub_grid,true);
?>
<?php if (empty($grid_data['data'])) : ?>
    <p>Nessun dato disponibile</p>
<?php else : ?>
    <div class="table-scrollable table-scrollable-borderless">
        <table data-lengthmenu='[[20, 100, 200, 400, 1000, -1], [10, 50, 100, 200, 500, "Tutti"]]' <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" class="table table-striped table-bordered nowrap table-hover table-hover <?php echo $grid['grids']['grids_append_class']; ?>">
            <thead>
                <tr>
                    <?php foreach ($grid['grids_fields'] as $field) : ?>
                        <th><?php echo $field['grids_fields_column_name']; ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $key => $dato) : ?>
                    <?php $sub_data = isset($all_sub_data[$dato[$grid['grids']['entity_name'] . "_id"]]) ? $all_sub_data[$dato[$grid['grids']['entity_name'] . "_id"]] : []; ?>
                    <tr class="__odd __gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php foreach ($grid['grids_fields'] as $field) : ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>

                        <td class="text-right" <?php echo $sub_data ? ' rowspan="2"' : '' ?> style="vertical-align:top!important;">
                            <?php
                            $this->load->view('box/grid/actions', array(
                                'links' => $grid['grids']['links'],
                                'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                                'row_data' => $dato,
                                'grid' => $grid['grids']
                            ));
                            ?>
                            <?php if ($sub_data) : ?>

                                <a style="margin-top: 5px;" onclick="javascript:setTimeout(function () {initComponents();}, 500);" class="btn btn-primary btn-xs" data-toggle="collapse" href="#<?php echo ($collapse_id = "collapser{$grid['grids']['grids_id']}_{$sub_grid['grids']['grids_id']}_{$key}"); ?>">
                                    <?php e('expand'); ?> <span data-toggle="tooltip" class="caret"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php /* INIZIO SUB ENTITY */ ?>
                    <?php if ($sub_data) : ?>
                        <tr style="background-color:#DDD!important;">
                            <td <?php echo 'colspan="' . (count($grid['grids_fields']) + 1) . '"'; ?> style="padding: 0;border: none;">
                                <div <?php echo "id='{$collapse_id}'"; ?> class="collapse" style="margin-top:25px;padding: 5px;">
                                    <?php if (!$sub_grid['grids']['grids_layout']) : ?>
                                        <table class="table table-bordered table-full-width" style="margin-bottom: 0!important">
                                            <thead>
                                                <tr>
                                                    <?php foreach ($sub_grid['grids_fields'] as $field) : ?>
                                                        <th style="background-color: #dcdcdc; color: #000"><?php echo $field['grids_fields_column_name']; ?></th>
                                                    <?php endforeach; ?>
                                                    <th><?php e('Action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sub_data as $sub_dato) : ?>
                                                    <tr class="odd gradeX" data-id="<?php echo $sub_dato[$sub_grid['grids']['entity_name'] . "_id"]; ?>">
                                                        <?php foreach ($sub_grid['grids_fields'] as $field) : ?>
                                                            <td><?php echo $this->datab->build_grid_cell($field, $sub_dato); ?></td>
                                                        <?php endforeach; ?>
                                                        <td class="text-right">
                                                            <?php
                                                            $this->load->view('box/grid/actions', array(
                                                                'links' => $sub_grid['grids']['links'],
                                                                'id' => $sub_dato[$sub_grid['grids']['entity_name'] . "_id"],
                                                                'row_data' => $sub_dato,
                                                                'grid' => $sub_grid['grids']
                                                            ));
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else : ?>
                                        <?php

                                        //debug($dato,true);
                                        $this->load->view(
                                            "pages/layouts/grids/{$sub_grid['grids']['grids_layout']}",
                                            array(
                                                'grid' => $sub_grid,
                                                'sub_grid' => false,
                                                'grid_data' => $sub_data,
                                                //'value_id' => $value_id, 
                                                'layout_data_detail' => [],
                                                'is_sub_grid' => true,
                                                'where' => "{$sub_grid['grid_relation_field']} = '{$dato[$grid['grids']['entity_name'] . "_id"]}'"
                                            )
                                        );
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php /* Fine Sub entity */ ?>

                <?php endforeach; ?>
            </tbody>
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
<?php endif; ?>