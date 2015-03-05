<table class="table table-striped table-bordered table-hover table-hover table-responsive-scrollable" id="grid_<?php echo $grid['grids']['grids_id'] ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field): ?>
                <th><?php echo $field['fields_draw_label']; ?></th>
            <?php endforeach; ?>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($grid_data['data'])): ?>
            <?php foreach ($grid_data['data'] as $key => $dato): ?>
                <tr class="odd gradeX">
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <?php /*<td><?php $this->load->view('box/grid/td', array('field' => $field, 'dato' => $dato)); ?></td>*/ ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>
                        
                    <?php
                    if (empty($sub_grid) || empty($grid_data['sub_grid_data']['data'])) {
                        $sub_data = array();
                    } else {
                        $parent_id = $dato[$grid_data['entity']['entity_name'] . "_id"];
                        $relation_field = $sub_grid['grid_relation_field'];

                        $sub_data = array_filter($grid_data['sub_grid_data']['data'], function ($sub_dato) use($parent_id, $relation_field) {
                            return $sub_dato[$relation_field] == $parent_id;
                        });
                    }
                    ?>
                        
                    <td <?php if(! empty($sub_data)): ?>style="border-bottom: 1px solid #000000 !important;" rowspan="2"<?php endif; ?>>
                        <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                        <?php if ( ! empty($sub_data)): ?>
                            <a class="btn btn-primary btn-xs pull-right" data-toggle="collapse" href="#<?php echo ($collapse_id = "collapser{$grid['grids']['grids_id']}_{$sub_grid['grids']['grids_id']}_{$key}"); ?>">
                                <span data-toggle="tooltip" class="caret"></span>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php /* INIZIO SUB ENTITY */ ?>
                <?php if ( ! empty($sub_data)): ?>
                    <tr>
                        <td colspan="<?php echo count($grid['grids_fields']); ?>" style="padding: 0; border-bottom: 1px solid #000000 !important;">
                            <table id="<?php echo $collapse_id; ?>" class="collapse table table-bordered table-condensed table-full-width" style="margin-bottom: 0!important;">
                                <thead>
                                    <tr>
                                        <?php foreach ($sub_grid['grids_fields'] as $field): ?>
                                            <th style="background-color: #999999; color: #ffffff"><?php echo $field['fields_draw_label']; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sub_data as $sub_dato): ?>
                                        <tr class="odd gradeX">
                                            <?php foreach ($sub_grid['grids_fields'] as $field): ?>
                                                <?php /*<td><?php $this->load->view('box/grid/td', array('field' => $field, 'dato' => $sub_dato)); ?></td>*/ ?>
                                            <td><?php echo $this->datab->build_grid_cell($field, $sub_dato); ?></td>
                                            <?php endforeach; ?>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php /* Fine Sub entity */ ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

