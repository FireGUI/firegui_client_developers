<section class="content-header page-title">
    <h1>
        <?php e('Changelog client'); ?>
        <small><?php e('openbuilder changelog clients'); ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('main/layout/1'); ?>"><i class="fas fa-tachometer-alt"></i> Home</a></li>

        <li class="active"> <?php e('Support tables'); ?></li>
    </ol>
</section>



<section class="content">


    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">

                    <?php
                    $changelogFile = FCPATH . 'changelog.md';
                    if (file_exists($changelogFile)) {
                        $changelogContent = file_get_contents($changelogFile);
                        echo '<pre>' . htmlspecialchars($changelogContent) . '</pre>';
                    } else {
                        echo '<p>Changelog file not found.</p>';
                    }
                    ?>

                </div>
            </div>
        </div>
</section>