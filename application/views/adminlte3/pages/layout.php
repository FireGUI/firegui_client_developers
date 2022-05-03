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

<section class="content js_layout" data-layout_id="<?php echo $dati['layout_container']['layouts_id']; ?>">
    <div class="container-fluid">
        <?php echo $dati['pre-layout']; ?>

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