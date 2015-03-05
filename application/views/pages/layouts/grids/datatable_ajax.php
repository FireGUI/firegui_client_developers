<table class="table table-striped table-bordered table-hover table-responsive-scrollable js_ajax_datatable" <?php if($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?> data-value-id="<?php echo $value_id; ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>" id="grid_<?php echo $grid['grids']['grids_id'] ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th <?php if($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label']; ?></th>
            <?php endforeach; ?>
                
            <?php if(grid_has_action($grid['grids'])): ?>
                <th>Action</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody></tbody>
</table>

