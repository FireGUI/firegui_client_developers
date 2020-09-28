<?php

if (!function_exists('base_url')) {
    function base_url($text = '')
    {
        return config_item('base_url') . $text;
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Oh no :( - Page not found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>


    <link rel="stylesheet" type="text/css" href="<?php echo base_url("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css?v=" . time()); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url("template/adminlte/bower_components/font-awesome/css/font-awesome.min.css?v=" . time()); ?>" />

    <link rel="stylesheet" href="<?php echo base_url('script/frontend/css/style.css'); ?>">


    <script>
        var base_url = '<?php echo base_url(); ?>';
    </script>


    <link href="<?php echo base_url('script/frontend/css/404.css'); ?>" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-6 col-lg-6">
                    <div class="intro-text">
                        <p><span class="ops">Oops!</span>
                            <span class="text">Oh no :(<br />
                                Page not found!</span>
                        </p>
                        <p><a class="btn btn-squared pull-left" href="<?php echo base_url(); ?>">Go to home</a></p>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 hidden-xs">
                    <img class="img-responsive" src="<?php echo base_url('images/einstein_baffoni.gif'); ?>" alt="">
                </div>
            </div>
        </div>
    </header>
</body>

</html>