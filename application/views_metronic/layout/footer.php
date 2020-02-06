<!-- BEGIN FOOTER -->
<div class="page-footer">
    <div class="page-footer-inner"></div>
    <div class="scroll-to-top">
        <i class="icon-arrow-up"></i>
    </div>
</div>
<!-- END FOOTER -->

<div id="js_modal_container"></div>


<!-- IE COMPATIBILITY SCRIPTS -->
<!--[if lt IE 9]>
    <script src="https://html5shiv.googlecode.com/svn/trunk/html5.js?v=".VERSION); ?>"></script>
    <script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/respond.min.js?v=" . VERSION); ?>"></script>
    <script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/excanvas.min.js?v=" . VERSION); ?>"></script>
<![endif]-->

<!-- COMMON PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-ui/jquery-ui.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.blockui.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.cokie.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/jquery.uniform.min.js?v=" . $this->config->item('version')); ?>"></script>

<!-- INPUT DATA PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-select/bootstrap-select.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/select2/select2.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/select2/select2_locale_it.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js?v=" . $this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/moment.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/ion.rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js?v=" . VERSION); ?>"></script>

<!-- OUTPUT DATA PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/fullcalendar/fullcalendar.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/fullcalendar/lang-all.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js?v=" . VERSION); ?>"></script>


<!-- LIBRARIES -->
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/ckeditor/ckeditor.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/timeline/build/js/storyjs-embed.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/lightbox2/js/lightbox.min.js?v=" . $this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/bootstrap-colorselector/dist/bootstrap-colorselector.min.js?v=" . $this->config->item('version')); ?>"></script>

<!-- LEAFLET-JS -->
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/leaflet.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/leaflet.geocoding.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/marker-cluster/leaflet.markercluster-src.js?v=" . VERSION); ?>"></script>

<!-- LEAFLET DRAWER -->
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Leaflet.draw.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Toolbar.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Tooltip.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/GeometryUtil.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/LatLngUtil.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/LineUtil.Intersect.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/Polygon.Intersect.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/ext/Polyline.Intersect.js?v=" . VERSION); ?>"></script>


<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/DrawToolbar.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Feature.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.SimpleShape.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Polyline.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Circle.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Marker.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Polygon.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/draw/handler/Draw.Rectangle.js?v=" . VERSION); ?>"></script>


<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/EditToolbar.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Edit.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Delete.js?v=" . VERSION); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/Control.Draw.js?v=" . VERSION); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Poly.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.SimpleShape.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Circle.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Rectangle.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/lib/leaflet-0.7.2/edit/handler/Edit.Marker.js?v=" . VERSION); ?>"></script>


<!-- METRONIC SCRIPTS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/scripts/metronic.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/admin/layout/scripts/layout.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/admin/layout/scripts/metronic_components.js?v=" . VERSION); ?>"></script>

<!-- CUSTOM COMPONENTS -->
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/components.js?v=" . VERSION); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_scripts("script/js/ajax-tables.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/data-tables.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/datatable_inline.js?v=" . VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_scripts("script/js/bulk-grid.js?v=" . VERSION); ?>"></script>