<?php 
$default_form_id = $this->datab->get_default_form($dati['entity_id']);
$form = $this->datab->get_form($default_form_id);
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-reorder"></i> Default <?php echo $form['forms']['forms_name']; ?>
                </div>
                <div class="actions">
                    <a href="#<?php echo ($id="entity_{$dati['entity_id']}_form_{$form['forms']['forms_id']}"); ?>" class="btn btn-sm btn-success" data-toggle="collapse">
                        Add new <i class="icon-plus"></i>
                    </a>
                </div>
            </div>
            <div id="<?php echo $id ?>" class="portlet-body form <?php if (isset($visible_on_start) && $visible_on_start == FALSE): ?>collapse<?php endif; ?>">
                <?php $this->load->view("form/form_{$form['forms']['forms_layout']}", array('form'=>$form)); ?>
            </div>
        </div>
        <!-- END SAMPLE FORM PORTLET-->
    </div>
</div>