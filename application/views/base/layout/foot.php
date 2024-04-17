<div id="js_modal_container"></div>

<!-- Side view modal -->
<div id="modal-side-view" class="modal-side-hidden mobile-full-width js_page_content">
    <button id="close-modal-side-view" class="modal-side-close-button">×</button>
    <div id="modal-side-content-view">
        <div id="modal-side-content-form-view"></div>
    </div>
</div>

<!-- COMMON PLUGINS -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-ui/jquery-ui.min.js?v=" . VERSION); ?>"></script>

<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . VERSION); ?>"></script>

<!-- Morris.js charts -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/moment/min/moment.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/fastclick/lib/fastclick.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte.min.js?v=" . VERSION); ?>"></script>

<!-- DataTables -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net/js/jquery.dataTables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js?v=" . VERSION); ?>"></script>

<!-- Full Calendar 4 -->
<script src='<?php echo base_url_scripts('script/lib/fullcalendar-4/core/main.js') ?>'></script>
<script src='<?php echo base_url_scripts('script/lib/fullcalendar-4/core/locales-all.js') ?>'></script>
<script src='<?php echo base_url_scripts('script/lib/fullcalendar-4/interaction/main.js') ?>'></script>
<script src='<?php echo base_url_scripts('script/lib/fullcalendar-4/daygrid/main.js') ?>'></script>
<script src='<?php echo base_url_scripts('script/lib/fullcalendar-4/timegrid/main.js') ?>'></script>

<!-- INPUT DATA PLUGINS -->
<script src="<?php echo base_url_scripts("script/global/plugins/jquery.blockui.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/jquery.cokie.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/uniform/jquery.uniform.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("template/adminlte/bower_components/select2/dist/js/select2.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("template/adminlte/bower_components/select2/dist/js/i18n/it.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v=" . VERSION); ?>"></script>

<!-- OUTPUT DATA PLUGINS -->
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v=" . VERSION); ?>"></script>

<!-- LIBRARIES -->
<script src="<?php echo base_url_scripts("script/lib/ckeditor/ckeditor.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/timeline/build/js/storyjs-embed.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/lightbox2/js/lightbox.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/bootstrap-colorselector/dist/bootstrap-colorselector.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootbox/bootbox.min.js?v=" . VERSION); ?>"></script>

<!-- LEAFLET-JS -->
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7.1/leaflet-src.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/leaflet.geocoding.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_scripts("script/js/leaflet.markercluster.min.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_template('script/global/plugins/dropzone/dropzone.js'); ?>"></script>

<!-- tinymce -->
<script src="<?php echo base_url_scripts('script/global/plugins/tinymce/') ?>tinymce.min.js"></script>
<script src="<?php echo base_url_scripts('script/global/plugins/tinymce/') ?>themes/silver/theme.min.js"></script>
<script src="<?php echo base_url_scripts('script/global/plugins/tinymce/') ?>jquery.tinymce.min.js"></script>

<!-- ADMINLTE SCRIPTS -->
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte_components.js?v=" . VERSION); ?>"></script>

<!-- SWEETALERT2 -->
<script src="<?php echo base_url_scripts("script/lib/sweetalert2/sweetalert2.all.min.js?v=" . VERSION); ?>"></script>

<!-- TOASTR -->
<script src="<?php echo base_url_scripts("script/lib/toastr/toastr.min.js?v=" . VERSION); ?>"></script>

<!-- CUSTOM COMPONENTS -->
<script src="<?php echo base_url_scripts("script/js/submitajax.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/ajax-tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/datatable_inline.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/data-tables.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/bulk-grid.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/components.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/IE.missing.functions.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/inline_actions.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/maps.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/multiupload.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/calendars/calendar_full_json_sidebar.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_template("script/js/request.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("script/js/toast.js?v=" . VERSION); ?>"></script>

<!-- CHARTS -->
<script src="<?php echo base_url_scripts("script/js/charts/apexcharts.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/apexcharts_bar.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/apexcharts_line_smooth.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/apexcharts_customizable.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_scripts("script/js/charts/simple_pie.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/simple_pie_legend.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/simple_line.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/simple_column.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/charts/semi_donut.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_scripts("script/js/charts/funnel_chart.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_scripts("script/js/charts/chartjs_customizable.js?v=" . VERSION); ?>"></script>


<script src="<?php echo base_url_scripts("script/js/core.js?v=" . VERSION); ?>"></script>