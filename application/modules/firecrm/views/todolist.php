<link href='https://fonts.googleapis.com/css?family=Handlee' rel='stylesheet' type='text/css' />
<link href='https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css' rel='stylesheet' type='text/css' />

<?php $this->layout->addModuleStylesheet('firecrm', 'todolist/css/waves.min.css'); ?>
<?php $this->layout->addModuleStylesheet('firecrm', 'todolist/css/animate.min.css'); ?>
<?php $this->layout->addModuleStylesheet('firecrm', 'todolist/css/todo.css'); ?>

<?php
$current_layout = $this->layout->getCurrentLayoutIdentifier();

$todoitems = [];

if ($current_layout) {
    if ($current_layout === 'project-detail') {
        $todoitems = $this->apilib->search('todolist', array('todolist_user' => $this->auth->get('id'), 'todolist_project_id' => $value_id), null, null, 'todolist_id ASC');
    } elseif ($current_layout === 'customer-detail') {
        $todoitems = $this->apilib->search('todolist', array('todolist_user' => $this->auth->get('id'), 'todolist_customer_id' => $value_id), null, null, 'todolist_id ASC');
    } else {
        $todoitems = $this->apilib->search('todolist', array('todolist_user' => $this->auth->get('id')), null, null, 'todolist_id ASC');
    }
}

?>

<!-- Todo Lists -->
<div id="todo-lists">
    <div class="tl-header">
        <h2>Todo-List</h2>
        <small>Manage your personal todo-list</small>
    </div>

    <div class="clearfix"></div>

    <div class="tl-body">
        <div id="add-tl-item">
            <i class="add-new-item zmdi zmdi-plus"></i>

            <div class="add-tl-body">
                <textarea name="todolist_text" placeholder="What's your plan?"></textarea>
                <input type="hidden" name="todolist_user" value="<?php echo $this->auth->get('id'); ?>" />

                <?php if ($current_layout && $current_layout === 'project-detail') : ?>
                    <input type="hidden" name="todolist_project_id" value="<?php echo $value_id; ?>" />
                <?php elseif ($current_layout && $current_layout === 'customer-detail') : ?>
                    <input type="hidden" name="todolist_customer_id" value="<?php echo $value_id; ?>" />
                <?php endif; ?>

                <div class="add-tl-actions">
                    <a href="" data-tl-action="dismiss"><i class="zmdi zmdi-close"></i></a>
                    <a href="" data-tl-action="save"><i class="zmdi zmdi-check"></i></a>
                </div>
            </div>
        </div>

        <?php foreach ($todoitems as $todoitem) : ?>
            <div class="checkbox media">
                <div class="media-body">
                    <label>
                        <input class="toggle" type="checkbox" value="<?php echo $todoitem['todolist_id']; ?>" <?php echo ($todoitem['todolist_deleted'] == DB_BOOL_TRUE) ? 'checked' : '' ?>>
                        <i class="input-helper"></i>
                        <span><?php echo $todoitem['todolist_text']; ?></span>
                    </label>
                    <span class="pull-right js_remove_todo" role="button" data-todo-id="<?php echo $todoitem['todolist_id']; ?>">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php $this->layout->addModuleJavascript('firecrm', 'todolist/js/todo.js'); ?>