<table class="table table-striped table-bordered table-hover table-responsive-scrollable js_datatable" id="grid_<?php echo $grid['grids']['grids_id'] ?>" <?php if($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th <?php if($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label']; ?></th>
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

