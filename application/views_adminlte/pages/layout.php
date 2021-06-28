<?php
$iconsMapForContentType = [
    'grid' => 'fas fa-th',
    'form' => 'fas fa-edit',
    'chart' => 'far fa-chart-bar',
    'map' => 'fas fa-map-marker-alt',
    'calendar' => 'fas fa-calendar-alt',
];

$portletBgColorMap = [];
?>
<?php echo $dati['pre-layout']; ?>

<?php if ($dati['show_title']) : ?>

    <section class="content-header page-title">
        <h1>
            <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?>
            <small><?php e($dati['layout_container']['layouts_subtitle']); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url('main/layout/1'); ?>"><i class="fas fa-tachometer-alt"></i> Home</a></li>
            <?php if (!empty($dati['layout_container']['modules_name'])) : ?>
                <li><i class="fas fa-plug"></i> <?php echo $dati['layout_container']['modules_name'] . " (" . $dati['layout_container']['modules_version'] . ")"; ?></li>
            <?php endif; ?>
            <li class="active"> <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?></li>
        </ol>
    </section>

<?php endif; ?>

<section class="content">
    <?php foreach ($dati['layout'] as $row) : ?>

        <div class="row">
            <?php $max_col = 0; ?>
            <?php foreach ($row as $layout) : ?>

                <?php
                // =============================================================
                // Calcolo se andare alla prossima riga
                $max_col += $layout['layouts_boxes_cols'];
                if ($max_col > 12) {
                    $max_col = $layout['layouts_boxes_cols'];
                    echo '</div><div class="row">';
                }

                // =============================================================
                // Calcolo classi e colori

                ?>
                <div class="<?php echo sprintf('col-md-%s', $layout['layouts_boxes_cols']); ?> ">

                    <?php $this->load->view('pages/layout_box', ['layout' => $layout]); ?>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>


    <?php echo $dati['post-layout']; ?>


    <?php if (isset($layout['layouts_fullscreen']) && $layout['layouts_fullscreen'] == DB_BOOL_TRUE) : ?>
        <script>
            $(() => {
                $('body').addClass('sidebar-collapse');
            });
        </script>
    <?php endif; ?>

</section>