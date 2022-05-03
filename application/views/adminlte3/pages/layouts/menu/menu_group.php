<div class="btn-group sortableMenu">
    <?php foreach ($data as $link) : ?>
        <?php if (count($link['submenu']) > 0) : ?>
            <div class="btn-group">
                <button type="button" class="btn dropdown-toggle default <?php echo $link['menu_css_class']; ?> menu-<?php echo $link['menu_id'] ?>" data-toggle="dropdown">
                    <?php echo $link['menu_icon_class'] ? sprintf('<i class="%s"></i>', $link['menu_icon_class']) : ''; ?>
                    <?php e($link['menu_label']); ?> <i class="fas fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($link['submenu'] as $sub_menu) : ?>
                        <li>

                            <a href="<?php echo $this->datab->generate_menu_link($sub_menu, $value_id, $layout_data_detail); ?>" <?php echo ($link['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?> class="<?php if ($link['menu_modal'] == DB_BOOL_TRUE) echo 'js_open_modal'; ?> menu-<?php echo $sub_menu['menu_id'] ?>" <?php if ($link['menu_modal'] == DB_BOOL_TRUE) : ?>data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" <?php endif; ?>>
                                <?php e($sub_menu['menu_label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (($href = $this->datab->generate_menu_link($link, $value_id, $layout_data_detail))) : ?>
            <a data-id="<?php echo $link['menu_id']; ?>" data-layout-id="<?php echo (!empty($link['layouts_id'])) ? $link['layouts_id'] : ''; ?>" href="<?php echo $href; ?>" <?php echo ($link['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?> <?php echo $link['menu_html_attr'] ? $link['menu_html_attr'] : ''; ?> class="menu_item btn  <?php echo ($link['menu_css_class']) ? $link['menu_css_class'] : 'btn-default'; ?> 
               <?php if ($link['menu_modal'] == DB_BOOL_TRUE) echo 'js_open_modal';
                else echo ($link['layouts_ajax_allowed']) ? 'js_ajax_content' : ''; ?> menu-<?php echo $link['menu_id'] ?> mr-3 rounded" <?php if ($link['menu_modal'] == DB_BOOL_TRUE) : ?>data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" <?php endif; ?>>
                <?php echo $link['menu_icon_class'] ? sprintf('<i class="%s"></i>', $link['menu_icon_class']) : ''; ?>
                <?php e($link['menu_label']); ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>