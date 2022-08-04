<!-- Builder console -->
<?php if ($this->auth->is_admin()) : ?>


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
            <?php foreach ($this->datab->executed_hooks as $hook) : ?>
            - Type: <?php echo $hook['type']; ?> Ref: <?php echo $hook['ref']; ?> Value id: <?php echo $hook['value_id']; ?> <br />

            <?php foreach ($hook['hooks'] as $single_hook) : ?>
            |- [<?php echo $single_hook['hooks_id']; ?>] Title: <a href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/events_builder/<?php echo $single_hook['hooks_id']; ?>" target="_blank"><?php echo $single_hook['hooks_title']; ?></a> Module: <?php echo $single_hook['hooks_module']; ?> <span class="js_show_code">Show Code</span><br />
            <span class="line4 hide"><br /><?php echo htmlentities($single_hook['hooks_content']); ?><br /><br /></span>
            <?php endforeach; ?>
            <br />
            <?php endforeach; ?>
        </p>

        <!-- Queries -->
        <p class="line1 js_console_command">$ get slowest queries</p>
        <p class="line2 hide">
            <?php foreach ($this->session->userdata('slow_queries') as $query => $execution_time) : ?>
            - (<?php echo $execution_time; ?>s) <?php echo $query; ?> <br />
            <?php endforeach; ?>
        </p>

        <!-- Queries -->
        <p class="line1 js_console_command">$ get executed queries</p>
        <p class="line2 hide">
            <?php foreach ($this->db->queries as $query) : ?>
            - <?php echo $query; ?> <br />
            <?php endforeach; ?>
        </p>

        <!-- Crons -->
        <p class="line1 js_console_command">$ get crons</p>
        <p class="line2 hide">
            <?php foreach ($this->fi_events->getCrons() as $cron) : ?>
            - [<?php echo $cron['fi_events_id']; ?>] <a href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/events_builder/<?php echo $cron['fi_events_id']; ?>" target="_blank"><?php echo $cron['fi_events_title']; ?></a> Type: <?php echo $cron['crons_type']; ?> Freq: <?php echo $cron['crons_frequency']; ?> min Active: <span class="line4"><?php echo $cron['crons_active']; ?></span> Last Exec: <?php echo $cron['crons_last_execution']; ?> Module: <?php echo $cron['crons_module']; ?> <span class="js_show_code">Show code/url</span><br />
            <span class="line4 hide"><br /><code><?php echo ($cron['crons_text']) ? htmlentities($cron['crons_text']) : $cron['crons_file']; ?></code><br /><br /></span>
            <?php endforeach; ?>
        </p>

        <p class="line1 js_console_command">$ count table records</p>
        <p class="line2 hide">
            ci_sessions (<?php echo $this->db->query("SELECT COUNT(*) AS c FROM ci_sessions")->row()->c; ?>) <a target="_blank" href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gY2lfc2Vzc2lvbnM=">Truncate</a>
            <br />log_crm (<?php echo $this->db->query("SELECT COUNT(*) AS c FROM log_crm")->row()->c; ?>) <a target="_blank" href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gbG9nX2NybQ==">Truncate</a>
            <br />log_api (<?php echo $this->db->query("SELECT COUNT(*) AS c FROM log_api")->row()->c; ?>) <a target="_blank" href="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>main/query/REVMRVRFIEZST00gbG9nX2FwaQ==">Truncate</a>
        </p>

        <p class="line3">[?] What are you looking for? (Click command to execute)<span class="cursor3">_</span></p>
        <p class="line4">><span class="cursor4">_</span></p>
    </div>
</div>
<?php endif; ?>
<!-- Fixed footer -->
<footer class="main-footer">

    <div class="left_side pull-left hidden-xs">
        <div class="btn-group">
            <div class="">
                {tpl-pre-footer}
            </div>
        </div>
    </div>


    <div class="center_side pull-left hidden-xs">
        <strong>Copyright &copy; 2015-<?php echo date('Y'); ?> - <?php e('All rights reserved.'); ?> - Built with <a href="https://www.openbuilder.net/">Open Builder</a> - By <a href="https://h2web.it/">H2 S.r.l.</a></strong>
    </div>

    <div class="right_side pull-right hidden-xs">
        <div class="">
            {tpl-post-footer}
        </div>

        <b><?php e('Version'); ?></b> <?php echo VERSION; ?>

    </div>

</footer>

<div id="js_modal_container"></div>

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


<?php if ($this->auth->check()) :  ?>
<script src="<?php echo base_url_template("script/js/crmNotifier.js?v=" . VERSION); ?>"></script>
<?php endif; ?>