<?php


/*$project = $this->apilib->searchFirst("projects", ["projects_id = '$value_id'"]);*/
$allLeads = $this->apilib->search('leads');
$columns = $this->apilib->search("leads_status", [], null, 'leads_status_order');

?>

<?php $this->layout->addModuleStylesheet('firecrm', 'css/planner-style.css'); ?>

<div id="planner-container" class="limited planner-boards">
    <?php if (empty($columns)) : ?>
        <p>There are no leads status yet. <a href="<?php echo base_url(); ?>main/layout/leads-status">Add a status</a>.</p>
    <?php endif; ?>
    <div class="planner-header">
        <?php foreach ($columns as $column) : ?>
            <div class="column" <?php if ($column['leads_status_color']) : ?>style="background-color:<?php echo $column['leads_status_color']; ?>" <?php endif; ?>>
                <h5 data-toggle="tooltip" title="<?php echo $column['leads_status_status']; ?>"><?php echo character_limiter($column['leads_status_status'], 20); ?></h5>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="planner-body">
        <?php foreach ($columns as $column) : ?>
            <div class="column sortable" data-update="column" data-column="<?php echo $column['leads_status_id']; ?>">
                <?php
                $this->load->view('kanban-leads/kanban-column', array('leads' => $allLeads, 'column' => $column, 'viewMode' => 'columns')); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php $this->layout->addModuleJavascript('firecrm', 'js/kanban-leads.js'); ?>