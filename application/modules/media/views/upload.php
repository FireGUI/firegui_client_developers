<h3 class="page-title">Upload</h3>

<form action="<?php echo base_url('media/db_ajax/upload'); ?>" class="dropzone" id="js_upload_form" style="margin-top: 125px;">
    <div class="row" style="margin-top: -125px;">
        <div class="col-md-4">
            <div class="form-group">
                <label><?php e("entità"); ?></label>
                <select name="entity_id" class="form-control">
                    <option></option>
                    <?php foreach ($dati['entities'] as $entity): ?>
                    <option value="<?php echo $entity['entity_id']; ?>" data-name="<?php echo $entity['entity_name']; ?>"><?php echo ucwords(str_replace('_', ' ', $entity['entity_name'])); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label><?php e("valore"); ?></label>
                <input type="hidden" name="value" data-ref="" class="form-control js_select_ajax" />
            </div>
        </div>
    </div>
</form>