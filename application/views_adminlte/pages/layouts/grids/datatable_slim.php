<?php

$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$grid_id = 'grid_' . $grid['grids']['grids_id'];
$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        break;
    }
}
?>
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
    <div class="table-scrollable table-scrollable-borderless">
        <table data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" id="<?php echo $grid_id; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered nowrap table-condensed table-hover js_datatable_slim <?php echo $grid['grids']['grids_append_class']; ?>" <?php // if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' 
                                                                                                                                                                                                                                                                                                                                                                                                                    ?>>
            <thead>
                <tr>
                    <?php foreach ($grid['grids_fields'] as $field) : ?>
                        <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $dato) : ?>
                    <tr data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php foreach ($grid['grids_fields'] as $field) : ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($has_totalable) : ?>
                <tfoot>
                    <tr>
                        <?php if ($has_bulk) : ?>
                            <th data-prevent-order data-name="_foo">
                                <input type="checkbox" class="js-bulk-select-all" />
                            </th>
                        <?php endif; ?>

                        <?php foreach ($grid['grids_fields'] as $field) : ?>
                            <?php $name = ($field['grids_fields_eval_cache_type'] == 'query_equivalent') ? $field['grids_fields_eval_cache_data'] : $field['fields_name']; ?>
                            <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>" data-name="<?php echo $name; ?>" <?php if ($field['fields_draw_html_type'] === 'upload_image') echo ' class="firegui_width50"'; ?>>
                            </th>
                        <?php endforeach; ?>



                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
<?php endif; ?>