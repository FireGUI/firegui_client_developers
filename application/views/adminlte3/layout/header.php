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

$profile_menu_list = $this->datab->get_menu('profile');
?>


<nav class="main-header navbar navbar-expand navbar-white navbar-light text-sm">

    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo base_url(); ?>" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">Contact</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link toggleDark" href="#" role="button">
                <i class="fas fa-adjust"></i>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">


                <?php if (!empty($profile_menu_list)) : ?>
                <?php foreach ($profile_menu_list as $menu) : ?>
                <div class="dropdown-divider"></div>
                <a href="<?php echo $this->datab->generate_menu_link($menu); ?>"
                    <?php echo ($menu['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?>
                    class="dropdown-item p-3 <?php echo ($menu['menu_modal'] == DB_BOOL_TRUE) ? 'js_open_modal' : ''; ?>">
                    <i
                        class="mr-2 <?php echo ($menu['menu_icon_class'] ? $menu['menu_icon_class'] : 'fas fa-list') ?>"></i>
                    <?php echo ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label'])); ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($this->datab->is_admin()) : ?>
                <?php if ($this->mycache->isCacheEnabled()) : ?>
                <a href="<?php echo base_url('main/cache_control/off'); ?>" class="dropdown-item p-3">
                    <i class="fas fa-cogs mr-2"></i> <span><?php e('Disable'); ?> cache</span>
                </a>
                <?php else : ?>
                <a href="<?php echo base_url('main/cache_control/on'); ?>" class="dropdown-item p-3">
                    <i class="fas fa-cogs mr-2"></i>
                    <span><?php e('Enable'); ?> cache</span>
                </a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a href="<?php echo base_url('main/cache_control/clear'); ?>" class="dropdown-item p-3">
                    <i class="fas fa-trash-alt mr-2"></i>
                    <span><?php e('Clear'); ?> cache</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="javascript:void(0)" id="js_enable_dev" class="dropdown-item p-3">
                    <i class="fas fa-tools mr-2"></i>
                    <span><?php e('Builder ToolBar'); ?></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($_COOKIE['webauthn_easylogin']) && $_COOKIE['webauthn_easylogin'] == '__never__') : ?>
                <a href="<?php echo base_url('access/easylogin'); ?>" class="dropdown-item p-3">
                    <i class="fas fa-fingerprint mr-2"></i>
                    <span><?php e('Enable Touch-Id'); ?></span>
                </a>
                <?php endif; ?>

                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">
                    <div class="row">
                        <div class="col-6">
                            <?php
                            $layout_profile = $this->db->where('layouts_identifier', 'profile-page')->get('layouts')->num_rows();
                            if ($layout_profile) :
                            ?>
                            <a href="<?php echo base_url("main/layout/profile-page"); ?>"
                                class="btn btn-outline-info"><?php e('Profile'); ?></a>
                            <?php else :
                                $form_user_default = $this->db->query("SELECT * FROM forms WHERE forms_default = '" . DB_BOOL_TRUE . "' AND forms_entity_id = (SELECT entity_id FROM entity WHERE entity_name = '" . LOGIN_ENTITY . "')");

                                if ($form_user_default->num_rows() != 0) :
                                ?>
                            <a href="<?php echo base_url("get_ajax/modal_form/" . $form_user_default->row()->forms_id . "/" . $this->auth->get('id')); ?>"
                                class="btn btn-outline-info js_open_modal"><?php e('Profile'); ?></a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo base_url("access/logout"); ?>"
                                class="btn btn-danger"><?php e('Sign out'); ?></a>
                        </div>
                    </div>
                </a>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>


<script>
$(function() {
    const toggle = $('.toggleDark');
    const body = $('body');

    toggle.on('click', function() {
        body.toggleClass('dark-mode');
    })
})
</script>
?>