<?php if (empty($grid_data['data'])): ?>
    <p>Al momento non sono ancora stati inseriti dati!</p>
<?php else: ?>
    <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-bordered table-condensed table-hover table-responsive-scrollable js_datatable_slim" <?php if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label']; ?></th>
                <?php endforeach; ?>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($grid_data['data'])): ?>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <tr class="odd gradeX">
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <?php /* <td><?php $this->load->view('box/grid/td', array('field' => $field, 'dato' => $dato)); ?></td> */ ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                        <td><?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name'] . "_id"], 'row_data' => $dato)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

