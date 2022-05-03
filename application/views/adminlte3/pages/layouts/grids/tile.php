<?php
$itemId = "chats{$grid['grids']['grids_id']}";
$items = array();

if (isset($grid_data['data'])) {
    foreach ($grid_data['data'] as $x => $dato) {

        $items[$dato[$grid['grids']['entity_name'] . "_id"]] = array(
            'thumb' => isset($grid['replaces']['thumbnail']) ? base_url_uploads("uploads/{$dato[$grid['replaces']['thumbnail']['fields_name']]}") : null,
            'nome' => isset($grid['replaces']['progetto']) ? $this->datab->build_grid_cell($grid['replaces']['progetto'], $dato) : null,
            'consegnaDemo' => isset($grid['replaces']['consegnaDemo']) ? $this->datab->build_grid_cell($grid['replaces']['consegnaDemo'], $dato) : null,
            'consegna' => isset($grid['replaces']['consegna']) ? $this->datab->build_grid_cell($grid['replaces']['consegna'], $dato) : null,
            'stato' => isset($grid['replaces']['stato']) ? $this->datab->build_grid_cell($grid['replaces']['stato'], $dato) : null,
        );
    }
}

/*
 * verde  : task tutte completate
 * blu    : task aperte
 * giallo : almeno una in lavorazione
 */
?>


<div class="row">
    <?php foreach ($items as $id => $item) : ?>
        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3">
            <div class="dashboard-stat blue big-tile-list-item">
                <a href="<?php echo (empty($grid['grids']['links']['view']) ? 'javascript:void(0);' : $grid['grids']['links']['view'] . $id) ?>">
                    <div class="details">
                        <div class="number"><?php echo $item['nome']; ?></div>
                        <p class="text-center">
                            <?php if ($item['thumb']) : ?>
                                <img src="<?php echo $item['thumb']; ?>" width="140" />
                            <?php endif; ?>
                        </p>
                        <?php if ($item['consegna']) : ?>
                            <div class="desc"><?php e('Consegna prevista:'); ?> <?php echo $item['consegna']; ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="more">Stato: <?php echo $item['stato']; ?> <i class="m-icon-swapright m-icon-white"></i></span>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>