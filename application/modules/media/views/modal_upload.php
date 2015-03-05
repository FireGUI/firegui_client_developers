<?php $this->load->view('layout/module_css'); ?>

<form action="<?php echo base_url('media/db_ajax/upload'); ?>" class="dropzone" id="js_upload_form">
    <input type="hidden" name="entity_id" value="<?php echo $dati['entity']['entity_id']; ?>" />
    <input type="hidden" name="value" value="<?php echo $dati['value_id']; ?>" />
</form>

<?php $this->load->view('layout/module_js'); ?>


<script>
    $(document).ready(function() {
        new Dropzone("#js_upload_form");
        
        // Reload page on modal hide
        $('#js_upload_form').parents('.modal').filter(':first').on('hide.bs.modal', function() {
            window.location.reload();
        });
        
        
    });
</script>