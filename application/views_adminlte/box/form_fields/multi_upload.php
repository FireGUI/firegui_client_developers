<?php
$unique = $field['fields_id'];
$form_id = $field['forms_fields_forms_id'];
//debug($field);
?>
<style>
    div.upload-drop-zone {
        min-height: 200px !important;
        border-width: 3px !important;
        background: url('<?php echo base_url(); ?>images/drop_zone.png');
        background-repeat: no-repeat;
        background-position: 50% 50%;
        background-size: 300px auto;
        border-style: dashed !important;
        border-color: #3c8dbc;
        line-height: 20px;
        text-align: left;
    }

    .dropzone .dz-preview .dz-details img,
    .dropzone-previews .dz-preview .dz-details img {
        background-color: #ffffff;
    }

    .dz-message {
        display: none !important;
    }
</style>


<?php //debug($value); 
?>
<?php echo $label; ?>
<br />
<div class="col-md-12 <?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
    <input type="hidden" class="default" data-name="<?php echo $field['fields_name']; ?>" />
    <?php if (is_array($value)) : ?>

        <?php foreach ($value as $file_id => $file) : ?>
            <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>[]" value="<?php echo $file_id; ?>" />
        <?php endforeach; ?>
    <?php elseif (!empty($value)) : ?>
        <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
    <?php endif; ?>

    <div class="row  my_dropzone<?php echo $unique; ?> dropzone upload-drop-zone">

    </div>

</div>
<?php echo $help; ?>



