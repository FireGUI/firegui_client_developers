<?php

/**
 * File CSS tema
 */
$tprefix = empty($dati['title_prefix']) ? '' : $dati['title_prefix'];
$title = (empty($this->settings['settings_company_short_name']) ? 'MasterCRM' : $this->settings['settings_company_short_name']);
$tsuffix = 'CRM';
$lang = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
?>

<title><?php echo ucwords(htmlspecialchars(trim(implode(' - ', array_filter([$tprefix, $title, $tsuffix]))))); ?></title>

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta http-equiv="Cache-control" content="public">
<meta http-equiv="Content-type" content="text/html; charset=utf-8">

<meta name="application-name" content="<?php echo $title; ?>" />
<meta name="msapplication-TileColor" content="#FFFFFF" />

<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />

<!-- jQuery -->
<script src="<?php echo base_url("assets/plugins/jquery/jquery.min.js?v=" . VERSION); ?>"></script>

<!-- CORE LEVEL STYLES -->
<!-- Bootstrap 4 -->
<script src="<?php echo base_url_scripts("assets/plugins/core/js/bootstrap.bundle.min.js?v=" . VERSION); ?>"></script>

<!-- Theme style (AdminLTE App) -->
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/core/css/adminlte.min.css?v=" . VERSION); ?>">
<script src="<?php echo base_url_template("assets/plugins/core/js/adminlte.min.js?v=" . VERSION); ?>"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/fontawesome/css/all.min.css"); ?>">
<script src="<?php echo base_url("assets/plugins/fontawesome/js/all.min.js"); ?>"></script>

<!-- CUSTOM CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url("assets/css/custom.css?v=" . VERSION); ?>" />

<!-- STYLE OVERRIDE -->
<?php if (defined('CUSTOM_CSS_PATH') && CUSTOM_CSS_PATH) : ?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(CUSTOM_CSS_PATH . "?v=" . VERSION); ?>" />
<?php endif; ?>

<?php if (defined('CUSTOM_FAVICON') && CUSTOM_FAVICON) : ?>
    <link rel="shortcut icon" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>" />
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-16x16.png">
    <meta name="msapplication-TileImage" content="/<?php echo base_url_admin(CUSTOM_FAVICON); ?>-144x144.png">
    <meta name="theme-color" content="#ffffff">
<?php else : ?>
    <link rel="shortcut icon" href="<?php echo base_url('/favicon.ico'); ?>" />
<?php endif; ?>

<?php $this->layout->addDinamicJavascript([
    //"var base_url = '" . base_url() . "';",
    //"var base_url_admin = '" . base_url_admin() . "';",
    //"var base_url_template = '" . base_url_template() . "';",
    //"var base_url_scripts = '" . base_url_scripts() . "';",
    //"var base_url_uploads = '" . base_url_uploads() . "';",
//"var base_url_builder = '" . OPENBUILDER_BUILDER_BASEURL . "';",

    "var lang_code = '" . ((!empty($lang['languages_code'])) ? $lang['languages_code'] : 'en-EN') . "';",
    "var builderProjectHash = '" . base64_encode(json_encode(array($this->db->database, $this->db->hostname, $this->db->username, md5($this->db->password)))) . "';",
    "var lang_short_code = '" . ((!empty($lang['languages_code'])) ? (explode('-', $lang['languages_code'])[0]) : 'en') . "';",
], 'config.js'); ?>