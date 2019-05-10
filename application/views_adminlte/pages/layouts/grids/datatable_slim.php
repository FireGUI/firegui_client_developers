<?php if (empty($grid_data['data'])): ?>
    <p>Nessun dato disponibile</p>
<?php else: ?>
    <div class="table-scrollable table-scrollable-borderless">
        <table <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="table table-striped table-bordered table-condensed table-hover js_datatable_slim" <?php if ($grid['grids']['grids_order_by']) echo 'data-prevent-order' ?>>
            <thead>
                <tr>
                    <?php foreach ($grid['grids_fields'] as $field): ?>
                        <th <?php if ($field['fields_draw_html_type'] === 'upload_image') echo 'style="width:50px;"'; ?>><?php echo $field['grids_fields_column_name']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <tr data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                        <?php foreach ($grid['grids_fields'] as $field): ?>
                            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
