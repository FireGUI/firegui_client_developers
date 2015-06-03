<h3 class="page-title">Importer <small>import from a CSV file</small></h3>


<form class="formAjax" id="import_form" action="<?php echo base_url('importer/db_ajax/import_1'); ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-upload-alt"></i> Select an entity to import
                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">Entity to import</label>
                            <div class="col-md-5">
                                <select class="form-control" name="entity_id">
                                    <option></option>
                                    <?php foreach($dati['entities'] as $e): ?>
                                        <option value="<?php echo $e['entity_id'] ?>"><?php echo $e['entity_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-3">CSV file</label>
                            <div class="col-md-9">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <span class="uneditable-input">
                                                <i class="icon-file fileupload-exists"></i> 
                                                <span class="fileupload-preview"></span>
                                            </span>
                                        </span>
                                        <span class="btn default btn-file">
                                            <span class="fileupload-new"><i class="icon-paper-clip"></i> Select file</span>
                                            <span class="fileupload-exists"><i class="icon-undo"></i> Change</span>
                                            <input type="file" class="default" name="csv_file" />
                                        </span>
                                        <a href="#" class="btn red fileupload-exists" data-dismiss="fileupload">
                                            <i class="icon-trash"></i> Remove
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        
                        
                        
                        <div class="form-group">
                            <label class="control-label col-md-3">Field separator</label>
                            <div class="col-md-5">
                                <input type="text" class="input-xsmall form-control" name="field_separator" value=";" />
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-3">Multiple values separator</label>
                            <div class="col-md-5">
                                <input type="text" class="input-xsmall form-control" name="multiple_values_separator" value="," />
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-md-3">Action on data present?</label>
                            <div class="col-md-9">
                                <label class="radio-inline">
                                    <input type="radio" name="action_on_data_present" value="1" /> Delete data before import
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="action_on_data_present" value="2" /> Update data (will ask for unique key)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="action_on_data_present" value="3" checked /> Insert
                                </label>
                                
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        
                        <div class="form-group">
                            <div id="msg_import_form" class="alert alert-danger hide"></div>
                        </div>
                        
                    </div>

                    <div class="form-actions fluid">
                        <div class="col-md-offset-8 col-md-4">
                            <div class="pull-right">
                                <a href="<?php echo base_url('importer'); ?>" class="btn default">Cancel</a>
                                <button type="submit" class="btn blue">Continue</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
