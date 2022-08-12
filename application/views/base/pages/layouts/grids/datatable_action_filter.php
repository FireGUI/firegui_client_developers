<?php
/*
 * Filter
 */
$_filter_prefix = "grid_filter[{$grid['grids']['grids_id']}]";
$_filter_data = $this->input->post('grid_filter');
$_collapser_id = "grid_{$grid['grids']['grids_id']}_filter";
$_fields_type_to_skip = array(
    'upload_image',
    'input_hidden',
    'input_password',
    'date_range',
    'upload',
    'textarea',
    'map',
    'color',
    'multi_upload',
);
?>

<a data-toggle="collapse" href="#<?php echo $_collapser_id; ?>">Filtra dati</a>
<div <?php echo "id='{$_collapser_id}'"; ?> class="<?php echo (empty($_filter_data[$grid['grids']['grids_id']]) ? 'collapse' : 'in'); ?>">
    <form action="<?php echo base_url(uri_string()); ?>" method="POST" class="filter_form">
        <?php add_csrf(); ?>
        <div class="row">
            <?php $i = 0; ?>
            <?php foreach ($grid['grids_fields'] as $k => $field) : ?>
            <?php if (in_array($field['fields_draw_html_type'], $_fields_type_to_skip)) continue; ?>
            <div class="col-md-6">
                <?php
                    // non ha senso filtrare su upload vari e textarea
                    $value = $_filter_data[$grid['grids']['grids_id']][$field['fields_name']];
                    $field['fields_name'] = "{$_filter_prefix}[{$field['fields_name']}]";
                    $field['support_data'] = $this->datab->get_support_data($field['fields_ref']);
                    $this->load->view("box/form_fields/{$field['fields_draw_html_type']}", array('field' => $field, 'value' => $value));
                    ?>
            </div>
            <?php if ($i++ % 2) echo "<div class='clearfix'></div>" ?>
            <?php endforeach; ?>
        </div>

        <div class="clearfix"></div>
        <div class="col-md-6">
            <input type="submit" class="btn" value="<?php e("filtra"); ?>" />
        </div>

    </form>
</div>
<div class="clearfix"></div>
<br />


<?php if (empty($grid_data['data'])) : ?>
<p><?php e('No records found'); ?></p>
<?php else : ?>
<table <?php echo "id='grid_{$grid['grids']['grids_id']}'" ?> class="table table-striped table-bordered table-hover nowrap js_ajax_datatable <?php echo $grid['grids']['grids_append_class']; ?>" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field) : ?>
            <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'class="firegui_width50"'; ?>><?php e($field['grids_fields_column_name']);  ?></th>
            <?php endforeach; ?>

            <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
            <th data-prevent-order><?php e('Actions'); ?></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($grid_data['data'] as $dato) : ?>
        <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
            <?php endforeach; ?>
            <?php if (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) : ?>
            <td>
                <?php $this->load->view('box/grid/actions', array(
                                'links' => $grid['grids']['links'],
                                'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                                'row_data' => $dato
                            )); ?></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>