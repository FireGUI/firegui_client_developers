<form class="inbox-compose form-horizontal formAjax" id="fileupload" action="<?php echo base_url('messages/send'); ?>" method="POST" enctype="multipart/form-data">
    <div class="inbox-compose-btn">
        <button class="btn blue"><i class="icon-check"></i>Send</button>
        <button class="btn">Discard</button>
    </div>
    <div class="inbox-form-group mail-to">
        <label class="control-label">To:</label>
        <div class="controls ">
            <?php if(isset($data['message'])): ?>
                <input type="hidden" name="<?php echo MESSAGES_TABLE_JOIN_FIELD; ?>" value="<?php echo $data['message'][MESSAGES_TABLE_FROM_FIELD]; ?>" />
                <p class="form-control-static"><?php echo "{$data['message'][MESSAGES_USER_NAME]} {$data['message'][MESSAGES_USER_LASTNAME]}" ?></p>
            <?php else: ?>
                <select class="form-control js_select_to" name="<?php echo MESSAGES_TABLE_JOIN_FIELD; ?>">
                    <?php foreach($data['users'] as $user): ?>
                    <option value="<?php echo $user[MESSAGES_USER_JOIN_FIELD]; ?>"><?php echo "{$user[MESSAGES_USER_NAME]} {$user[MESSAGES_USER_LASTNAME]}" ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>
    
    
    <div class="inbox-form-group">
        <textarea class="inbox-editor inbox-wysihtml5 form-control" name="<?php echo MESSAGES_TABLE_TEXT_FIELD; ?>" rows="12"></textarea>
    </div>
    
    
    <div class="inbox-compose-btn">
        <button class="btn blue"><i class="icon-check"></i>Send</button>
        <button class="btn">Discard</button>
    </div>
</form>




<script>
    
    $(document).ready(function() {
        $('.js_select_to').select2({
            allowClear: true
        });
    });
    
</script>