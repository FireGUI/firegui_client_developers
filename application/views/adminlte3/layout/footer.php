<!-- Builder console -->
<?php if ($this->auth->is_admin()): ?>
  <div class="builder_console hide">
    <div class=fakeMenu>
      <div class="fakeButtons fakeClose"></div>
      <div class="fakeButtons fakeMinimize"></div>
      <div class="fakeButtons fakeZoom"></div>
    </div>
    <div class="fakeScreen">

      <!-- Hooks -->
      <p class="line1 js_console_command">$ get executed hooks</p>
      <p class="line2 hide">
        <?php foreach ($this->datab->executed_hooks as $hook): ?>
          - Type:
          <?php echo $hook['type']; ?> Ref:
          <?php echo $hook['ref']; ?> Value id:
          <?php echo $hook['value_id']; ?> <br />

          <?php foreach ($hook['hooks'] as $single_hook): ?>
            |- [
            <?php echo $single_hook['hooks_id']; ?>] Title: <a
              href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/events_builder/<?php echo $single_hook['hooks_id']; ?>"
              target="_blank"><?php echo $single_hook['hooks_title']; ?></a> Module:
            <?php echo $single_hook['hooks_module']; ?> <span class="js_show_code">Show Code</span><br />
            <span class="line4 hide"><br />
              <?php echo htmlentities($single_hook['hooks_content']); ?><br /><br />
            </span>
          <?php endforeach; ?>
          <br />
        <?php endforeach; ?>
      </p>

      <!-- Queries -->
      <p class="line1 js_console_command">$ get executed queries</p>
      <p class="line2 hide">
        <?php foreach ($this->db->queries as $query): ?>
          -
          <?php echo $query; ?> <br />
        <?php endforeach; ?>
      </p>

      <!-- Crons -->
      <p class="line1 js_console_command">$ get crons</p>
      <p class="line2 hide">
        <?php foreach ($this->fi_events->getCrons() as $cron): ?>
          - [
          <?php echo $cron['fi_events_id']; ?>] <a
            href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/events_builder/<?php echo $cron['fi_events_id']; ?>"
            target="_blank"><?php echo $cron['fi_events_title']; ?></a> Type:
          <?php echo $cron['crons_type']; ?> Freq:
          <?php echo $cron['crons_frequency']; ?> min Active: <span class="line4">
            <?php echo $cron['crons_active']; ?>
          </span> Last Exec:
          <?php echo $cron['crons_last_execution']; ?> Module:
          <?php echo $cron['crons_module']; ?> <span class="js_show_code">Show code/url</span><br />
          <span
            class="line4 hide"><br /><code><?php echo ($cron['crons_text']) ? htmlentities($cron['crons_text']) : $cron['crons_file']; ?></code><br /><br /></span>
        <?php endforeach; ?>
      </p>

      <p class="line1 js_console_command">$ count table records</p>
      <p class="line2 hide">
        ci_sessions (
        <?php echo $this->db->query("SELECT COUNT(*) AS c FROM ci_sessions")->row()->c; ?>) <a target="_blank"
          href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gY2lfc2Vzc2lvbnM=">Truncate</a>
        <br />log_crm (
        <?php echo $this->db->query("SELECT COUNT(*) AS c FROM log_crm")->row()->c; ?>) <a target="_blank"
          href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gbG9nX2NybQ==">Truncate</a>
        <br />log_api (
        <?php echo $this->db->query("SELECT COUNT(*) AS c FROM log_api")->row()->c; ?>) <a target="_blank"
          href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gbG9nX2FwaQ==">Truncate</a>
      </p>

      <p class="line3">[?] What are you looking for? (Click command to execute)<span class="cursor3">_</span></p>
      <p class="line4">><span class="cursor4">_</span></p>
    </div>
  </div>
<?php endif; ?>

<footer class="main-footer text-sm">
  <div class="float-right d-none d-sm-block">
    <b>
      <?php e('Version'); ?>
    </b>
    <?php echo VERSION; ?>
  </div>
  <div class="center_side pull-left hidden-xs">
    <strong>Copyright &copy; 2015-
      <?php echo date('Y'); ?> - Powered by <a href="https://www.openbuilder.net/">Open Builder</a>.
    </strong>
    <?php e('All rights reserved.'); ?>
  </div>

  <div class="left_side pull-left hidden-xs">
    <div class="btn-group">
      <div class="">
        <?php /* {tpl-pre-footer} */?>
      </div>
    </div>
  </div>
</footer>


<!-- Fixed footer -->
<?php
/*
<footer class="main-footer">
<div class="left_side pull-left hidden-xs">
<div class="btn-group">
<div class="">
{tpl-pre-footer}
</div>
</div>
</div>
<div class="right_side pull-right hidden-xs">
<div class="">
{tpl-post-footer}
</div>
<b><?php e('Version'); ?></b> <?php echo VERSION; ?>
</div>
</footer>
*/
?>

<div id="js_modal_container"></div>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables/datatables.min.css?v=' . VERSION) ?>">
<script src="<?php echo base_url('assets/plugins/datatables/datatables.min.js?v=' . VERSION) ?>"></script>

<!-- Generic -->
<script src="<?php echo base_url("assets/plugins/jquery/jquery-ui.min.js?v=" . VERSION); ?>"></script>

