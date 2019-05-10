<?php
$form_id = "form_{$form['forms']['forms_id']}";
$rowStart = '<div class="row">';
$rowEnd = '</div>';
$rowCol = 0;
?>
<form <?php echo "id='{$form_id}'"; ?> role="form" method="post" action="<?php echo $form['forms']['action_url']; ?>" class="formAjax" enctype="multipart/form-data" data-edit-id="<?php echo $value_id; ?>">
    
    <!-- FORM HIDDEN DATA -->
    <?php echo implode(PHP_EOL, $form['forms_hidden']); ?>
    
    <div class="box-body">
        <?php foreach ($form['forms_fields'] as $k => $field): ?>
            <?php 
            // Stampa la prima row
            echo $rowCol? '': $rowStart;
            $col = $field['size'] ? : 6;
            $rowCol += $col;
            
            if ($rowCol > 12) {
                $rowCol = $col;
                echo $rowEnd, $rowStart;
            }
            ?>
        
            <div class="<?php echo sprintf('col-lg-%d', $col); ?>"><?php echo $field['html']; ?></div>
        <?php endforeach; ?>
        <?php echo $rowCol? $rowEnd: ''; ?>

        <div class="row">
            <div class="col-md-12">
                <div <?php echo "id='msg_{$form_id}'"; ?> class="alert alert-danger hide"></div>
            </div>
        </div>
    </div>

    <div class="form-actions pull-right">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php e('Annulla'); ?></button>
        <button type="submit" class="btn btn-primary"><?php e('Salva'); ?></button>
    </div>
</form>
