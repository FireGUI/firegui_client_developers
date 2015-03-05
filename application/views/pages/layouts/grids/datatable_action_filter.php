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
<div id="<?php echo $_collapser_id; ?>" class="<?php echo (empty($_filter_data[$grid['grids']['grids_id']])? 'collapse': 'in'); ?>">
    <form action="<?php echo base_url(uri_string()); ?>" method="POST" class="filter_form">
        <div class="row">
            <?php $i = 0; ?>
            <?php foreach($grid['grids_fields'] as $k=>$field): ?>
                <?php if(in_array($field['fields_draw_html_type'], $_fields_type_to_skip)) continue; ?>
                <div class="col-md-6">
                    <?php
                    // non ha senso filtrare su upload vari e textarea
                    $value = $_filter_data[$grid['grids']['grids_id']][$field['fields_name']];
                    $field['fields_name'] = "{$_filter_prefix}[{$field['fields_name']}]";
                    $field['support_data'] = $this->datab->get_support_data($field['fields_ref']);
                    $this->load->view("box/form_fields/{$field['fields_draw_html_type']}", array('field' => $field, 'value' => $value));
                    ?>
                </div>
                <?php if($i++ % 2) echo "<div class='clearfix'></div>" ?>
            <?php endforeach; ?>
        </div>

        <div class="clearfix"></div>
        <div class="col-md-6">
            <input type="submit" class="btn" value="<?php e("filtra"); ?>" />
        </div>

    </form>
</div>
<div class="clearfix"></div>
<br/>


<?php 
/*
 * Table
 */
?>
<table class="table table-striped table-bordered table-hover js_ajax_datatable" id="grid_<?php echo $grid['grids']['grids_id'] ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th <?php if($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label'];  ?></th>
            <?php endforeach; ?>
                
            <?php if(grid_has_action($grid['grids'])): ?>
                <th>Action</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($grid_data['data'])): ?>
            <?php foreach ($grid_data['data'] as $dato): ?>
                <tr class="odd gradeX">
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <?php /*<td><?php $this->load->view('box/grid/td', array('field' => $field, 'dato' => $dato)); ?></td>*/ ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>
                    <?php 
                    $links = array(
                        'view' => ($grid['grids']['grids_view_layout'])? base_url("main/layout/{$grid['grids']['grids_view_layout']}/{$dato[$grid_data['entity']['entity_name']."_id"]}"): $grid['grids']['grids_view_link'],
                        'edit' => ($grid['grids']['grids_edit_layout'])? base_url("main/layout/{$grid['grids']['grids_edit_layout']}/{$dato[$grid_data['entity']['entity_name']."_id"]}"): $grid['grids']['grids_edit_link'],
                        'delete' => '#'
                    );
                    ?>
                    <?php if(grid_has_action($grid['grids'])): ?>
                    <td>
                        <ul class="list-inline">
                            <li>
                                <a href="<?php echo $links['view']; ?>" class="btn blue btn-xs">
                                    <span class="icon-zoom-in"></span>
                                </a>
                            </li>
                            
                            <li>
                                <a href="<?php echo $links['edit']; ?>" class="btn purple btn-xs">
                                    <span class="icon-pencil"></span>
                                </a>
                            </li>
                            
                            <li>
                                <a href="<?php echo $links['delete']; ?>" class="btn btn-danger btn-xs">
                                    <span class="icon-remove"></span>
                                </a>
                            </li>
                        </ul>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
