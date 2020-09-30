<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$grid_id = 'grid_' . $grid['grids']['grids_id'];
?>

<?php if (empty($grid_data['data'])) : ?>
    <p><?php e('No records found'); ?></p>
<?php else : ?>


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

    <div class="table-scrollable table-scrollable-borderless">
        <table id="<?php echo $grid_id; ?>" class="table table-striped table-condensed">
            <thead>
                <tr>
                    <?php if ($has_bulk) : ?>
                        <th>
                            <input type="checkbox" class="js-bulk-select-all" />
                        </th>
                    <?php endif; ?>
                    <?php foreach ($grid['grids_fields'] as $field) : ?>
                        <th><?php e($field['grids_fields_column_name']);  ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>

            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $dato) : ?>
                    <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php if ($has_bulk) : ?>
                            <td>
                                <input type="checkbox" class="js_bulk_check" value="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" />
                            </td>
                        <?php endif; ?>
                        <?php foreach ($grid['grids_fields'] as $field) : ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>

                        <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                            <td><?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($has_bulk) : ?>

            <select class="form-control js-bulk-action" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>">
                <option value="" class="js-bulk-first-option" selected="selected"></option>

                <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                    <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>" disabled="disabled"><?php e('Edit'); ?></option>
                <?php endif; ?>
                <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                    <option value="bulk_delete" disabled="disabled"><?php e('Delete'); ?></option>
                <?php endif; ?>
            </select>


        <?php endif; ?>

    </div>
<?php endif; ?>