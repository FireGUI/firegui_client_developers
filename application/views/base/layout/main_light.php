<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    
    <!-- BEGIN HEAD -->
    <head>
        <?php echo $head; ?>
    </head>
    <!-- END HEAD -->
    
    <!-- BEGIN BODY -->
    <body data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>">
        <script src="<?php echo base_url_scripts("script/js/grep_config.js?v=" . VERSION); ?>"></script>
    
        <div id="js_layout_content_wrapper" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>">
            <div class="js_page_content" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>"  data-value_id="<?php echo (!empty($value_id)) ? $value_id : ''; ?>" data-title="<?php echo (!empty($dati['title_prefix'])) ? $dati['title_prefix'] : ''; ?>" data-related_entities="<?php echo (!empty($dati['related_entities']))?implode(',',$dati['related_entities']):'' ; ?>">
                <?php echo $page; ?>
            </div>
        </div>
    
        <?php echo $foot; ?>
    </body>
    <!-- END BODY -->
</html>