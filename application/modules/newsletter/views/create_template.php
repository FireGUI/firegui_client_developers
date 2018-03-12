<h3 class="page-title">Newsletter <small>create template</small></h3>

<div class="row">
    <div class="col-md-4">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-html5"></i> Templates
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse"></a>
                    <a href="javascript:;" class="remove"></a>
                </div>
            </div>
            <div class="portlet-body">
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Creation date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dati['templates'] as $template): ?>
                        <tr>
                            <td><?php echo $template['email_templates_id']; ?></td>
                            <td>
                                <a href="<?php echo base_url("newsletter/create_template/{$template['email_templates_id']}"); ?>" class="btn-link">
                                    <?php echo $template['email_templates_name']; ?>
                                </a>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($template['email_templates_date'])); ?></td>
                            <td><a href="<?php echo base_url("newsletter/remove_template/{$template['email_templates_id']}"); ?>" class="btn-link">Delete</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    
    
    
    
    <div class="col-md-8">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-html5"></i> Template form
                </div>
            </div>
            <div class="portlet-body form">
                <form class="formAjax" action="<?php echo base_url("newsletter/save_template"); ?>">
                    <div class="form-body">
                        <?php if(isset($dati['template'])): ?>
                            <input type="hidden" class="form-control" name="email_templates_id" value="<?php echo $dati['template']['email_templates_id'] ?>" />
                        <?php endif; ?>
                        
                        
                        <div class="form-group">
                            <label class="control-label">Template name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="email_templates_name" value="<?php if(isset($dati['template'])) echo $dati['template']['email_templates_name'] ?>" />
                            </div>
                        </div>
                        <div class="clearfix"></div>
                            

                        <div class="form-group">
                            <label class="control-label">Content</label>
                            <textarea name="email_templates_content" id="ckeditor"><?php if(isset($dati['template'])) echo $dati['template']['email_templates_content'] ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions fluid">
                        <div class="col-md-12">
                            <button type="submit" class="btn blue">Save template</button>
                            <a href="<?php echo base_url('newsletter/create_template'); ?>" class="btn default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>









<hr/>





