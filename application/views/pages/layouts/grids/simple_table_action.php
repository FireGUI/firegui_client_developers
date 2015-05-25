<table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-condensed table-responsive-scrollable">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th><?php echo $field['fields_draw_label'];  ?></th>
            <?php endforeach; ?>
                <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($grid_data['data'])): ?>
            <?php foreach ($grid_data['data'] as $dato): ?>
                <tr class="odd gradeX">
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>
                    <td><?php $this->load->view('box/grid/actions', array( 'links'=> $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name']."_id"], 'row_data' => $dato )); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
                
    </tbody>
</table>