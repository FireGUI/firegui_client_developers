<?php $current_page = (isset($dati['current_page'])? $dati['current_page']: NULL); ?>


<!-- BEGIN SIDEBAR MENU -->        
<ul class="page-sidebar-menu">
    <li>
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler hidden-phone"></div>
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
    </li>
    <li>
        <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
        <form class="sidebar-search" action="<?php echo base_url('main/search'); ?>" method="POST">
            <div class="form-container">
                <div class="input-box">
                    <a href="javascript:;" class="remove"></a>
                    <input type="text" name="search" value="<?php if(isset($dati['search_string'])) echo $dati['search_string']; ?>" placeholder="<?php e("Cerca..."); ?>"/>
                    <input type="button" class="submit" value=""/>
                </div>
            </div>
        </form>
        <!-- END RESPONSIVE QUICK SEARCH FORM -->
    </li>
    

    <?php foreach ($this->datab->get_menu('sidebar') as $menu): ?>
        <?php
        $link = $this->datab->generate_menu_link($menu);
        $hasSubmenu = count($menu['submenu']) > 0;
        $isLinkOrContainer = ($link OR $hasSubmenu);
        $isCurrent = in_array($current_page, $menu['pages_names']);
        $label = ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label']));
        ?>
        <?php if($isLinkOrContainer): ?>
            <li class="<?php if ($isCurrent) echo "active"; ?> menu-<?php echo $menu['menu_id'] ?>">
                <a href="<?php echo $link; ?>">
                    <i class="<?php echo ($menu['menu_icon_class']?:'icon-list') ?>"></i>
                    <?php echo "<span class='title'>{$label}</span>" . ($isCurrent? '<span class="selected"></span>': '') . ($hasSubmenu? '<span class="arrow "></span>': ''); ?>
                </a>
                
                <?php if ($hasSubmenu): ?>
                    <ul class="sub-menu">
                        <?php foreach ($menu['submenu'] as $sub_menu): ?>
                            <li class="<?php if (in_array($current_page, $sub_menu['pages_names'])) echo "active"; ?> menu-<?php echo $sub_menu['menu_id'] ?>">
                                <a href="<?php echo $this->datab->generate_menu_link($sub_menu); ?>">
                                    <i class="<?php echo ($sub_menu['menu_icon_class']?:'icon-empty'); ?>"></i>
                                    <?php echo ucfirst(str_replace(array('_', '-'), ' ', $sub_menu['menu_label'])); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php else: ?>
            <li class="heading menu-<?php echo $menu['menu_id'] ?>"><span><?php echo $label; ?></span></li>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php foreach ($this->datab->get_modules() as $module): ?>
        <li class="<?php if ($current_page == "module_{$module['modules_name']}") echo "active"; ?>">
            <a href="<?php echo base_url($module['modules_home_url']) ?>">
                <i class="icon-plus-sign"></i>
                <span class="title"><?php echo $module['modules_label']; ?></span>
                <span class="selected"></span>
            </a>
        </li>
    <?php endforeach; ?>
        
    <?php if(SHOW_MEDIA_MODULE===true): ?>
        <li class="<?php if ($current_page == "module_media") echo "active"; ?>">
            <a href="<?php echo base_url('media/upload') ?>">
                <i class="icon-upload"></i>
                <span class="title">Media</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php endif; ?>


</ul>
<!-- END SIDEBAR MENU -->