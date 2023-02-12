<section class="content-header page-title">
    <h1>
        <?php e('Support tables'); ?>
        <small>todo...</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('main/layout/1'); ?>"><i class="fas fa-tachometer-alt"></i> Home</a></li>

        <li class="active">
            <?php e('Support tables'); ?>
        </li>
    </ol>
</section>

<?php //debug($dati, true); 
?>
<section class="content">


    <div class="row">
        <?php foreach ($dati['grids'] as $grid):
            $grid_id = $grid['grids_id']; ?>
            <div class="col-md-6">
                <div class="box box blue ">



                    <div class="box-header with-border  ">

                        <div class="box-title">
                            <i class=" fas fa-bars"></i>
                            <span class=" ">

                                <?php echo $grid['grids_name']; ?>
                            </span>
                        </div>

                    </div>

                    <div class="box-body layout_box grid " data-layout-box="" data-value_id="">
                        <?php echo ($dati['grids_html'][$grid_id]); ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>



</section>