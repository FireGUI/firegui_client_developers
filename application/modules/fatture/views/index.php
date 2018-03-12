<h3 class="page-title">Offerte <small>tutte</small></h3>

<div class="row">
    <div class="col-sm-3">
        <a href="<?php echo base_url("fatture/crea"); ?>">
            <div class="dashboard-stat green">
                <div class="visual"><i class="fa fa-upload"></i></div>
                <div class="details">
                    <div class="number"><?php echo count($dati['fatture']) ?></div>
                    <div class="desc">Fatture create</div>
                </div>
                
                <span class="more">Nuova fattura <i class="m-icon-swapright m-icon-white"></i></span>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet gren">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-list-alt"></i> Lista fatture
                </div>
            </div>

            <div class="portlet-body">
                <table id="js_dtable" class="table table-striped table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th>Numero fattura</th>
                            <th>Cliente</th>
                            <th>Data emissione</th>
                            <th>Totale</th>
                            <th>Pagata</th>
                            <th>Metodo di pagamento</th>
                            <th>Scadenza</th>
                            <th width="135"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dati['fatture'] as $fattura): ?>
                            <tr>
                                <td><?php echo $fattura['fatture_numero'] . $fattura['fatture_serie']; ?></td>
                                <?php if(isset($dati['clienti'][$fattura['fatture_cliente']])): ?>
                                    <td><a href="<?php echo $this->datab->get_detail_layout_link(FATTURE_E_CUSTOMERS, $fattura['fatture_cliente']); ?>"><?php echo $dati['clienti'][$fattura['fatture_cliente']] ?></a></td>
                                <?php else: ?>
                                    <td><small>Cliente eliminato</small></td>
                                <?php endif; ?>
                                <td><?php echo dateFormat($fattura['fatture_data_creazione']); ?></td>
                                <td><?php echo number_format($fattura['fatture_totale'],2); ?> &euro;</td>
                                <td><?php echo $fattura['fatture_pagato']=='t' ? 'Si': '<span class="text-muted">No</span>'; ?></td>
                                <td><?php echo $fattura['fatture_metodo_pagamento'] ?: '-'; ?></td>
                                <td><?php echo dateFormat($fattura['fatture_scadenza_pagamento']); ?></td>
                                <td>
                                    <!--<a href="<?php echo base_url("offers_ndr/mailto/{$fattura['fatture_id']}"); ?>" class="btn btn-xs btn-default js_open_modal" data-toggle="tooltip" title="Invia offerta" data-placement="left"><i class="icon-envelope"></i></a>-->
                                    <a href="<?php echo base_url("fatture/edit/{$fattura['fatture_id']}"); ?>" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></a>
                                    <?php if (FATTURAZIONE_URI_STAMPA): ?>
                                        <a href="<?php echo base_url(FATTURAZIONE_URI_STAMPA . '/' . $fattura['fatture_id']); ?>" target="_blank" class="btn btn-xs btn-warning">PDF</a>
                                    <?php endif; ?>
                                    <a href="<?php echo base_url("db_ajax/generic_delete/fatture/{$fattura['fatture_id']}"); ?>" data-confirm-text="<?php e("Eliminare la fattura %s?", 1, [$fattura['fatture_numero'] . $fattura['fatture_serie']]); ?>" class="btn btn-danger btn-xs js_confirm_button js_link_ajax pull-right"><i class="fa fa-remove"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>