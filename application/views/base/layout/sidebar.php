<!-- sidebar: style can be found in sidebar.less -->
<?php $current_page = isset($dati['current_page']) ? $dati['current_page'] : null; ?>
<section class="sidebar">
    <!-- Sidebar user panel -->
    <?php if (file_exists(FCPATH . "application/views/custom/layout/sidebar-search.php")) : ?>
    <?php $this->load->view('custom/layout/sidebar-search'); ?>
    <?php else : ?>
    <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
    <form class="sidebar-form firegui_sidebar-form" action="<?php echo base_url('main/search'); ?>" method="POST" id="search_form">
        <?php add_csrf(); ?>
        <div class="input-group">
            <input tabindex="0" type="text" name="search" placeholder="<?php e("Search..."); ?>" value="<?php echo isset($dati['search_string']) ? $dati['search_string'] : ''; ?>" class="form-control"> <span class="input-group-btn">
                <button type="submit" name="___search" id="search-btn" class="btn btn-flat" onclick="document.getElementById('search_form').submit();">
                    <i class="fas fa-search"></i>
                </button>

            </span>
        </div>
    </form>
    <?php endif; ?>
    <div class="">
        {tpl-pre-sidebar_menu}
    </div>
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu sortableMenu js_sidebar_menu" data-widget="tree">
        <!-- END RESPONSIVE QUICK SEARCH FORM -->
        <?php
        $first = true;
        foreach ($this->datab->get_menu('sidebar') as $menu) :
        ?>
        <?php
            $link = $this->datab->generate_menu_link($menu);
            $hasSubmenu = count($menu['submenu']) > 0;
            $isLinkOrContainer = ($link or $hasSubmenu);
            $isCurrent = in_array($current_page, $menu['pages_names']);
            $label = ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label']));
            $classes = [sprintf('menu-%s', $menu['menu_id']), $menu['menu_css_class']];
            if ($first) {
                $classes[] = 'start';
            }

            if ($isCurrent) {
                $classes[] = 'active';
            }
            if ($hasSubmenu && !$link) {
                $classes[] = 'treeview';
            }
            ?>

        <?php if ($isLinkOrContainer) : ?>
        <li data-id="<?php echo $menu['menu_id']; ?>" class="js_sidebar_menu_item <?php echo implode(' ', $classes); ?>" <?php echo $menu['menu_html_attr'] ? $menu['menu_html_attr'] : ''; ?>>

            <a href="<?php echo $link ?: 'javascript:;'; ?>" class="<?php if ($menu['layouts_ajax_allowed'] == DB_BOOL_TRUE) : ?>js_ajax_content<?php endif; ?>" data-layout-id="<?php echo (!empty($menu['layouts_id'])) ? $menu['layouts_id'] : ''; ?>">
                <i class="<?php echo $menu['menu_icon_class'] ?: 'fas fa-list'; ?>"></i> <span class="title"><?php e($label, true, ['module_name' => $menu['menu_module']]); ?></span>
                <?php if ($isCurrent) : ?><span class="selected"></span><?php endif; ?>
                <?php if ($hasSubmenu) : ?><span class="pull-right-container"><i class="fas fa-angle-left pull-right"></i></span><?php endif; ?>
            </a>

            <?php if ($hasSubmenu) : ?>
            <ul class="treeview-menu">
                <?php foreach ($menu['submenu'] as $sub_menu) : ?>
                <?php
                                $classes = [sprintf('menu-%s', $sub_menu['menu_id'])];
                                if (in_array($current_page, $sub_menu['pages_names'])) {
                                    $classes[] = 'active';
                                }
                                ?>
                <li class="js_submenu_item <?php echo implode(' ', $classes); ?>" <?php echo $menu['menu_html_attr'] ? $menu['menu_html_attr'] : ''; ?>>
                    <a href="<?php echo $this->datab->generate_menu_link($sub_menu); ?>" class="<?php if ($sub_menu['layouts_ajax_allowed'] == DB_BOOL_TRUE) : ?>js_ajax_content<?php endif; ?>" data-layout-id="<?php echo (!empty($sub_menu['layouts_id'])) ? $sub_menu['layouts_id'] : ''; ?>">
                        <i class="<?php echo $sub_menu['menu_icon_class'] ?: 'fas fa-empty'; ?>"></i>
                        <?php e(ucfirst(str_replace(array('_', '-'), ' ', $sub_menu['menu_label'])), true, ['module_name' => $sub_menu['menu_module']]); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php else : ?>
        <li class="heading menu-<?php echo $menu['menu_id'] ?>">
            <h3 class="uppercase"><?php e($label, true); ?></h3>
        </li>
        <?php endif; ?>
        <?php
            $first = false;
        endforeach;
        ?>
    </ul>
    <div class="">
        {tpl-post-sidebar_menu}
    </div>
</section>
