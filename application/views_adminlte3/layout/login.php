<?php
if (file_exists(VIEWPATH . 'custom/layout/login.php')) {
    $this->load->view('custom/layout/login');
} else {
    // What is today's date - number
    $day = date("z");

    //  Days of spring
    $spring_starts = date("z", strtotime("March 21"));
    $spring_ends   = date("z", strtotime("June 20"));

    //  Days of summer
    $summer_starts = date("z", strtotime("June 21"));
    $summer_ends   = date("z", strtotime("September 22"));

    //  Days of autumn
    $autumn_starts = date("z", strtotime("September 23"));
    $autumn_ends   = date("z", strtotime("December 20"));

    //  If $day is between the days of spring, summer, autumn, and winter
    if ($day >= $spring_starts && $day <= $spring_ends) :
        $season = "spring";
    elseif ($day >= $summer_starts && $day <= $summer_ends) :
        $season = "summer";
    elseif ($day >= $autumn_starts && $day <= $autumn_ends) :
        $season = "autumn";
    else :
        $season = "winter";
    endif;


?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | Log in</title>

        <!-- Google Font: Source Sans Pro -->
        <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="<?php echo base_url("assets/plugin/core/fontawesome-free/all.min.css"); ?>">
        <!-- icheck bootstrap -->
        <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="<?php echo base_url("assets/plugin/core/css/adminlte.min.css"); ?>">
    </head>

    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo">
                <a href="../../index2.html"><b>Admin</b>LTE</a>
            </div>
            <!-- /.login-logo -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>

                    <form action="<?php echo base_url('access/login_start'); ?>" method="POST" class="formAjax">
                        <?php add_csrf(); ?>
                        <div class="input-group mb-3">
                            <input type="hidden" class="webauthn_enable" name="webauthn_enable" value="0" />

                            <input type="email" class="form-control" placeholder="<?php e('E-mail address'); ?>" name="users_users_email">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" placeholder="<?php e('Password'); ?>" name="users_users_password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="remember">
                                    <label for="remember">
                                        Remember Me
                                    </label>
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>

                    <div class="social-auth-links text-center mb-3">
                        <p>- OR -</p>
                        <a href="#" class="btn btn-block btn-primary">
                            <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                        </a>
                        <a href="#" class="btn btn-block btn-danger">
                            <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                        </a>
                    </div>
                    <!-- /.social-auth-links -->

                    <p class="mb-1">
                        <a href="forgot-password.html">I forgot my password</a>
                    </p>
                    <p class="mb-0">
                        <a href="register.html" class="text-center">Register a new membership</a>
                    </p>
                </div>
                <!-- /.login-card-body -->
            </div>
        </div>
        <!-- /.login-box -->

        <!-- jQuery -->
        <script src="../../plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App -->
        <script src="../../dist/js/adminlte.min.js"></script>
    </body>

    </html>


<?php } ?>