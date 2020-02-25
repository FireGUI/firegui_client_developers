<!-- Logo -->
<a href="<?php echo base_url('main/dashboard'); ?>" class="logo">


    <?php if (empty($this->settings['settings_company_logo'])) : ?>
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <?php echo empty($this->settings['settings_company_short_name']) ? 'Companny' : htmlspecialchars($this->settings['settings_company_short_name']); ?>

        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <?php echo empty($this->settings['settings_company_name']) ? 'Company Name' : htmlspecialchars($this->settings['settings_company_name']); ?>

        </span>

    <?php else : ?>
        <img class="logo-default img-responsive" src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" style="height:100%;margin: 0 auto">
    <?php endif; ?>

</a>
<style>
    <?php if (defined('TOPBAR_COLOR') && !empty(TOPBAR_COLOR)) : ?>.navbar-static-top,
    .logo {
        background-color: <?php echo TOPBAR_COLOR; ?> !important;
        box-shadow: 0 4px 2px 0 rgba(60,64,67,.3),0 1px 3px 1px rgba(60,64,67,.35);
    }

    <?php if (defined('TOPBAR_HOVER') && !empty(TOPBAR_HOVER)) : ?>.sidebar-toggle:hover {
        background-color: <?php echo TOPBAR_HOVER; ?> !important;
    }

    <?php endif; ?>.user-header {
        background-color: <?php echo TOPBAR_COLOR; ?> !important;
    }

    <?php endif; ?><?php if (defined('SIDEBAR_ELEMENT') && !empty(SIDEBAR_ELEMENT)) : ?>.skin-blue .sidebar-menu>li:hover>a,
    .skin-blue .sidebar-menu>li.active>a,
    .skin-blue .sidebar-menu>li.menu-open>a {
        color: #fff;
        background: <?php echo SIDEBAR_ELEMENT; ?>;
    }

    <?php endif; ?>

    .navbar-custom-menu {
        font-size: 16px;
    }
    .dropdown-menu {
        border: none !important;
        box-shadow: 0 4px 2px 0 rgba(60,64,67,.3),0 1px 3px 1px rgba(60,64,67,.35);
    }
     .dropdown-menu > .user-header {
         background-color: #222d32 !important;
     }
     .dropdown-menu > .user-body:hover {
        background: #26546f;
        transition: all .25s ease-in;
     }
     .navbar-nav > .user-menu > .dropdown-menu > .user-body:hover a {
        color: #fff !important;
        transition: color .25s ease-in;
     }
    .navbar-nav > .notifications-menu > .dropdown-toggle >  i {
        margin-right: 5px !important;
    }
