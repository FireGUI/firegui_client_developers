<h3 class="page-title"><?php e("Offers"); ?> <small><?php e("list"); ?></small></h3>



<div class="row">
    <div class="col-sm-3">
        <a href="<?php echo base_url("offers/create_offer"); ?>">
            <div class="dashboard-stat green">
                <div class="visual"><i class="icon-upload-alt"></i></div>
                <div class="details">
                    <div class="number"><?php echo count($dati['offers']) ?></div>
                    <div class="desc">Offerte create</div>
                </div>
                
                <span class="more">New offer <i class="m-icon-swapright m-icon-white"></i></span>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="<?php echo base_url("offers/expired_offers"); ?>">
            <div class="dashboard-stat red">
                <div class="visual"><i class="icon-upload-alt"></i></div>
                <div class="details">
                    <div class="number"><?php echo count($dati['offers_expired']) ?></div>
                    <div class="desc">Offerte scadute</div>
                </div>
                
                <span class="more">Guarda le offerte scadute <i class="m-icon-swapright m-icon-white"></i></span>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-list-alt"></i> Offerte create
                </div>
            </div>

            <div class="portlet-body">
                <table id="js_dtable" class="table table-striped table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th><?php e("Offer number"); ?></th>
                            <th><?php e("Customer"); ?></th>
                            <th><?php e("User"); ?></th>
                            <th><?php e("Discount"); ?></th>
                            <th><?php e("Notes"); ?></th>
                            <th><?php e("Created at"); ?></th>
                            <th><?php e("From"); ?></th>
                            <th><?php e("To"); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dati['offers'] as $fattura): ?>
                            <tr>
                                <td><?php echo get_offer_number($fattura['offers_number'], $fattura['offers_date_creation']); ?></td>
                                <td><?php echo $dati['customers'][$fattura['offers_customer']] ?></td>
                                <td><?php echo $dati['users'][$fattura['offers_user']] ?></td>
                                <td><?php echo $fattura['offers_discount'] ?> %</td>
                                <td><?php echo word_limiter(strip_tags($fattura['offers_notes']), 20) ?></td>
                                <td><?php echo $fattura['offers_date_creation']; ?></td>
                                <td><?php echo $fattura['offers_date_start']; ?></td>
                                <td><?php echo $fattura['offers_date_end']; ?></td>
                                <td>
                                    <a href="<?php echo base_url("offers/pdf/generate/{$fattura['offers_id']}"); ?>" target="_blank" class="btn btn-xs btn-danger">PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>