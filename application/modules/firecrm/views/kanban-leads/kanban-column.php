<?php

if (empty($thumbSize)) {
    $thumbSize = 40;
}

?>

<?php foreach ($leads as $lead) :  ?>
    <?php $user = $this->db->query("SELECT * FROM users WHERE users_id = '{$lead['leads_member_id']}'")->row_array(); ?>

    <?php if ($viewMode == 'columns' && ($lead['leads_status'] != $column['leads_status_id'])) continue; ?>

    <?php if ($viewMode == 'users' && !array_key_exists($user['users_id'], $users)) continue;
    ?>

    <div data-lead="<?php echo $lead['leads_id']; ?>" class="task-box" style="background-color:#ffffff">
        <div class="task-inner">
            <div class="left pull-left">
                <div class="task-head">
                    <div class="task-title" data-toggle="tooltip" title="<?php echo $lead['leads_title']; ?>">
                        <a href="<?php echo base_url("main/layout/36/{$lead['leads_id']}"); ?>" target="_blank">
                            <?php echo $lead['leads_title']; ?>
                        </a>
                    </div>


                    <?php /* if ($lead['leads_working_on'] == DB_BOOL_TRUE) : ?>
                        <small class="label label-warning">Working on</small>
                    <?php endif; */ ?>
                </div>

                <div class="task-body">
                    <div class="text"><?php echo word_limiter(strip_tags($lead['leads_description']), 15); ?></div>
                </div>

                <div class="task-foot">
                    <div class="dates">
                        <strong>Recall date:</strong>
                        <?php if ($lead['leads_recall_date']) : ?>
                            <span class="expired"><?php echo $lead['leads_recall_date'] ? dateFormat($lead['leads_recall_date']) : '-'; ?></span> <img src="data:image/gif;base64,R0lGODlhMgAsAEcAACH/C05FVFNDQVBFMi4wAwEFAAAh+QQAZAAAACwAAAAAMgAsAIPgSk/wnZ/vwcTz4eTKdYXLmandytXm2eHEtsrHzuHT2Of3+Prf5vHo7/fuGBf///8E+/DJSatdxGkyrP9gOAlaWSxiqlZYqQHGKqeBWxLorFcDYL+xnfCRufleuaFs4CIkipqCcgl1IBaMowNwmKpqNwUKHE16PQstANGQNNTdM5rsKDCSpFtbXhGoE3sSLSUCfBQLdHYDAowdBi4AgXx5G2IFWw44D4mGD2kubIg3d28lXIaUdWKDWwoSdJpnPaZsnmROglAwcokMgmqunqmxSrMvgBO3CROiJlOsVr7JN8sTB5DSQsMJZrfBgpxCny+1FJQE2W5qQTqw3yOjFrBmKsZb5RQHR3YWDbrsK6rws6BvSwFJ8EwhDLGNngRjB+eAkiEwHYVSaxZKKPgizY6IYWI+DMiwJgSdAA5ZqMF3wQcAbiBKvXjnAdYdkwAIXGHYRONDSMhCDChQgKaHKoU+VEyBRcHNEBy3WJQAMqU5AliThoB2QiUonxRu1VNjsSqNeCkumUhi7yVYcwDiolMxbkuQZlGMfligAIHfqR8emRrTBOaKBQ0YNLDqQW0ZLVsCSJ5MubLly5gDVAHA5Ifnz6BDe+YCWbTp054JKPCDurVrAPwOFCAwG6vt27hz695tuzaCp4gbCFesOPFw48iJK0+emPjx4osXRAAAIfkEAGQAAAAsAAAAADIALACD4EpP8J2f78HE8+HkynWFy5mp3crV5tnhxLbKx87h09jn9/j63+bx6O/37hgX////BDTwyUmrvTjrzbv/YCiOZGmeaKqubOu+cCzPdG3feK7vfO//wKBwSCwaj8ikcslsOp/Q6DECACH5BABkAAAALAAAAAAyACwAg+BKT/Cdn+/BxPPh5Mp1hcuZqd3K1ebZ4cS2ysfO4dPY5/f4+t/m8ejv9+4YF////wT78MlJq13EaTKs/2A4CVpZLGKqVlipAcYqp4FbEuisVwNgv7Gd8JG5+V65oWzgIiSKmoJyCXUgFoyjA3CYqmo3BQocTXo9Cy0A0ZA01N0zmuwoMJKkW1teEagTexItJQJ8FAt0dgMCjB0GLgCBfHkbYgVbDjgPiYYPaS5siDd3byVchpR1YoNbChJ0mmc9pmyeZE6CUDByiQyCaq6eqbFKsy+AE7cJE6ImU6xWvsk3yxMHkNJCwwlmt8GCnEKfL7UUlATZbmpBOrDfI6MWsGYqxlvlFAdHdhYNuuwrqvCzoG9LAUnwTCEMsY2eBGMH54CSITAdhVJrFkoo+CLNjohhYj4MyLAmBJ0ADlmowXfBBwBuIEq9eOcB1h2TAAhcYdhE40NIyEIMKFCApocqhT5UTIFFwc0QHLdYlAAypTkCWJOGgHZCJSifFG7VU2OxKo14KS6ZSGLvJVhzAOKiUzFuS5BmUYx+WKAAgd+pHx6ZGtME5ooFDRg0sOpBbRktWwJInky5suXLmANUAcDkh+fPoEN75gJZtOnTngko8IO6tWsA/A4UIDAbq+3buHPr3m27NoKniBsIV6w48XDjyIkrT56Y+PHiixdEAAAh/rZGSUxFIElERU5USVRZDQpDcmVhdGVkIG9yIG1vZGlmaWVkIGJ5DQpLRUlUSCBTT0FSRVMNCihLZWl0aCBNLiBTb2FyZXMpDQpCZWFuIENyZWF0aXZlDQoNCkNyZWF0ZWQgYnkgQWxjaGVteSBNaW5kd29ya3MnDQpHSUYgQ29uc3RydWN0aW9uIFNldCBQcm9mZXNzaW9uYWwNCmh0dHA6Ly93d3cubWluZHdvcmtzaG9wLmNvbQAh/wtHSUZDT050YjEuMAIBAA4KAAYAAwAAAAAAAAAAAApDbGlwYm9hcmQAADs=" class="warning-icon" />
                        <?php else : ?>
                            <?php echo $lead['leads_recall_date'] ? dateFormat($lead['leads_recall_date']) : '-'; ?>
                        <?php endif; ?>
                    </div>

                    <div class="actions text-right">

                        <?php /*if (array_key_exists($this->auth->get('users_id'), $users)) : ?>
                            <!--<?php if ($lead['tasks_working_on'] == DB_BOOL_TRUE) : ?>
                                <a href="<?php echo base_url("personal-kanban-board/main/task_working_on/2/{$lead['tasks_id']}"); ?>" style="background-color:red;color:#ffffff" class="btn btn-xs red js_link_ajax" data-toggle="tooltip" title="Stop time tracker"><i class="fa fa-stop"></i></a>
                            <?php else : ?>
                                <a href="<?php echo base_url("personal-kanban-board/main/task_working_on/1/{$lead['tasks_id']}"); ?>" style="background-color:green;color:#ffffff" class="btn btn-xs green js_link_ajax" data-toggle="tooltip" title="Start time tracker"><i class="fa fa-play"></i></a>
                            <?php endif; ?>-->
                        <?php endif; */ ?>



                        <?php if ($this->auth->is_admin()) : ?>
                            <a style="color:#ffffff;background-color:purple" href="<?php echo base_url("get_ajax/modal_form/kanban-form-task/{$lead['leads_id']}"); ?>" class="btn btn-xs purple js_open_modal" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="right pull-left">
                <div class="photos">
                    <a href="#">
                        <?php if (!empty($user)) : ?>
                            <img class="user-avatar" src="<?php echo $user['users_avatar'] ? base_url_template("imgn/1/40/40/uploads/{$user['users_avatar']}") : "https://via.placeholder.com/{$thumbSize}&text=" . substr($user['users_first_name'], 0, 3); ?>" data-toggle="tooltip" alt="<?php echo $user['users_first_name']; ?>" title="<?php echo $user['users_first_name']; ?>" <?php echo "width='{$thumbSize}'"; ?> />
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Add new task -->
<?php if ($viewMode == 'columns') : ?>
    <div class="add_new_task">
        <a href="<?php echo base_url(); ?>get_ajax/modal_form/firecrm-leads-form?leads_status=<?php echo $column['leads_status_id']; ?>&leads_member_id=<?php echo $this->auth->get('users_id'); ?>" class="js_open_modal small-box-footer" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>"><i class="fas fa-plus"></i></a>
    </div>
<?php endif; ?>
<!-- End new task -->