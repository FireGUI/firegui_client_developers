<div class="scroller list-grid" data-always-visible="0" data-rail-visible="0">
    <ul class="feeds">
        <?php foreach ($grid_data['data'] as $dato) : ?>
        <li>
            <div class="col1">
                <div class="cont" style="border-bottom: solid 1px #ccc;padding-bottom: 15px;margin-bottom: 15px;">
                    <div class="cont-col2">
                        <div class="desc">
                            <?php
                                if (isset($grid['replaces']['title'])) {
                                    echo $this->datab->build_grid_cell($grid['replaces']['title'], $dato);
                                }
                                ?>
                            <?php if (isset($grid['replaces']['date'])) : ?>
                            <small class="date">
                                <?php echo $this->datab->build_grid_cell($grid['replaces']['date'], $dato); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col2">
                <?php if (grid_has_action($grid['grids'])) : ?>
                <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>