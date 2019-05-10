<a class="js_datatable_inline_add btn btn-success btn-xs pull-right" data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">Nuovo valore</a>
<div class="clearfix"></div>
<br/>

<table data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>" <?php echo "id='grid_{$grid['grids']['grids_id']}'" ?> default-limit="<?php echo (defined('DEFAULT_GRID_LIMIT'))?DEFAULT_GRID_LIMIT:10; ?>" class="table table-striped table-bordered table-hover table-middle js_ajax_datatable js_datatable_new_inline <?php echo $grid['grids']['grids_append_class']; ?>" data-value-id="<?php echo $value_id; ?>" data-entity="<?php echo $grid['grids']['entity_name']; ?>" data-form="<?php echo $grid['grids']['grids_inline_form']; ?>" <?php // if($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?> data-grid-id="<?php echo $grid['grids']['grids_id']; ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th data-name="<?php echo $field['fields_name']; ?>"><?php echo $field['grids_fields_column_name']; ?></th>
            <?php endforeach; ?>

            <th data-prevent-order>&nbsp;</th>
            
        </tr>
    </thead>
    <tbody></tbody>
</table>

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
$this->load->view("pages/layouts/forms/form_{$form['forms']['forms_layout']}", 
        array(
            'form' => $form, 
            'ref_id' => $grid['grids']['grids_inline_form'], 
            'value_id' => null, 
            //'layout_data_detail' => $layoutEntityData
        ), false
        );
?>
</div>