<?php global $task_manager_top_bar_in; ?>
<?php
if ($task_manager_top_bar_in) {
    return;
} else {
    $task_manager_top_bar_in = true;
}
?>
<style>
    .js_topbar_timetracker {
        float: right;
        padding: 8px 10px 0 0;
    }

    .js_topbar_task_title {
        background: #e4e4e4;
        padding: 4px;
        font-size: 0.9em;
        border-radius: 3px;
        margin-right: 10px;
    }

    .js_topbar_task_title.working_on {
        background: #f39c12 !important;
    }
</style>
<?php

$my_user_id = $this->auth->get('users_id');

$last_tasks = $this->db->query("SELECT * FROM timesheet LEFT JOIN tasks ON (tasks_id = timesheet_task) WHERE timesheet_member = '$my_user_id' GROUP BY timesheet_task ORDER BY timesheet_end_time DESC LIMIT 10")->result_array();

$last_task = $this->apilib->searchFirst('timesheet', array("timesheet_end_time IS NULL AND timesheet_member = '$my_user_id'"));

if (empty($last_task)) {
    $last_task = $this->apilib->searchFirst('timesheet', array("timesheet_member = '$my_user_id'"), 0, 'timesheet_end_time', 'DESC');
} else {
    //debug($task['tasks_working_periods_start'],true);
    $datetime1 = new DateTime($last_task['timesheet_start_time']);
    $datetime2 = new DateTime();
    $interval = $datetime1->diff($datetime2);
}
?>

<?php if (isset($last_task['tasks_title']) && $last_task['tasks_working_on'] == DB_BOOL_TRUE) : ?>
    <div class="js_topbar_timetracker">
        <span class="js_topbar_task_title working_on" style="height:34px;display:block;padding-top:8px;float:left;">Working on: <?php echo $last_task['tasks_title']; ?> - <?php if (!empty($interval)) : ?><label id="hours"><?php echo $interval->format('%h'); ?></label>:<label id="minutes"><?php echo $interval->format('%i'); ?></label>:<label id="seconds"><?php echo $interval->format('%s');; ?></label><?php endif; ?></span>
        <a href="<?php echo base_url("firecrm/main/task_working_on/2/{$last_task['timesheet_task']}"); ?>" style="background-color:red;color:#ffffff" class="btn red js_link_ajax" data-toggle="tooltip" title="Stop time tracker">
            <i class="fas fa-pause"></i>
        </a>
    </div>
<?php else : ?>
    <div class="js_topbar_timetracker" style="width:190px;">
        <div style="width:140px;float:left;">
            <select class="form-control select2_standard js_top_tasks_select">
                <?php foreach ($last_tasks as $task) : ?>
                    <option <?php if (isset($last_task['tasks_title']) && $last_task['timesheet_task'] == $task['tasks_id']) : ?>selected="selected" <?php endif; ?>value="<?php echo $task['timesheet_task']; ?>" data-working_on="<?php echo ($task['tasks_working_on']); ?>"><?php echo $task['tasks_title']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <a href="javascript:void(0);" style="background-color:green;color:#ffffff;float:left;" class="btn green js_play_task" data-toggle="tooltip" title="Start time tracker">
            <i class="fas fa-play"></i>
        </a>
    </div>
<?php endif; ?>

<script>
    $('.navbar').append($('.js_topbar_timetracker'));
    $('.navbar .js_topbar_timetracker').not(':first').remove();

    var minutesLabel = document.getElementById("minutes");
    var secondsLabel = document.getElementById("seconds");
    var hoursLabel = document.getElementById("hours");

    <?php if (isset($interval)) : ?>
        var totalSeconds = pad(<?php echo $interval->format('%i') * 60 + $interval->format('%s'); ?>);
        setInterval(setTime, 1000);
    <?php endif; ?>

    function setTime() {
        ++totalSeconds;
        secondsLabel.innerHTML = pad(totalSeconds % 60);
        minutesLabel.innerHTML = pad(parseInt(totalSeconds / 60));
        hoursLabel.innerHTML = pad(parseInt(totalSeconds / 60 / 60));
    }

    function pad(val) {
        var valString = val + "";
        if (valString.length < 2) {
            return "0" + valString;
        } else {
            return valString;
        }
    }

    $('body').on('click', '.js_play_task', function(e) {
        e.preventDefault(); // Prevent follow links
        e.stopPropagation(); // Prevent propagation on parent DOM elements
        e.stopImmediatePropagation(); // Prevent other handlers to be fired

        var task_id = $('.js_top_tasks_select').val();

        var url = base_url + 'firecrm/main/task_working_on/1/' + task_id;
        loading(true);
        $.ajax({
            url: url,
            dataType: 'json',
            complete: function() {
                loading(false);
            },
            success: function(msg) {
                handleSuccess(msg);
            },
            error: function(xhr, ajaxOptions, thrownError) {

                var errorContainerID = 'ajax-error-container';
                var errorContainer = $('#' + errorContainerID);

                if (errorContainer.size() === 0) {
                    errorContainer = $('<div/>').attr('id', errorContainerID).css({
                        "z-index": 99999999,
                        "background-color": '#fff'
                    });
                    $('body').prepend(errorContainer);
                }

                errorContainer.html("Errore ajax:" + xhr.responseText);
            }
        });
    });
</script>