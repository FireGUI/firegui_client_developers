<section class="content-header page-title">
    <h1>
        <?php e('General settings'); ?>
        <small><?php e('Global configuration page'); ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('main/layout/1'); ?>"><i class="fas fa-tachometer-alt"></i> Home</a></li>

        <li class="active"> <?php e('Support tables'); ?></li>
    </ol>
</section>

<?php //debug($dati, true); 
?>
<section class="content">


    <div class="row">

        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cog"></i>

                    <h3 class="box-title">System Settings</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <li><a href="<?php echo base_url('api_manager'); ?>"><?php e('API Manager'); ?></a></li>
                        <li><a href="<?php echo base_url('main/support_tables'); ?>"><?php e('Support Tables'); ?></a></li>
                        <li><?php e('SMTP Configuration'); ?></li>
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

                    <?php foreach ($dati['settings_layout'] as $key => $layouts) : ?>
                        <h5><?php echo ($key) ? $key : t('Generic settings'); ?></h5>
                        <ul>
                            <?php foreach ($layouts as $layout) : ?>
                                <li><a href="<?php echo base_url('main/layout/' . $layout['layouts_id']); ?>"><?php e(ucfirst(str_replace(array('_', '-'), ' ', $layout['layouts_title']))); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>



                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-life-ring"></i>

                    <h3 class="box-title">Logs</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <li><a href="<?php echo base_url('main/system_log'); ?>"><?php e('System logs'); ?></a></li>
                        <li><?php e('Email logs'); ?></li>
                        <li><a href=""><?php e('API logs'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>


    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-user-cog"></i>

                    <h3 class="box-title">Users Tools / Settings</h3>
                </div>
                <div class="box-body">
                    <ul>
                        <li><a href=""><?php e('Users manager'); ?></a></li>
                        <li><a href="<?php echo base_url('main/permissions'); ?>"><?php e('Permissions'); ?></a></li>
                        <li><?php e('Send notifications'); ?></li>
                        <li><?php e('Send emails'); ?></li>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</section>