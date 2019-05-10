<a class="js_datatable_inline_add btn btn-success btn-xs pull-right" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">Nuovo valore</a>
<div class="clearfix"></div>
<br/>

<table <?php echo "id='grid_{$grid['grids']['grids_id']}'" ?> class="table table-striped table-bordered table-hover table-middle js_ajax_datatable js_datatable_inline" data-value-id="<?php echo $value_id; ?>" data-entity="<?php echo $grid['grids']['entity_name']; ?>" <?php if($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?> data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th data-name="<?php echo $field['fields_name']; ?>"><?php echo $field['grids_fields_column_name']; ?></th>
            <?php endforeach; ?>

            <th data-prevent-order>&nbsp;</th>
            <th data-prevent-order>&nbsp;</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>