<?php if (empty($grid_data['data'])) : ?>
    <p>No records found</p>
<?php else : ?>
    <?php
    if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE && isset($links['custom']) && $links['custom']) {
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
    <table class="table table-condensed table-striped nowrap table-bordered table-hover <?php echo $grid['grids']['grids_append_class']; ?>">
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <th><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato) : ?>
                <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                    <?php foreach ($grid['grids_fields'] as $field) : ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>