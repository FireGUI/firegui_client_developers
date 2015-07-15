<h3 class="page-title">Newsletter <small>write a new mail</small></h3>

<div class="row inbox">
    <div class="col-md-12">
        <div class="inbox-content">
            <form class="inbox-compose form-horizontal formAjax" action="<?php echo base_url("newsletter/add_to_queue"); ?>">

                <div class="inbox-compose-btn">
                    <button class="pull-left btn blue"><i class="icon-check"></i>Send</button>
                    <button type="button" onclick="javascript:history.back();" class="pull-left btn">Cancel</button>
                    <?php if(count($dati['templates']) > 0): ?>
                        <div class="pull-left dropdown">
                            <button type="button" class="btn" id="templatesmenu" data-toggle="dropdown">Templates <span class="icon-angle-down"></span></button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="templatesmenu">
                                <?php foreach ($dati['templates'] as $template): ?>
                                    <li role="presentation">
                                        <a role="menuitem" tabindex="-1" href="<?php echo base_url("newsletter/write_mail/{$template['email_templates_id']}"); ?>">
                                            <?php echo $template['email_templates_name'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>

                <div class="inbox-form-group mail-to">
                    <label class="control-label">Emails:</label>
                    <div class="controls controls-to">
                        <input type="text" class="form-control col-md-12" name="to" data-provide="typeahead" data-items="4" data-source="[<?php echo $dati['email_list']; ?>]">
                    </div>
                </div>

                <div class="inbox-form-group">
                    <label class="control-label">Lists:</label>
                    <div class="controls">
                        <div style="height: 34px;">
                            <?php foreach ($dati['mailing_lists'] as $name => $list): ?>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="mailing_lists[]" value="<?php echo $list; ?>" />
                                    <?php echo $name; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                            
                        <div class="checkbox-inline" style="padding-bottom:10px">
                            <select id="entity-list" data-toggle="tooltip" title="Seleziona un'entità" name="entity_mails[entity]">
                                <option></option>
                                <?php foreach($dati['entities'] as $entity): ?>
                                    <option value="<?php echo $entity['entity_id']; ?>"><?php echo ucwords(str_replace('_', ' ', $entity['entity_name'])); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="fields-list" style='display:none;' name="entity_mails[field]"></select>
                            <label id="n-mails"></label>


                            <label style="margin:0 -21px;display:none;" class="checkbox"> <input type="checkbox" id="email-filter-toggle" name="entity_mails[filter]" value="1" /> Filtra dati </label>
                            <div id="email-filter-container" style="display:none;">
                                <select class="fields-list" name="entity_mails[filter_field]"></select>
                                <select id="operators-list" name="entity_mails[op_field]">
                                    <option></option>
                                    <option value="=">uguale a</option>
                                    <option value="ILIKE">contiene</option>
                                    <option value="IN">in</option>
                                    <option value="NOT IN">non in</option>
                                </select>
                                <input id="value-input" type="text" class="form-control" style="padding:3px;width: 200px;display:inline-block;height:20px;" name="entity_mails[val_field]" />
                                <button type="button" class="btn blue btn-xs">Filtra</button>
                                <textarea id="manual-where" name="entity_mails[manual_where]" class="form-control" style="margin-top:10px"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inbox-form-group">
                    <label class="control-label">Subject:</label>
                    <div class="controls">
                        <input type="text" class="form-control" name="subject" value="<?php echo isset($dati['newsletter']['newsletter_subject'])? $dati['newsletter']['newsletter_subject']: null; ?>" required>
                    </div>
                </div>
                
                <div class="inbox-form-group">
                    <label class="control-label">Headers:</label>
                    <div class="controls">
                        <textarea name="headers" class="form-control" rows="5" style="resize:none;border:none"><?php echo isset($dati['newsletter']['newsletter_headers'])? $dati['newsletter']['newsletter_headers']: null; ?></textarea>
                    </div>
                </div>
                
                
                <div class="inbox-form-group">
                    <textarea name="mail" id="ckeditor" class="hidden"><?php echo (isset($dati['newsletter']['newsletter_content'])? $dati['newsletter']['newsletter_content']: (isset($dati['template']['email_templates_content'])? $dati['template']['email_templates_content']: null)); ?></textarea>
                </div>
                <div class="inbox-form-group" style="padding: 10px;">
                    <input name="block_size" placeholder="E-mail da inviare per blocco" class="form-control input-large inline" required value="<?php echo isset($dati['newsletter']['newsletter_block_size'])? $dati['newsletter']['newsletter_block_size']: null; ?>" />
                    <input name="block_time" placeholder="Tempo tra un blocco e il successivo (min.)" class="form-control input-large inline" required value="<?php echo isset($dati['newsletter']['newsletter_block_time'])? $dati['newsletter']['newsletter_block_time']: null; ?>" />
                    <div class="clearfix"></div>
                </div>
                <div class="inbox-compose-btn">
                    <button class="btn blue"><i class="icon-check"></i>Send</button>
                    <button type="button" onclick="javascript:window.location = '<?php echo base_url('newsletter') ?>'" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    
    $(document).ready(function() {
        
        var entities = $('#entity-list');
        var fields = $('.fields-list');
        
        var filterToggle = $('#email-filter-toggle');
        var filterContainer = $('#email-filter-container');
        
        var filterFields = $('.fields-list', filterContainer);
        
        var operators = $('#operators-list');
        var nMails = $('#n-mails');
        var button = $('button', filterContainer);
        
        var value = $('#value-input');
        
        entities.on('change', function() {
            var entityID = entities.val();
            
            $('option', fields).remove();
            nMails.text('');
            
            if(entityID) {
                $.ajax(base_url+'newsletter/get_mailable_fields/'+entityID, {dataType:'json'}).success(function(jsonArray) {
                    
                    var select = fields.not(filterFields);
                    
                    $('<option></option>').appendTo(select);
                    $.each(jsonArray, function(k, jsonObject) {
                        $('<option></option>').val(jsonObject.id).text(jsonObject.name).appendTo(select);
                    });

                    if($('option', select).size() > 0) {
                        select.show();
                    } else {
                        select.hide();
                    }
                });
                
                $.ajax(base_url+'newsletter/get_fields/'+entityID, {dataType:'json'}).success(function(jsonArray) {
                    
                    var select = filterFields;
                    
                    $('<option></option>').appendTo(select);
                    $.each(jsonArray, function(k, jsonObject) {
                        $('<option></option>').val(jsonObject.id).text(jsonObject.name).appendTo(select);
                    });

                    if($('option', select).size() > 0) {
                        select.show();
                    } else {
                        select.hide();
                    }
                });
            }
        });
        
        
        fields.not(filterFields).on('change', function() {
            var fieldsID = $(this).val();
            nMails.text('');
            operators.hide();
            
            if(fieldsID) {
                $.ajax(base_url+'newsletter/count_emails/'+fieldsID).success(function(out) {
                    var count = parseInt(out);
                    
                    if(count > 0) {
                        operators.show();
                        nMails.html(count + ' e-mail trovate');
                        filterToggle.parents('label').filter(':first').show();
                    } else {
                        operators.hide();
                        filterToggle.parents('label').filter(':first').hide();
                    }
                });
            }
        });
        
        
        filterToggle.on('change', function() {
            if($(this).is(':checked')) {
                filterContainer.show();
            } else {
                filterContainer.hide();
            }
        });
        
        
        button.on('click', function() {
            var fieldsID = fields.not(filterFields).val();
            nMails.text('');
            
            if(fieldsID) {
                $.ajax(base_url+'newsletter/count_emails/'+fieldsID, {type:'POST', data: { filter_field: filterFields.val(), filter_op: operators.val(), filter_val: value.val(), manual_where: $('#manual-where').val() }}).success(function(out) {
                    var count = parseInt(out);
                    if(count >= 0) {
                        nMails.html(count + ' e-mail trovate');
                    }
                });
            }
        });
        
        
    });
    
</script>