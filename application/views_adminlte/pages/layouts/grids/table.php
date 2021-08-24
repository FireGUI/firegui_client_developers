<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);

$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$cols = ($has_bulk && $has_exportable) ? 6 : 12;

$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        break;
    }
}

$links = $grid['grids']['links'];

// Check if datatable and if ajax yes or not
$grid_is_ajax = false;
$datatable_class = "";

if ($grid['grids']['grids_datatable'] == DB_BOOL_TRUE) {
    if ($grid['grids']['grids_ajax'] == DB_BOOL_TRUE) {
        $datatable_class = 'js_ajax_datatable';
        $grid_is_ajax = true;
    } else {
        $datatable_class = 'js_datatable';
        $grid_is_ajax = false;
    }
}

// Actions
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
//debug($grid['grids']);
?>

<div class="___table-scrollable table-scrollable-borderless">
    <table data-ajax="<?php echo $grid['grids']['grids_ajax']; ?>" data-design="<?php echo $grid['grids']['grids_design']; ?>" data-datatable="<?php echo $grid['grids']['grids_datatable']; ?>" data-searchable="<?php echo $grid['grids']['grids_searchable']; ?>" data-pagination="<?php echo $grid['grids']['grids_pagination']; ?>" data-inline="<?php echo $grid['grids']['grids_inline_edit']; ?>" data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover nowrap table-middle js_newTable js_fg_grid_<?php echo $grid['grids']['entity_name']; ?> <?php echo $grid['grids']['grids_append_class']; ?>" data-value-id="<?php echo $value_id; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>" data-where_append="<?php echo (empty($where)) ? '' : $where; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                    <th data-prevent-order>
                        <input type="checkbox" class="js-bulk-select-all" />
                    </th>
                <?php endif; ?>
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>" <?php echo ($field['grids_fields_replace_type'] !== 'field' && ($field['grids_fields_eval_cache_type'] == '' or $field['grids_fields_eval_cache_type'] == 'no_cache') && empty($field['grids_fields_eval_cache_data'])) ? 'data-prevent-order' : ''; ?> <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
                <?php endforeach; ?>

                <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                    <th data-prevent-order><?php e('Actions'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php if ($grid_is_ajax == false) : ?>
                <?php foreach ($grid_data['data'] as $dato) : ?>
                    <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php if ($has_bulk) : ?>
                            <td>
                                <input type="checkbox" class="js_bulk_check" value="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" />
                            </td>
                        <?php endif; ?>
                        <?php foreach ($grid['grids_fields'] as $field) : ?>
                            <?php
                            if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
                                if (!empty($this->datab->build_grid_cell($field, $dato))) {
                                    @$sums[$field['grids_fields_id']] += (float) ($this->datab->build_grid_cell($field, $dato));
                                }
                            }
                            ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
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

                    <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                        <th data-prevent-order>&nbsp;</th>
                    <?php endif; ?>

                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if ($has_bulk or $has_exportable) : ?>
        <div class="row">
            <?php if ($has_bulk) : ?>
                <div class="col-md-<?php echo $cols; ?>">
                    <select class="form-control js-bulk-action firegui_widthauto" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>">
                        <option value="" class="js-bulk-first-option" selected="selected"></option>

                        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                            <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>" disabled="disabled">Edit</option>
                        <?php endif; ?>
                        <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit') : ?>
                            <option value="bulk_delete" disabled="disabled">Delete</option>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($has_exportable) : ?>
                <?php $this->load->view('pages/layouts/grids/export_button', ['grid' => $grid, 'cols' => $cols]); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>