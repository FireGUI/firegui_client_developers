<?php echo $label; ?>
<br/>
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
<!--    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
    <span class="btn default btn-file btn-sm">
        <span class="fileinput-new">Seleziona file</span>
        <span class="fileinput-exists">Cambia</span>
        <input type="file" name="<?php echo $field['fields_name']; ?>">
    </span>

    <span class="fileinput-filename"><?php echo $value ?></span>
    &nbsp;
    <a href="javascript:;" class="close fileinput-exists" data-dismiss="fileinput"></a>-->
    
    <div class="row  my_dropzone dropzone upload-drop-zone">
        
    </div>
    
</div>
<?php echo $help; ?>

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template('template/crm-v2/assets/global/plugins/dropzone/css/dropzone.css'); ?>" />
    <script src="<?php echo base_url_template('template/crm-v2/assets/global/plugins/dropzone/dropzone.js'); ?>"></script>
    <script>
        Dropzone.autoDiscover = false;
        $(document).ready(function () {
            
            var myDropzone = new Dropzone(document.querySelector('.my_dropzone'), {
                url: "<?php echo base_url('custom/spese/addFile'); ?>", //TODO....
                autoProcessQueue: false,
                parallelUploads: 1,
                addRemoveLinks: true,
                maxThumbnailFilesize: <?php echo (int)((defined('MAX_UPLOAD_SIZE')?MAX_UPLOAD_SIZE:10000)/1000); ?>,
                maxFilesize: <?php echo (int)((defined('MAX_UPLOAD_SIZE')?MAX_UPLOAD_SIZE:10000)/1000); ?>,
                clickable: true,
                complete: function (file) {
                    //alert(1);
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        //alert('tst');
                        $('#new_fattura').trigger('submit');
                        loading(false);
                    } else {
                        loading(true);
                        $('#new_fattura [type="submit"]').trigger('click');
                    }
                },
                removedfile: function(file) {
                    x = confirm('Do you want to delete?');
                    //console.log(file);
                    if(!x) {
                        return false;
                    } else {
                        if (file.id) {
                            $.ajax(base_url+'custom/spese/removeFile/'+file.id, {
                                //dataType: 'json',
                                success: function() {
                                    file.previewElement.remove();
                                    return true;
                                }
                            });
                        } else {
                            file.previewElement.remove();
                            return true;
                        }                      
                        
                    }
                }

            });
            $('[type="submit"]').click(function(e){

                loading(true);
                e.preventDefault();
                e.stopPropagation();
                if (myDropzone.files.length > 0) {
                    myDropzone.processQueue();
                } else {
                    $('#new_fattura').trigger('submit');
                }

            });

            $('.my_dropzone *').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('.my_dropzone').trigger('click');
            });
            
            
            
            <?php foreach ($spesa['allegati'] as $key => $allegato) : ?>
                // Create the mock file:
                var mockFile = { name: "Allegato <?php echo $key+1; ?>", size: 1000000, id: <?php echo $allegato['spese_allegati_id']; ?>, url: "<?php echo base_url_uploads($allegato['spese_allegati_file']); ?>" };

                // Call the default addedfile event handler
                myDropzone.emit("addedfile", mockFile);

                // And optionally show the thumbnail of the file:
                myDropzone.emit("thumbnail", mockFile, "<?php echo base_url('images/docs_icon.svg'); ?>");

            <?php endforeach; ?>
        });
    </script>