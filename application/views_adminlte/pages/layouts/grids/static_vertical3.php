
<?php if (isset($grid_data['data'])): ?>
    <?php foreach ($grid_data['data'] as $dato): ?>
        
            <dl <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" class="dl-horizontal dl-horizontal-compact static-vertical-grid" >
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <?php 
                    $_field = $this->datab->build_grid_cell($field, $dato);
                    if($_field): ?>
                        <dt class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $field['grids_fields_column_name']; ?>:</dt>
                        <dd class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></dd>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (grid_has_action($grid['grids'])): ?>
                    <dt class="dl-actions-label">Azioni disponibili</dt>
                    <dd class="dl-actions">
                        <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                    </dd>
                <?php endif; ?>
            </dl>
        
    <?php endforeach; ?>
<?php endif; ?>
