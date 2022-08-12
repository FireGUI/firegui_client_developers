<?php if (isset($grid_data['data'])) : ?>
<?php foreach ($grid_data['data'] as $dato) : ?>
<ul class="list-group <?php echo $grid['grids']['grids_append_class']; ?>" <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
    <?php foreach ($grid['grids_fields'] as $field) : ?>
    <?php if (($_field = trim($this->datab->build_grid_cell($field, $dato, true, false)))) : ?>
    <li class="list-group-item cleafix">
        <h4 class="list-group-item-heading <?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php e($field['grids_fields_column_name']);  ?></h4>
        <p class="list-group-item-text <?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></p>
    </li>
    <?php endif; ?>
    <?php endforeach; ?>

    <?php if (grid_has_action($grid['grids'])) : ?>
    <li class="list-group-item">
        <h4 class="list-group-item-heading font-bold <?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php e('Actions'); ?></h4>
        <p class="list-group-item-text <?php echo "js-grid-field-{$field['fields_id']}" ?>">
            <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
        </p>
    </li>
    <?php endif; ?>
</ul>
<?php endforeach; ?>
<?php endif; ?>