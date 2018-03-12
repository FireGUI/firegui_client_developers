<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>
    <div class="table-scrollable table-scrollable-borderless">
        <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-condensed">
            <thead>
                <tr>
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <th><?php echo $field['grids_fields_column_name'];  ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                        <td><?php $this->load->view('box/grid/actions', array( 'links'=> $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name']."_id"], 'row_data' => $dato )); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
