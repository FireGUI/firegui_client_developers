<h3 class="page-title">Offerte <small>tutte</small></h3>

<div class="row">
    <div class="col-sm-3">
        <a href="<?php echo base_url("fatture/crea"); ?>">
            <div class="dashboard-stat green">
                <div class="visual"><i class="icon-upload-alt"></i></div>
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
                            <th>Totale</th>
                            <th>Data creazione</th>
                            <th>Esito</th>
                            <th>Data di scadenza</th>
                            <th width="135"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dati['fatture'] as $fattura): ?>
                            <tr>
                                <td><?php echo get_offer_number($fattura['offers_number'], $fattura['offers_date_creation']); ?></td>
                                <?php if(isset($dati['customers'][$fattura['offers_customer']])): ?>
                                    <td><a href="<?php echo $this->datab->get_detail_layout_link($entity['entity_id'], $fattura['offers_customer']); ?>"><?php echo $dati['customers'][$fattura['offers_customer']] ?></a></td>
                                <?php else: ?>
                                    <td><small>Azienda eliminata</small></td>
                                <?php endif; ?>
                                <td><?php echo ((isset($dati['users'][$fattura['offers_user']]))? $dati['users'][$fattura['offers_user']]: 'Utente eliminato'); ?></td>
                                <td><?php echo number_format(isset($fattura['price'])? $fattura['price']: 0,2); ?> &euro;</td>
                                <td><?php echo ((isset($dati['mandanti'][$fattura['offers_mandante']]))? $dati['mandanti'][$fattura['offers_mandante']]: ($fattura['offers_mandante']? 'Mandante eliminata': '-')); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($fattura['offers_date_creation'])); ?></td>
                                <td><?php echo $fattura['offers_esito']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($fattura['offers_date_end'])); ?></td>
                                <td>
                                    <a href="<?php echo base_url("offers_ndr/mailto/{$fattura['offers_id']}"); ?>" class="btn btn-xs btn-default js_open_modal" data-toggle="tooltip" title="Invia offerta" data-placement="left"><i class="icon-envelope"></i></a>
                                    <a href="<?php echo base_url("offers_ndr/edit/{$fattura['offers_id']}"); ?>" class="btn btn-xs btn-primary"><i class="icon-pencil"></i></a>
                                    <a href="<?php echo base_url("offers_ndr/pdf/generate/{$fattura['offers_id']}"); ?>" target="_blank" class="btn btn-xs btn-warning">PDF</a>
                                    <a href="<?php echo base_url("db_ajax/generic_delete/offers/{$fattura['offers_id']}"); ?>" data-confirm-text="<?php e("l'elemento selezionato verrà eliminato. Sei sicuro?"); ?>" class="btn btn-danger btn-xs js_confirm_button js_link_ajax pull-right"><i class="icon-remove"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>