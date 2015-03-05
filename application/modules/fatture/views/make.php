<?php $fattura = (isset($dati['fattura_id']))? $dati['fattura']: array(); ?>
<?php if(empty($dati['fattura_id'])): ?>
    <h3 class="page-title">Fatture <small>crea nuova</small></h3>
<?php else: ?>
    <h3 class="page-title">Fatture <small>modifica</small></h3>
<?php endif; ?>
    
    


<form class="formAjax" id="new_fattura" action="<?php echo base_url('fatture/db_ajax/create_new'); ?>">
    
    <?php if(isset($dati['fattura_id'])): ?>
        <input name="fattura_id" type="hidden" value="<?php echo $dati['fattura_id']; ?>" />
    <?php endif; ?>
    
    
    
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-plus-sign"></i> Crea fattura
                    </div>
                </div>

                <div class="portlet-body form">
                    <div class="form-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Cliente <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <?php /*if(isset($fattura['fatture_customer'])): ?>
                                            <input name="fattura[fatture_customer]" type="hidden" value="<?php echo $fattura['fatture_customer']; ?>" />
                                            <input type="hidden" disabled data-ref="<?php echo ENTITY_CUSTOMERS; ?>" class="form-control js_select_ajax js_fattura_customer" value="<?php echo $fattura['fatture_customer']; ?>" />
                                        <?php else: ?>
                                            <input type="hidden" name="fattura[fatture_cliente]" data-ref="<?php echo ENTITY_CUSTOMERS; ?>" class="form-control js_select_ajax js_fattura_customer" value="<?php echo ((isset($fattura['fatture_cliente']))? $fattura['fatture_customer']: $this->input->get('customer')); ?>" />
                                        <?php endif;*/ ?>
                                        <input type="hidden" name="fattura[fatture_cliente]" data-ref="<?php echo ENTITY_CUSTOMERS; ?>" class="form-control js_select_ajax js_fattura_customer" value="<?php echo ((isset($fattura['fatture_cliente']))? $fattura['fatture_cliente']: $this->input->get('cliente')); ?>" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Scadenza pagamento <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="input-group js_form_datepicker date">
                                                        <input name="fattura[fatture_scadenza_pagamento]" type="text" class="form-control" value="<?php if(isset($fattura['fatture_scadenza_pagamento'])): echo date('d/m/Y', strtotime($fattura['fatture_scadenza_pagamento'])); endif; ?>" />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="icon-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    
                                    <label class="control-label col-md-4">Metodo di pagamento <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                        <select class="form-control js_select2" name="fattura[fatture_metodo_pagamento]">
                                            <option></option>
                                            <option value="1">m1</option>
                                            <option value="2">m2</option>
                                            <option value="3">m3</option>
                                        </select>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        
                        
                        
                        <div class="row">
                            <div class="col-md-6">
                                
                                <div class="form-group">
                                    <label class="control-label col-md-4">Serie fatturazione <span class="text-danger">*</span></label>
                                    <div class="col-md-4">
                                        <input type="text" class="input-small form-control" name="fattura[fatture_serie]" value="<?php if(isset($fattura['fatture_serie'])): echo $fattura['fatture_serie']; endif; ?>" placeholder="<?php if(isset($fattura['fatture_serie'])): echo $fattura['fatture_serie']; endif; ?>" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                    
                                <div class="form-group">
                                    <label class="control-label col-md-4">Numero fattura <span class="text-danger">*</span></label>
                                    <div class="col-md-4">
                                        <input type="text" class="input-small form-control" name="fattura[fatture_numero]" value="<?php if(isset($fattura['fatture_numero'])): echo $fattura['fatture_numero']; endif; ?>" placeholder="<?php if(isset($fattura['fatture_numero'])): echo $fattura['fatture_numero']; endif; ?>" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                    
                                <div class="form-group">
                                    <label class="control-label col-md-4">IVA (%)<span class="text-danger">*</span></label>
                                    <div class="col-md-4">
                                        <input type="text" class="input-small form-control" name="fattura[fatture_iva]" value="<?php if(isset($fattura['fatture_iva'])): echo $fattura['fatture_iva']; endif; ?>" placeholder="<?php if(isset($fattura['fatture_iva'])): echo $fattura['fatture_iva']; endif; ?>" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                
                                
                                <div class="form-group">
                                    <label class="control-label col-md-4">Pagata</label>
                                    <div class="col-md-8">
                                        
                                        <?php $pagato = (isset($fattura['fatture_pagato']) && $fattura['fatture_pagato'] === 't'); ?>
                                        
                                        <label class="radio-inline"><input type="radio" value="t" <?php if($pagato) echo 'checked'; ?> name="fatture[fatture_pagato]" /> Si</label>
                                        <label class="radio-inline"><input type="radio" value="f" <?php if( ! $pagato) echo 'checked'; ?> name="fatture[fatture_pagato]" /> No</label>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        
                        
                        

                        <hr/>
                        
                        <?php if (isset($dati['fattura_products'])): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a data-toggle="collapse" href="#fatturaproducttable">Vedi Prodotti fatturata</a>
                                        <br/>

                                        <!-- Product list -->
                                        <table id="fatturaproducttable" class="table table-condensed table-striped collapse">
                                            <thead>
                                                <tr>
                                                    <th width="12%">Quantità</th>
                                                    <th>Prodotto</th>
                                                    <th width="15%">Prezzo</th>
                                                    <th width="5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dati['fattura_products'] as $product): ?>
                                                    <tr>
                                                        <td><?php echo $product['fatture_products_quantity']; ?></td>
                                                        <td>
                                                            <input type="hidden" name="fatture_products[]" value="<?php echo $product['fatture_products_id'] ?>" />
                                                            <?php echo $product['fatture_products_name']; ?><br/>
                                                            COD.: <strong><?php echo $product['fatture_products_code']; ?></strong>
                                                            <div class="js_accessories_container_fatture_ndr">
                                                                <table class="table table-condensed table-striped">
                                                                    <tbody>
                                                                        <?php foreach ($dati['fattura_products_accessories'] as $acc): ?>
                                                                        <?php if($acc['fatture_products_accessories_product_id'] == $product['fatture_products_product_id']): ?>
                                                                            <tr>
                                                                                <td><?php echo $acc['fatture_products_accessories_quantity']; ?></td>
                                                                                <td><?php echo $acc['fatture_products_accessories_name']; ?></td>
                                                                                <td><?php echo $acc['fatture_products_accessories_code']; ?></td>
                                                                                <td><?php echo $acc['fatture_products_accessories_price']; ?></td>
                                                                            </tr>
                                                                        <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                        <td> &euro; <?php echo number_format($product['fatture_products_price'],2); ?></td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-default btn-danger btn-sm js_remove_product">
                                                                <span class="icon-remove"></span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        
                        
                            <br/>
                            <br/>
                        <?php endif; ?>

                        
                                

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Prodotti</label>
                                    <br/>

                                    <!-- Product list -->
                                    <table id="js_product_table" class="table table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th width="30">Quantità</th>
                                                <th>Nome prodotto / servizio</th>
                                                <th width="15%">Prezzo unitario (iva escl.)</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="hidden">
                                                <td><input type="text" class="form-control" data-name="fatture_prodotti_quantita" placeholder="1" /></td>
                                                <td><input type="text" class="form-control" data-name="fatture_prodotti_nome" placeholder="Inserisci il nome e/o il codice del prodotto/servizio" /></td>
                                                <td><input type="text" class="form-control js_product_price_fatture_ndr" data-name="fatture_prodotti_imponibile" placeholder="0.00" /></td>
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
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>



                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                            

                        <hr/>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Note</label>
                                    <textarea name="fattura[fatture_note]" class="form-control"></textarea>
                                    <div class="clearfix"></div>
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
                                <a href="<?php echo base_url('fatture_ndr'); ?>" class="btn default">Annulla</a>
                                <button type="submit" class="btn blue">Crea</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
