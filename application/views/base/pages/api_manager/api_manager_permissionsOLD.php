<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="api_permissions_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="api_permissions_label"><?php e('Specific permissions'); ?></h4>
            </div>
            <form id="form_permessi" role="form" method="post" action="<?php echo base_url("api_manager/set_permissions/{$dati['token']}"); ?>" class="form formAjax" enctype="multipart/form-data">
                <?php add_csrf(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-8 col-sm-6">
                            <label class="control-label"><strong><?php e('Entity'); ?></strong></label>
                            <select class="form-control select2_standard field_101 entity_name" name="entity_name" data-source-field="" data-ref="entity_name" data-val="">
                                <option></option>
                                <?php foreach ($this->apilib->tableList() as $entity) : ?>
                                    <option value="<?php echo $entity['name']; ?>"><?php echo $entity['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-4 col-sm-6 hidden">
                            <label class="control-label"><?php e('Permissions'); ?></label>
                            <select class="form-control select2_standard entity_permission" name="entity_permission">
                                <option value="0"><?php e('No permissions'); ?></option>
                                <option value="1">R (<?php e('read only'); ?>)</option>
                                <option value="2">RW (<?php e('update only'); ?>)</option>
                                <option value="3">RW (<?php e('insert only'); ?>)</option>
                                <option value="4">RW (<?php e('insert and update'); ?>)</option>
                                <option value="5"><?php e('All permissions'); ?></option>
                            </select>
                        </div>

                        <div class="form-group col-sm-12 hidden">
                            <label class="control-label"><?php e('Optional where'); ?></label>
                            <textarea class="form-control __select2_standard entity_where" name="entity_where"></textarea>
                        </div>

                        <div class="form-group col-sm-12 hidden">
                            <label class="control-label"><strong><?php e('Single fields permissions'); ?></strong></label>
                            <div id="campi">

                            </div>
                        </div>

                        <div class="form-group col-sm-12">
                            <div id='msg_form_permessi' class="alert alert-danger hide"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group col-sm-12">
                        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal"><?php e('Cancel'); ?></button>
                        <button type="submit" class="btn btn-sm btn-primary pull-right"><?php e('Save'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        'use strict';
        $('.entity_name').on('change', function() {
            var entity_name = $('.entity_name').val();
            if ($('.entity_permission, .entity_where').parent().hasClass('hidden')) {
                $('.entity_permission, .entity_where').parent().removeClass('hidden');
            }
            $.ajax(base_url + 'api_manager/get_entity_permissions/<?php echo $dati['token']; ?>/' + entity_name, {
                dataType: 'json',
                success: function(entity_permission) {
                    $('.entity_permission > option[value="' + entity_permission.api_manager_permissions_chmod + '"]').attr('selected', 'selected');
                    $('.entity_where').html(entity_permission.api_manager_permissions_where);
                    $('.entity_permission').trigger('change');
                }
            });

        });
        $('.entity_permission').on('change', function() {
            var chmod = $('option:selected', $(this)).val();
            var entity_name = $('.entity_name').val();
            //5 corrisponde a "personalizzato", per cui mostro le opzioni disponibili e mostro il blocco, altrimenti ritorno
            //In realtà mostro sempre le personlizzazioni, altrimenti non potrei indicare che su un entità non si può cancellare, ma solo leggere alcuni campi...

            //Prendo tutti i field di questa entità con relativi eventuali permessi assegnati
            $.ajax(base_url + 'api_manager/get_fields_by_entity_name/' + entity_name, {
                dataType: 'json',
                success: function(fields) {
                    $('#campi').html('');
                    if ($('#campi').parent().hasClass('hidden')) {
                        $('#campi').parent().removeClass('hidden');
                    }

                    $.each(fields, function(i, field) {
                        $('#campi').append(`
                            <div class="col-lg-4">
                                <div class="form-group" >
                                    <label class="control-label">` + field.fields_name_friendly + `</label>
                                    <select class="form-control select2_standard" name="` + field.fields_name + `">
                                        <option value=""><?php e('All permissions'); ?></option>
                                        <option value="0"><?php e('No permissions'); ?></option>
                                        <option value="1">R (<?php e('read only'); ?>)</option>
                                        <option value="2">RW (<?php e('update only'); ?>)</option>
                                        <option value="3">RW (<?php e('insert only'); ?>)</option>
                                        <option value="4">RW (<?php e('insert and update'); ?>)</option>
                                    </select>
                                </div>
                            </div>
                        `);
                    });

                    //Dopo aver creato le varie select, prendo gli attuali permessi e li setto
                    $.ajax(base_url + 'api_manager/get_fields_permissions/<?php echo $dati['token']; ?>/' + entity_name, {
                        dataType: 'json',
                        success: function(fields_permission) {
                            $.each(fields_permission, function(i, field) {
                                $('select[name="' + field.fields_name + '"] > option[value="' + field.api_manager_fields_permissions_chmod + '"]').attr('selected', 'selected');
                            });
                        }
                    });
                }
            });
        });
    });
</script>