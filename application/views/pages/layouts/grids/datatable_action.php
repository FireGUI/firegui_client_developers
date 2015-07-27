<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>
    <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-bordered table-hover table-responsive-scrollable js_datatable" <?php if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label']; ?></th>
                <?php endforeach; ?>
                <?php if (grid_has_action($grid['grids'])): ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato): ?>
                <tr class="odd gradeX" data-id="<?php echo $dato[$grid_data['entity']['entity_name'] . "_id"]; ?>">
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>

                    <?php if (grid_has_action($grid['grids'])): ?>
                        <td><?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name'] . "_id"], 'row_data' => $dato)); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
