<div class="js_action_inline action_inline">
    <?php if (isset($links['custom']) && $links['custom']): ?>
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
        <?php foreach ($links['custom'] as $key => $custom_action): ?>

            <?php if (!$this->conditions->accessible('grids_actions', $custom_action['grids_actions_id'], $id, $row_data) || $custom_action['grids_actions_show'] == 'bulk') {
                continue;
            } ?>

            <?php if (!empty($custom_action['grids_actions_html']) && (empty($custom_action['grids_actions_type']) || 'custom' == $custom_action['grids_actions_type'])): ?>
                <?php
                ob_start();
                eval(' ?> ' . str_replace($replace_from, $replace_to, $custom_action['grids_actions_html']) . ' <?php ');
                $action = trim(ob_get_clean());
                if (!$action) {
                    continue;
                }
                ?>
                <?php if ($key != 0): ?> |
                <?php endif; ?>

                <?php echo $action; ?>
           

            <?php else: ?>

                <?php
                $confirm = false;
                if (empty($custom_action['grids_actions_link'])) {
                    if (!empty($custom_action['grids_actions_type']) && in_array($custom_action['grids_actions_type'], ['detail', 'edit_layout'])) {
                        $url = "{base_url}main/layout/{$custom_action['grids_actions_layout']}/$id";
                        if ('modal' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id";
                        } elseif ('modal_large' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id?_size=large";
                        } elseif ('modal_extra' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id?_size=extra";
                        } elseif ('side_view' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_layout']}/$id?_mode=side_view";
                        }
                    } elseif (!empty($custom_action['grids_actions_type']) && 'delete' == $custom_action['grids_actions_type']) {
                        if ($skip_delete) {
                            continue;
                        } else {
                            $confirm = true;
                            if (!empty($grid['grids']['entity_name'])) {
                                $url = "{base_url}db_ajax/generic_delete/{$grid['grids']['entity_name']}/$id";
                            } else {
                                $url = "{base_url}db_ajax/generic_delete/{$grid['entity_name']}/$id";
                            }
                        }
                    } elseif (!empty($custom_action['grids_actions_type']) && 'edit_form' == $custom_action['grids_actions_type']) {
                        $url = "{base_url}main/form/{$custom_action['grids_actions_form']}/$id";
                        if ('modal' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id";
                        } elseif ('modal_large' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_size=large";
                        } elseif ('modal_extra' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_size=extra";
                        } elseif ('side_view' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/layout_modal/{$custom_action['grids_actions_form']}/$id?_mode=side_view";
                        }
                    } elseif ( 'run_php_code' == $custom_action['grids_actions_type']) {
                        //debug('Gestire il ritorno come se fosse un submit ajax...',true);
                        $url = "{base_url}db_ajax/run_php_code/{$custom_action['grids_actions_id']}/$id";
                    } else {
                        $url = '';
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
                <?php if ($key != 0): ?> |
                <?php endif; ?>

                <a class="<?php if ($confirm): ?> js_confirm_button js_link_ajax<?php endif; ?> <?php if (in_array($custom_action['grids_actions_mode'], ['modal', 'modal_large', 'modal_extra', 'side_view'])): ?> js_open_modal <?php endif; ?><?php if ('run_php_code' == $custom_action['grids_actions_type']): ?> js_link_ajax<?php endif; ?>"
                    href="<?php echo $url; ?>" <?php if ($custom_action['grids_actions_mode'] == 'new_tab'): ?>target="_blank" <?php endif; ?>style="color: <?php echo ($custom_action['grids_actions_color']) ?: '#CCCCCC'; ?>" <?php if ($confirm): ?> data-confirm-text="<?php e('Are you sure to delete this record?'); ?>" data-toggle="tooltip" <?php endif; ?>
                    <?php if (in_array($custom_action['grids_actions_mode'], ['modal', 'modal_large', 'modal_extra'])): ?>
                        data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" <?php endif; ?>>
                    <?php echo t($custom_action['grids_actions_name']); ?>
                </a>


            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>


</div>