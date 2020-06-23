<style>
    .list-group-item>span {
        color: #3c8dbc !important;
        word-wrap: break-word;
    }

    .list-group-item>a {
        font-weight: bold !important;
    }

    .list-group-item {
        overflow: hidden !important;
        position: relative !important;
        display: block !important;
        padding: 10px 15px !important;
        margin-bottom: -1px !important;
        background-color: #fff !important;
    }
</style>
<?php //debug($grid_data); 
?>
<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>
        <ul class="list-group <?php echo $grid['grids']['grids_append_class']; ?>" <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php if (($_field = trim($this->datab->build_grid_cell($field, $dato, true, false)))) : ?>
                    <li class="list-group-item cleafix">
                        <b class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $field['grids_fields_column_name']; ?></b>
                        <span class="pull-right <?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (grid_has_action($grid['grids'])) : ?>
                <li class="list-group-item">
                    <b class="dl-actions"><?php e('Actions'); ?></b>
                    <span class="pull-right dl-actions-label" style="word-wrap: break-word !important"><?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?></span>
                </li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>
<?php endif; ?>