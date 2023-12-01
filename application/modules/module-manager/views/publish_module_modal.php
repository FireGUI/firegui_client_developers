<?php $f = isset($dati['form']) ? $dati['form'] : NULL; ?>
<div class="modal fade " id="publish_module_modal">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="<?php echo base_url("module_manager/publish_to_repository") ?>" id="js_publish_module_form" class="form-horizontal formAjax">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                        <h3>Are you ready to publish your own module? Congratulations and good luck!</h3>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="modules_id" class="js_module_id" value="" />
                    <input type="hidden" name="modules_repository_created_by_user" value="" />

                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Name:</label>
                                        <input required type="text" name="modules_repository_name" class="form-control" value="" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Identifier:</label>
                                        <input required type="text" name="modules_repository_identifier" class="form-control" value="" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Small description:</label>
                                        <textarea name="modules_repository_small_description" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Description:</label>
                                        <textarea name="modules_repository_description" id="modules_repository_description" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Thumbnail:</label>
                                        <input type="file" name="modules_repository_thumbnail" class="form-control"></input>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Version:</label>
                                        <input required type="text" name="modules_repository_version" class="form-control" value="1.0.0" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">$ Price:</label>
                                        <input required type="text" name="modules_repository_price" class="form-control" value="0" />
                                    </div>
                                </div>
                            </div>


                            <!--<div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Versione Code: </label>
                                        <input required type="text" name="modules_version_code" class="form-control" value="1" />
                                        <small>must be integer and you have to increment for each version</small>
                                    </div>
                                </div>
                            </div>-->
                        </div>



                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-3">
                                <div id="msg_js_publish_module_form" class="hide alert alert-danger"></div>
                            </div>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->

</div>


<script>
    $("#publish_module_modal").on('shown.bs.modal', function (event) {
        
        var button = $(event.relatedTarget) // Button that triggered the modal
        var module_id = button.data('id');
        $.ajax({
            url: base_url + "ajax/get_element_details/modules/modules_id/" + module_id,
            dataType: 'json',
            cache: false,
            success: data => {

                $('.js_module_id').val(data.modules_id);
                $.each(data, function(key, value){

                    key = key.replace('modules_', 'modules_repository_');

                    $('[name='+key+']', $("#js_publish_module_form")).val(value);
                    $('.js_'+key, $("#publish_module_modal")).html(value);
                });

            },
            error: err => {
                console.error(err);
            }
        })
    });
</script>
