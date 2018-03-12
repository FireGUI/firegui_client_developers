<!-- BEGIN Module Related Javascript -->
<script src="<?php // echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap2-typeahead.min.js");  ?>"></script>




<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        if ($('#ckeditor').size()) {
            CKEDITOR.replace('ckeditor', {
                filebrowserBrowseUrl: '<?php echo base_url('uploads') ?>',
                filebrowserUploadUrl: '<?php echo base_url('upload/ckeditor') ?>'
            });
        }
    });
</script>
<!-- END Module Related Javascript -->