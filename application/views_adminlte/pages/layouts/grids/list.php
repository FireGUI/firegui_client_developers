<?php

if (grid_has_action($grid['grids']) && isset($grid['grids']['links']['custom']) && $grid['grids']['links']['custom']) {
    $links = $grid['grids']['links'];
    $preload_colors = ['CCCCCC' => '#CCCCCC'];
    foreach ($links['custom'] as $custom_action) {
        $preload_colors[md5($custom_action['grids_actions_color'])] = $custom_action['grids_actions_color'];
    }
    $preload_colors = array_unique($preload_colors);
    $preload_colors = array_filter($preload_colors, 'strlen');

    $data['background-colors'] = $preload_colors;

    $this->layout->addDinamicStylesheet($data, "grid_{$links['custom'][0]['grids_actions_grids_id']}.css");
}
?>

<div class="scroller list-grid" data-always-visible="0" data-rail-visible="0">
    <ul class="feeds">
        <?php foreach ($grid_data['data'] as $dato) : ?>
            <li>
                <div class="col1">
                    <div class="cont">
                        <div class="cont-col1">
                            <div class="label label-sm label-info">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
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