<script
  src="<?php echo base_url("template/adminlte/bower_components/moment/min/moment.min.js?v=" . VERSION); ?>"></script>

<!-- INPUT DATA PLUGINS -->
<link rel="stylesheet" type="text/css"
  href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css?v=" . VERSION); ?>" />
<link rel="stylesheet" type="text/css"
  href="<?php echo base_url_template("script/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?v=" . VERSION); ?>" />
<link rel="stylesheet" type="text/css"
  href="<?php echo base_url_template("script/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css?v=" . VERSION); ?>" />
<link rel="stylesheet" type="text/css"
  href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.css?v=" . VERSION); ?>" />
<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url_template('template/adminlte/dist/css/jquery-ui.min.css'); ?>" /> -->
<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/lib/lightbox2/css/lightbox.css?v=" . VERSION); ?>" /> -->
<link rel="stylesheet" type="text/css"
  href="<?php echo base_url_scripts("script/lib/fancybox-2.1.5/jquery.fancybox.css?v=" . VERSION); ?>" />
<script
  src="<?php echo base_url("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("template/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("script/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("script/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("script/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("script/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("script/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.it.js?v=" . VERSION); ?>"></script>
<!-- <script src="<?php echo base_url("script/lib/lightbox2/js/lightbox.min.js?v=" . VERSION); ?>"></script> -->
<!-- <script src="<?php echo base_url("script/lib/bootstrap-colorselector/dist/bootstrap-colorselector.min.js?v=" . VERSION); ?>"></script> -->
<script src="<?php echo base_url("script/lib/fancybox-2.1.5/jquery.fancybox.pack.js?v=" . VERSION); ?>"></script>

<!-- CHARTS -->
<script src="<?php echo base_url("assets/js/components/charts/apexcharts.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/apexcharts_bar.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/apexcharts_line_smooth.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/apexcharts_customizable.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url("assets/js/components/charts/simple_pie.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/simple_pie_legend.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/simple_line.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/simple_column.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/charts/semi_donut.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url("assets/js/components/charts/funnel_chart.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url("assets/js/components/charts/chartjs_customizable.js?v=" . VERSION); ?>"></script>

<!-- Select2 -->
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/select2/css/select2.min.css?v=" . VERSION); ?>" />
<script src="<?php echo base_url("assets/plugins/select2/js/select2.full.min.js?v=" . VERSION); ?>"></script>

<!-- LEAFLET -->
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/leaflet/leaflet.css?v=" . VERSION); ?>" />
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/leaflet/MarkerCluster.css?v=" . VERSION); ?>" />
<link rel="stylesheet"
  href="<?php echo base_url("assets/plugins/leaflet/MarkerCluster.Default.css?v=" . VERSION); ?>" />

<script src="<?php echo base_url("assets/plugins/leaflet/leaflet-src.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/plugins/leaflet/leaflet.geocoding.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/plugins/leaflet/leaflet.markercluster.min.js?v=" . VERSION); ?>"></script>

<!-- Full Calendar 4 -->
<link href='<?php echo base_url_scripts('assets/plugins/fullcalendar/core/main.css') ?>' rel='stylesheet' />
<link href='<?php echo base_url_scripts('assets/plugins/fullcalendar/daygrid/main.css') ?>' rel='stylesheet' />
<link href='<?php echo base_url_scripts('assets/plugins/fullcalendar/timegrid/main.css') ?>' rel='stylesheet' />

<script src='<?php echo base_url('assets/plugins/fullcalendar/core/main.js') ?>'></script>
<script src='<?php echo base_url('assets/plugins/fullcalendar/core/locales-all.js') ?>'></script>
<script src='<?php echo base_url('assets/plugins/fullcalendar/interaction/main.js') ?>'></script>
<script src='<?php echo base_url('assets/plugins/fullcalendar/daygrid/main.js') ?>'></script>
<script src='<?php echo base_url('assets/plugins/fullcalendar/timegrid/main.js') ?>'></script>

<!-- TinyMCE -->
<script src="<?php echo base_url('assets/plugins/tinymce/tinymce.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/tinymce/themes/silver/theme.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/tinymce/jquery.tinymce.min.js') ?>"></script>

<!-- DropzoneJS -->
<link rel="stylesheet" href="<?php echo base_url("assets/plugins/dropzone/css/dropzone.css?v=" . VERSION); ?>" />
<script src="<?php echo base_url('assets/plugins/dropzone/dropzone.min.js'); ?>"></script>

<!-- Core Components -->
<script src="<?php echo base_url("assets/js/core/submitajax.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/client.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url("assets/js/components/tables/inline_actions.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/maps.js?v=" . VERSION); ?>"></script>
<script
  src="<?php echo base_url("assets/js/components/calendars/calendar_full_json_sidebar.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url_template("assets/js/components/tabs.js?v=" . VERSION); ?>"></script>

<!-- <script src="<?php echo base_url("assets/js/components/multiupload.js?v=" . VERSION); ?>"></script> -->

<script src="<?php echo base_url("assets/js/components/tables/tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/tables/data-tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/tables/ajax-tables.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/tables/datatable_inline.js?v=" . VERSION); ?>"></script>
<script src="<?php echo base_url("assets/js/components/tables/bulk-grid.js?v=" . VERSION); ?>"></script>

<script src="<?php echo base_url("assets/js/core/client.js?v=" . VERSION); ?>"></script>