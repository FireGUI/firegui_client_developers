<div class="action-list">
    <?php //debug($links); 
    ?>
    <?php if (isset($links['custom']) && $links['custom']) : ?>
        <?php
            // Filtra tutti i valori array e oggetto dall'array dati
            $row_data = array_filter(isset($row_data) ? $row_data : array(), function ($value) {
                return !is_array($value) && !is_object($value);
            });
            $replace_from = empty($row_data) ? array() : array_map(function ($data) {
                return '{' . $data . '}';
            }, array_keys($row_data));
            $replace_to = empty($row_data) ? array() : array_values($row_data);

            $replace_from[] = '{value_id}';
            $replace_to[] = $id;
            ?>
        <?php foreach ($links['custom'] as $custom_action) : ?>
            <?php if (!empty($custom_action['grids_actions_html']) && (empty($custom_action['grids_actions_type']) || 'custom' == $custom_action['grids_actions_type'])) : ?>
                <?php
                            ob_start();
                            eval(' ?> ' . str_replace($replace_from, $replace_to, $custom_action['grids_actions_html']) . ' <?php ');
                            $action = trim(ob_get_clean());
                            if (!$action) {
                                continue;
                            }
                            ?>
                <span <?php echo $custom_action['grids_actions_name'] ? "data-toggle='tooltip' title='{$custom_action['grids_actions_name']}'" : null; ?> style="color:white !important; margin-left: 3px;"><?php echo $action; ?></span>
            <?php else : ?>

                <?php
                            /*
            [grids_actions_id] =&gt; 2
            [grids_actions_grids_id] =&gt; 5
            [grids_actions_order] =&gt; 1
            [grids_actions_name] =&gt; edit_layout
            [grids_actions_html] =&gt; 
            [grids_actions_link] =&gt; 
            [grids_actions_layout] =&gt; 3
            [grids_actions_icon] =&gt; far fa-edit
            [grids_actions_mode] =&gt; default
            [grids_actions_type] =&gt; edit_layout
            [grids_actions_color] =&gt; rgb(255, 193, 7)
             */
                            $confirm = false;
                            if (empty($custom_action['grids_actions_link'])) {
                                if (!empty($custom_action['grids_actions_type']) && in_array($custom_action['grids_actions_type'], ['detail', 'edit_layout'])) {
                                    $url = "{base_url}main/layout/{$custom_action['grids_actions_layout']}/$id";
                                    if ('modal' == $custom_action['grids_actions_mode']) {
                                        $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id";
                                    } elseif ('modal_large' == $custom_action['grids_actions_mode']) {
                                        $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id?_size=large";
                                    }
                                } elseif (!empty($custom_action['grids_actions_type']) && 'delete' == $custom_action['grids_actions_type']) {
                                    $confirm = true;
                                    $url = "{base_url}db_ajax/generic_delete/{$grid['entity_name']}/$id";
                                } elseif (!empty($custom_action['grids_actions_type']) && 'edit_form' == $custom_action['grids_actions_type']) {
                                    $url = "{base_url}main/form/{$custom_action['grids_actions_form']}/$id";
                                    if ('modal' == $custom_action['grids_actions_mode']) {
                                        $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id";
                                    } elseif ('modal_large' == $custom_action['grids_actions_mode']) {
                                        $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_size=large";
                                    }
                                } else {
                                    die('TODO type!!!');
                                }
                            } else {
                                if (stripos($custom_action['grids_actions_link'], '{value_id}')) {
                                    $url = str_ireplace('{value_id}', $id, $custom_action['grids_actions_link']);
                                } else {
                                    $url = "{$custom_action['grids_actions_link']}/$id";
                                }
                            }

                            $url = str_replace('{base_url}', base_url(), $url);

                            ?>
                <span <?php echo $custom_action['grids_actions_name'] ? "data-toggle='tooltip' title='{$custom_action['grids_actions_name']}'" : null; ?>>
                    <a class="js-action_button btn btn-xs btn-primary<?php if ($confirm) : ?> js_confirm_button js_link_ajax<?php endif; ?> <?php if (in_array($custom_action['grids_actions_mode'], ['modal', 'modal_large'])) echo 'js_open_modal'; ?>" href="<?php echo $url; ?>" <?php if ($custom_action['grids_actions_mode'] == 'new_tab') : ?>target="_blank" <?php endif; ?>style="background-color: <?php echo ($custom_action['grids_actions_color']) ?: '#CCCCCC'; ?>" <?php if ($confirm) : ?> data-confirm-text="<?php e('Sei sicuro di voler eliminare questo record?'); ?>" data-toggle="tooltip" <?php endif; ?>>
                        <span class="<?php echo $custom_action['grids_actions_icon']; ?>" style="color:white !important;"></span>
                    </a>
                </span>

            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($links['view']) && $links['view']) : ?>
        <a href="<?php echo $links['view'] . $id; ?>" class="btn btn-success btn-xs <?php if (!empty($links['view_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="<?php e('Visualizza'); ?>">
            <span class="fas fa-search-plus" style="color:white !important;"></span>
        </a>
    <?php endif; ?>

    <?php if (isset($links['edit']) && $links['edit']) : ?>
        <a href="<?php echo $links['edit'] . $id; ?>" class="btn bg-purple btn-xs <?php if (!empty($links['edit_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="<?php e('Modifica'); ?>">
            <span class="fas fa-edit" style="color:white !important;"></span>
        </a>
    <?php endif; ?>

    <?php if (isset($links['delete']) && $links['delete']) : ?>
        <a href="<?php echo $links['delete'] . $id; ?>" data-confirm-text="<?php e("Sei sicuro di voler eliminare questo record?"); ?>" class="btn btn-danger btn-xs js_confirm_button js_link_ajax <?php if (!empty($links['delete_modal'])) echo 'js_open_modal'; ?>" data-toggle="tooltip" title="<?php e('Elimina'); ?>">
            <span class="fas fa-times" style="color:white !important;"></span>
        </a>
    <?php endif; ?>

</div>