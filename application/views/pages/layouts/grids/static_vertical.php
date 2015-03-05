
<?php if (isset($grid_data['data'])): ?>
    <?php foreach ($grid_data['data'] as $dato): ?>
        <div class="row">
            <dl class="dl-horizontal static-vertical-grid" >
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <dt><?php echo $field['fields_draw_label']; ?>:</dt>
                    <dd><?php echo $this->datab->build_grid_cell($field, $dato); ?></dd>
                <?php endforeach; ?>
                <?php if (grid_has_action($grid['grids'])): ?>
                    <dt class="dl-actions-label">Azioni disponibili</dt>
                    <dd class="dl-actions">
                        <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                    </dd>
                <?php endif; ?>
            </dl>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
