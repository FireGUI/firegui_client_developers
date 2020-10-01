<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b><?php e('Version'); ?></b> <?php echo VERSION; ?>
    </div>
    <strong>Copyright &copy; 2015-<?php echo date('Y'); ?> - Powered by <a href="https://www.firegui.com/">FireGUI</a>.</strong> <?php e('All rights reserved.'); ?>
</footer>

<div id="js_modal_container"></div>

<!-- COMMON PLUGINS -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-ui/jquery-ui.min.js?v=" . VERSION); ?>"></script>

<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . VERSION); ?>"></script>

<!-- Morris.js charts -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/raphael/raphael.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-knob/dist/jquery.knob.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/moment/min/moment.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/fastclick/lib/fastclick.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte.min.js?v=" . VERSION); ?>"></script>

<!-- DataTables -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net/js/jquery.dataTables.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template("template/adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js?v=" . VERSION); ?>"></script>

<!-- INPUT DATA PLUGINS -->
<script src="<?php echo base_url_scripts("script/global/plugins/fullcalendar/fullcalendar.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/global/plugins/fullcalendar/lang-all.js?v=" . VERSION); ?>"></script>
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

<!-- LEAFLET-JS -->
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/leaflet.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/leaflet.geocoding.js?v=" . VERSION); ?>"></script>

<!-- LEAFLET-DRAW-LIBRARY-JS !-->
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/Leaflet.draw.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/Leaflet.Draw.Event.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/Toolbar.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/Tooltip.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/GeometryUtil.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/LatLngUtil.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/LineUtil.Intersect.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/Polygon.Intersect.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/Polyline.Intersect.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/ext/TouchEvents.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/DrawToolbar.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Feature.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.SimpleShape.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Polyline.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Marker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Circle.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.CircleMarker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Polygon.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/draw/handler/Draw.Rectangle.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/EditToolbar.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/EditToolbar.Edit.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/EditToolbar.Delete.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/Control.Draw.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.Poly.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.SimpleShape.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.Rectangle.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.Marker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.CircleMarker.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-1.7/Leaflet.draw/src/edit/handler/Edit.Circle.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/leaflet-fullscreen/Leaflet.fullscreen.min.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_template('script/global/plugins/dropzone/dropzone.js'); ?>"></script>


<!-- ADMINLTE SCRIPTS -->
<script src="<?php echo base_url_template("template/adminlte/dist/js/adminlte_components.js?v=" . VERSION); ?>"></script>

<!-- CUSTOM COMPONENTS -->
<script src="<?php echo base_url_scripts("script/js/submitajax.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/ajax-tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/datatable_inline.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/data-tables.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/bulk-grid.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/components.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/IE.missing.functions.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/inline_actions.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url_scripts("script/js/map_standard.js?v=" . VERSION); ?>"></script>

<!-- CHARTS -->
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