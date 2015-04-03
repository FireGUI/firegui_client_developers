<h3 class="page-title">Fatture <small><?php echo $dati['fattura']? 'modifica': 'crea nuova'; ?></small></h3>
    
    


<form class="form-horizontal formAjax" id="new_fattura" action="<?php echo base_url('fatture/db_ajax/create'); ?>">
    
    <?php if($dati['id']): ?>
        <input name="fattura_id" type="hidden" value="<?php echo $dati['id']; ?>" />
    <?php endif; ?>
    
    
    
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-plus-sign"></i> <?php echo $dati['fattura']? 'Modifica fattura': 'Crea fattura'; ?>
                    </div>
                </div>

                <div class="portlet-body form">
                    <div class="form-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Cliente <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <input type="hidden" name="fattura[fatture_cliente]" data-ref="<?php echo ENTITY_CUSTOMERS; ?>" class="form-control js_select_ajax js_fattura_customer" value="<?php echo $dati['fatture_cliente']; ?>" />
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-md-4">Identificativo <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <?php if (count($dati['serie']) === 1): ?>
                                            <div class="input-group">
                                                <input type="text" name="fattura[fatture_numero]" class="form-control" placeholder="Numero" />
                                                <span class="input-group-addon" id="basic-addon2"><?php echo reset($dati['serie']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="input-group">
                                                <input type="text" name="fattura[fatture_numero]" class="form-control" placeholder="Numero" />
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="js-serie-prev"><?php echo reset($dati['serie']); ?></span> <span class="caret"></span></button>
                                                    <ul class="dropdown-menu">
                                                        <?php foreach($dati['serie'] as $serie): ?>
                                                            <li><a href="javascript:setSeries('<?php echo $serie; ?>');"><?php echo $serie; ?></a></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <script>
                                                function setSeries(serie) {
                                                    $('.js-serie-prev').html(serie);
                                                    $('[name="fattura[fatture_serie]"]').val(serie);
                                                }
                                            </script>
                                        <?php endif; ?>
                                        <input type="hidden" name="fattura[fatture_serie]" value="<?php echo reset($dati['serie']); ?>" />
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Scadenza pagamento <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <div class="input-group js_form_datepicker date">
                                            <input name="fattura[fatture_scadenza_pagamento]" type="text" class="form-control" value="<?php if(isset($fattura['fatture_scadenza_pagamento'])): echo date('d/m/Y', strtotime($fattura['fatture_scadenza_pagamento'])); endif; ?>" />
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="icon-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">

                                    <label class="control-label col-md-4">Metodo di pagamento <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-3">
                                        <select class="form-control" name="fattura[fatture_metodo_pagamento]">
                                            <?php foreach($dati['metodi_pagamento'] as $metodo): ?>
                                                <option value="<?php echo $metodo; ?>"><?php echo $metodo; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                
                                    <div class="clearfix visible-md"></div>
                                    <label class="control-label col-md-4 col-lg-2">Pagata</label>
                                    <div class="col-md-8 col-lg-3">
                                        <label class="radio-inline"><input type="radio" value="t" class="toggle radio" <?php if($dati['fatture_pagato']) echo 'checked'; ?> name="fattura[fatture_pagato]" /> Si</label>
                                        <label class="radio-inline"><input type="radio" value="f" class="toggle radio" <?php if( ! $dati['fatture_pagato']) echo 'checked'; ?> name="fattura[fatture_pagato]" /> No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Prodotti</label>
                                    <div class="col-md-10">
                                        <table id="js_product_table" class="table table-condensed table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="50">Codice</th>
                                                    <th>Descrizione</th>
                                                    <th width="30">Quantità</th>
                                                    <th width="90">Prezzo</th>
                                                    <th width="75">Sconto</th>
                                                    <th width="75">IVA</th>
                                                    <th width="100">Importo</th>
                                                    <th width="35"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="hidden">
                                                    <td><input type="text" class="form-control input-sm" data-name="fatture_prodotti_codice" /></td>
                                                    <td><input type="text" class="form-control input-sm" data-name="fatture_prodotti_nome" /></td>
                                                    <td><input type="text" class="form-control input-sm" data-name="fatture_prodotti_quantita" placeholder="1" /></td>
                                                    <td><input type="text" class="form-control input-sm text-right" data-name="fatture_prodotti_prezzo" placeholder="0.00" /></td>
                                                    <td><input type="text" class="form-control input-sm text-right" data-name="fatture_prodotti_sconto" placeholder="0" /></td>
                                                    <td><input type="text" class="form-control input-sm text-right" data-name="fatture_prodotti_iva" placeholder="0" /></td>
                                                    <td><p class="form-control-static text-right js-importo">0.00</p></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-default btn-danger btn-sm js_remove_product">
                                                            <span class="icon-remove"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td>
                                                        <button id="js_add_product" type="button" class="btn btn-default btn-sm blue">Aggiungi prodotto</button>
                                                    </td>
                                                    <td colspan="7"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                            

                        <hr/>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Note</label>
                                    <div class="col-md-10">
                                        <textarea name="fattura[fatture_note]" rows="10" class="form-control" placeholder="Inserisci delle note [opzionali]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>




                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div id="msg_new_fattura" class="alert alert-danger hide"></div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="form-actions fluid">
                        <div class="col-md-offset-8 col-md-4">
                            <div class="pull-right">
                                <a href="<?php echo base_url('fatture'); ?>" class="btn default">Annulla</a>
                                <button type="submit" class="btn blue">Crea</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

