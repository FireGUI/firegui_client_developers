<style>
    .dl-vertical>hr {
        margin-top: 0.5% !important;
        margin-bottom: 0.5% !important;
    }

    .dl-vertical>dt,
    .dl-vertical>dd {
        word-wrap: break-word
    }
</style>

<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>
        <dl class="dl-vertical <?php echo $grid['grids']['grids_append_class']; ?>" <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php if (($_field = trim($this->datab->build_grid_cell($field, $dato)))) : ?>
                    <dt class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $field['grids_fields_column_name']; ?>:</dt>
                    <dd class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></dd>
                    <hr>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (grid_has_action($grid['grids'])) : ?>
                <dt class="dl-actions-label"><?php e('Actions'); ?></dt>
                <dd class="dl-actions">
                    <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                </dd>
                </dt>
            <?php endif; ?>
        </dl>
    <?php endforeach; ?>
<?php endif; ?>