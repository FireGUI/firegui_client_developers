<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url("offers_ndr/db_ajax/mailto/{$dati['offer_id']}"); ?>" class="form-horizontal formAjax">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Invia offerta per email</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-md-3">E-mail mittente:</label>
                        <div class="col-md-9">
                            <input type="email" required name="mail_from" value="<?php echo $dati['defaults']['mail_from'] ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">E-mail cliente:</label>
                        <div class="col-md-9">
                            <input type="email" required name="mail_to" value="<?php echo $dati['defaults']['mail_to'] ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">CC:</label>
                        <div class="col-md-9">
                            <input type="text" name="mails_cc" value="<?php echo $dati['defaults']['mail_cc'] ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">CCN:</label>
                        <div class="col-md-9">
                            <input type="text" name="mails_bcc" value="" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">PDF:</label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                <a href="<?php echo base_url("pdf/offerta_{$dati['offer_id']}"); ?>" target="_blank">Offerta <strong><?php echo $dati['offer_realnumber']; ?></strong></a>
                                &nbsp;
                                <a download="offerta_<?php echo $dati['offer_realnumber']; ?>" href="<?php echo base_url("pdf/offerta_{$dati['offer_id']}"); ?>" class="btn btn-xs blue"><i class="icon-download"></i></a>
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">Oggetto:</label>
                        <div class="col-md-9">
                            <input type="text" name="mail_subject" value="<?php echo $dati['defaults']['mail_subject'] ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">E-mail:</label>
                        <div class="col-md-9">
                            <textarea name="mail_text" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    
                    
                    
                    
                    <div class="clearfix"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Annulla</button>
                    <button type="submit" class="btn blue">Invia e-mail</button>
                </div>
            </div>
        </div>
    </form>
</div>