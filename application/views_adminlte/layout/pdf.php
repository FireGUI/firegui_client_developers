<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">

    <title>Layout PDF</title>

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
        <?php
        foreach ($dati['layout'] as $row) {
            foreach ($row as $layout) {
                echo (($layout['layouts_boxes_titolable'] === DB_BOOL_TRUE) ? '<h2>' . ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])) . '</h2>' : '') . "<div>{$layout['content']}</div>";
            }
        }
        ?>
    </div>
</body>

</html>