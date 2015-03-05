<!-- BEGIN Module Related Javascript -->
<script src="<?php echo base_url_template("script/lib/elfinder/js/elfinder.min.js"); ?>"></script>




<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        var elfDiv = $('#elfinder');
        var e = elfDiv.elfinder({url : elfDiv.attr('data-url')}).elfinder('instance');
        e.isRejected = function() {
            alert('ciao :)');
        };
    });
</script>
<!-- END Module Related Javascript -->