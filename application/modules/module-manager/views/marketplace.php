<?php

$settings = $this->apilib->searchFirst('modules_manager_settings');

if (defined('ADMIN_PROJECT')) {
    $admin_project = ADMIN_PROJECT;
} else {
    $admin_project = 0;
}
// Check repository
if (empty($settings['modules_manager_settings_modules_repository'])) {
    echo "Failed. Modules repository is not defined. Please insert a valid repository and try again";
    return false;
}

$installed_modules = $this->db->get('modules')->result_array();

// Get modules from repository


$url = $settings['modules_manager_settings_modules_repository'] . "public/client/get_all_modules/" . $admin_project . "/" . $settings['modules_manager_settings_license_token'];

$result = file_get_contents($url);

//debug($result,true);

// Repo
$dati['modules_repository'] = json_validate($result, true);

foreach ($dati['modules_repository'] as $module) {
    $dati['_modules_repository'][$module['modules_repository_identifier']] = $module;

    // Array for published modules
    //TODO ... Il client non sa il customer id!!
}

// Array for installed modules
$dati['modules_installed_identifiers'] = array();
foreach ($installed_modules as $module) {
    $dati['modules_installed_identifiers'][] = $module['modules_identifier'];
}

?>
<style>
    .table_modules,
    th,
    td {
        vertical-align: middle !important;

    }

    vhr {
        border: none;
        border-left: 1px solid #ddd;
        height: 100vh;
        width: 1px;
    }

    @media (min-width: 992px) {
        .modal-xl {
            width: 90%;
            max-width: 1500px;
        }
    }
</style>

