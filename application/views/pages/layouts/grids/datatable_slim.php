<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>
    <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-bordered table-condensed table-hover table-responsive-scrollable js_datatable_slim" <?php if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
        <thead>
            <tr>
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['fields_draw_label']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grid_data['data'] as $dato): ?>
                <tr class="odd gradeX" data-id="<?php echo $dato[$grid_data['entity']['entity_name'] . "_id"]; ?>">
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
