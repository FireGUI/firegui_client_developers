<?php if (empty($grid_data['data'])) : ?>
<p>No records found</p>
<?php else : ?>

<div class="table-scrollable table-scrollable-borderless">
    <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover nowrap js_datatable <?php echo $grid['grids']['grids_append_class']; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>">
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>
                <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                <th data-prevent-order><?php e('Actions'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato) : ?>
            <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                <?php endforeach; ?>

                <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>

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
</div>
<?php endif; ?>