<section class="content">


    <?php if (!defined('ADMIN_PROJECT')): ?>
        <div class="row bg-warning">
            <div class="col-md-12" style="font-size: 16px;padding:20px;"> Project id is not defined in enviroment!</div>
        </div>
    <?php endif; ?>

    <?php if (empty($settings['modules_manager_settings_license_token'])): ?>
        <div class="row bg-warning">
            <div class="col-md-12" style="font-size: 16px;padding:20px;">Invalid license token, you can see only free
                modules.</div>
        </div>
    <?php endif; ?>

    <!-- UPLOAD AND INSTALL MODULE 
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <i class="fa fa-download"></i>
                    <div class="box-title">
                        Upload a module
                    </div>
                </div>

                <div class="box-body">
                    <form method="POST" action="<?php echo base_url("module_manager/upload_module") ?>" id="new_module"
                        class="form-horizontal formAjax">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Choose file</label>
                                <div class="col-md-4">
                                    <input type="file" name="module_file" class="form-control" />
                                </div>

                                <div class="col-md-4  pull-right">
                                    <button type="submit" class="btn btn-primary">Install
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <div id="msg_new_entity" class="hide alert alert-danger"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>-->



    <div class="row">
        <div class="col-md-12">
            <div class="">

                <div class="box-body">

                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            <div class="form-body" style="margin-bottom:45px;">
                                <div class="form-group">
                                    <div class="col-md-12" style="padding-left:0; ">
                                        <input id="search-input" type="text" name="module_file"
                                            class="form-control input-lg" placeholder="Search on repository..." />
                                    </div>


                                </div>
                                <div class="row" style>
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <div id="msg_new_entity" class="hide alert alert-danger"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modules_list">

                        <div class="row">

                            <?php foreach ($dati['modules_repository'] as $module): ?>
                                <div class="col-md-6 col-sm-6 col-xs-12 module-box"
                                    data-title="<?php echo $module['modules_repository_name']; ?>">
                                    <div class="info-box" style="height:185px">
                                        <span class="info-box-icon" style="background-color:#ffffff">
                                            <?php if ($module['modules_repository_thumbnail']): ?>
                                                <img src="<?php echo $settings['modules_manager_settings_modules_repository']; ?>uploads/modules_repository/<?php echo $module['modules_repository_thumbnail']; ?>"
                                                    stlye="max-width:90px" />
                                            <?php else: ?>
                                                <img src="<?php echo base_url(); ?>modulesbridge/loadAssetFile/module-manager?file=falcon.png"
                                                    stlye="max-width:90px" />
                                            <?php endif; ?>

                                            <h3 style="color:#0c5460;">$
                                                <?php echo $module['modules_repository_price']; ?>
                                            </h3>
                                        </span>

                                        <div class="info-box-content">
                                            <span class="info-box-number">
                                                <?php echo $module['modules_repository_name']; ?>
                                            </span>
                                            <span class="info-box-text">⭐⭐⭐⭐⭐</span>
                                            <span class="">
                                                <?php echo $module['modules_repository_small_description']; ?>
                                            </span>

                                            <hr />
                                            +1500 active installations | v
                                            <?php echo $module['modules_repository_version']; ?>

                                            <?php if (!in_array($module['modules_repository_identifier'], $dati['modules_installed_identifiers'])): ?>
                                                <a target="_blank"
                                                    href="<?php echo base_url("module-manager/main/install_module/{$module['modules_repository_identifier']}"); ?>"
                                                    class="btn btn-primary pull-right"><i class="fa fa-cloud-download"></i>
                                                    Install
                                                </a>
                                            <?php else: ?>
                                                <a target="_blank"
                                                    href="<?php echo base_url("module-manager/main/install_module/{$module['modules_repository_identifier']}"); ?>"
                                                    class="btn btn-primary pull-right"><i class="fa fa-cloud-download"></i>
                                                    Force re-install
                                                </a>

                                                <a href="javascript:void(0);" class="btn btn-success pull-right"><i
                                                        class="fa fa-cloud-download"></i>
                                                    Installed
                                                </a>


                                            <?php endif; ?>

                                            <button class="btn btn-info pull-right" disabled="disabled"
                                                data-module_id="<?php echo $module['modules_repository_id']; ?>"
                                                data-toggle="modal" data-target="#modal-default"
                                                style="margin-right: 5px;"><i class="fa fa-info"></i>
                                                Info
                                            </button>

                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>




        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/css/lightslider.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/js/lightslider.min.js"></script>
        <style>
            a:hover {
                text-decoration: none !important
            }

            a {
                text-decoration: none !important
            }

            #lightSlider {
                list-style: none outside none;
                padding-left: 0;
                margin-bottom: 0;
            }

            li.liSlider {
                display: block;
                float: left;
                margin-right: 6px;
                cursor: pointer;
            }

            img.liSlider {
                display: block;
                height: auto;
                max-width: 100%;
            }
        </style>
        <div class="modal fade" id="modal-default">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!--<div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Info <b>Contabilità Easy</b></h4>
                    </div>-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul id="lightSlider">
                                    <!-- <?php for ($i = 1; $i < 10; $i++): ?>
                                                                                                <li class="liSlider"
                                                                                                    data-thumb="http://sachinchoolur.github.io/lightslider/img/thumb/cS-<?php echo $i ?>.jpg">
                                                                                                    <img class="liSlider"
                                                                                                        src="http://sachinchoolur.github.io/lightslider/img/cS-<?php echo $i ?>.jpg" />
                                                                                                </li>
                                    <?php endfor; ?> -->
                                    <!-- <li data-thumb="http://i3.ytimg.com/vi/aqz-KE-bpKQ/hqdefault.jpg">
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <iframe class="embed-responsive-item"
                                                src="https://www.youtube.com/embed/aqz-KE-bpKQ" frameborder="0"
                                                allowfullscreen></iframe>
                                        </div>
                                    </li> -->
                                </ul>
                                <hr>
                                <p>Modulo per creazione di fatture, fatture elettroniche, preventivi, ddt e note di
                                    credito</p>

                                <a role="button" data-toggle="collapse" data-target="#changelog" aria-expanded="false"
                                    aria-controls="changelog">View changelog</a>

                                <div class="collapse" id="changelog">
                                    <hr>
                                    <p>Modulo contabilit&agrave;.</p>

                                    <p>Fixato xml fattura elettronica</p>
                                    <p>Fixato empty_date di troppo su nuovo_documento.php (Michael)</p>
                                    <p>Aggiunto campo rif_documento_id su documenti_contabilita</p>
                                    <p>Aggiunto identificatore al menu amministrazione (menu-class)</p>
                                    <p>Aggiunto trigger click tabkey nuovo_documento</p>
                                    <p>Sistemato graficamente impostazioni contabilità e spostato grid IVA dentro
                                        impostazioni contabilità e reso datatable_inline </p>
                                    <p>Aggiunti raw data</p>
                                    <p>Aggiunto riferimento normativo per split payment</p>

                                    <p>Corretto escape apice su autocomplete</p>

                                    <p>scadenziario assegnato al modulo</p>

                                    <p>Voce menu scadenziario</p>
                                    <p>Fix numero/serie a volte non visualizzato</p>
                                    <p>Fix filtro scadenziario</p>

                                    <p>Generazione RiBa su scadenziario pagamenti</p>

                                    <p>Bugfix: getDocumentiPadri andava in loop quando un documento puntava a se stesso
                                    </p>

                                    <p>Aggiornamento per raw_data (non sovrascrive iva in caso di update e neanche
                                        template pdf)</p>

                                    <p>Nuovi eval ricercabili e ordinabili in base alla nuova funzionalità del builder
                                    </p>

                                    <p>Aggiunto campo documenti_contabilita_metodo_pagamento compilato automaticamente
                                        quando si inserisce un fornitore in un nuovo documento</p>

                                    <p>Fixed undefined variable documento.</p>
                                    <p>Fix funzione getDocumentiPadri nel model</p>
                                    <p>Risolto grave bug su rif documento id e migliorata la logica</p>
                                    <p>Risolto bug rifdocid</p>

                                    <p>Bugfix esigibilità iva da differita a immediata se split payment false</p>
                                    <p>Solo il primo articolo è obbligatorio</p>

                                    <p>Corretta modale pagamenti con tabella di supporto saldato_con</p>
                                    <p>Risolto bug modifica form su conti correnti</p>
                                    <p>Reso la tabella "ordini clienti" bulkable</p>
                                    <p>Aggiunto campo documenti_contabilita_luogo_destinazione_id</p>

                                    <p>Completata funzionalità per generazione fatture distinte per ddt (apre nuove tab,
                                        una per ddt)</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="page-header">
                                    <h4>Contabilità Easy <small>v1.2.8</small> <small
                                            class="pull-right label label-primary">$ 10</small></h4>
                                </div>

                                <p>Modulo contabilit&agrave;.</p>

                                <p>Fixato xml fattura elettronica</p>
                                <p>Fixato empty_date di troppo su nuovo_documento.php (Michael)</p>
                                <p>Aggiunto campo rif_documento_id su documenti_contabilita</p>
                                <p>Aggiunto identificatore al menu amministrazione (menu-class)</p>
                                <p>Aggiunto trigger click tabkey nuovo_documento</p>
                                <p>Sistemato graficamente impostazioni contabilità e spostato grid IVA dentro
                                    impostazioni contabilità e reso datatable_inline </p>
                                <p>Aggiunti raw data</p>
                                <p>Aggiunto riferimento normativo per split payment</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Install</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<?php $this->load->view('publish_module_modal'); ?>
