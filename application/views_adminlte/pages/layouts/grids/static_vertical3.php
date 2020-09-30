<?php

if (grid_has_action($grid['grids']) && isset($grid['grids']['links']['custom']) && $grid['grids']['links']['custom']) {
    $links = $grid['grids']['links'];
    $preload_colors = ['CCCCCC' => '#CCCCCC'];
    foreach ($links['custom'] as $custom_action) {
        $preload_colors[md5($custom_action['grids_actions_color'])] = $custom_action['grids_actions_color'];
    }
    $preload_colors = array_unique($preload_colors);
    $preload_colors = array_filter($preload_colors, 'strlen');

    $data['background-colors'] = $preload_colors;

    $this->layout->addDinamicStylesheet($data, "grid_{$links['custom'][0]['grids_actions_grids_id']}.css");
}
?>

<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>
        <ul class="list-group <?php echo $grid['grids']['grids_append_class']; ?>" <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php if (($_field = trim($this->datab->build_grid_cell($field, $dato, true, false)))) : ?>
                    <li class="list-group-item cleafix">
                        <b class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php e($field['grids_fields_column_name']);  ?></b>
                        <span class="pull-right <?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (grid_has_action($grid['grids'])) : ?>
                <li class="list-group-item">
                    <b class="dl-actions"><?php e('Actions'); ?></b>
                    <span class="pull-right dl-actions-label"><?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?></span>
                </li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>
<?php endif; ?>