<h3 class="page-title">Offers <small>new offer</small></h3>

<form class="formAjax" id="new_offer" action="<?php echo base_url('offers/db_ajax/create_new'); ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-plus-sign"></i> Create offer
                    </div>
                </div>

                <div class="portlet-body form">
                    <div class="form-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Customer</label>
                                    <div class="col-md-8">
                                        <select class="form-control js_select2" name="offer[offers_customer]">
                                            <option></option>
                                            <?php foreach($dati['customers'] as $id=>$preview): ?>
                                                <option value="<?php echo $id ?>"><?php echo $preview; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Validity range</label>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="input-group js_form_daterangepicker">
                                                        <input name="offer[offers_date]" type="text" class="form-control">
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="icon-calendar"></i>
                                                                &nbsp;
                                                                <i class="icon-calendar-empty"></i>
                                                            </button>
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
                                    <label class="control-label col-md-4">Offer number</label>
                                    <div class="col-md-8">
                                        <input type="text" class="input-small form-control" name="offer[offers_number]" value="<?php echo $dati['offer_number']; ?>" placeholder="<?php echo $dati['offer_number']; ?>" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4">User</label>
                                    <div class="col-md-8">
                                        <select class="form-control js_select2" name="offer[offers_user]">
                                            <option></option>
                                            <?php foreach($dati['users'] as $id=>$preview): ?>
                                                <option value="<?php echo $id ?>"><?php echo $preview; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        
                        <div class="row">
                            <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Products</label>
                                <br/>

                                <!-- Product list -->
                                <table id="js_product_table" class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th width="12%">Quantity</th>
                                            <th>Description/code</th>
                                            <th width="15%">Price</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hidden">
                                            <td><input type="text" class="form-control" data-name="offers_products_quantity" /></td>
                                            <td>
                                                <select class="form-control js_table_select2" data-name="offers_products_product_id">
                                                    <option></option>
                                                    <?php foreach($dati['products'] as $id=>$preview): ?>
                                                        <option value="<?php echo $id ?>"><?php echo $preview; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control" data-name="offers_products_price" /></td>
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
                                                <button id="js_add_product" type="button" class="btn btn-default btn-sm"><?php e("add product"); ?></button>
                                            </td>
                                            <td></td>
                                            <td><input type="text" class="form-control input-sm" name="offer[offers_discount]" placeholder="Discount on subtotal" /></td>
                                            <td></td>
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
                                    <textarea name="offer[offers_notes]" class="form-control"></textarea>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>




                        <div class="row">
                            <div class="form-group">
                                <div id="msg_new_offer" class="alert alert-danger hide"></div>
                            </div>
                        </div>
                    </div>



                    <div class="form-actions fluid">
                        <div class="col-md-offset-8 col-md-4">
                            <div class="pull-right">
                                <a href="<?php echo base_url('offers'); ?>" class="btn default">Cancel</a>
                                <button type="submit" class="btn blue">Continue</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>