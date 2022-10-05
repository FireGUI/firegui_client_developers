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