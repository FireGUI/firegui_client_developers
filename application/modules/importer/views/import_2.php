<span style="font-size: 20px;color:#FF0000;">MODULO IN AGGIORNAMENTO, non utilizzare!</span>

<h3 class="page-title">Importer <small>CSV-entity mapping</small></h3>


<form class="formAjax" id="import_map_form" action="<?php echo base_url('importer/db_ajax/import_2'); ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-upload-alt"></i> Map the CSV with the entity fields
                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <div class="form-group">
                            <?php // debug($dati['csv']) ?>
                            <table class="table table-striped table-condensed table-bordered table-responsive-scrollable">
                                <thead>
                                    <tr>
                                        <?php foreach ($dati['csv_head'] as $k=>$field): ?>
                                            <?php if ($field): ?>
                                                <th class="text-center">
                                                    <?php echo $field; ?><?php if ($dati['import_data']['action_on_data_present'] == 2): ?> (<input style="margin-left:0px;" type="radio" name="unique_key" value="<?php echo $k; ?>" /> chiave per update)<?php endif; ?>
                                                    <br/>
                                                    <select class="js-select-field" name="csv_fields[<?php echo $k; ?>]">
                                                        <option data-ref=""></option>
                                                        <?php foreach ($dati['fields'] as $e_field): ?>
                                                            <option data-ref="<?php echo $e_field['fields_ref']; ?>" data-key="<?php echo $k; ?>" value="<?php echo $e_field['fields_name']; ?>"><?php echo $e_field['fields_name']; ?></option>
                                                        <?php endforeach; ?>
                                                            <?php //Ciclo di nuovo i field e prendo solo quelli con fields_ref impostato, per stampare anche la mappatura dell'entità referenziata ?>
                                                        <?php /*foreach ($dati['fields'] as $e_field): ?>
                                                            <?php if ($e_field['fields_ref']) : ?>
                                                            <option>-----------------</option>
                                                            <?php foreach ($e_field[$e_field['fields_ref']]['fields'] as $ref_field) : ?>
                                                            <option value="<?php echo $e_field['fields_name']; ?>##<?php echo $ref_field['fields_name']; ?>">[<?php echo $e_field['fields_name']; ?>] <?php echo $ref_field['fields_name']; ?></option>
                                                            <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; */?>
                                                    </select>
                                                </th>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dati['csv_body'] as $row): ?>
                                        <?php if (!empty($row)): ?>
                                            <tr>
                                                <?php foreach ($row as $field): ?>
                                                    <td><?php echo $field; ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>


                        <div class="form-group">
                            <div id="msg_import_map_form" class="alert alert-danger hide"></div>
                        </div>
                        
                        <div class="form-group">
                            <div id="js_import_test_result" class="alert hide"></div>
                        </div>


                    </div>



                    <div class="form-actions fluid">
                        <div class="col-md-offset-8 col-md-4">
                            <div class="pull-right">
                                <a href="<?php echo base_url('importer'); ?>" class="btn default">Cancel</a>
                                <button type="button" class="btn purple js_test_import">Test</button>
                                <button type="submit" class="btn blue">Continue</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('.js-select-field').change(function() {
            $(this).siblings('select').remove();
            var current_select = $(this);
            var entity_name = $(this).find(":selected").attr('data-ref');
            var key = $(this).find(":selected").attr('data-key');
            if (entity_name != '') {
                //Fare ajax per chiedere su quale campo chiave mappare
                $.ajax(base_url+'importer/db_ajax/get_fields_by_entity_name/'+entity_name, {
                    dataType: 'json',
                    success: function(fields) {
                        
                        var new_select = document.createElement('select');
                        new_select.name = "ref_fields["+key+"]";
                        
                        var option = document.createElement('option');
                        option.value = entity_name+"_id";
                        option.text = entity_name+"_id";
                        new_select.add(option);
                        
                        $.each(fields,function (i, field) {
                            var option = document.createElement('option');
                            option.value = field.fields_name;
                            option.text = field.fields_name;
                            new_select.add(option);
                            //console.log(field);
                        });
                        
                        current_select.parent().append(new_select);
                    }
                });
                    //Con l'output dell'ajax, creare altra select dalla quale scegliere il field (di default l'id) sul quale mappare
                    
            } else {
                //TODO: rimuovo eventuale select precedentemente appesa
            }
        });
    });
    </script>