<?php $this->load->view('update_module_modal'); ?>

<script>
    const input = document.getElementById('search-input');
    const moduleBoxes = document.querySelectorAll('.module-box');

    input.addEventListener('input', function () {
        const searchTerm = input.value.toLowerCase();

        moduleBoxes.forEach(function (moduleBox) {
            if (moduleBox.getAttribute('data-title').toLowerCase().includes(searchTerm)) {
                moduleBox.style.display = 'block';
            } else {
                moduleBox.style.display = 'none';
            }
        });
    });

    $('#lightSlider').lightSlider({
        gallery: true,
        item: 1,
        loop: true,
        slideMargin: 0,
        thumbItem: 9,
        /*onSliderLoad: function() {
            $( window ).resize();
        }, onAfterSlide: function() {
            $( window ).resize();
        }*/
    });

    $('#modal-default').on('shown.bs.modal', function (e) {
        $(window).resize();
    })

    function myAfterMoveToRight() {
        $('#undo_redo_to option').each(function () {

            //Se non esiste il checkbox, lo creo
            if (!$('[ref_entity="' + $(this).val() + '"]').length) {
                checked = ($(this).html().includes('[support]')) ? ' checked ' : '';

                $('#import_data_container').append('<div class="col-md-12" ref_entity="' + $(this).val() + '"><input type="checkbox" ' + checked + ' value="1" class="_form-control entitydata" name="module[data][' + $(this).val() + ']" /> ' + $(this).html() + '</div>');
            }
        });
    }
</script>