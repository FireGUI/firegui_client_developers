<?php $form_id = "form_{$form['forms']['forms_id']}"; ?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    
    <!-- FORM HIDDEN DATA -->
    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>
    
    
    <div class="form-body">
        <?php foreach ($form['forms_fields'] as $field): ?>
            <div class="row">
                <div class="<?php echo sprintf('col-lg-%d', $field['size'] ? : 12); ?>"><?php echo $field['html']; ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="row">
            <div class="col-md-12">
                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
            </div>
        </div>
    </div>

    <div class="form-actions right">
        <button type="button" class="btn default" data-dismiss="modal">Annulla</button>
        <button type="submit" class="btn green">Salva</button>
    </div>
</form>