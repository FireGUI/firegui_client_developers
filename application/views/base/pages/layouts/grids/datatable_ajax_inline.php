<a class="js_datatable_inline_add btn btn-success btn-xs pull-right" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>"><?php e('New row'); ?></a>
<div class="clearfix"></div>
<br />
<table <?php echo "id='grid_{$grid['grids']['grids_id']}'" ?> default-limit="<?php echo (defined('OVERRIDE_GRID_LIMIT')) ? OVERRIDE_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover table-middle nowrap js_ajax_datatable js_datatable_inline js_fg_grid_<?php echo $grid['grids']['entity_name']; ?> <?php echo $grid['grids']['grids_append_class']; ?>" data-value-id="<?php echo $value_id; ?>" data-entity="<?php echo $grid['grids']['entity_name']; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field) : ?>
            <th data-name="<?php echo $field['fields_name']; ?>"><?php e($field['grids_fields_column_name']);  ?></th>
            <?php endforeach; ?>

            <th data-prevent-order>&nbsp;</th>
            <th data-prevent-order>&nbsp;</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>