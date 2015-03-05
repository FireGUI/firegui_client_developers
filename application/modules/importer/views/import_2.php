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
                                                    <?php echo $field; ?>
                                                    <br/>
                                                    <select name="csv_fields[<?php echo $k; ?>]">
                                                        <option></option>
                                                        <?php foreach ($dati['fields'] as $e_field): ?>
                                                            <option value="<?php echo $e_field['fields_name']; ?>"><?php echo $e_field['fields_name']; ?></option>
                                                        <?php endforeach; ?>
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
