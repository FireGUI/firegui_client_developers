<!-- BEGIN FOOTER -->
<div class="page-footer">
    <div class="page-footer-inner"><?php /*Powered by <a href="http://h2-web.it" class="text-danger" target="_blank">H2 web</a>*/ ?></div>
    <div class="page-footer-tools">
        <span class="go-top">
            <i class="fa fa-angle-up"></i>
        </span>
    </div>
</div>
<!-- END FOOTER -->

<div id="js_modal_container"></div>


<!-- IE COMPATIBILITY SCRIPTS -->
<!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js?v={$this->config->item('version')}"); ?>"></script>
    <script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/respond.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/excanvas.min.js?v={$this->config->item('version')}"); ?>"></script>
<![endif]-->

<!-- COMMON PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-ui/jquery-ui.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v={$this->config->item('version')}"); ?>" ></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.blockui.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.cokie.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/jquery.uniform.min.js?v=".$this->config->item('version')); ?>" ></script>

<?php /*
<script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/flot/jquery.flot.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/flot/jquery.flot.resize.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.pulsate.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/gritter/js/jquery.gritter.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
 */ ?>

<!-- INPUT DATA PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-select/bootstrap-select.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/select2/select2.min.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/select2/select2_locale_it.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js?v=".$this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js?v=".$this->config->item('version')); ?>"></script>     
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/moment.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v={$this->config->item('version')}"); ?>"></script>

<!-- OUTPUT DATA PLUGINS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/fullcalendar/fullcalendar.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js?v={$this->config->item('version')}"); ?>"></script>


<!-- LIBRARIES -->
<script type="text/javascript" src="<?php echo base_url_template("script/lib/ckeditor/ckeditor.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/timeline/build/js/storyjs-embed.js?v=".$this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/lightbox2/js/lightbox.min.js?v=".$this->config->item('version')); ?>"></script>

<!-- LEAFLET-JS -->
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/js/leaflet.geocoding.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/marker-cluster/leaflet.markercluster-src.js?v={$this->config->item('version')}"); ?>"></script>

<!-- LEAFLET DRAWER -->
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/Leaflet.draw.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/Toolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/Tooltip.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/ext/GeometryUtil.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/ext/LatLngUtil.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/ext/LineUtil.Intersect.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/ext/Polygon.Intersect.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/ext/Polyline.Intersect.js?v={$this->config->item('version')}"); ?>"></script>


<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/DrawToolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Feature.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.SimpleShape.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Polyline.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Circle.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Marker.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Polygon.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/draw/handler/Draw.Rectangle.js?v={$this->config->item('version')}"); ?>"></script>


<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/EditToolbar.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Edit.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/EditToolbar.Delete.js?v={$this->config->item('version')}"); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/Control.Draw.js?v={$this->config->item('version')}"); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/Edit.Poly.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/Edit.SimpleShape.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/Edit.Circle.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/Edit.Rectangle.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/leaflet-0.7.2/edit/handler/Edit.Marker.js?v={$this->config->item('version')}"); ?>"></script>


<!-- METRONIC SCRIPTS -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/scripts/metronic.js?v={$this->config->item('version')}"); ?>"></script> 
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/admin/layout/scripts/layout.js?v={$this->config->item('version')}"); ?>"></script> 

<!-- CUSTOM COMPONENTS -->
<script type="text/javascript" src="<?php echo base_url_template("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script> 
<script type="text/javascript" src="<?php echo base_url_template("script/js/components.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/js/ajax-tables.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/js/data-tables.js?v={$this->config->item('version')}"); ?>"></script>
