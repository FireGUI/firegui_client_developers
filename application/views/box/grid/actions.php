<ul class="list-inline list-actions" style="min-width:30px;margin: 0 -8px;">
    
    <?php if(isset($links['custom']) && $links['custom']): ?>
        <?php 
        // Filtra tutti i valori array e oggetto dall'array dati
        $row_data = array_filter(isset($row_data)? $row_data: array(), function($value) { return !is_array($value) && !is_object($value); });
        $replace_from = empty($row_data)? array(): array_map(function($data) { return '{'.$data.'}'; }, array_keys($row_data));
        $replace_to = empty($row_data)? array(): array_values($row_data);
        
        $replace_from[] = '{value_id}';
        $replace_to[] = $id;
        ?>
        <?php foreach($links['custom'] as $custom_action): ?>
            <?php 
            ob_start();
            eval(' ?> '.str_replace($replace_from, $replace_to, $custom_action['grids_actions_html']).' <?php ');
            $action = trim(ob_get_clean());
            if (!$action) {
                continue;
            }
            ?>
            <li <?php echo $custom_action['grids_actions_name']? "data-toggle='tooltip' title='{$custom_action['grids_actions_name']}'": null; ?>><?php echo $action; ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
    
    
    
    
    
    <?php if(isset($links['view']) && $links['view']): ?>
        <li>
            <a href="<?php echo $links['view'].$id; ?>" class="btn blue btn-xs <?php if(!empty($links['view_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="Visualizza">
                <span class="icon-zoom-in"></span>
            </a>
        </li>
    <?php endif; ?>

    <?php if(isset($links['edit']) && $links['edit']): ?>
        <li>
            <a href="<?php echo $links['edit'].$id; ?>" class="btn purple btn-xs <?php if(!empty($links['edit_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="Modifica">
                <span class="icon-pencil"></span>
            </a>
        </li>
    <?php endif; ?>

    <?php if(isset($links['delete']) && $links['delete']): ?>
        <li>
            <a href="<?php echo $links['delete'].$id; ?>" data-confirm-text="<?php e("l'elemento selezionato verrà eliminato. Sei sicuro?"); ?>" class="btn btn-danger btn-xs js_confirm_button js_link_ajax <?php if(!empty($links['delete_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="Elimina">
                <span class="icon-remove"></span>
            </a>
        </li>
    <?php endif; ?>
</ul>