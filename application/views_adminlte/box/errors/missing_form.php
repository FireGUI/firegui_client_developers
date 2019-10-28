<div class="error-page">
    <h2 class="headline text-yellow" style="font-size:50px"> Oops!</h2>

    <div class="error-content" style="margin-left:160px;">
        <h3><i class="fa fa-warning text-yellow"></i><?php e('Form %s not found.', 0, [$form_id]); ?></h3>

        <p>
            <?php e('Non siamo riusciti a trovare ciò che cerchi.'); ?><br />
            <?php e('Nel frattempo, puoi <a href="%s">tornare alla dashboard</a><br />oppure contattare gli amministratori per risolvere il problema', 0, [base_url()]); ?>
        </p>
    </div>
    <!-- /.error-content -->
</div>