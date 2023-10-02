<?php

// Bulk and export options
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);
$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);
$cols = ($has_bulk && $has_exportable) ? 6 : 12;

//debug($grid['grids']['links']);

// Totals option
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
if ($grid['grids']['grids_datatable'] == DB_BOOL_TRUE) {
    if ($grid['grids']['grids_ajax'] == DB_BOOL_TRUE) {
        $grid_is_ajax = true;
    } else {
        $grid_is_ajax = false;
    }
}

$append_class = '';
if ($grid['grids']['grids_design'] == 2) { //Slim
    $append_class .= ' table-condensed ';
}

if (defined('DEFAULT_GRID_LIMIT')) {
    $limit = DEFAULT_GRID_LIMIT;
} elseif ($grid['grids']['grids_pagination']) {
    $limit = $grid['grids']['grids_pagination'];
} else {
    $limit = 10;
}

?>

<?php if (!$grid_is_ajax && empty($grid_data['data'])): ?>
    <p>
        <?php e('No records found'); ?>
    </p>
<?php else: ?>

    <div class="table-scrollable-borderless">
        <?php if ($grid['grids']['grids_inline_edit']): ?>

            <a class="js_datatable_inline_add btn btn-success btn-xs pull-right"
                data-grid-id="<?php echo $grid['grids']['grids_id']; ?>"><?php e('New row'); ?></a>
            <div class="clearfix"></div>
            <br />
        <?php endif; ?>

        <table data-entity="<?php echo $grid['grids']['entity_name']; ?>"
            data-form="<?php echo $grid['grids']['grids_inline_form']; ?>"
            data-ajax="<?php echo $grid['grids']['grids_ajax']; ?>"
            data-design="<?php echo $grid['grids']['grids_design']; ?>"
            data-datatable="<?php echo $grid['grids']['grids_datatable']; ?>"
            data-searchable="<?php echo $grid['grids']['grids_searchable']; ?>"
            data-pagination="<?php echo $grid['grids']['grids_pagination']; ?>"
            data-inline="<?php echo $grid['grids']['grids_inline_edit']; ?>"
            data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>"
            data-default-limit="<?php echo $limit; ?>"
            class="table table-striped table-bordered table-hover nowrap table-middle js_table js_fg_grid_<?php echo $grid['grids']['entity_name']; ?>             <?php echo $append_class; ?>             <?php echo $grid['grids']['grids_append_class']; ?>"
            data-value-id="<?php echo $value_id; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>"
            data-grid-id="<?php echo $grid['grids']['grids_id']; ?>"
            data-where_append="<?php echo (empty($where)) ? '' : $where; ?>">
            <thead>
                <tr>
                    <?php if ($has_bulk): ?>
                        <th data-prevent-order>
                            <input type="checkbox" class="js-bulk-select-all" />
                        </th>
                    <?php endif; ?>
                    <?php foreach ($grid['grids_fields'] as $field): ?>

                        <?php
                        $name = ($field['grids_fields_eval_cache_data']) ? $field['grids_fields_eval_cache_data'] : $field['fields_name'];
                        $classes = [];

                        if ($field['fields_draw_html_type'] === 'upload_image') {
                            $classes[] = 'firegui_width50';
                        }
                        ?>
                        <th class="<?php echo (!empty($classes)) ? implode(' ', $classes) : ''; ?>" <?php echo (!empty($field['grids_fields_width']) && is_numeric($field['grids_fields_width'])) ? "width='{$field['grids_fields_width']}%'" : ''; ?>
                            data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>"
                            data-name="<?php echo $name; ?>" <?php echo ($grid['grids']['grids_ajax'] && ($field['grids_fields_replace_type'] !== 'field' && ($field['grids_fields_eval_cache_type'] == '' or $field['grids_fields_eval_cache_type'] == 'no_cache') && empty($field['grids_fields_eval_cache_data']))) ? 'data-prevent-order' : ''; ?>><?php
                                                    e($field['grids_fields_column_name']); ?></th>

                    <?php endforeach; ?>

                    <?php if (grid_has_action($grid['grids'])): ?>
                        <th data-prevent-order>
                            <?php e('Actions'); ?>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php if ($grid_is_ajax == false): ?>
                    <?php foreach ($grid_data['data'] as $dato): ?>
                        <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                            <?php if ($has_bulk): ?>
                                <td>
                                    <input type="checkbox" class="js_bulk_check"
                                        value="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" />
                                </td>
                            <?php endif; ?>
                            <?php foreach ($grid['grids_fields'] as $field): ?>
                                <?php
                                if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
                                    if (!empty($this->datab->build_grid_cell($field, $dato))) {
                                        @$sums[$field['grids_fields_id']] += (float) ($this->datab->build_grid_cell($field, $dato));
                                    }
                                }
                                ?>
                                <td>
                                    <?php echo $this->datab->build_grid_cell($field, $dato); ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (grid_has_action($grid['grids'])): ?>
                                <td>
                                    <?php $this->load->view(
                                        'box/grid/actions',
                                        array(
                                            'links' => $grid['grids']['links'],
                                            'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                                            'row_data' => $dato,
                                            'grid' => $grid['grids'],
                                        )
                                    ); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

            <?php if ($has_totalable): ?>
                <tfoot>
                    <tr>
                        <?php if ($has_bulk): ?>
                            <th data-prevent-order data-name="_foo">
                                <input type="checkbox" class="js-bulk-select-all" />
                            </th>
                        <?php endif; ?>

                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <?php $name = ($field['grids_fields_eval_cache_type'] == 'query_equivalent') ? $field['grids_fields_eval_cache_data'] : $field['fields_name']; ?>
                            <th data-totalable="<?php echo ($field['grids_fields_totalable'] == DB_BOOL_TRUE) ? 1 : 0; ?>"
                                data-name="<?php echo $name; ?>" <?php if ($field['fields_draw_html_type'] === 'upload_image') {
                                       echo ' class="firegui_width50"';
                                   }
                                   ?>>
                                <!-- CALCOLARE TOTALE PER STAMPE PDF -->
                            </th>
                        <?php endforeach; ?>

                        <?php if (grid_has_action($grid['grids'])): ?>
                            <th data-prevent-order>&nbsp;</th>
                        <?php endif; ?>

                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>

        <?php if ($has_bulk or $has_exportable): ?>
            <div class="row">
                <?php if ($has_bulk): ?>
                    <div class="col-md-<?php echo $cols; ?>">
                        <select class="form-control js-bulk-action firegui_widthauto"
                            data-entity-name="<?php echo $grid['grids']['entity_name']; ?>">
                            <option value="" class="js-bulk-first-option" selected="selected"></option>

                            <?php foreach ($grid['grids']['links']['custom'] as $bulk_action): ?>
                                <?php if ($bulk_action['grids_actions_show'] == "table" || empty($bulk_action['grids_actions_show'])) {
                                    continue;
                                }
                                ?>
                                <option value="bulk_action" data-custom_code="<?php echo $bulk_action['grids_actions_html']; ?>"
                                    data-bulk_type="<?php echo $bulk_action['grids_actions_type']; ?>"
                                    data-form_id="<?php echo $bulk_action['grids_actions_form']; ?>" disabled="disabled"><?php echo $bulk_action['grids_actions_name']; ?></option>
                            <?php endforeach; ?>
                            <!-- old bulk actions (compatibility) -->
                            <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_edit' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit'): ?>
                                <option value="bulk_edit" data-form_id="<?php echo $grid['grids']['grids_bulk_edit_form']; ?>"
                                    disabled="disabled">Edit</option>
                            <?php endif; ?>
                            <?php if ($grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete' or $grid['grids']['grids_bulk_mode'] == 'bulk_mode_delete_edit'): ?>
                                <option value="bulk_delete" disabled="disabled">Delete</option>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($has_exportable): ?>
                    <?php $this->load->view('pages/layouts/grids/export_button', ['grid' => $grid, 'cols' => $cols]); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($grid['grids']['grids_inline_edit']): ?>
        <?php

        $form = $this->datab->get_form($grid['grids']['grids_inline_form'], null, $value_id);

        if (!$form || !$this->datab->can_write_entity($form['forms']['forms_entity_id'])) {

            return str_repeat('&nbsp;', 3) . t('You don\'t have permissions to write in this table');
        }
        ?>
        <div class="js_inline_hidden_form_container hidden" grid_id="<?php echo $grid['grids']['grids_id']; ?>">
            <?php
            $this->load->view(
                "pages/layouts/forms/form_{$form['forms']['forms_layout']}",
                array(
                    'form' => $form,
                    'ref_id' => $grid['grids']['grids_inline_form'],
                    'value_id' => null,
                ),
                false
            );
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>