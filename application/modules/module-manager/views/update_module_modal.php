<?php $f = isset($dati['form']) ? $dati['form'] : NULL; ?>
<style>
    .dropzone {
        min-height: 200px !important;
        border-width: 0 !important;
        background-repeat: no-repeat !important;
        background-position: 50% 50% !important;
        background-size: 300px auto !important;
        border-style: none !important;
        border-color: transparent !important;
        line-height: 20px !important;
        text-align: left !important;
        background-image: url(<?php echo base_url('images/drop_zone.png'); ?>) !important;
    }

    .dz-preview {
        float:left;
        margin: 10px 10px 10px 0px;
    }
</style>
<div class="modal fade js_module_repository_modal" id="update_module_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4>Publish to repositor an update of your module</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?php echo base_url("module_manager/publish_to_repository") ?>" id="js_update_module_form" class="form-horizontal formAjax">
                    <input type="hidden" name="modules_repository_identifier" value="" />

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
                                        <label class="control-label">Small description:</label>
                                        <input name="modules_repository_small_description" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
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
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Description:</label>
                                        <textarea class="ck_editor" name="modules_repository_description" id="_modules_repository_description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Changelog:</label>
                                        <textarea required name="modules_repository_changelog" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Thumbnail:</label>
                                        <input type="file" name="modules_repository_thumbnail" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="control-label">Youtube link:</label>
                                        <input type="text" __name="modules_repository_youtube_link" class="form-control">
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

                        <hr >
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-3">
                                <div id="msg_js_update_module_form" class="hide alert alert-danger"></div>
                            </div>
                        </div>

                        <ul>
                            <li>Current version: <span class="js_modules_repository_version label label-primary"></span></li>
                            <li>Version Code: <span class="js_modules_repository_version_code label label-default"></span></li>
                            <li>Identifier: <span class="js_modules_repository_identifier label label-info"></span></li>
                        </ul>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>


                <div class="callout callout-danger" id="msg_error" style="display:none;"></div>
                <form action="" autocomplete="off" enctype="multipart/form-data" method="post" id="module_screenshots">
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label">Screenshots:</label>
                            <div class="dropzone myDropzone">
                                <input type="hidden" name="screenshot" class="form-control" multiple />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    Dropzone.autoDiscover = false;
    Dropzone.options.myDropzone = false;
    var myDropzone;
    $(".js_module_repository_modal").on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var module_identifier = button.data('id');
        var id = $('.ck_editor').attr('id');
        CKEDITOR.replace(id);

        myDropzone = new Dropzone('.myDropzone', {
            url: base_url + 'module_manager/upload_module_screenshots',
            paramName: "screenshot",
            maxFilesize: 2,
            maxFiles: 5,
            parallelUploads: 5,
            addRemoveLinks: true,
            dictMaxFilesExceeded: "You can only upload up to 5 images",
            dictRemoveFile: "Delete",
            dictCancelUploadConfirmation: "Are you sure to cancel upload?",
            dictDefaultMessage: '',
            params: {'module_ref': module_identifier},

            init: function () {
                $.ajax({
                    url: base_url + "module_manager/get_module_screenshots/",
                    dataType: 'json',
                    type: 'post',
                    cache: false,
                    data: {module_ref: module_identifier},
                    success: function(data) {
                        console.log(data);
                        if(data.status == 1){
                            $.each(data.txt, function (key, value) {
                                var myMockFile = {name: value.module_screenshots_file, size: value.module_screenshots_size, type: value.module_screenshots_mime_type};
                                var file = {width: 128, height: 128};
                                
                                myDropzone.emit("addedfile", myMockFile);
                                myDropzone.emit("thumbnail", myMockFile, base_url+'uploads/modules_repository/'+myMockFile.name);
                                myDropzone.createThumbnailFromUrl(file, base_url+'uploads/modules_repository/'+myMockFile.name);
                                myDropzone.emit("complete", myMockFile);
                            });
                        }
                    },
                });
            },

            accept: function (file, done) {
                if ((file.type).toLowerCase() != "image/jpg" &&
                    (file.type).toLowerCase() != "image/gif" &&
                    (file.type).toLowerCase() != "image/jpeg" &&
                    (file.type).toLowerCase() != "image/png"
                ) {
                    $(file.previewElement).remove();
                    $('#msg_error').html('');
                    $('#msg_error').html('Invalid file').show();

                    setTimeout(function(){
                        $('#msg_error').html('').hide();
                    }, 2500);
                } else {
                    done();
                }
            },
        });

        $.ajax({
            url: base_url + "ajax/get_module_repository_details/" + module_identifier,
            dataType: 'json',
            cache: false,
            success: data => {
            $.each(data, function(key, value){
                if(key == 'modules_repository_description'){
                    CKEDITOR.instances[id].setData(value);
                    // $('[name='+key+']', $("#js_update_module_form")).val(value);
                } else {
                    $('[name='+key+']', $("#js_update_module_form")).val(value);
                }

                $('.js_'+key, $("#update_module_modal")).html(value);
            });
    },
        error: err => {
            console.error(err);
        }
    });


    });

    $(".js_module_repository_modal").on('hidden.bs.modal', function (event) {
        var cke_id = $('.ck_editor').attr('id');

        if(CKEDITOR.instances[cke_id]){
            CKEDITOR.instances[cke_id].destroy();
        }

        myDropzone.destroy();
    });
</script>
