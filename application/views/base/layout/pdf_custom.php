<?php
$settings = $this->apilib->searchFirst('settings');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">

    <title><?php echo $this->input->get('pageTitle') ?? 'PDF' ?></title>

    <!-- CDN Stylesheets -->
    <link rel="stylesheet" href="<?php echo base_url("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css"); ?>" />

    <!-- Custom Stylesheet -->
    <style>
        .table>tbody>tr>td,
        .table>tbody>tr>th {
            border: none;
        }

        body {
            font-size: 1.5em;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row" style="margin-bottom:50px;">
            <div class="col-xs-6">
                <img src="<?php echo base_url('uploads/' . $settings['settings_company_logo']); ?>" class="img-responsive" style="max-height: 100px;" alt="logo">
            </div>
            <div class=" col-xs-6">
                <p style="text-align: right !important;">
                    <strong><?php echo $settings['settings_company_name']; ?></strong> <br />
                    <?php echo $settings['settings_company_address'] ?> - <?php echo $settings['settings_company_city'] ? $settings['settings_company_city'] : '/' ?><br />
                    <?php e('Phone');
                    echo $settings['settings_company_telephone'] ? " " . $settings['settings_company_telephone'] : '/' ?> - Sito Web <?php echo $settings['settings_company_web'] ? $settings['settings_company_web'] : '/'; ?><br />
                    <?php echo t('CF'), ': ', $settings['settings_company_codice_fiscale'] ? $settings['settings_company_codice_fiscale'] : '/'; ?> - <?php echo t('P.IVA'), ': ', $settings['settings_company_vat_number'] ? $settings['settings_company_vat_number'] : '/'; ?>
                </p>
            </div>

            <?php if($this->input->get('pageTitle') !== null): ?>
                <div class='col-md-12' style='text-align:center'>
                    <h3 style="margin: 0; padding: 0; border-bottom: 1px solid black; display: inline-block;"><?php echo $this->input->get('pageTitle'); ?> </h3>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-body">
                        <?php echo $html; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
