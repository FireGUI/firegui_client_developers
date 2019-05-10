<div class="btn-group">
    <?php foreach ($data as $link): ?>
        <?php if (count($link['submenu']) > 0): ?>
            <div class="btn-group">
                <button type="button" class="btn dropdown-toggle default <?php echo $link['menu_css_class']; ?> menu-<?php echo $link['menu_id'] ?>" data-toggle="dropdown">
                    <?php echo $link['menu_icon_class']? sprintf('<i class="%s"></i>', $link['menu_icon_class']): ''; ?>
                    <?php echo $link['menu_label']; ?> <i class="fa fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($link['submenu'] as $sub_menu): ?>
                        <li>
                            <a href="<?php echo $this->datab->generate_menu_link($sub_menu, $value_id, $layout_data_detail); ?>" <?php echo ($link['layouts_pdf']==DB_BOOL_TRUE) ? 'target="_blank"': ''; ?>
                               class="<?php if($link['menu_modal']==DB_BOOL_TRUE) echo 'js_open_modal'; ?> menu-<?php echo $sub_menu['menu_id'] ?>">
                                   <?php echo $sub_menu['menu_label']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif(($href=$this->datab->generate_menu_link($link, $value_id, $layout_data_detail))): ?>
            <a href="<?php echo $href; ?>" <?php echo ($link['layouts_pdf']==DB_BOOL_TRUE) ? 'target="_blank"': ''; ?>
               class="btn default <?php echo $link['menu_css_class']; ?> <?php if($link['menu_modal']==DB_BOOL_TRUE) echo 'js_open_modal'; ?> menu-<?php echo $link['menu_id'] ?>">
                <?php echo $link['menu_icon_class']? sprintf('<i class="%s"></i>', $link['menu_icon_class']): ''; ?>
                <?php echo $link['menu_label']; ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>