</style>
<nav class="navbar navbar-static-top">

    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">

    </a>

    <div class="navbar-custom-menu">


        <ul class="nav navbar-nav">
            <?php if (file_exists(__DIR__ . '/custom/header-menu.php')) $this->load->view('layout/custom/header-menu'); ?>
            <?php $this->load->view('box/notification_dropdown_list'); ?>
            <?php $this->hook->message_dropdown(); ?>
            <!-- Notifications: style can be found in dropdown.less -->
            <?php if (false) : ?>
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="far fa-bell"></i> <span class="label label-warning">10</span> </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li>
                                    <a href="#"> <i class="fas fa-users text-aqua"></i> 5 new members joined today </a>
                                </li>
                                <li>
                                    <a href="#"> <i class="fas fa-exclamation-triangle text-yellow"></i> Very long description here
                                        that may not fit into the page and may cause design problems </a>
                                </li>
                                <li>
                                    <a href="#"> <i class="fas fa-users text-red"></i> 5 new members joined </a>
                                </li>
                                <li>
                                    <a href="#"> <i class="fas fa-shopping-cart text-green"></i> 25 sales made </a>
                                </li>
                                <li>
                                    <a href="#"> <i class="fas fa-user text-red"></i> You changed your username </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>
                <!-- Tasks: style can be found in dropdown.less -->
            <?php endif; ?>

            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php
                    $_img = ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) ? base_url_admin("uploads/" . $this->auth->get(LOGIN_IMG_FIELD)) : base_url_admin("imgn/1/100/100/uploads/" . $this->auth->get(LOGIN_IMG_FIELD));
                    ?>

                    <img src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD) ? $_img : base_url_admin('images/user.png')); ?>" class="user-image" alt="User Image"> <span class="hidden-xs"><?php echo $this->auth->get(LOGIN_NAME_FIELD); ?> <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?></span>
                    <i class="fas fa-angle-down"></i> </a>
                <ul class="dropdown-menu menu">
                    <!-- User image -->
                    <li class="user-header">
                        <img src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD) ? $_img : base_url_admin('images/user.png')); ?>" class="img-circle" alt="User Image">
                        <p>
                            <?php echo $this->auth->get(LOGIN_NAME_FIELD); ?> <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?>
                            <small><?php echo $this->auth->get('users_email'); ?></small>
                        </p>
                    </li>


                    <!-- Menu Body -->

                    <?php $profile_menu_list = $this->datab->get_menu('profile'); ?>


                    <?php if (!empty($profile_menu_list)) : ?>

                        <?php foreach ($profile_menu_list as $menu) : ?>
                            <li class="user-body">
                                <div class="row">
                                    <div class="col-xs-12 text-center">
                                        <a href="<?php echo $this->datab->generate_menu_link($menu); ?>" <?php echo ($menu['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?> class="<?php echo ($menu['menu_modal'] == DB_BOOL_TRUE) ? 'js_open_modal' : ''; ?>">
                                            <i class="<?php echo ($menu['menu_icon_class'] ? $menu['menu_icon_class'] : 'fas fa-list') ?>"></i>
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
                                    <a href="<?php echo base_url('main/permissions'); ?>"><i class="fas fa-lock"></i>
                                        <?php e('Permissions'); ?></a>
                                </div>
                            </div>


                        </li>

                        <li class="user-body">
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <a href="<?php echo base_url('main/system_log'); ?>"><i class="fas fa-history"></i>
                                        <?php e('System Log'); ?></a>
                                </div>
                            </div>
                        </li>

                        <li class="user-body">
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <a href="<?php echo base_url('api_manager'); ?>"><i class="fas fa-cubes"></i> <?php e('API Manager'); ?></a>
                                </div>
                            </div>
                        </li>

                        <li class="user-body">
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <?php if ($this->apilib->isCacheEnabled()) : ?>
                                        <a href="<?php echo base_url('main/cache_control/off'); ?>"><i class="fas fa-cogs"></i> <?php e('Disable'); ?> cache</a>
                                    <?php else : ?>
                                        <a href="<?php echo base_url('main/cache_control/on'); ?>"><i class="fas fa-cogs"></i> <?php e('Enable'); ?> cache</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>

                        <li class="user-body">
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <a href="<?php echo base_url('main/cache_control/clear'); ?>"><i class="fas fa-trash-alt"></i> <?php e('Clean'); ?> cache</a>
                                </div>
                            </div>
                        </li>

                    <?php endif; ?>

                    <li class="user-footer">
                        <div class="pull-left">
                            <?php
                            $form_user_default = $this->db->query("SELECT * FROM forms WHERE forms_default = '" . DB_BOOL_TRUE . "' AND forms_entity_id = (SELECT entity_id FROM entity WHERE entity_name = '" . LOGIN_ENTITY . "')");

                            if ($form_user_default->num_rows() != 0) :
                            ?>
                                <a href="<?php echo base_url("get_ajax/modal_form/" . $form_user_default->row()->forms_id . "/" . $this->auth->get('id')); ?>" class="btn btn-default btn-flat js_open_modal"><?php e('Profile'); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="pull-right">
                            <a href="<?php echo base_url("access/logout"); ?>" class="btn btn-default btn-flat"><?php e('Sign out'); ?></a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

</nav>