<script>
    var form_selector = '#form_<?php echo $form_id; ?>';
    $(document).ready(function() {
        var modalContainer = $('#js_modal_container');


        var campo = $('[data-name="<?php echo $field['fields_name']; ?>"]');
        var files<?php echo $unique; ?> = [];
        var myDropzone<?php echo $unique; ?> = new Dropzone(document.querySelector('.my_dropzone<?php echo $unique; ?>'), {
            url: "<?php echo base_url("db_ajax/multi_upload_async/{$field['fields_id']}"); ?>",
            autoProcessQueue: true,
            parallelUploads: 1,
            addRemoveLinks: true,
            maxThumbnailFilesize: <?php echo (int) ((defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10000) / 1000); ?>,
            maxFilesize: <?php echo (int) ((defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10000) / 1000); ?>,
            clickable: true,


            success: function(file, response) {
                var drop_obj = this;
                var cansubmit = (drop_obj.getUploadingFiles().length === 0 && drop_obj.getQueuedFiles().length === 0);
                if (cansubmit) {
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = false;
                } else {
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                }
                $(form_selector).on('submit', function() {
                    return (drop_obj.getUploadingFiles().length === 0 && drop_obj.getQueuedFiles().length === 0);
                });

                //console.log(file);
                response = JSON.parse(response);
                //console.log(response);
                if (!response.status) {

                    error(response.txt, 'form_<?php echo $form_id; ?>');

                } else {

                    files<?php echo $unique; ?>.push(response.file);

                    if (Number.isInteger(response.file)) {
                        $(form_selector).append(campo.clone().attr('name', campo.data('name') + '[]').val(response.file));

                    } else {
                        if (!$('[name="<?php echo $field['fields_name']; ?>"]').length) {
                            //console.log(typeof campo.attr('name'));
                            campo.attr('name', campo.data('name'));
                            campo.val(JSON.stringify(files<?php echo $unique; ?>));
                        } else {
                            $('[name="<?php echo $field['fields_name']; ?>"]').val(JSON.stringify(files<?php echo $unique; ?>));
                        }



                    }

                    //console.log(files<?php echo $unique; ?>.length);
                    //                        //console.log(files);
                    //                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    //                            campo.val(JSON.stringify(files));
                    //                            //console.log('invio form (<?php echo $unique; ?>)');
                    //                            $(form_selector+' [type="submit"]').trigger('click');
                    //                            //$(form_selector).trigger('submit');
                    //                            loading(false);
                    //                        } else {
                    //                            loading(true);
                    //                            $(form_selector+' [type="submit"]').trigger('click');
                    //                        }
                }
            },
            removedfile: function(file) {
                x = confirm('Do you want to delete?');
                //console.log(file);
                if (!x) {
                    return false;
                } else {
                    if (file.id) {
                        <?php if ($field['fields_type'] == 'JSON') : ?>
                            $.ajax(base_url + 'db_ajax/removeFileFromJson/' + file.id, {
                                success: function() {
                                    file.previewElement.remove();
                                    //delete files<?php echo $unique; ?>[file.key];
                                    files<?php echo $unique; ?>.splice(file.key, 1);
                                    $('[name="<?php echo $field['fields_name']; ?>"]').val(JSON.stringify(files<?php echo $unique; ?>));
                                    return true;
                                }
                            });
                        <?php else : ?>
                            $.ajax(base_url + 'db_ajax/removeFileFromRelation/' + file.id, {
                                //dataType: 'json',
                                success: function() {
                                    file.previewElement.remove();
                                    $('[name="<?php echo $field['fields_name']; ?>[]"][value="' + file.intid + '"]').remove();
                                    return true;
                                }
                            });
                        <?php endif; ?>
                    } else {
                        file.previewElement.remove();
                        return true;
                    }

                }
            }

        });
        //            $(form_selector+' [type="submit"]').click(function(e){
        //                console.log('dentro<?php echo $unique; ?>');
        //                loading(true);
        //                e.preventDefault();
        //                e.stopImmediatePropagation();
        //                if (myDropzone<?php echo $unique; ?>.files.length > 0) {
        //                    myDropzone<?php echo $unique; ?>.processQueue();
        //                } else {
        //                    console.log('invio form (<?php echo $unique; ?>)');
        //                    //$(form_selector+' [type="submit"]').unbind('click');
        //                    //$(form_selector).trigger('submit');
        //                }
        //
        //            });

        $('.my_dropzone<?php echo $unique; ?> *').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.my_dropzone<?php echo $unique; ?>').trigger('click');
        });



        <?php
        //il decode lo devo fare perchè nel datab.php viene fatto htmlspecialchars...
        if (is_string($value)) {
            $value = htmlspecialchars_decode($value);
        }

        //debug(json_decode(htmlspecialchars_decode($value)),true);
        if (@json_decode($value)) {

            $files = json_decode($value);
            //debug($files,true);
            foreach ($files as $key => $file) : //debug($file,true);
        ?>
                files<?php echo $unique; ?>.push(<?php echo json_encode($file); ?>);

                // Create the mock file:
                var mockFile = {
                    name: "<?php echo $file->client_name; ?>",
                    key: <?php echo $key; ?>,
                    size: <?php echo $file->file_size; ?>,
                    id: '<?php echo "{$field['forms_fields_fields_id']}/"; ?>' + $(form_selector).data('edit-id') + '<?php echo "/{$key}"; ?>',
                    url: "<?php echo base_url_uploads('uploads/' . $file->path_local); ?>"
                };

                // Call the default addedfile event handler
                myDropzone<?php echo $unique; ?>.emit("addedfile", mockFile);

                // And optionally show the thumbnail of the file:
                myDropzone<?php echo $unique; ?>.emit("thumbnail", mockFile, "<?php echo base_url_uploads(($file->is_image) ? "uploads/{$file->path_local}" : 'no-image.png'); ?>");

            <?php endforeach;
        } else {
            //debug($value,true);
            $files = (array) $value;
            $key = 0;
            foreach ($files as $file_id => $file) : ?>
                <?php if (empty($field['field_support_id'])) {
                    continue;
                } ?>
                // Create the mock file:
                var mockFile = {
                    name: "Allegato <?php echo $key + 1; ?>",
                    intid: <?php echo $file_id; ?>,
                    size: 1000000,
                    id: '<?php echo "{$field['field_support_id']}/{$field['fields_id']}/{$file_id}"; ?>',
                    url: "<?php echo base_url_uploads('uploads/' . $file); ?>"
                };

                // Call the default addedfile event handler
                myDropzone<?php echo $unique; ?>.emit("addedfile", mockFile);
                //files_count_uploading<?php echo $form_id; ?>--;
                // And optionally show the thumbnail of the file:
                myDropzone<?php echo $unique; ?>.emit("thumbnail", mockFile, "<?php echo base_url_uploads('uploads/' . $file); ?>");

        <?php endforeach;
        }
        ?>
    });
</script>