<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b><?php e('Version'); ?></b> <?php echo VERSION; ?>
    </div>
    <strong>Copyright &copy; 2015-2019 <a href="<?php echo base_url(); ?>">H2 Web S.n.c.</a>.</strong> All rights
    reserved.
</footer>

<div id="js_modal_container"></div>


<!-- COMMON PLUGINS -->


<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-ui/jquery-ui.min.js?v={$this->config->item('version')}"); ?>"></script>

<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v={$this->config->item('version')}"); ?>"></script>

<!-- Morris.js charts -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/raphael/raphael.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_template("template/adminlte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-knob/dist/jquery.knob.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/moment/min/moment.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/fastclick/lib/fastclick.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte.min.js?v={$this->config->item('version')}"); ?>"></script>
<!--<script  src="<?php echo base_url_template("template/adminlte/bower_components/fullcalendar/dist/fullcalendar.min.js?v={$this->config->item('version')}"); ?>"></script>-->


<!-- DataTables -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net/js/jquery.dataTables.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js?v={$this->config->item('version')}"); ?>"></script>

<!-- INPUT DATA PLUGINS -->
<script src="<?php echo base_url_scripts("script/global/plugins/fullcalendar/fullcalendar.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/fullcalendar/lang-all.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/jquery.blockui.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/jquery.cokie.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/uniform/jquery.uniform.min.js?v={$this->config->item('version')}"); ?>"></script>
<!--<script  src="<?php echo base_url_scripts("script/global/plugins/bootstrap-select/bootstrap-select.min.js?v={$this->config->item('version')}"); ?>"></script>-->


<script src="<?php echo base_url_scripts("template/adminlte/bower_components/select2/dist/js/select2.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("template/adminlte/bower_components/select2/dist/js/i18n/it.js?v={$this->config->item('version')}"); ?>"></script>



<!--<script  src="<?php echo base_url_scripts("script/global/plugins/jquery-multi-select/js/jquery.multi-select.js?v={$this->config->item('version')}"); ?>"></script>-->
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v={$this->config->item('version')}"); ?>"></script>
<!--<script  src="<?php echo base_url_scripts("script/global/plugins/ion.rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js?v={$this->config->item('version')}"); ?>"></script>-->

<!-- OUTPUT DATA PLUGINS -->



<!--<script  src="<?php echo base_url_scripts("script/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js?v={$this->config->item('version')}"); ?>"></script>-->

<script src="<?php echo base_url_scripts("script/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v={$this->config->item('version')}"); ?>"></script>
<!--<script  src="<?php echo base_url_scripts("script/global/scripts/metronic.js?v={$this->config->item('version')}"); ?>"></script>-->


<!--<script  src="<?php echo base_url_scripts("script/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script  src="<?php echo base_url_scripts("script/global/plugins/bootstrap-daterangepicker/moment.min.js?v={$this->config->item('version')}"); ?>"></script>
<script  src="<?php echo base_url_scripts("script/global/plugins/bootstrap-daterangepicker/daterangepicker.js?v={$this->config->item('version')}"); ?>"></script>
script  src="<?php echo base_url_scripts("script/global/plugins/datatables/media/js/jquery.dataTables.min.js?v={$this->config->item('version')}"); ?>"></script>-->


<!-- LIBRARIES -->
<script src="<?php echo base_url_scripts("script/lib/ckeditor/ckeditor.js?v=" . $this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=" . $this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/timeline/build/js/storyjs-embed.js?v=" . $this->config->item('version')); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/lightbox2/js/lightbox.min.js?v=" . $this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/bootstrap-colorselector/dist/bootstrap-colorselector.min.js?v=" . $this->config->item('version')); ?>"></script>

<!-- LEAFLET-JS -->
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/leaflet.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/leaflet.geocoding.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/marker-cluster/leaflet.markercluster-src.js?v={$this->config->item('version')}"); ?>"></script>

<!-- LEAFLET DRAWER -->
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Leaflet.draw.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Toolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Tooltip.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/GeometryUtil.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/LatLngUtil.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/LineUtil.Intersect.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/Polygon.Intersect.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/Polyline.Intersect.js?v={$this->config->item('version')}"); ?>"></script>


<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/DrawToolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Feature.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.SimpleShape.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Polyline.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Circle.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Marker.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Polygon.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Rectangle.js?v={$this->config->item('version')}"); ?>"></script>


<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/EditToolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Edit.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Delete.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Control.Draw.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Poly.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.SimpleShape.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Circle.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Rectangle.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Marker.js?v={$this->config->item('version')}"); ?>"></script>

<script src="<?php echo base_url_template('script/global/plugins/dropzone/dropzone.js'); ?>"></script>
<script>
    Dropzone.autoDiscover = false;
</script>

<!-- ADMINLTE SCRIPTS -->
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte_components.js?v={$this->config->item('version')}"); ?>"></script>

<!-- CUSTOM COMPONENTS -->
<script src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/ajax-tables.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/datatable_inline.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/data-tables.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/bulk-grid.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/components.js?v={$this->config->item('version')}"); ?>"></script>