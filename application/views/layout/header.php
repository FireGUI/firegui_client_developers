<!-- BEGIN TOP NAVIGATION BAR -->
<div class="header-inner">
    
    
    <!-- BEGIN LOGO -->  
    <?php if($this->settings===array()): ?>
        <a class="navbar-brand" href="<?php echo base_url(); ?>" style="padding-left: 5px; font-size: 19px; line-height: 12px;">
            <strong>Your Company</strong>
        </a>
    <?php elseif($this->settings['settings_company_logo']): ?>
        <a class="navbar-brand text-center" href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="height:30px;margin:-10px 0 0;" />
        </a>
    <?php else: ?>
        <a class="navbar-brand" href="<?php echo base_url(); ?>" style="padding-left: 5px; font-size: 19px; line-height: 12px;">
            <strong><?php echo $this->settings['settings_company_short_name']; ?></strong>
        </a>
    <?php endif; ?>
    <!-- END LOGO -->
    
    
    
    <!-- BEGIN RESPONSIVE MENU TOGGLER --> 
    <a href="javascript:;" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <img src="<?php echo base_url_template("template/crm/img/menu-toggler.png"); ?>" alt="" />
    </a>
    <!-- END RESPONSIVE MENU TOGGLER -->
    
    
    <div class="js_loading">
        <img src="<?php echo base_url_template('template/crm/img/ajax-modal-loading.gif'); ?>" width="16" /><br/>
        <small>Caricamento</small>
    </div>
    
    
    
    <!-- BEGIN TOP NAVIGATION MENU -->
    <ul class="nav navbar-nav pull-right">
        <?php if(file_exists(__DIR__.'/custom/header-menu.php')) $this->load->view('layout/custom/header-menu'); ?>
        <?php $this->load->view('box/notification_dropdown_list'); ?>
        <?php $this->hook->message_dropdown(); ?>
        
        <!-- BEGIN USER LOGIN DROPDOWN -->
        <li class="dropdown user">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <img alt="" src="<?php echo ($this->auth->get(LOGIN_IMG_FIELD)? base_url_template("imgn/1/29/29/uploads/".$this->auth->get(LOGIN_IMG_FIELD)): base_url_template('images/no-image-29x29.gif')); ?>" width="29"/>
                <span class="username"><?php echo $this->auth->get(LOGIN_NAME_FIELD); ?> <?php echo $this->auth->get(LOGIN_SURNAME_FIELD); ?></span>
                <i class="icon-angle-down"></i>
            </a>
            <ul class="dropdown-menu">
                <?php $profile_menu_list=$this->datab->get_menu('profile'); ?>
                <?php if(!empty($profile_menu_list)): ?>
                    <?php foreach ($profile_menu_list as $menu): ?>
                        <li>
                            <a href="<?php echo $this->datab->generate_menu_link($menu); ?>" class="<?php if($menu['menu_modal']=='t') echo 'js_open_modal'; ?>">
                                <i class="<?php echo ($menu['menu_icon_class'] ? $menu['menu_icon_class'] : 'icon-list') ?>"></i>
                                <span class="title"><?php echo ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label'])); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="divider"></li>
                <?php endif; ?>
                    
                <?php if($this->datab->is_admin()): ?>
                    <li><a href="<?php echo base_url('main/permissions'); ?>"><i class="icon-lock"></i> Permessi</a></li>
                    <?php if ($this->apilib->isCacheEnabled()): ?>
                        <li><a href="<?php echo base_url('main/cache_control/off'); ?>"><i class="icon-cogs"></i> Disabilita cache</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo base_url('main/cache_control/on'); ?>"><i class="icon-cogs"></i> Ri-abilita cache</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo base_url('main/cache_control/clear'); ?>"><i class="icon-trash"></i> Pulisci cache</a></li>
                <?php endif; ?>
                    
                <li><a href="<?php echo base_url("access/logout"); ?>"><i class="icon-key"></i> Log Out</a></li>
            </ul>
        </li>
        <!-- END USER LOGIN DROPDOWN -->
        
        
    </ul>
    <!-- END TOP NAVIGATION MENU -->
</div>
<!-- END TOP NAVIGATION BAR -->