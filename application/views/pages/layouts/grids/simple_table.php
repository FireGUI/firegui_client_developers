<?php if (empty($grid_data['data'])): ?>
    <p>Al momento non sono ancora stati inseriti dati!</p>
<?php else: ?>
    <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-condensed">
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <th><?php echo $field['fields_draw_label'];  ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($grid_data['data'])): ?>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <tr class="odd gradeX">
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <?php /*<td><?php $this->load->view('box/grid/td', array('field'=>$field, 'dato'=>$dato)); ?></td>*/ ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>