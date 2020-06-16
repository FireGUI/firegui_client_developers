<?php

if (empty($value_id)) {
    echo "Project id not found";
    return false;
}

/*$project = $this->apilib->searchFirst("projects", ["projects_id = '$value_id'"]);*/
$allTasks = $this->apilib->search('tasks', ["tasks_project_id = '{$value_id}'"]);
$columns = $this->apilib->search("tasks_status", [], null, 'tasks_status_order');

?>

<?php $this->layout->addModuleStylesheet('firecrm', 'css/planner-style.css'); ?>

<div id="planner-container" class="limited planner-boards">
    <?php if (empty($columns)) : ?>
        <p>There are no tasks status yet. <a href="<?php echo base_url(); ?>main/layout/tasks-status">Add a status</a>.</p>
    <?php endif; ?>
    <div class="planner-header">
        <?php foreach ($columns as $column) : ?>
            <div class="column" <?php if ($column['tasks_status_color']) : ?>style="background-color:<?php echo $column['tasks_status_color']; ?>" <?php endif; ?>>
                <h5 data-toggle="tooltip" title="<?php echo $column['tasks_status_status']; ?>"><?php echo character_limiter($column['tasks_status_status'], 20); ?></h5>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="planner-body">
        <?php foreach ($columns as $column) : ?>
            <div class="column sortable" data-update="column" data-column="<?php echo $column['tasks_status_id']; ?>">
                <?php
                $this->load->view('planner/planner-column', array('tasks' => $allTasks, 'column' => $column, 'viewMode' => 'columns')); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php $this->layout->addModuleJavascript('firecrm', 'js/planner.js'); ?>