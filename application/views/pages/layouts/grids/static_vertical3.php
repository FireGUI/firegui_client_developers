
<?php if (isset($grid_data['data'])): ?>
    <?php foreach ($grid_data['data'] as $dato): ?>
        <div class="row">
            <dl <?php echo "id='grid_{$grid['grids']['grids_id']}'"; ?> class="dl-horizontal dl-horizontal-compact static-vertical-grid" >
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <?php 
                    $_field = $this->datab->build_grid_cell($field, $dato);
                    if(!empty($_field)): ?>
                        <dt class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $field['fields_draw_label']; ?>:</dt>
                        <dd class="<?php echo "js-grid-field-{$field['fields_id']}" ?>"><?php echo $_field; ?></dd>
                        <hr>
                    <?php endif; ?>
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
