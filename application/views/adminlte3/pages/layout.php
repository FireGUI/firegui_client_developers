<?php
$iconsMapForContentType = [
    'grid' => 'fas fa-th fa-fw',
    'form' => 'fas fa-edit fa-fw',
    'chart' => 'far fa-chart-bar fa-fw',
    'map' => 'fas fa-map-marker-alt fa-fw',
    'calendar' => 'fas fa-calendar-alt fa-fw',
];

$portletBgColorMap = [];
?>

<?php if (is_maintenance()) : ?>
    <section class="content-header">
        <div class="alert alert-warning mb-0">
            <h5><?php e('Updates in progress'); ?></h5>

            <div><?php e('Dear customer, we are making updates to your platform, the service may be subject to slight interruptions, we apologize for the inconvenience.'); ?></div>
        </div>
    </section>
<?php endif; ?>

<?php echo $dati['pre-layout']; ?>

<?php if ($dati['show_title'] == true && $dati['layout_container']['layouts_show_header'] == DB_BOOL_TRUE) : ?>
    <section class="content-header page-title">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?>
                        <small><?php e($dati['layout_container']['layouts_subtitle']); ?></small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right text-xs">
                        <li class="breadcrumb-item">
                            <a href="<?php echo base_url('main/layout/1'); ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                Home
                            </a>
                        </li>
                        <?php if (!empty($dati['layout_container']['modules_name'])) : ?>
                            <li class="breadcrumb-item">
                                <i class="fas fa-plug"></i>
                                <?php echo $dati['layout_container']['modules_name'] . " (" . $dati['layout_container']['modules_version'] . ")"; ?>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active"> <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="content js_layout" data-layout_id="<?php echo $dati['layout_container']['layouts_id']; ?>">
    <div class="container-fluid">
        <label class="label_highlight hide label_highlight_l">Layout #<?php echo $dati['layout_container']['layouts_id'] . " " . $dati['layout_container']['layouts_title'] . " Ident: " . $dati['layout_container']['layouts_identifier'] . " Module: " . $dati['layout_container']['layouts_module']; ?></label>

        <?php foreach ($dati['layout'] as $row_num => $row) : ?>
            <div class="row" data-row="<?php echo $row_num; ?>" data-layout_id="<?php echo $dati['layout_container']['layouts_id']; ?>">
                <!-- Load layouts boxes -->
                <?php foreach ($row as $layout) : ?>
                    <div data-id="<?php echo $layout['layouts_boxes_id']; ?>" data-cols="<?php echo $layout['layouts_boxes_cols']; ?> " class=" <?php echo sprintf('col-sm-%s', $layout['layouts_boxes_cols']); ?>">

                        <?php $this->load->view('pages/layout_box', ['layout' => $layout]); ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach;  ?>

        <?php echo $dati['post-layout']; ?>

        <?php if (isset($layout['layouts_fullscreen']) && $layout['layouts_fullscreen'] == DB_BOOL_TRUE) : ?>
            <script>
                $(() => {
                    $('body').addClass('sidebar-collapse');
                });
            </script>
        <?php endif; ?>
    </div>
</section>