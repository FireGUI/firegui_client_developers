<?php echo $label; ?>
<br/>
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">

    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />

    <div class="fileinput-new thumbnail" style="width: 156px; height: 100px;">
        <img src="<?php echo base_url();?>images/upload_icon.png" alt="" style="max-height:80px;"/>
    </div>
    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">
        <img src="<?php echo $value ? base_url_uploads("uploads/{$value}") : ''; ?>" alt="" style="max-height: 140px;">
    </div>
    <div >
        <span class="btn default btn-sm btn-file" style="padding-left:0px;">
            <span class="fileinput-new btn btn-primary">Seleziona immagine</span>
            <span class="fileinput-exists btn btn-primary">Cambia</span>
            <input type="file" name="<?php echo $field['fields_name']; ?>">
        </span>
        <a href="javascript:;" class="btn red btn-sm fileinput-exists btn btn-danger" data-dismiss="fileinput">Rimuovi</a>
    </div>
</div>
<?php echo $help; ?>
