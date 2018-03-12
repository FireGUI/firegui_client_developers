

<div class="js_loading_overlay"></div>
<div class="js_loading">
    <img src="<?php echo base_url_template('images/loading.gif'); ?>" width="60" /><br/>
    <small><?php e("Caricamento"); ?></small>
</div>



<div class="page-header-inner">
    
    <!-- BEGIN LOGO -->
    <div class="page-logo text-center">
        <?php if (empty($this->settings['settings_company_logo'])): ?>
            <a class="crm-name" href="<?php echo base_url(); ?>"><?php echo empty($this->settings['settings_company_short_name'])? 'Dashboard': $this->settings['settings_company_short_name']; ?></a>
        <?php else: ?>
            <a href="<?php echo base_url(); ?>">
                <img class="logo-default" src="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="<?php echo empty($this->settings['settings_company_short_name'])? '': $this->settings['settings_company_short_name']; ?>">
            </a>
        <?php endif; ?>
    </div>
    <!-- END LOGO -->
    
    
    <!-- BEGIN RESPONSIVE MENU TOGGLER -->
    <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"></a>
    <!-- END RESPONSIVE MENU TOGGLER -->
    
    
    <!-- BEGIN TOP NAVIGATION MENU -->
    <div class="top-menu">
        <ul class="nav navbar-nav pull-right">
            
            <?php if(file_exists(__DIR__.'/custom/header-menu.php')) $this->load->view('layout/custom/header-menu'); ?>
            <?php $this->load->view('box/notification_dropdown_list'); ?>
            <?php $this->hook->message_dropdown(); ?>

            <!-- BEGIN USER LOGIN DROPDOWN -->
            <li class="dropdown dropdown-user pull-right">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                    <img alt="" src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD)? base_url_template("imgn/1/29/29/uploads/".$this->auth->get(LOGIN_IMG_FIELD)): base_url_template('images/no-image-29x29.gif')); ?>" width="29"/>
                    <span class="username"><?php echo $this->auth->get(LOGIN_NAME_FIELD); ?> <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?></span>
                    <i class="fa fa-angle-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <?php $profile_menu_list=$this->datab->get_menu('profile'); ?>


                    <?php if(!empty($profile_menu_list)): ?>
                        <?php foreach ($profile_menu_list as $menu): ?>
                            <li>
                                <a href="<?php echo $this->datab->generate_menu_link($menu); ?>" class="<?php if($menu['menu_modal']=='t') echo 'js_open_modal'; ?>">
                                    <i class="<?php echo ($menu['menu_icon_class'] ? $menu['menu_icon_class'] : 'fa fa-list') ?>"></i>
                                    <?php echo ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label'])); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="divider"></li>
                    <?php endif; ?>


                    <?php if($this->datab->is_admin()): ?>
                        <li><a href="<?php echo base_url('main/permissions'); ?>"><i class="fa fa-lock"></i> Permessi</a></li>
                        <?php if ($this->apilib->isCacheEnabled()): ?>
                            <li><a href="<?php echo base_url('main/cache_control/off'); ?>"><i class="fa fa-cogs"></i> Disabilita cache</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo base_url('main/cache_control/on'); ?>"><i class="fa fa-cogs"></i> Ri-abilita cache</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo base_url('main/cache_control/clear'); ?>"><i class="fa fa-trash"></i> Pulisci cache</a></li>
                    <?php endif; ?>


                    <li><a href="<?php echo base_url("access/logout"); ?>"><i class="fa fa-key"></i> Log Out</a></li>
                </ul>

            </li>
            <!-- END USER LOGIN DROPDOWN -->
            
        </ul>
    </div>
    <!-- END TOP NAVIGATION MENU -->
</div>


