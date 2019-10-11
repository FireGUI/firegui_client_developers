<?php
$has_bulk = !empty($grid['grids']['grids_bulk_mode']);

$has_exportable = ($grid['grids']['grids_exportable'] == DB_BOOL_TRUE);

$is_sub_grid = (empty($is_sub_grid)) ? false : $is_sub_grid; //TODO: usare questo parametro per rendere più "bellina" la grid quando è dentro un tr td di un altra grid madre e filtrare i risultati in base alla row madre

//debug($where);

$cols = ($has_bulk && $has_exportable) ? 6 : 12;
?>
<a class="js_datatable_inline_add btn btn-success btn-xs pull-right" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">Nuovo valore</a>
<div class="clearfix"></div>
<br />

<div class="___table-scrollable table-scrollable-borderless">
    <table data-where_append="<?php echo (empty($where)) ? '' : $where; ?>" data-parent_field="<?php echo (empty($parent_field)) ? '' : $parent_field; ?>" data-parent_id="<?php echo (empty($parent_id)) ? '' : $parent_id; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT')) ? DEFAULT_GRID_LIMIT : 10; ?>" class="table table-striped table-bordered table-hover table-middle js_ajax_datatable js_datatable_new_inline <?php echo $grid['grids']['grids_append_class']; ?>" data-value-id="<?php echo $value_id; ?>" data-entity="<?php echo $grid['grids']['entity_name']; ?>" data-form="<?php echo $grid['grids']['grids_inline_form']; ?>" <?php // if($grid['grids']['grids_order_by']) echo 'data-prevent-order' 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ?> data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
        <thead>
            <tr>
                <?php if ($has_bulk) : ?>
                    <th data-prevent-order data-name="_foo">
                        <input type="checkbox" class="js-bulk-select-all" />
                    </th>
                <?php endif; ?>

                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <th data-name="<?php echo $field['fields_name']; ?>"><?php echo $field['grids_fields_column_name']; ?></th>
                <?php endforeach; ?>

                <th data-prevent-order>&nbsp;</th>

            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <?php if ($has_bulk or $has_exportable) : ?>
        <div class="row">
            <?php if ($has_bulk) : ?>
                <div class="col-md-<?php echo $cols; ?>">
                    <select class="form-control js-bulk-action" data-entity-name="<?php echo $grid['grids']['entity_name']; ?>" style="width: auto;">
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
$form = $this->datab->get_form($grid['grids']['grids_inline_form'], null);

if (!$this->datab->can_write_entity($form['forms']['forms_entity_id'])) {
    return str_repeat('&nbsp;', 3) . 'Non disponi dei permessi sufficienti per modificare i dati.';
}

//Forzo tutti i campi a essere sulla stessa row...
//$colonne = count($grid['grids_fields'])+1;
//$col_attr = ceil(12/$colonne);
//
//
//foreach ($form['forms_fields'] as $key => $field) {
//    $form['forms_fields'][$key]['size'] = $col_attr;
//}

//debug($form,true);
?>
<div class="js_inline_hidden_form_container hidden" grid_id="<?php echo $grid['grids']['grids_id']; ?>">
    <?php
    $this->load->view(
        "pages/layouts/forms/form_{$form['forms']['forms_layout']}",
        array(
            'form' => $form,
            'ref_id' => $grid['grids']['grids_inline_form'],
            'value_id' => null,
            //'layout_data_detail' => $layoutEntityData
        ),
        false
    );
    ?>
</div>