<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="myModalLabel">Permessi specifici</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form portlet gren">
                            <div class="portlet-body form">
                                <form id="form_permessi" role="form" method="post" action="<?php echo base_url("api_manager/set_permissions/{$dati['token']}"); ?>" class="form formAjax" enctype="multipart/form-data">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <div class="form-group" >
                                                    <label class="control-label"><strong>Entit&agrave;</strong></label>
                                                    <?php //debug($this->apilib->entityList()); ?>
                                                    <select class="form-control select2me field_101 entity_name" name="entity_name" data-source-field="" data-ref="entity_name" data-val="" >
                                                        <option></option>
                                                        <?php foreach ($this->apilib->tableList() as $entity) : ?>
                                                            <option value="<?php echo $entity['name']; ?>" ><?php echo $entity['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group" style="display:none;">
                                                    <label class="control-label">Permessi</label>
                                                    <select class="form-control select2me entity_permission" name="entity_permission">
                                                        <option value="">Tutti i permessi</option>
                                                        <option value="0">Nessun permesso</option>
                                                        <option value="1">R (sola lettura)</option>
                                                        <option value="2">RW (solo update)</option>
                                                        <option value="3">RW (solo insert)</option>
                                                        <option value="4">RW (insert e update)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group" style="display:none;">
                                                    <label class="control-label">Where opzionale</label>
                                                    <textarea class="form-control select2me entity_where" name="entity_where"></textarea>
                                                        
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group" style="display:none;">
                                                    <label class="control-label"><strong>Permessi sui singoli campi</strong></label>
                                                    <div id="campi">
                                                        
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div id='msg_form_permessi' class="alert alert-danger hide"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-actions right">
                                            <button type="button" class="btn default" data-dismiss="modal">Annulla</button>
                                            <button type="submit" class="btn green">Salva</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <script>
    $(document).ready(function () {
        $('.entity_name').on('change', function () {
            var entity_name = $('.entity_name').val();
            $('.entity_permission, .entity_where').parent().show();
            $.ajax(base_url+'api_manager/get_entity_permissions/<?php echo $dati['token']; ?>/'+entity_name, {
                dataType: 'json',
                success: function(entity_permission) {
                    $('.entity_permission > option[value="'+entity_permission.api_manager_permissions_chmod+'"]').attr('selected', 'selected');
                    $('.entity_where').html(entity_permission.api_manager_permissions_where);
                    $('.entity_permission').trigger('change');
                }
            });
            
        });
        $('.entity_permission').on('change', function () {
            var chmod = $('option:selected', $(this)).val();
            var entity_name = $('.entity_name').val();
            //5 corrisponde a "personalizzato", per cui mostro le opzioni disponibili e mostro il blocco, altrimenti ritorno
            //In realtà mostro sempre le personlizzazioni, altrimenti non potrei indicare che su un entità non si può cancellare, ma solo leggere alcuni campi...
//            if (chmod != 5) {
//                return;
//            }
            //Prendo tutti i field di questa entità con relativi eventuali permessi assegnati
            $.ajax(base_url+'api_manager/get_fields_by_entity_name/'+entity_name, {
                dataType: 'json',
                success: function(fields) {
                    $('#campi').html('');
                    $('#campi').parent().show();
                    
                    $.each(fields,function (i, field) {
                        //$('<input/>').val(field.fields_name).text(field.fields_name).appendTo($('.entity_name'));
                        $('#campi').append(`
                            <div class="col-lg-4">
                                <div class="form-group" >
                                    <label class="control-label">`+field.fields_name_friendly+`</label>
                                    <select class="form-control select2me" name="`+field.fields_name+`">
                                        <option value="">Tutti i permessi</option>
                                        <option value="0">Nessun permesso</option>
                                        <option value="1">R</option>
                                        <option value="2">RW (solo update)</option>
                                        <option value="3">RW (solo insert)</option>
                                        <option value="4">RW (insert e update)</option>
                                    </select>
                                </div>
                            </div>
                        `);
                    });
                    
                    //Dopo aver creato le varie select, prendo gli attuali permessi e li setto
                    $.ajax(base_url+'api_manager/get_fields_permissions/<?php echo $dati['token']; ?>/'+entity_name, {
                        dataType: 'json',
                        success: function(fields_permission) {
                            $.each(fields_permission,function (i, field) {
                                $('select[name="'+field.fields_name+'"] > option[value="'+field.api_manager_fields_permissions_chmod+'"]').attr('selected', 'selected');
                            });                            
                        }
                    });
                    
                    

                    
                }
            });
        });
    });
    </script>