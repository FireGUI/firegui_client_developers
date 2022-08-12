<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);

$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$is_sub_grid = (empty($is_sub_grid)) ? false : $is_sub_grid; //TODO: usare questo parametro per rendere più "bellina" la grid quando è dentro un tr td di un altra grid madre e filtrare i risultati in base alla row madre

$has_totalable = false;
foreach ($grid['grids_fields'] as $field) {
    if ($field['grids_fields_totalable'] == DB_BOOL_TRUE) {
        $has_totalable = true;
        break;
    }
}

$cols = ($has_bulk && $has_exportable) ? 6 : 12;
?>
<a class="js_datatable_inline_add btn btn-success btn-xs pull-right" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>"><?php e('New row'); ?></a>
<div class="clearfix"></div>
<br />

<div class="___table-scrollable table-scrollable-borderless">
    <table data-totalable="<?php echo $has_totalable ? 1 : 0; ?>" data-where_append="<?php echo (empty($where)) ? '' : $where; ?>" data-parent_field="<?php echo (empty($parent_field)) ? '' : $parent_field; ?>" data-parent_id="<?php echo (empty($parent_id)) ? '' : $parent_id; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover table-middle js_ajax_datatable nowrap js_datatable_new_inline js_fg_grid_<?php echo $grid['grids']['entity_name']; ?> <?php echo $grid['grids']['grids_append_class']; ?>" data-value-id="<?php echo $value_id; ?>" data-entity="<?php echo $grid['grids']['entity_name']; ?>" data-form="<?php echo $grid['grids']['grids_inline_form']; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                <th data-prevent-order data-name="_foo">
                    <input type="checkbox" class="js-bulk-select-all" />
                </th>
                <?php endif; ?>

                <?php foreach ($grid['grids_fields'] as $field) : ?>
                <?php $name = ($field['grids_fields_eval_cache_data']) ? $field['grids_fields_eval_cache_data'] : $field['fields_name']; ?>
                <th data-name="<?php echo $name; ?>" <?php if ($field['fields_draw_html_type'] === 'upload_image') echo ' class="firegui_width50"'; ?><?php echo ($field['grids_fields_replace_type'] !== 'field' && !$field['grids_fields_eval_cache_data']) ? ' data-prevent-order ' : ''; ?>><?php e($field['grids_fields_column_name']);  ?></th>

                <?php endforeach; ?>
                <?php if ($grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
                <th data-prevent-order>&nbsp;</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody></tbody>
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
                <?php if ($grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
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
<?php

$form = $this->datab->get_form($grid['grids']['grids_inline_form'], null, $value_id);

if (!$form || !$this->datab->can_write_entity($form['forms']['forms_entity_id'])) {

    return str_repeat('&nbsp;', 3) . 'Non disponi dei permessi sufficienti per modificare i dati.';
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