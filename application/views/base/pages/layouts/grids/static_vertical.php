<?php if (isset($grid_data['data'])) : ?>
<?php foreach ($grid_data['data'] as $dato) : ?>

<dl <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" class="dl-horizontal static-vertical-grid <?php echo $grid['grids']['grids_append_class']; ?>">
    <?php foreach ($grid['grids_fields'] as $field) : ?>
    <dt class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php e($field['grids_fields_column_name']);  ?>:</dt>
    <dd class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $this->datab->build_grid_cell($field, $dato); ?></dd>
    <hr>
    <?php endforeach; ?>
    <?php if (grid_has_action($grid['grids'])) : ?>
    <dt class="dl-actions-label"><?php e('Actions'); ?></dt>
    <dd class="dl-actions">
        <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
    </dd>
    <?php endif; ?>
</dl>
<?php endforeach; ?>
<?php endif; ?>