<?php

if (empty($thumbSize)) {
    $thumbSize = 40;
}

?>

<?php foreach ($tasks as $task) :  ?>
    <?php
    $form_id = $this->datab->get_form_id_by_identifier('task_form');
    //debug($form_id);
    $users = $this->db->query("SELECT * FROM tasks_members NATURAL LEFT JOIN users WHERE tasks_id = '{$task['tasks_id']}'")->result_array();
    $users_id = array_map(function ($user) {
        return $user['users_id'];
    }, $users);
    //debug($users_id);
    ?>

    <?php if ($viewMode == 'columns' && ($task['tasks_status'] != $column['tasks_status_id'])) continue; ?>

    <?php if ($viewMode == 'users' && !array_key_exists($user['users_id'], $users_id)) continue;
    ?>

    <div data-task="<?php echo $task['tasks_id']; ?>" class="task-box" style="background-color:#ffffff <?php //echo ($task['tasks_color']) ?: '#ffffff'; 
                                                                                                        ?>">
        <div class="task-inner">
            <div class="left pull-left">
                <div class="task-head">
                    <div class="task-title" data-toggle="tooltip" title="<?php echo $task['tasks_title']; ?>">
                        <a href="<?php echo base_url("main/layout/36/{$task['tasks_id']}"); ?>" target="_blank">
                            <?php echo $task['tasks_title']; ?>
                        </a>
                    </div>
                    <?php if ($viewMode == 'columns') : ?>
                        <a class="project" style="font-size: 1.2em;" href=""><?php //echo $task['tasks_category_value']; 
                                                                                ?></a>
                    <?php else : ?>
                        <a class="project" style="font-size: 1.2em;" href="<?php echo base_url(); ?>main/layout/planner-view/<?php echo $task['columns_board'];; ?>"><?php echo $task['projects_name']; ?></a> - <a class="project" style="font-size: 1.2em;" href="">CATEGORY? TAGS?<?php //echo $task['tasks_category_value']; 
                                                                                                                                                                                                                                                                                        ?></a>
                    <?php endif; ?>



                </div>

                <div class="task-body">
                    <div class="text"><?php echo word_limiter(strip_tags($task['tasks_description']), 15); ?></div>
                </div>

                <div class="task-foot">
                    <div class="dates">
                        <strong><?php e('closed at'); ?>:</strong>
                        <?php echo $task['tasks_done_date'] ? dateFormat($task['tasks_done_date']) : '-'; ?><br />

                        <strong>Deadline:</strong>
                        <?php if ($task['tasks_due_date']) : ?>
                            <span class="expired"><?php echo $task['tasks_due_date'] ? dateFormat($task['tasks_due_date']) : '-'; ?></span> <img src="data:image/gif;base64,R0lGODlhMgAsAEcAACH/C05FVFNDQVBFMi4wAwEFAAAh+QQAZAAAACwAAAAAMgAsAIPgSk/wnZ/vwcTz4eTKdYXLmandytXm2eHEtsrHzuHT2Of3+Prf5vHo7/fuGBf///8E+/DJSatdxGkyrP9gOAlaWSxiqlZYqQHGKqeBWxLorFcDYL+xnfCRufleuaFs4CIkipqCcgl1IBaMowNwmKpqNwUKHE16PQstANGQNNTdM5rsKDCSpFtbXhGoE3sSLSUCfBQLdHYDAowdBi4AgXx5G2IFWw44D4mGD2kubIg3d28lXIaUdWKDWwoSdJpnPaZsnmROglAwcokMgmqunqmxSrMvgBO3CROiJlOsVr7JN8sTB5DSQsMJZrfBgpxCny+1FJQE2W5qQTqw3yOjFrBmKsZb5RQHR3YWDbrsK6rws6BvSwFJ8EwhDLGNngRjB+eAkiEwHYVSaxZKKPgizY6IYWI+DMiwJgSdAA5ZqMF3wQcAbiBKvXjnAdYdkwAIXGHYRONDSMhCDChQgKaHKoU+VEyBRcHNEBy3WJQAMqU5AliThoB2QiUonxRu1VNjsSqNeCkumUhi7yVYcwDiolMxbkuQZlGMfligAIHfqR8emRrTBOaKBQ0YNLDqQW0ZLVsCSJ5MubLly5gDVAHA5Ifnz6BDe+YCWbTp054JKPCDurVrAPwOFCAwG6vt27hz695tuzaCp4gbCFesOPFw48iJK0+emPjx4osXRAAAIfkEAGQAAAAsAAAAADIALACD4EpP8J2f78HE8+HkynWFy5mp3crV5tnhxLbKx87h09jn9/j63+bx6O/37hgX////BDTwyUmrvTjrzbv/YCiOZGmeaKqubOu+cCzPdG3feK7vfO//wKBwSCwaj8ikcslsOp/Q6DECACH5BABkAAAALAAAAAAyACwAg+BKT/Cdn+/BxPPh5Mp1hcuZqd3K1ebZ4cS2ysfO4dPY5/f4+t/m8ejv9+4YF////wT78MlJq13EaTKs/2A4CVpZLGKqVlipAcYqp4FbEuisVwNgv7Gd8JG5+V65oWzgIiSKmoJyCXUgFoyjA3CYqmo3BQocTXo9Cy0A0ZA01N0zmuwoMJKkW1teEagTexItJQJ8FAt0dgMCjB0GLgCBfHkbYgVbDjgPiYYPaS5siDd3byVchpR1YoNbChJ0mmc9pmyeZE6CUDByiQyCaq6eqbFKsy+AE7cJE6ImU6xWvsk3yxMHkNJCwwlmt8GCnEKfL7UUlATZbmpBOrDfI6MWsGYqxlvlFAdHdhYNuuwrqvCzoG9LAUnwTCEMsY2eBGMH54CSITAdhVJrFkoo+CLNjohhYj4MyLAmBJ0ADlmowXfBBwBuIEq9eOcB1h2TAAhcYdhE40NIyEIMKFCApocqhT5UTIFFwc0QHLdYlAAypTkCWJOGgHZCJSifFG7VU2OxKo14KS6ZSGLvJVhzAOKiUzFuS5BmUYx+WKAAgd+pHx6ZGtME5ooFDRg0sOpBbRktWwJInky5suXLmANUAcDkh+fPoEN75gJZtOnTngko8IO6tWsA/A4UIDAbq+3buHPr3m27NoKniBsIV6w48XDjyIkrT56Y+PHiixdEAAAh/rZGSUxFIElERU5USVRZDQpDcmVhdGVkIG9yIG1vZGlmaWVkIGJ5DQpLRUlUSCBTT0FSRVMNCihLZWl0aCBNLiBTb2FyZXMpDQpCZWFuIENyZWF0aXZlDQoNCkNyZWF0ZWQgYnkgQWxjaGVteSBNaW5kd29ya3MnDQpHSUYgQ29uc3RydWN0aW9uIFNldCBQcm9mZXNzaW9uYWwNCmh0dHA6Ly93d3cubWluZHdvcmtzaG9wLmNvbQAh/wtHSUZDT050YjEuMAIBAA4KAAYAAwAAAAAAAAAAAApDbGlwYm9hcmQAADs=" class="warning-icon" />
                        <?php else : ?>
                            <?php echo $task['tasks_due_date'] ? dateFormat($task['tasks_due_date']) : '-'; ?>
                        <?php endif; ?>
                    </div>

                    <div class="actions text-right">

                        <?php if (array_key_exists($this->auth->get('users_id'), $users_id)) : ?>
                            <?php if ($task['tasks_working_on'] == DB_BOOL_TRUE) : ?>
                                <a href="<?php echo base_url("firecrm/main/task_working_on/2/{$task['tasks_id']}"); ?>" style="background-color:red;color:#ffffff" class="btn btn-xs red js_link_ajax" data-toggle="tooltip" title="Stop time tracker">
                                    <i class="fas fa-pause"></i>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo base_url("firecrm/main/task_working_on/1/{$task['tasks_id']}"); ?>" style="background-color:green;color:#ffffff" class="btn btn-xs green js_link_ajax" data-toggle="tooltip" title="Start time tracker">
                                    <i class="fas fa-play"></i>
                                </a>
                            <?php endif; ?>
                        <?php endif;  ?>

                        <?php if ($this->auth->is_admin()) : ?>
                            <span data-toggle="tooltip" title="" data-original-title="Edit">
                                <a class="btn btn-xs purple js_open_modal" href="<?php echo base_url("get_ajax/modal_form/{$form_id}/{$task['tasks_id']}"); ?>" style="color:#ffffff;background-color:purple;" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>">
                                    <span class="fas fa-edit" style="color:white !important;"></span>
                                </a>
                            </span>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="right pull-left">
                <div class="photos">
                    <?php foreach ($users as $user) : ?>
                        <?php //debug($user, true); 
                        ?>
                        <a href="#">
                            <img class="user-avatar" src="<?php echo $user['users_avatar'] ? base_url_template("imgn/1/40/40/uploads/{$user['users_avatar']}") : "https://via.placeholder.com/{$thumbSize}&text=" . substr($user['users_first_name'], 0, 3); ?>" data-toggle="tooltip" alt="<?php echo $user['users_first_name']; ?>" title="<?php echo $user['users_first_name']; ?>" <?php echo "width='{$thumbSize}'"; ?> />
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Add new task -->
<?php if ($viewMode == 'columns') : ?>
    <div class="add_new_task">
        <a href="<?php echo base_url(); ?>get_ajax/modal_form/kanban-form-task?tasks_status=<?php echo $column['tasks_status_id']; ?>&tasks_users=<?php echo $this->auth->get('users_id'); ?>" class="js_open_modal small-box-footer" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>"><i class="fas fa-plus"></i></a>
    </div>
<?php endif; ?>
<!-- End new task -->