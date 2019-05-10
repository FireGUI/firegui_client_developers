<?php echo $label; ?>
<br/>
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
    <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
        <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
    </div>
    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">
        <img src="<?php echo $value ? base_url_template("uploads/{$value}"): ''; ?>" alt="" style="max-height: 140px;">
    </div>
    <div>
        <span class="btn default btn-sm btn-file">
            <span class="fileinput-new">Seleziona immagine</span>
            <span class="fileinput-exists">Cambia</span>
            <input type="file" name="<?php echo $field['fields_name']; ?>">
        </span>
        <a href="javascript:;" class="btn red btn-sm fileinput-exists" data-dismiss="fileinput">Rimuovi</a>
    </div>
</div>
<?php echo $help; ?>
