<!-- sidebar: style can be found in sidebar.less -->
<?php $current_page = isset($dati['current_page']) ? $dati['current_page'] : null; ?>
<!-- Logo -->


<?php
if ($this->settings['settings_topbar_logo']) {
    $logo = $this->settings['settings_topbar_logo'];
} else {
    $logo = null;
}
if ($this->settings['settings_topbar_logo_small']) {
    $logo_small = $this->settings['settings_topbar_logo_small'];
} else {
    $logo_small = null;
}
?>


<a src="<?php echo base_url('main/dashboard'); ?>" class="brand-link">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <?php if ($logo_small) : ?>
        <img src="<?php echo base_url_uploads("uploads/{$logo_small}"); ?>" class="brand-image img-circle elevation-3" style="opacity: .8">
    <?php else : ?>
        <span class="brand-text font-weight-light"> <?php echo empty($this->settings['settings_company_short_name']) ? 'Company' : htmlspecialchars($this->settings['settings_company_short_name']); ?></span>
    <?php endif; ?>

    <?php /*
    <span class="logo-mini">
        <?php if ($logo_small) : ?>
            <img class="logo-default img-responsive" src="<?php echo base_url_uploads("uploads/{$logo_small}"); ?>">
        <?php else : ?>
            <?php echo empty($this->settings['settings_company_short_name']) ? 'Company' : htmlspecialchars($this->settings['settings_company_short_name']); ?>
        <?php endif; ?>
    </span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg">
        <?php if ($logo) : ?>
            <img class="logo-default img-responsive" src="<?php echo base_url_uploads("uploads/{$logo}"); ?>">
        <?php else : ?>
            <?php echo empty($this->settings['settings_company_name']) ? 'Company Name' : htmlspecialchars($this->settings['settings_company_name']); ?>
        <?php endif; ?>
    </span>
    */
    ?>
</a>

<?php
$data['custom'] = [];
if ($this->settings['settings_topbar_color']) {
    $topbar_color = $this->settings['settings_topbar_color'];
} elseif (defined('TOPBAR_COLOR') && !empty(TOPBAR_COLOR)) {
    $topbar_color = TOPBAR_COLOR;
} else {
    $topbar_color = false;
}

if ($topbar_color) {
    $data['custom'] = array_merge([
        '.logo' => [
            'background-color' => $topbar_color . '!important',
            'box-shadow' => '0 4px 2px 0 rgba(60, 64, 67, .3), 0 1px 3px 1px rgba(60, 64, 67, .35)'
        ],
        '.user-header, .navbar' => [
            'background-color' => $topbar_color . '!important',

        ]
    ], $data['custom']);
}
if (defined('TOPBAR_HOVER') && !empty(TOPBAR_HOVER)) {
    $data['custom'] = array_merge([
        '.sidebar-toggle:hover' => [
            'background-color' => TOPBAR_HOVER
        ]
    ], $data['custom']);
}
if (defined('TOPBAR_COLOR') && !empty(TOPBAR_COLOR)) {
    $data['custom'] = array_merge([
        '.sidebar-toggle:hover' => [
            'background-color' => TOPBAR_HOVER
        ]
    ], $data['custom']);
}

if (defined('SIDEBAR_ELEMENT') && !empty(SIDEBAR_ELEMENT)) {
    $data['custom'] = array_merge([
        '.skin-blue .sidebar-menu>li:hover>a,
        .skin-blue .sidebar-menu>li.active>a,
        .skin-blue .sidebar-menu>li.menu-open>a' => [
            'background' => SIDEBAR_ELEMENT,
            'color' => '#FFF',
        ]
    ], $data['custom']);
}
//$this->layout->addDinamicStylesheet($data, "header.css");
?>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Sidebar user panel -->
    <?php if (file_exists(FCPATH . "application/views_adminlte/custom/layout/sidebar-search.php")) : ?>
        <?php $this->load->view('custom/layout/sidebar-search'); ?>
        <!-- Sidebar user (optional) -->
        <?php
        /*
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div>
        </div>
        */
        ?>
    <?php else : ?>
        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <form class="sidebar-form firegui_sidebar-form" action="<?php echo base_url('main/search'); ?>" method="POST" id="search_form">
                <?php add_csrf(); ?>
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" value="<?php echo isset($dati['search_string']) ? $dati['search_string'] : ''; ?>" placeholder="<?php e("Search..."); ?>" aria-label="<?php e("Search..."); ?>">
                    <div class="input-group-append">
                        <button type="submit" name="___search" id="search-btn" onclick="document.getElementById('search_form').submit();" class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    <div class="">
        {tpl-pre-sidebar_menu}
    </div>

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-compact nav-child-indent text-sm" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-header"><?php e('MAIN SECTION'); ?></li>
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
                    <li data-id="<?php echo $menu['menu_id']; ?>" class="nav-item js_sidebar_menu_item <?php echo implode(' ', $classes); ?>" <?php echo $menu['menu_html_attr'] ? $menu['menu_html_attr'] : ''; ?>>
                        <a href="<?php echo $link ?: 'javascript:;'; ?>" class="nav-link <?php if ($menu['layouts_ajax_allowed'] == DB_BOOL_TRUE) : ?>js_ajax_content<?php endif; ?>" data-layout-id="<?php echo (!empty($menu['layouts_id'])) ? $menu['layouts_id'] : ''; ?>">
                            <!-- <i class="<?php echo $menu['menu_icon_class'] ?: 'nav-icon fas fa-list'; ?>"></i> -->
                            <i class="nav-icon <?php echo $menu['menu_icon_class'] ?: 'fas fa-list'; ?>"></i>
                            <p>
                                <?php e($label, true, ['module_name' => $menu['menu_module']]); ?>
                                <?php if ($isCurrent) : ?>
                                    <span class="selected"></span>
                                <?php endif; ?>
                                <?php if ($hasSubmenu) : ?>
                                    <i class="right fas fa-angle-left"></i>
                                <?php endif; ?>
                            </p>
                        </a>

                        <?php if ($hasSubmenu) : ?>
                            <ul class="nav nav-treeview">
                                <?php foreach ($menu['submenu'] as $sub_menu) : ?>
                                    <?php
                                    $classes = [sprintf('menu-%s', $sub_menu['menu_id'])];
                                    if (in_array($current_page, $sub_menu['pages_names'])) {
                                        $classes[] = 'active';
                                    }
                                    ?>
                                    <li class="nav-item js_submenu_item <?php echo implode(' ', $classes); ?>" <?php echo $menu['menu_html_attr'] ? $menu['menu_html_attr'] : ''; ?>>
                                        <a href="<?php echo $this->datab->generate_menu_link($sub_menu); ?>" class="nav-link <?php if ($sub_menu['layouts_ajax_allowed'] == DB_BOOL_TRUE) : ?>js_ajax_content<?php endif; ?>" data-layout-id="<?php echo (!empty($sub_menu['layouts_id'])) ? $sub_menu['layouts_id'] : ''; ?>">
                                            <i class="nav-icon <?php echo $sub_menu['menu_icon_class'] ?: 'fas fa-empty'; ?>"></i>
                                            <p><?php e(ucfirst(str_replace(array('_', '-'), ' ', $sub_menu['menu_label'])), true, ['module_name' => $sub_menu['menu_module']]); ?></p>
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
    </nav>
    <div class="">
        {tpl-post-sidebar_menu}
    </div>
</div>