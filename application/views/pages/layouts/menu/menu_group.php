<div class="btn-group">
    <?php foreach ($data as $link): ?>
        <?php if (count($link['submenu']) > 0): ?>
            <div class="btn-group">
                <button type="button" class="btn dropdown-toggle default <?php echo $link['menu_css_class']; ?> menu-<?php echo $link['menu_id'] ?>" data-toggle="dropdown">
                    <i class="<?php echo $link['menu_icon_class']; ?>"></i>
                    <?php echo $link['menu_label']; ?> <i class="icon-angle-down"></i>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($link['submenu'] as $sub_menu): ?>
                        <li><a href="<?php echo $this->datab->generate_menu_link($sub_menu, $value_id, $layout_data_detail); ?>" class="<?php if($link['menu_modal']=='t') echo 'js_open_modal'; ?> menu-<?php echo $sub_menu['menu_id'] ?>"><?php echo $sub_menu['menu_label']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif(($href=$this->datab->generate_menu_link($link, $value_id, $layout_data_detail))): ?>
            <a href="<?php echo $href; ?>" class="btn default <?php echo $link['menu_css_class']; ?> <?php if($link['menu_modal']=='t') echo 'js_open_modal'; ?> menu-<?php echo $link['menu_id'] ?>">
                <i class="<?php echo $link['menu_icon_class']; ?>"></i>
                <?php echo $link['menu_label']; ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>