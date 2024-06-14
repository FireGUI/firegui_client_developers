<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = get_instance();
if ($CI === null) {

    new MX_Controller();
    $CI = &get_instance();
}
if (empty($CI->db->conn_id)) {
    $error['type'] = 'database_connection';
} else {
    $error['type'] = 'database';
}

//Trick to avoid ci query where/select not flushed after query fail
$CI->db->reset_query();

$CI->load->helper('url');
$error['error_title'] = 'Database Error';
$error['error_type'] = $heading;
$error['error_message'] = $message;
$error['error_filename'] = '';
$error['error_linenumber'] = '';
$error['error_extra_data'] = json_encode(debug_backtrace());

log_message('error', "*** WARNING *** Database error: " . $error['error_message']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Database Error</title>
    <style type="text/css">
        ::selection {
            background-color: #E13300;
            color: white;
        }

        ::-moz-selection {
            background-color: #E13300;
            color: white;
        }

        body {
            background-color: #fff;
            margin: 40px;
            font: 13px/20px normal Helvetica, Arial, sans-serif;
            color: #4F5155;
        }

        a {
            color: #003399;
            background-color: transparent;
            font-weight: normal;
        }

        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        .container {
            margin: 10px;
            border: 1px solid #D0D0D0;
            box-shadow: 0 0 8px #D0D0D0;
        }

        p {
            margin: 12px 15px 12px 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>
            <?php echo $heading; ?>
        </h1>
        <pre><?php echo $message; ?></pre>

    </div>


    <?php if ((!empty($CI->db->conn_id) && is_maintenance()) || is_development()): ?>
        <div class="container">
            <h1>
                Backtrace
            </h1>
            <pre> <?php debug('*** DEBUG ***'); ?></pre>
        </div>

    <?php endif; ?>

    <?php if ((!empty($CI->db->conn_id)) && $CI->auth->is_admin()): ?>
        <div class="container">
            <h1>
                Developers utility
            </h1>
            <p>When you get a db error, try to <a target="_blank"
                    href="<?php echo base_url('install/update'); ?>">update</a> your database
                schema. </p>
        </div>
    <?php endif; ?>
</body>

</html>
