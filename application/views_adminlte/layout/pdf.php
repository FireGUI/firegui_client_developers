<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">

    <title><?php echo $dati['layout_container']['layouts_title']; ?></title>

    <!-- CDN Stylesheets -->
    <link rel="stylesheet" href="<?php echo base_url("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css"); ?>" />

    <!-- Custom Stylesheet -->
    <style>
        .table>tbody>tr>td,
        .table>tbody>tr>th {
            border: none;
        }

        .list-group-item {
            border: 2px solid #ddd !important;
        }

        body {
            font-size: 1.5em;
        }
    </style>
</head>

<body>
    <div class="container">
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
                    $titleColor = '';
                    $baseClasses = [$layout['layouts_boxes_content_type']];
                    $userClasses = array_filter(explode(' ', $layout['layouts_boxes_css']));
                    $isLight = in_array('light', $userClasses);
                    $isGren = in_array('gren', $userClasses);
                    $isBox = in_array('box', $userClasses);

                    if ($isLight or $isGren) {
                        // Devo colorare il titolo
                        $titleColor = $layout['layouts_boxes_color'];
                    } else {
                        // Devo colorare il portlet
                        $userClasses[] = $layout['layouts_boxes_color'];
                    }

                    if ($isLight or $isGren or $isBox) {
                        $baseClasses[] = 'portlet';
                    }

                    $classes = array_unique(array_merge($baseClasses, $userClasses));
                    ?>
                    <div class="<?php echo sprintf('col-md-%s', $layout['layouts_boxes_cols']); ?>" data-layout-box="<?php echo $layout['layouts_boxes_id']; ?>">

                        <div class="<?php if ($layout['layouts_boxes_show_container'] === DB_BOOL_TRUE) : ?>box<?php endif; ?> <?php echo $layout['layouts_boxes_css'] . " " . $layout['layouts_boxes_color']; ?> <?php echo ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE) ? 'collapsed-box' : ''; ?>">

                            <?php if ($layout['layouts_boxes_titolable'] === DB_BOOL_TRUE) : ?>

                                <div class="box-header with-border <?php echo ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) ? 'js_title_collapse' : ''; ?> ">

                                    <div class="box-title">
                                        <i class="<?php echo $titleColor ? 'font-' . $titleColor : ''; ?> <?php echo isset($iconsMapForContentType[$layout['layouts_boxes_content_type']]) ? $iconsMapForContentType[$layout['layouts_boxes_content_type']] : 'fas fa-bars'; ?>"></i>
                                        <span class="<?php echo ($titleColor ? 'font-' . $titleColor : '') . ' ' . ($isLight ? 'caption-subject bold uppercase' : ''); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])); ?>
                                        </span>
                                    </div>

                                    <div class="box-tools">

                                        <?php if ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) : ?><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fas fa-minus"></i></button><?php endif; ?>
                                        <?php if ($layout['layouts_boxes_discardable'] === DB_BOOL_TRUE) : ?><button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fas fa-times"></i></button><?php endif; ?>
                                        <?php if ($layout['layouts_boxes_reloadable'] === DB_BOOL_TRUE) : ?><a href="javascript:;" class="reload"></a><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="box-body <?php echo $layout['layouts_boxes_content_type'] ?> <?php echo ($layout['layouts_boxes_collapsible'] == DB_BOOL_TRUE && $layout['layouts_boxes_collapsed'] == DB_BOOL_TRUE) ? 'display-hide' : ''; ?>">
                                <?php echo $layout['content'] ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>


        <?php echo $dati['post-layout']; ?>


        <?php if (isset($layout['layouts_fullscreen']) && $layout['layouts_fullscreen'] === DB_BOOL_TRUE) : ?>
            <script>
                $(document).ready(function() {
                    'use strict';
                    $('body').addClass('page-sidebar-closed').find('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
                });
            </script>
        <?php endif; ?>

    </div>
</body>

</html>