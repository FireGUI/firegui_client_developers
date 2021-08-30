<section class="content-header page-title">
    <h1>
        <?php e('Support tables'); ?>
        <small>todo...</small>
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
        <?php foreach ($dati['grids'] as $grid) : $grid_id = $grid['grids_id']; ?>
            <div class="col-md-6">
                <div class="box box blue ">



                    <div class="box-header with-border  ">

                        <div class="box-title">
                            <i class=" fas fa-bars"></i>
                            <span class=" ">

                                <?php echo $grid['grids_name']; ?></span>
                        </div>

                        <div class="box-tools">



                            <button type="button" class="btn btn-box-tool js_builder_toolbar_btn hide" data-action="option" data-element-type="grid" data-element-ref="<?php echo $grid_id; ?>" data-toggle="tooltip" title="" data-widget="chat-pane-toggle" data-original-title="grid option">
                                <i class="fa fa-edit"></i>
                            </button>

                            <button type="button" class="btn btn-box-tool js_builder_toolbar_btn hide" data-action="builder" data-element-type="grid" data-element-ref="<?php echo $grid_id; ?>" data-toggle="tooltip" title="" data-widget="chat-pane-toggle" data-original-title="grid builder">
                                <i class="fa fa-hat-wizard"></i>
                            </button>

                            <button type="button" class="btn btn-box-tool js_builder_toolbar_btn hide" data-widget="remove"><i class="fa fa-times"></i></button>
                            <!-- End Builder actions -->

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