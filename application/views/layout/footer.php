<!-- BEGIN FOOTER -->
<div class="footer">
    <div class="footer-inner">
        Powered by <a href="http://h2-web.it" class="text-danger" target="_blank">H2 web</a>
    </div>
    <div class="footer-tools">
        <span class="go-top">
            <i class="icon-angle-up"></i>
        </span>
    </div>
</div>

<div id="js_modal_container"></div>

<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->   
<!--[if lt IE 9]>
<script src="<?php echo base_url_template("template/crm/plugins/respond.min.js?v=".$this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_template("template/crm/plugins/excanvas.min.js?v=".$this->config->item('version')); ?>"></script> 
<![endif]-->   



<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="<?php echo base_url_template("template/crm/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/bootstrap/js/bootstrap.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js?v=".$this->config->item('version')); ?>" type="text/javascript" ></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery.blockui.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>  
<script src="<?php echo base_url_template("template/crm/plugins/jquery.cookie.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/uniform/jquery.uniform.min.js?v=".$this->config->item('version')); ?>" type="text/javascript" ></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo base_url_template("template/crm/plugins/flot/jquery.flot.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/flot/jquery.flot.resize.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery.pulsate.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/moment.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>     
<script src="<?php echo base_url_template("template/crm/plugins/gritter/js/jquery.gritter.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>

<!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
<script src="<?php echo base_url_template("template/crm/plugins/fullcalendar/fullcalendar/fullcalendar.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery-easy-pie-chart/jquery.easy-pie-chart.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery.sparkline.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>  
<!-- END PAGE LEVEL PLUGINS -->

<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo base_url_template("template/crm/scripts/app.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/scripts/index.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/scripts/tasks.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>        
<!-- END PAGE LEVEL SCRIPTS -->  

<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/select2/select2.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/data-tables/jquery.dataTables.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/data-tables/DT_bootstrap.js?v=".$this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_template("template/crm/scripts/table-managed.js?v=".$this->config->item('version')); ?>"></script> 
<script src="<?php echo base_url_template("template/crm/plugins/bootstrap-fileupload/bootstrap-fileupload.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>     


<!-- Datepicker -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-datepicker/js/locales/bootstrap-datepicker.it.js?v=".$this->config->item('version')); ?>"></script>

<!-- Datetime picker -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v=".$this->config->item('version')); ?>"></script>

<!-- Colorpicker / Timepicker -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v=".$this->config->item('version')); ?>"></script>


<!-- Daterange picker -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker.js?v=".$this->config->item('version')); ?>"></script> 


<script src="<?php echo base_url_template("script/js/submitajax.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script> 
<script src="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.js?v=".$this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_template("script/js/leaflet.geocoding.js?v=".$this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_template("script/lib/marker-cluster/leaflet.markercluster-src.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/js/ajax-tables.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/js/data-tables.js?v=".$this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_template("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("script/lib/ckeditor/ckeditor.js?v=".$this->config->item('version')); ?>"></script>
<!-- END JAVASCRIPTS -->









<script>
    function initComponents() {
        
        /*
         * Form dates
         */
        $('.js_form_datepicker').datepicker({ todayBtn: 'linked', format: 'dd/mm/yyyy', todayHighlight: true, weekStart: 1, language: 'it' });
        $('.js_form_timepicker').timepicker({ autoclose: true, modalBackdrop: true, showMeridian: false, format: 'hh:ii', minuteStep: 5 });
        $('.js_form_datetimepicker').datetimepicker({ todayBtn: 'linked', format: 'dd/mm/yyyy hh:ii', minuteStep: 5, autoclose: true, pickerPosition: 'bottom-left', forceParse: false, todayHighlight: true, weekStart: 1, language: 'it' });
        
        $('.js_form_daterangepicker').each(function() {
            var jqDateRange = $(this);
            var sDate = $('input', jqDateRange).val();
            
            var start = new Date(), end = new Date();
            if(sDate) {
                var aDates = sDate.split(' - ');
                if(aDates.length === 2) {
                    start = aDates[0];
                    end = aDates[1];
                }
            }
                    
            jqDateRange.daterangepicker({
                format: 'DD/MM/YYYY',
                separator: ' to ',
                startDate: start,
                endDate: end,
                locale: {
                    applyLabel: 'Applica',
                    cancelLabel: 'Annulla',
                    fromLabel: 'Da',
                    toLabel: 'A',
                    weekLabel: 'W',
                    customRangeLabel: 'Range custom',
                    daysOfWeek: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa"],
                    monthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
                    firstDay: 1
                }
            }, function (start, end) {
                $('input', this.element).val(start.format('DD/MM/YYYY')+' - '+end.format('DD/MM/YYYY'));
            });
        });
        $('.js_form_colorpicker').colorpicker({ format: 'hex' });
        
        
        /*
         * Grid-filtering forms
         */
        var forms = $('.js_filter_form');
        forms.each(function() {
            var form = $(this);
            var btn = $('.js_filter_form_add_row', form).on('click', function() {
                var container = $('.js_filter_form_rows_container', form),
                    rows = $('.js_filter_form_row', container);
                var size = rows.size();
                var obj = rows.filter(':first').clone();
                $('input, select', obj).each(function() {
                    var name = 'conditions['+size+']['+$(this).attr('data-name')+']';
                    $(this).attr('name', name).removeAttr('data-name');
                });
                obj.removeClass('hide').appendTo(container);
            });
        });
        
        /*
         * Select, Multiselect e AjaxSelect
         */
        try {
            $('.js_multiselect:not(.select2-offscreen):not(.select2-container)').each(function() {
                var minInput = $(this).attr('data-minimum-input-length');
                $(this).select2({allowClear: true, minimumInputLength: minInput? minInput: 0});
            });
            $('.select2me').select2({allowClear: true});
        } catch (e) {}
        
        var fieldsSources = [];
        $('[data-source-field]:not([data-source-field=""])').each(function() {
            // Prendo il form dell'elemento
            var jsMultiselect = $(this);
            var jqForm = jsMultiselect.parents('form');
            var sSourceField = jsMultiselect.attr('data-source-field');
            var sFieldRef = jsMultiselect.attr('data-ref');
            
            // Prendo il campo da osservare
            var jqField = $('[name="'+sSourceField+'"]', jqForm);
            jqField.on('change', function() {
                var previousValue = jsMultiselect.attr('data-val');
                jsMultiselect.select2('val', '');
                $('option', jsMultiselect).remove();
                
                loading(true);
                $.ajax(base_url+'get_ajax/filter_multiselect_data', {
                    type: 'POST',
                    data: { field_name_to: jsMultiselect.attr('name'), field_ref: sFieldRef, field_from_val: jqField.val() },
                    dataType: 'json',
                    complete: function() {
                        loading(false);
                    },
                    success: function(data) {
                        $('<option></option>').appendTo(jsMultiselect);
                        var previousValueFound = false;
                        $.each(data, function(k, v) {
                            var jqOption = $('<option></option>').val(k).text(v);
                            
                            if(previousValue == k) {
                                previousValueFound = true;
                            }
                            
                            jsMultiselect.append(jqOption);
                        });
                        
                        if(previousValueFound) {
                            jsMultiselect.select2('val', previousValue);
                        }
                    }
                });
            });
            
            if($.inArray(jqField.selector, fieldsSources) === -1) {
                fieldsSources.push(jqField.selector);
            }
        });
        
        $.each(fieldsSources, function(k, selector) {
            var field = $(selector);
            if(field.val() !== '') {
                field.trigger('change');
            }
        });
        
        
        
        
        /*
         * Select ajax
         */
         $('.js_select_ajax').each(function() {
             $(this).select2({
                allowClear: true,
                placeholder: 'Cerca valori',
                minimumInputLength: 0,
                ajax: {
                    url: base_url+'get_ajax/select_ajax_search',
                    dataType: 'json',
                    type: 'POST',
                    data: function (term, page) { return {q: term, limit: 100, table: $(this).attr('data-ref')}; },
                    results: function (data, page) { return {results: data}; }
                },
                initSelection: function (element, callback) {
                    var id = $(element).val();
                    if (id !== "") {
                        $.ajax(base_url+'get_ajax/select_ajax_search', {
                            type: 'POST',
                            dataType: "json",
                            data: { table: $(element).attr('data-ref'), id: id }
                        }).done(function (data) {
                            callback(data);
                        });
                    }
                },
                formatResult: function(rowData) { return rowData.name; },
                formatSelection: function(rowData) { return rowData.name; }
            });
         });
         
         $('.js_select_ajax_distinct').each(function() {
             $(this).select2({
                allowClear: true,
                placeholder: 'Cerca',
                minimumInputLength: 0,
                ajax: {
                    url: base_url+'get_ajax/get_distinct_values',
                    dataType: 'json',
                    type: 'POST',
                    data: function (term, page) { return {q: term, limit: 50, field: $(this).attr('data-field-id')}; },
                    results: function (data, page) { return {results: data}; }
                },
                initSelection: function (element, callback) {
                    var id = $(element).val();
                    if (id !== "") {
                        $.ajax(base_url+'get_ajax/get_distinct_values', {
                            type: 'POST',
                            dataType: "json",
                            data: { limit: 50, field: $(element).attr('data-field-id'),table: $(element).attr('data-ref'), id: id }
                        }).done(function (data) {
                            callback(data);
                        });
                    }
                },
                formatResult: function(rowData) { return rowData.name; },
                formatSelection: function(rowData) { return rowData.name; }
            });
         });
         
         
         /**
          * Fancybox
          */
         $('a.js_thumbnail:not([rel=""]), .fancybox').fancybox();


         /**
          * Double click on checked checkboxes and radio
          * to activate onclick actions by default
          */
         $('input[type=checkbox][onclick][checked]').click().click();
         $('input[type=radio][onclick][checked]').click().click();
         
         
         
         
         /**
          * DataTables
          */
         startDataTables();
         startAjaxTables();
    }
    
    
    /*
     * Modal
     */
    var mAjaxCall = null;
    function loadModal(url, data, callbackSuccess) {
        var modalContainer = $('#js_modal_container');
        if(typeof data === 'undefined') {
            data = {};
        }
        
        if (mAjaxCall !== null) {
            mAjaxCall.abort();
        }
        
        mAjaxCall = $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'html',
            success: function(data) {
                modalContainer.html(data);
                $('.modal', modalContainer).modal()
                .on('shown.bs.modal', function (e) {
                    initComponents();

                    // Disable by default the confirmation request
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = false;
            
                    if($('form', modalContainer).size() > 0) {
                        $('input:not([type=hidden]), select, textarea', modalContainer).on('change', function() {
                            $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                        });
                    }
                    
                }).on('hide.bs.modal', function(e) {
                    // FIX: ogni tanto viene lanciato un evento per niente - ad esempio sui datepicker
                    if($('.modal', modalContainer).is(e.target)) {
                        var askConfirmationOnClose = $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose;
                        if(askConfirmationOnClose && !confirm('Ci sono dati non salvati, uscendo ogni modifica sarà persa. Vuoi uscire comunque?')) {
                            // Stop hiding the modal
                            $('.modal', modalContainer).data('bs.modal').isShown = false;
                        } else {
                            $('.modal', modalContainer).data('bs.modal').isShown = true;
                        }
                    }
                }).on('hidden.bs.modal', function (e) {
                    if(typeof callbackSuccess !== 'undefined') {
                        callbackSuccess();
                    }
                });
                mAjaxCall = null;
            },
            error: function() {
                mAjaxCall = null;
            }
        });
    }
    
    
    /** Fix per focus su select in modale **/
    $.fn.modal.Constructor.prototype.enforceFocus = function () {};
    
    
    function formatDate(dateTime) {
        
        var day = dateTime.getDate();
        var month = dateTime.getMonth()+1;
    
        var date = [day<10? '0'+day: day, month<10? '0'+month: month, dateTime.getFullYear()].join('/');
        
        var hours = dateTime.getHours();
        var minutes = dateTime.getMinutes();
        var time = [hours<10? '0'+hours: hours, minutes<10? '0'+minutes: minutes].join(':');
        return date+' '+time;
    }
    
    
    
    function changeStarsStatus(el) {
        var star = $(el);
        var val = parseInt(star.attr('data-val'));
        $('input', star.parent()).val(val);
        $('.star', star.parent()).each(function() {
            if(parseInt($(this).attr('data-val')) <= val) {
                // Stella prima da attivare
                $('i', $(this)).removeClass('icon-star-empty').addClass('icon-star');
            } else {
                // Stella dopo
                $('i', $(this)).removeClass('icon-star').addClass('icon-star-empty');
                
            }
        });
    }
    
    
    
    
    $(document).ready(function() {
        App.init();
        try {
            load_calendar();
        } catch(e) {}
        initComponents();
        $('body').on('click', '.js_open_modal', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var sUrl = $(this).attr('href');
            if(sUrl) {
                loadModal(sUrl, {});
            }
        });
    
        $('body').on('click', '.js_title_collapse', function() {
            $('.expand, .collapse', $(this)).click();
        });

        $('body').on('click', '.expand, .collapse', function(e) {
            e.stopPropagation();
        });
        
        $('body').tooltip({selector: '[data-toggle=tooltip]'});

        
        try {
            Ready();
        } catch(e) {}
    });
        

    
</script>
