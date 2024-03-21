function initDropzones() {
    var dzfiles = [];
    var myDropzones = [];
    $('.js_dropzone').each(function () {
        var formid = $(this).data('formid');
        var fieldname = $(this).data('fieldname');
        var fieldid = $(this).data('fieldid');
        var unique = $(this).data('unique');
        var url = $(this).data('url');
        var maxuploadsize = $(this).data('maxuploadsize');
        var fieldtype = $(this).data('fieldtype');
        var preview = $(this).data('preview');
        var uploaded_text = $('.dz-helptext', $(this).parent());
        
        if ($(this).data('value')) {
            var value = JSON.parse(atob($(this).data('value')));
        } else {
            var value = $(this).data('value');
        }
        
        var form_selector = '#form_' + formid;
        var modalContainer = $('#js_modal_container');
        
        var campo = $('[data-name="' + fieldname + '"]');
        dzfiles[unique] = [];
        
        let dropzoneControl = $(this)[0].dropzone;
        if (dropzoneControl) {
            dropzoneControl.destroy();
        }
        
        myDropzones[unique] = new Dropzone($(this).get(0), { //queryselector before...
            url: url,
            autoProcessQueue: true,
            parallelUploads: 1,
            addRemoveLinks: true,
            maxThumbnailFilesize: maxuploadsize,
            maxFilesize: maxuploadsize,
            clickable: true,
            params: {
                [token_name]: token_hash,
            },
            init: function () {
                this.on("addedfiles", function (files) {
                    if (typeof $('.modal', modalContainer).data('bs.modal') != 'undefined') {
                        $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                    }
                });
                
                this.on("sending", function (file) {
                    uploaded_text.hide();
                    $(this.element).find('.dz-progress').show();
                    // $('.dz-progress', ).show();
                })
            },
            success: function (file, response) {
                var drop_obj = this;
                $(this.element).find('.dz-progress').hide();
                
                if (typeof $('.modal', modalContainer).data('bs.modal') != 'undefined') {
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                }
                
                $(form_selector).on('submit', function () {
                    return (drop_obj.getUploadingFiles().length === 0 && drop_obj.getQueuedFiles().length === 0);
                });
                
                if (response != null) {
                    response = JSON.parse(response);
                    if (!response.status) {
                        
                        error(response.txt, 'form_' + formid);
                        
                    } else {
                        
                        dzfiles[unique].push(response.file);
                        
                        if (Number.isInteger(response.file)) {
                            $(form_selector).append(campo.clone().attr('name', campo.data('name') + '[]').val(response.file));
                            
                        } else {
                            if (!$('[name="' + fieldname + '"]').length) {
                                campo.attr('name', campo.data('name'));
                                campo.val(JSON.stringify(dzfiles[unique]));
                            } else {
                                $('[name="' + fieldname + '"]').val(JSON.stringify(dzfiles[unique]));
                            }
                        }
                        
                        $('.dz-progress').hide();
                        uploaded_text.show();
                    }
                }
                if (preview && file.url) {
                    var a = document.createElement('a');
                    a.setAttribute('href', file.url);
                    a.setAttribute('class', 'dz-download');
                    a.setAttribute('target', '_blank');
                    a.innerHTML = "Download";
                    file.previewTemplate.appendChild(a);
                }
                
            },
            removedfile: function (file) {
                var x = confirm('Do you want to delete?');
                
                if (!x) {
                    return false;
                }
                
                var dzfile_index = dzfiles[unique].findIndex(item => item.original_filename == file.name);
                
                var file_id = (dzfile_index != '-1') ? dzfile_index : file.id;
                
                $.ajax(base_url + 'db_ajax/removeFileFromMultiUpload/' + fieldname + '/' + $(form_selector).data('edit-id') + '/' + file_id, {
                    success: function () {
                        file.previewElement.remove();
                        dzfiles[unique].splice(file_id, 1);
                        
                        $('[name="' + fieldname + '"]').val(JSON.stringify(dzfiles[unique]));
                        $('[name="' + fieldname + '[]"][value="' + file.intid + '"]').remove();
                    }
                });
                
                return true;
            }
        });
        
        //Trigger click on every internal element (div, text, etc...)
        $('*', $(this)).on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).trigger('click');
        });
        
        try {
            if (typeof value === "string") {
                var json_decoded_value = (value != '') ? (JSON.parse(value)) : [];
            } else {
                var json_decoded_value = value;
            }
            
            
            for (var index in json_decoded_value) {
                
                var file = json_decoded_value[index];
                dzfiles[unique].push(file);
                
                if (file.hasOwnProperty('client_name')) {
                    var mockFile = {
                        name: file.client_name,
                        key: index,
                        size: file.file_size,
                        id: index,
                        url: base_url_uploads + 'uploads/' + file.path_local,
                    };
                } else {
                    var mockFile = {
                        name: 'Attachment ' + index,
                        intid: index,
                        size: 1000000,
                        id: index,
                        url: base_url_uploads + 'uploads/' + file.path_local,
                    };
                }
                // Create the mock file:
                
                
                
                if (preview) {
                    myDropzones[unique].emit("addedfile", mockFile);
                    
                    myDropzones[unique].emit("thumbnail", mockFile, base_url_uploads + ((file.is_image) ? 'uploads/' + file.path_local : 'images/' + 'no_image.png'));
                    
                    myDropzones[unique].emit("success", mockFile, null);
                }
                
            };
        } catch (Exception) {
            console.log(Exception);
        }
    });
}

$(function () {
    'use strict';
    
    initDropzones();
    $(window).on('shown.bs.modal', function () {
        initDropzones();
    });
});
