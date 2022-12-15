
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