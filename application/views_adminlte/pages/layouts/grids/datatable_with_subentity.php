<?php
$all_sub_data = [];
if (!empty($sub_grid) && !empty($grid_data['sub_grid_data'])) {
    $relation_field = $sub_grid['grid_relation_field'];
    foreach ($grid_data['sub_grid_data'] as $sub_record) {
        $all_sub_data[$sub_record[$relation_field]][] = $sub_record;
    }

    unset($grid_data['sub_grid_data']); // Free memory
}
?>
<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>
    <div class="table-scrollable table-scrollable-borderless">
        <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-bordered table-hover table-hover <?php echo $grid['grids']['grids_append_class']; ?>">
            <thead>
                <tr>
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <th><?php echo $field['grids_fields_column_name']; ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $key => $dato): ?>
                    <?php $sub_data = isset($all_sub_data[$dato[$grid['grids']['entity_name'] . "_id"]]) ? $all_sub_data[$dato[$grid['grids']['entity_name'] . "_id"]] : []; ?>
                    <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>

                            <td class="text-right" <?php echo $sub_data ? ' rowspan="2"' : '' ?>>
                            <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                            <?php if ($sub_data): ?>
                                <br>
                                <a class="btn btn-primary btn-xs" data-toggle="collapse" href="#<?php echo ($collapse_id = "collapser{$grid['grids']['grids_id']}_{$sub_grid['grids']['grids_id']}_{$key}"); ?>">
                                   espandi <span data-toggle="tooltip" class="caret"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php /* INIZIO SUB ENTITY */ ?>
                    <?php if ($sub_data): ?>
                        <tr>
                            <td <?php echo 'colspan="' . count($grid['grids_fields']) . '"'; ?>  style="padding: 0;border: none;">
                                <div <?php echo "id='{$collapse_id}'"; ?> class="collapse">
                                    <table class="table table-bordered table-full-width" style="margin-bottom: 0!important;">
                                        <thead>
                                            <tr>
                                                <?php foreach ($sub_grid['grids_fields'] as $field): ?>
                                                    <th style="background-color: #dcdcdc; color: #000"><?php echo $field['grids_fields_column_name']; ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sub_data as $sub_dato): ?>
                                                <tr class="odd gradeX" data-id="<?php echo $sub_dato[$sub_grid['grids']['entity_name'] . "_id"]; ?>">
                                                    <?php foreach ($sub_grid['grids_fields'] as $field): ?>
                                                        <td><?php echo $this->datab->build_grid_cell($field, $sub_dato); ?></td>
                                                    <?php endforeach; ?>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php /* Fine Sub entity */ ?>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
