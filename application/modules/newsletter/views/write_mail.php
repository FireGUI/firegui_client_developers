<h3 class="page-title">Newsletter <small>write a new mail</small></h3>



<form class="formAjax" action="<?php echo base_url("newsletter/add_to_queue"); ?>">


    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">Template</label>
            <div class="input-group">
                <?php if (isset($dati['template'])): ?>
                    <p class="form-control-static">
                        <?php echo $dati['template']['email_templates_name'] ?>
                        <a href="<?php echo base_url('newsletter/write_mail'); ?>" class="btn btn-link">Cancel template</a>

                    </p>
                <?php else: ?>
                    <select id="template_chooser" class="form-control input-large" onchange="window.location = window.location + '/' + $(this).val();">
                        <option></option>
                        <?php foreach ($dati['templates'] as $template): ?>
                            <option value="<?php echo $template['email_templates_id']; ?>"><?php echo $template['email_templates_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">Write email addresses</label>
            <div class="input-group input-large">
                <textarea name="address_1" class="form-control"></textarea>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>


    <?php if (!empty($dati['all_email'])): ?>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">Select an email</label>
                <div class="input-group">
                    <select class="form-control input-large" multiple name="address_2">
                        <?php foreach ($dati['all_email'] as $mail): ?>
                            <option value="<?php echo $mail; ?>"><?php echo $mail; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>


        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">Select a mailing list</label>
                <div class="input-group">
                    <?php foreach ($dati['mailing_lists'] as $name => $list): ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="<?php echo $list; ?>" />
                            <?php echo $name; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php endif; ?>



    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">Content</label>
            <div class="input-group">
                <textarea name="mail" id="ckeditor"><?php if (isset($dati['template'])) echo $dati['template']['email_templates_content']; ?></textarea>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>



    <div class="col-md-12">
        <div class="form-group">
            <div class="input-group col-md-offset-3 col-md-9">
                <input type="submit" class="btn blue" value="Send Email(s)" />
            </div>
        </div>
    </div>

</form>

