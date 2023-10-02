<div class="action-list">
    <a href="#" class="js_edit btn bg-purple btn-grid-action-s" data-id="<?php echo $id; ?>">
        <span class="fas fa-edit"></span>
    </a>

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
        <?php foreach ($links['custom'] as $custom_action): ?>
            <?php if (!$this->conditions->accessible('grids_actions', $custom_action['grids_actions_id'], $id, $row_data)) {
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
                <span class="custom-action" <?php echo $custom_action['grids_actions_name'] ? "data-toggle='tooltip' title='{$custom_action['grids_actions_name']}'" : null; ?>>
                    <?php echo $action; ?>
                </span>
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
                        $confirm = true;
                        $url = "{base_url}db_ajax/generic_delete/{$grid['entity_name']}/$id";
                    } elseif (!empty($custom_action['grids_actions_type']) && 'edit_form' == $custom_action['grids_actions_type']) {
                        $url = "{base_url}main/form/{$custom_action['grids_actions_form']}/$id";
                        if ('modal' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id";
                        } elseif ('modal_large' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_size=large";
                        } elseif ('modal_extra' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_size=extra";
                        } elseif ('side_view' == $custom_action['grids_actions_mode']) {
                            $url = "{base_url}get_ajax/modal_form/{$custom_action['grids_actions_form']}/$id?_mode=side_view";
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
                    <?php
                    $btn_classes = '';
                    $btn_href = $url;
                    $btn_attrs = '';

                    if ($custom_action['grids_actions_type'] == 'delete') {
                        $btn_href = '#';
                        $btn_classes .= 'js_delete ';
                        $confirm = false;
                        $btn_attrs .= 'data-id="' . $id . '" ';
                    } /*elseif ($custom_action['grids_actions_type'] == 'edit_form') {
                   $btn_href = '#';
                   $btn_classes .= 'js_edit ';
                   $confirm = false;
                   $btn_attrs .= 'data-id="' . $id . '" ';
               }*/else {
                        //$btn_classes .= 'js_link_ajax '; // Michael - 2022 - Commento questo perchè qualsiasi custom action che viene messa gli viene appenso js_link_ajax, il che è sbagliato
                    }

                    if (in_array($custom_action['grids_actions_mode'], ['modal', 'modal_large', 'modal_extra', 'side_view'])) {
                        $btn_classes .= 'js_open_modal ';
                    } else {
                        // todo
                    }

                    if ($custom_action['grids_actions_mode'] == 'new_tab') {
                        $btn_attrs .= 'target="_blank" ';
                    } else {
                        // todo
                    }

                    if ($confirm) {
                        $btn_classes .= 'js_confirm_button ';
                        $btn_attrs .= 'data-confirm-text="' . t('Are you sure to delete this record?') . '" data-toggle="tooltip" ';
                    } else {
                        // todo
                    }

                    $btn_style = 'style="background-color: ' . ($custom_action['grids_actions_color'] ?? '#CCCCCC') . '"';

                    ?>

                    <a class="js-action_button btn btn-grid-action-s btn-primary <?php echo $btn_classes; ?>"
                        href="<?php echo $btn_href; ?>" <?php echo $btn_attrs, ' ', $btn_style; ?>>
                        <span class="<?php echo $custom_action['grids_actions_icon']; ?>"></span>
                    </a>
                </span>

            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>



</div>