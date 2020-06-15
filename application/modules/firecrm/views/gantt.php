<?php

/*$projects = $this->apilib->search('projects', []);
if ($value_id) {
    $project_tasks = $this->apilib->search('tasks', ["tasks_project_id = '{$value_id}'"]);
    $project_tasks = $this->apilib->search('tasks', [], null, 0, 'tasks_start_date', 'ASC', 2, null, ['group_by' => 'tasks_project_id']);
} else {
    $project_tasks = $this->apilib->search('tasks', [], null, 0, 'tasks_start_date', 'ASC', 2, null, []);
}
debug($project_tasks, true);*/

?>

<?php $this->layout->addModuleStylesheet('firecrm', 'vendor/jquery-gantt/css/style.css'); ?>
<?php $this->layout->addModuleJavascript('firecrm', 'vendor/jquery-gantt/js/jquery.fn.gantt.js'); ?>

<script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>

<style>
    .fn-gantt .navigate .nav-slider-button {
        background: url('<?php echo $this->layout->moduleAssets('firecrm', 'vendor/jquery-gantt/img/slider_handle.png'); ?>') center center no-repeat;
    }

    .fn-gantt .nav-link {
        background: #595959 url('<?php echo $this->layout->moduleAssets('firecrm', 'vendor/jquery-gantt/img/icon_sprite.png'); ?>') !important;
    }

    .fn-gantt .dataPanel {
        background-image: url('<?php echo $this->layout->moduleAssets('firecrm', 'vendor/jquery-gantt/img/grid.png'); ?>');
    }

    .fn-gantt {
        border: 2px solid #eef2f4;
        border-radius: 4px
    }

    .fn-gantt-hint {
        background: #6c7888;
        border: 0;
        color: #fff
    }

    .fn-gantt .leftPanel {
        width: 450px;
        border-right: 1px solid #e4e4e4
    }

    .fn-gantt .leftPanel .name {
        font-weight: 500;
        font-size: 13px;
        background: #717a86
    }

    .fn-gantt .rightPanel .fn-label:hover {
        cursor: pointer
    }

    .fn-gantt .rightPanel .month {
        color: #717a86
    }

    .fn-gantt .bottom,
    .fn-gantt .leftPanel .desc,
    .fn-gantt .rightPanel .month,
    .fn-gantt .rightPanel .year,
    .fn-gantt .spacer,
    .fn-gantt .wd {
        background-color: #fff
    }

    .fn-gantt .leftPanel .fn-label {
        width: 100%
    }

    .fn-gantt .leftPanel .desc,
    .fn-gantt .leftPanel .name {
        border-bottom: 1px solid #f5f5f5;
        height: 23px
    }

    .fn-gantt .leftPanel .name {
        width: 40%
    }

    .fn-gantt .leftPanel .desc {
        width: 60%
    }

    #gantt .gantt_project_name {
        font-weight: 500;
        text-align: center;
        font-size: 16px;
        margin: 0 auto;
        display: block;
        margin-top: 32px
    }

    .fn-gantt .leftPanel .name .fn-label {
        color: #fff
    }

    .fn-gantt table th:first-child {
        width: 150px
    }

    .gantt {
        margin: inherit;
        border: inherit;
        position: inherit;
        width: inherit
    }
</style>



<div class="gantt"></div>


<script>
    $(function() {
        "use strict";

        $(".gantt").gantt({
            source: "<?php echo base_url('firecrm/main/get_gantt_data/' . $value_id); ?>",
            itemsPerPage: 50,
            navigate: 'scroll',
            onAddClick: function(dt, rowId) {
                alert("Empty space clicked - add an item!");
            },
            onRender: function() {
                $('.gantt .leftPanel .name .fn-label:empty').parents('.name').css('background', 'initial');
            },
            onItemClick: function(data) {

                // TODO: OPEN LAYOUT MODAL
                /*if (typeof(data.project_id) != 'undefined') {
                    var projectViewUrl = '';
                    window.location.href = projectViewUrl + '/' + data.project_id;
                } else if (typeof(data.task_id) != 'undefined') {
                    init_task_modal(data.task_id);
                }*/
            },
        });


        $(".gantt").popover({
            selector: ".bar",
            title: function _getItemText() {
                return this.textContent;
            },
            container: '.gantt',
            content: "Here's some useful information.",
            trigger: "hover",
            placement: "auto right"
        });

        prettyPrint();

    });
</script>