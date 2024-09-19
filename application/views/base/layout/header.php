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


<a href="<?php echo base_url('main/dashboard'); ?>" class="logo">

    <!-- mini logo for sidebar mini 50x50 pixels -->
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
$this->layout->addDinamicStylesheet($data, "header.css");
?>

<div class="js_loading">
    <img src="<?php echo base_url_admin('images/loader.gif'); ?>" />
</div>

<nav class="navbar navbar-static-top">

    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"></a>
    <div class="navbar-left">
        {tpl-pre-top_bar}
    </div>


    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

            <?php if (file_exists(__DIR__ . '/custom/header-menu.php')) $this->load->view('layout/custom/header-menu'); ?>

            {tpl-pre-header_navbar}
            <?php //$this->hook->message_dropdown(); ?>

            <!-- BEGIN show pending processes -->
            <?php
            $this->db->cache_off();
            $pending_processes = $this->db->query("SELECT COUNT(*) AS c 
FROM _queue_pp
WHERE _queue_pp_executed = 0")->row()->c;
            $this->db->cache_on();
            ?>
            <?php if ($pending_processes > 0): ?>
            <li class=" pending_processes" style="color:white;" id="pending_processes">

            Pending processes: <?php echo $pending_processes; ?>

            </li>
            <?php endif; ?>
            <!-- END show pending processes -->

            <!-- BEGIN LANGUAGES DROPDOWN -->
            <li class="dropdown languages" id="languages">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php if (!empty($this->datab->getLanguage())) : ?>
                    <img src="<?php echo $this->datab->getLanguage()['flag'] ?>" class="language-flag">
                    <?php else : ?>
                    <img style="display:none" class="language-flag">
                    <i class="fas fa-globe-europe language-icon"></i>
                    <?php endif; ?>
                </a>
            </li>
            <!-- END LANGUAGES DROPDOWN -->


            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php
                    $_img = ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) ? base_url_admin("uploads/" . $this->auth->get(LOGIN_IMG_FIELD)) : base_url_admin("uploads/" . $this->auth->get(LOGIN_IMG_FIELD));
                    ?>

                    <img src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD) ? $_img : base_url_admin('images/user.png')); ?>"
                        class="user-image" alt="User Image"> <span
                        class="hidden-xs"><?php echo $this->auth->get(LOGIN_NAME_FIELD); ?>
                        <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?></span>
                    <i class="fas fa-angle-down"></i> </a>
                <ul class="dropdown-menu menu">
                    <!-- User image -->
                    <li class="user-header">
                        <img src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD) ? $_img : base_url_admin('images/user.png')); ?>"
                            class="img-circle" alt="User Image">
                        <p>
                            <?php echo $this->auth->get(LOGIN_NAME_FIELD); ?>
                            <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?>
                            <small><?php echo $this->auth->get('users_email'); ?></small>
                        </p>
                    </li>


                    {tpl-pre-top_right_menu}

                    <!-- Menu Body -->

                    <?php $profile_menu_list = $this->datab->get_menu('profile'); ?>


                    <?php if (!empty($profile_menu_list)) : ?>

                    <!-- New general Settings -->
                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo base_url('main/settings'); ?>"><i class="fas fa-cog"></i>
                                    <?php e('General Settings'); ?></a>
                            </div>
                        </div>
                    </li>

                    <?php foreach ($profile_menu_list as $menu) : ?>
                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo $this->datab->generate_menu_link($menu); ?>"
                                    <?php echo ($menu['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?>
                                    class="<?php echo ($menu['menu_modal'] == DB_BOOL_TRUE) ? 'js_open_modal' : ''; ?>">
                                    <i
                                        class="<?php echo ($menu['menu_icon_class'] ? $menu['menu_icon_class'] : 'fas fa-list') ?>"></i>
                                    <?php echo ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label'])); ?>
                                </a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>

                    <?php endif; ?>


                    <?php if ($this->datab->is_admin()) : ?>



                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <?php if ($this->mycache->isCacheEnabled()) : ?>
                                <a href="<?php echo base_url('main/cache_control/off'); ?>"><i class="fas fa-cogs"></i>
                                    <?php e('Disable'); ?> cache</a>
                                <?php else : ?>
                                <a href="<?php echo base_url('main/cache_control/on'); ?>"><i class="fas fa-cogs"></i>
                                    <?php e('Enable'); ?> cache</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo base_url('main/cache_control/clear'); ?>"><i
                                        class="fas fa-trash-alt"></i> <?php e('Clear'); ?> cache</a>
                            </div>
                        </div>
                    </li>
                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo base_url('main/cache_manager'); ?>">
                                <i class="fas fa-server"></i> <?php e('Cache manager'); ?>
                                    </a>
                            </div>
                        </div>
                    </li>

                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="javascript:void(0)" id="js_enable_dev"><i class="fas fa-tools"></i>
                                    <?php e('Builder ToolBar'); ?></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($_COOKIE['webauthn_easylogin']) && $_COOKIE['webauthn_easylogin'] == '__never__') : ?>
                    <li class="user-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo base_url('access/easylogin'); ?>"><i class="fas fa-fingerprint"></i>
                                    <?php e('Enable Touch-Id'); ?></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>


                    {tpl-post-top_right_menu}


                    <li class="user-footer">
                        <div class="pull-left">
                            <?php

                            $layout_profile = $this->db->where('layouts_identifier', 'profile-page')->get('layouts')->num_rows();
                            if ($layout_profile) : ?>
                            <a href="<?php echo base_url("main/layout/profile-page"); ?>"
                                class="btn btn-default btn-flat"><?php e('Profile'); ?></a>
                            <?php else :

                                $form_user_default = $this->db->query("SELECT * FROM forms WHERE forms_default = '" . DB_BOOL_TRUE . "' AND forms_entity_id = (SELECT entity_id FROM entity WHERE entity_name = '" . LOGIN_ENTITY . "')");

                                if ($form_user_default->num_rows() != 0) :
                                ?>
                            <a href="<?php echo base_url("get_ajax/modal_form/" . $form_user_default->row()->forms_id . "/" . $this->auth->get('id')); ?>"
                                class="btn btn-default btn-flat js_open_modal"><?php e('Profile'); ?></a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="pull-right">
                            <a href="<?php echo base_url("access/logout"); ?>"
                                class="btn btn-default btn-flat"><?php e('Sign out'); ?></a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="navbar-right">
        {tpl-post-top_bar}
    </div>
</nav>
