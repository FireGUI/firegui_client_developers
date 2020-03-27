<?php if ($error_level > 0) : ?>
    <div class="alert alert-danger">
        Please resolve all requirements before continue<br />
        <?php foreach ($error_req as $error) echo '- ', $error, "<br>"; ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <h2>PHP Extensions</h2>
</div>

<table class="table">
    <tr>
        <td class="table_heading col-md-4"><b>Extensions</b></td>
        <td class="table_heading col-md-4"><b>Result</b></td>
        <td class="table_heading col-md-4"><b>Note</b></td>
    </tr>
    <tr>
        <td>PHP</td>
        <td><?php echo " " . ($requirements['php_version'] ? $res_true : $res_false); ?></td>
        <td><?php echo " " . ($requirements['php_version'] ? '' : 'Your PHP versions is: ' . PHP_VERSION . ', we need at least PHP ' . $php_min_version); ?></td>
    </tr>
    <tr>
        <td>Path folder writable</td>
        <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
        <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
    </tr>
    <tr>
        <td>Mysqli PHP Extension</td>
        <td><?php echo $requirements['mysqli'] ? $res_true : $res_false; ?></td>
        <td><?php echo $requirements['mysqli'] ? '' : 'Mysqli is required!'; ?></td>
    </tr>
    <tr>
        <td>OpenSSL PHP Extension</td>
        <td><?php echo $requirements['openssl_enabled'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>PDO PHP Extension</td>
        <td><?php echo $requirements['pdo_enabled'] ? $res_true : $res_false; ?> </td>
        <td></td>
    </tr>
    <tr>
        <td>Mbstring PHP Extension</td>
        <td><?php echo $requirements['mbstring_enabled'] ? $res_true : $res_false; ?> </td>
        <td></td>
    </tr>
    <tr>
        <td>XML PHP Extension</td>
        <td> <?php echo $requirements['xml_enabled'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>CTYPE PHP Extension</td>
        <td><?php echo $requirements['ctype_enabled'] ? $res_true : $res_false; ?> </td>
        <td></td>
    </tr>
    <tr>
        <td>JSON PHP Extension</td>
        <td><?php echo $requirements['json_enabled'] ? $res_true : $res_false; ?> </td>
        <td></td>
    </tr>
    <tr>
        <td>Mcrypt PHP Extension</td>
        <td><?php echo $requirements['mcrypt_enabled'] ? $res_true : $res_false; ?> </td>
        <td><?php echo $requirements['mcrypt_enabled'] ? '' : 'Suggested but not required'; ?></td>
    </tr>
    <tr>
        <td>ImageMagick PHP Extension</td>
        <td><?php echo $requirements['imagick'] ? $res_true : $res_false; ?></td>
        <td><?php echo $requirements['imagick'] ? '' : 'Suggested but not required'; ?></td>
    </tr>
    <tr>
        <td>Curl PHP Extension</td>
        <td><?php echo $requirements['curl'] ? $res_true : $res_false; ?> </td>
        <td><?php echo $requirements['curl'] ? '' : 'Not required, but highly recommended.'; ?></td>
    </tr>
    <tr>
        <td>Zip PHP Extension</td>
        <td><?php echo $requirements['zip'] ? $res_true : $res_false; ?> </td>
        <td><?php echo $requirements['zip'] ? '' : 'Not required, but highly recommended.'; ?></td>
    </tr>
    <tr>
        <td>GD PHP Extension</td>
        <td><?php echo $requirements['gd'] ? $res_true : $res_false; ?> </td>
        <td><?php echo $requirements['gd'] ? '' : 'Not required, but highly recommended.'; ?></td>
    </tr>
</table>

<div class="page-header">
    <h2>PHP Configuration</h2>
</div>

<table class="table">
    <tr>
        <td class="table_heading col-md-4"><b>Configuration</b></td>
        <td class="table_heading col-md-4"><b>Result</b></td>
        <td class="table_heading col-md-4"><b>Note</b></td>
    </tr>
    <tr>
        <td>max_input_vars</td>
        <td><?php echo ini_get('max_input_vars'); ?></td>
        <td><?php echo (ini_get('max_input_vars') < 1200) ? 'We suggest you set this value to at least 1200' : ''; ?></td>
    </tr>
    <?php /*
    <tr>
        <td>magic_quotes_gpc: </td>
        <td><?php echo !ini_get('magic_quotes_gpc') ? $res_true : $res_false; ?> (value: <?php echo ini_get('magic_quotes_gpc') ?>)</td>
        <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
    </tr>
    <tr>
        <td>register_globals: </td>
        <td><?php echo !ini_get('register_globals') ? $res_true : $res_false; ?> (value: <?php echo ini_get('register_globals') ?>)</td>
        <td></td>
    </tr>
    <tr>
        <td>session.auto_start: </td>
        <td><?php echo !ini_get('session.auto_start') ? $res_true : $res_false; ?> (value: <?php echo ini_get('session.auto_start') ?>)</td>
        <td></td>
    </tr>
    <tr>
        <td>mbstring.func_overload: </td>
        <td><?php echo !ini_get('mbstring.func_overload') ? $res_true : $res_false; ?> (value: <?php echo ini_get('mbstring.func_overload') ?>) </td>
        <td></td>
    </tr>
    */ ?>
    <tr>
        <td>mod_rewrite</td>
        <td><?php echo $requirements['mod_rewrite_enabled'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>upload_max_filesize:</td>
        <td><?php echo ini_get('upload_max_filesize') ? $res_true : $res_false; ?>
            (value: <?php echo ini_get('upload_max_filesize') ?>)
        </td>
        <td><?php echo (ini_get('upload_max_filesize') < 64) ? 'We suggest you set this value to at least 64' : ''; ?></td>
    </tr>
    <tr>
        <td>post_max_size:</td>
        <td><?php echo ini_get('post_max_size') ? $res_true : $res_false; ?>
            (value: <?php echo ini_get('post_max_size') ?>)
        </td>
        <td><?php echo (ini_get('post_max_size') < 64) ? 'We suggest you set this value to at least 64' : ''; ?></td>
    </tr>
</table>

<div class="page-header">
    <h2>PHP Permissions</h2>
</div>

<table class="table">
    <tr>
        <td class="table_heading col-md-4"><b>Permission</b></td>
        <td class="table_heading col-md-4"><b>Result</b></td>
        <td class="table_heading col-md-4"><b>Note</b></td>
    </tr>
    <tr>
        <td>Script path</td>
        <td><?php echo $permissions['localpath']; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>Path folder writable</td>
        <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
        <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
    </tr>
    <tr>
        <td>mkdir() function</td>
        <td><?php echo $permissions['mkdir'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>rmdir() function</td>
        <td><?php echo $permissions['rmdir'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>fopen(write) function</td>
        <td><?php echo $permissions['fopen'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>unlink function</td>
        <td><?php echo $permissions['unlink'] ? $res_true : $res_false; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>eval function</td>
        <td><?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></td>
        <td><?php echo check_disabled_function('eval') ? '' : 'Eval function is required!'; ?></td>
    </tr>
</table>