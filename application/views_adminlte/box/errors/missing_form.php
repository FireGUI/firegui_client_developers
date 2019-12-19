<div class="error-page">
    <h2 class="headline text-yellow" style="font-size:50px"> Oops!</h2>

    <div class="error-content" style="margin-left:160px;">
        <h3><i class="fa fa-warning text-yellow"></i><?php e('Form %s not found.', 0, [$form_id]); ?></h3>

        <p>
            <?php e('We don\'t find what your are looking for.'); ?><br />
            <?php e('In the meantime you can <a href="%s">go back to the dashboard</a><br /> otherwise you can contact the administrators to resolve the problem', 0, [base_url()]); ?>
        </p>
    </div>
    <!-- /.error-content -->
</div>