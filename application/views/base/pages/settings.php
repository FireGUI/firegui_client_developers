<?php

// Cron check last execution
$interval_cron_execution = 10;
if (!empty($this->settings['settings_last_cron_check'])) {
    $start_date = new DateTime($this->settings['settings_last_cron_check']);
    $end_date = new DateTime();
    $interval = $start_date->diff($end_date);
    $interval_cron_execution = $interval->i;
}


$interval_cron_cli_execution = 10;
if (!empty($this->settings['settings_last_cron_cli'])) {
    $start_date = new DateTime($this->settings['settings_last_cron_cli']);
    $end_date = new DateTime();
    $interval = $start_date->diff($end_date);
    $interval_cron_cli_execution = $interval->i;
}

?>


<section class="content-header page-title">
    <h1>
        <?php e('General settings');?>
        <small><?php e('Global configuration page');?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('main/layout/1'); ?>"><i class="fas fa-tachometer-alt"></i> Home</a></li>
        
        <li class="active"> <?php e('Support tables');?></li>
    </ol>
</section>


<?php $settings_menu_list = $this->datab->get_menu('settings');?>

<section class="content">
    
    
    <div class="row">
        
        
        <div class="col-md-4">
            
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-user-cog"></i>
                    
                    <h3 class="box-title">Main Settings</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <li><a href="<?php echo base_url('main/cache_manager'); ?>"><?php e('Cache manager');?></a>
                        <li><a href="<?php echo base_url('main/permissions'); ?>"><?php e('User permissions');?></a>
                        <li><a href="<?php echo base_url('main/check_requirements'); ?>">
                                <?php e('Check requirements'); ?>
                            </a>
                        
                        </li>
                        <?php if (!empty($settings_menu_list)): ?>
                            <?php foreach ($settings_menu_list as $menu): ?>
                                <li>
                                    <a href="<?php echo $this->datab->generate_menu_link($menu); ?>"
                                        <?php echo ($menu['layouts_pdf'] == DB_BOOL_TRUE) ? 'target="_blank"' : ''; ?>
                                       class="<?php echo ($menu['menu_modal'] == DB_BOOL_TRUE) ? 'js_open_modal' : ''; ?>">
                                        <?php echo ucfirst(str_replace(array('_', '-'), ' ', $menu['menu_label'])); ?>
                                    </a>
                                
                                </li>
                            <?php endforeach;?>
                        
                        <?php endif;?>
                        </li>
                    </ul>
                </div>
            </div>
            
            
            <?php if ($this->auth->is_admin() && !empty($dati['core_settings_layout'])): ?>
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <i class="fa fa-code"></i>
                        
                        <h3 class="box-title">Core modules</h3>
                    </div>
                    <div class="box-body">
                        <?php foreach ($dati['core_settings_layout'] as $key => $layouts): ?>
                            <h5>
                                <?php echo ($key) ? $key : t('Generic'); ?>
                            </h5>
                            <ul>
                                <?php foreach ($layouts as $layout): ?>
                                    <li><a href="<?php echo base_url('main/layout/' . $layout['layouts_id']); ?>"><?php e(ucfirst(str_replace(array('_', '-'), ' ', $layout['layouts_title']))); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cog"></i>
                    
                    <h3 class="box-title">System Settings</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <li><a href="<?php echo base_url('api_manager'); ?>"><?php e('API Manager');?></a></li>
                        <li><a href="<?php echo base_url('main/support_tables'); ?>"><?php e('Support Tables');?></a>
                        
                        <?php if ($this->auth->is_admin()): ?>
                            <li><a href="<?php echo base_url('main/events_queue'); ?>">
                                    <?php e('Events deferred queue'); ?>
                                </a>
                            </li>
                            <li><a href="<?php echo base_url('main/trash'); ?>"><?php e('Trash');?></a></li>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
            
            
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-life-ring"></i>
                    
                    <h3 class="box-title">Logs</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <?php if ($this->auth->is_admin()): ?>
                            <li><a href="<?php echo base_url('main/system_log'); ?>"><?php e('System logs');?></a></li>
                            <li><?php e('Email logs');?></li>
                            <li><a href=""><?php e('API logs');?></a></li>
                        <?php endif;?>
                        <li><a href="<?php echo base_url('main/layout/changelog'); ?>"><?php e('Changelog');?></a></li>
                    </ul>
                </div>
            </div>
        
        
        
        </div>
        
        
        
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    
                    <h3 class="box-title">Settings Pages</h3>
                </div>
                <div class="box-body">
                    <?php
                    if (!empty($dati['settings_layout'])) {
                        $accessible_layouts = [];
                        
                        foreach ($dati['settings_layout'] as $key => $layouts) {
                            $group = $key ?: t('General settings');
                            
                            foreach ($layouts as $layout) {
                                if ($this->datab->can_access_layout($layout['layouts_id'], null)) {
                                    $accessible_layouts[$group][] = $layout;
                                }
                            }
                        }
                        
                        if (!empty($accessible_layouts)) {
                            foreach ($accessible_layouts as $group => $layouts) {
                                echo "<h5>{$group}</h5>";
                                echo '<ul>';
                                foreach ($layouts as $layout) {
                                    $layout_title = str_ireplace(array('_', '-'), ' ', $layout['layouts_title']);
                                    $layout_title = ucfirst($layout_title);
                                    $layout_title = t($layout_title);
                                    
                                    echo '<li>', anchor(base_url('main/layout/' . $layout['layouts_id']), $layout_title), '</li>';
                                }
                                echo '</ul>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fas fa-satellite-dish"></i>
                    
                    <h3 class="box-title">Status Monitor</h3>
                </div>
                
                <div class="box-body">
                    
                    <!-- Cron Check CURL -->
                    <?php if (!empty($this->settings['settings_last_cron_check'])): ?>
                        <?php if ($interval_cron_execution > 5): ?>
                            <div class="">
                                <span><i style="color:#FF0000;margin-right:15px" class="fas fa-thumbs-down"></i></span> Cron
                                check (CURL)<br />
                                <small>Last execution: <?php echo $this->settings['settings_last_cron_check']; ?> </small>
                            </div>
                        <?php else: ?>
                            <div class="">
                                <span><i style="color:#009933;margin-right:15px" class="fas fa-check-circle"></i></span> Cron
                                check (CURL)<br />
                                <small>Last execution: <?php echo $this->settings['settings_last_cron_check']; ?> </small>
                            </div>
                        <?php endif;?>
                    <?php else: ?>
                        <div class="">
                            <span><i style="color:#FF0000;margin-right:15px" class="fas fa-thumbs-down"></i></span> Cron
                            check (CURL)<br />
                            <small>Last execution: <i>unavailable</i> </small>
                        </div>
                    <?php endif;?>
                    <br />
                    <!-- Cron Check CLI -->
                    <?php if (!empty($this->settings['settings_last_cron_cli'])): ?>
                        <?php if ($interval_cron_cli_execution > 5): ?>
                            <div class="">
                                <span><i style="color:#FF0000;margin-right:15px" class="fas fa-thumbs-down"></i></span> Cron
                                check (CLI)<br />
                                <small>Last execution: <?php echo $this->settings['settings_last_cron_cli']; ?> </small>
                            </div>
                        <?php else: ?>
                            <div class="">
                                <span><i style="color:#009933;margin-right:15px" class="fas fa-check-circle"></i></span> Cron
                                check (CLI) <br />
                                <small>Last execution: <?php echo $this->settings['settings_last_cron_cli']; ?> </small>
                            </div>
                        <?php endif;?>
                    <?php else: ?>
                        <div class="">
                            <span><i style="color:#FF0000;margin-right:15px" class="fas fa-thumbs-down"></i></span> Cron
                            check (CLI)<br />
                            <small>Last execution: <i>unavailable</i> </small>
                        </div>
                    <?php endif;?>
                    <br />
                    <!-- Auto Update -->
                    <?php if (array_key_exists('settings_auto_update_client', $this->settings)): ?>
                        <div class="">
                            <?php if ($this->settings['settings_auto_update_client'] == DB_BOOL_TRUE): ?>
                                <span><i style="color:#009933;margin-right:15px" class="fas fa-check-circle"></i></span> Auto update client <?php echo $this->auth->is_admin() ? '(' . anchor(base_url("db_ajax/switch_bool/settings_auto_update_client/".$this->settings['settings_id']), 'disable it') . ')' : ''; ?>
                            <?php else: ?>
                                <span><i style="color:#FF0000;margin-right:15px" class="fas fa-thumbs-down"></i></span> Auto update client <?php echo $this->auth->is_admin() ? '(' . anchor(base_url("db_ajax/switch_bool/settings_auto_update_client/".$this->settings['settings_id']), 'enable it') . ')' : ''; ?>
                            <?php endif;?>
                            <br /><small>Last update: - </small>
                            <br /><small>Update in progress: <?php if ($this->settings['settings_update_in_progress'] == DB_BOOL_TRUE): ?>Yes now...<?php else:?>No<?php endif;?> </small>
                            <br /><small>Current Version: <b><?php echo VERSION; ?></b> </small>
                        </div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    
    
    </div>
    
    <!--<div class="row">


            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <i class="fas fa-microscope"></i>

                        <h3 class="box-title">Unit tests</h3>
                    </div>

                    <div class="box-body">
                            <iframe src="<?php echo base_url("application/tests/build/coverage/index.html"); ?>" />
                    </div>
                </div>
            </div>
    </div>-->
</section>
