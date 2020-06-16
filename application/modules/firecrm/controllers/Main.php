<?php
class Main extends MX_Controller
{


    function __construct()
    {
        parent::__construct();
    }


    function get_gantt_data($project_id = null)
    {
        if ($project_id)
            $tasks = $this->apilib->search("tasks", ['tasks_project_id' => $project_id]);
        else
            $tasks = $this->apilib->search("tasks", []);

        $gantt_tasks = array();
        $last_project_title = '';

        foreach ($tasks as $task) {

            if ($last_project_title != $task['projects_name']) {
                $last_project_title = $task['projects_name'];
                $title = $task['projects_name'];
            } else {
                $title = '';
            }

            $gantt_tasks[] = array(
                'name' => $title,
                'desc' => $task['tasks_title'],
                'values' => [
                    [
                        'from' => $task['tasks_start_date'],
                        'to' => $task['tasks_due_date'],
                        'label' => $task['tasks_title'],
                        'desc' => $task['tasks_title'],
                        'customClass' => 'ganttRed',
                        'dataObj' => array('projects_id' => '1')
                    ]
                ]
            );
        }

        echo json_encode($gantt_tasks);
    }


    function editTask($task_id)
    {
        if (!$task_id)
            return;

        $data = $this->input->post();


        // Is this a column change? If yes, to done column?
        if ($data['tasks_column']) {

            $column = $this->apilib->searchFirst('columns', ['columns_id' => $data['tasks_column']]);

            if ($column['columns_done_column'] == DB_BOOL_TRUE) {
                $data['tasks_close_date'] = date('Y-m-d');

                // Stop tracker if it was started
                $data['tasks_working_on'] = DB_BOOL_FALSE;
                $this->db->query("UPDATE tasks_working_periods SET tasks_working_periods_stop = CURRENT_TIMESTAMP WHERE tasks_working_periods_task_id = '{$task_id}' AND tasks_working_periods_stop IS NULL");
            }
        }

        $this->apilib->edit('tasks', $task_id, $data);
    }

    function editLead($lead_id)
    {
        if (!$lead_id)
            return;

        $data = $this->input->post();


        // Is this a column change? If yes, to done column?
        if ($data['leads_status']) {
            $this->apilib->edit('leads', $lead_id, $data);
        }
    }

    function task_working_on($status, $task_id)
    {
        if (!$task_id or !$status) {
            return false;
        }

        $user_id = $this->auth->get('users_id');
        //Stop all task playing by this user
        $timesheets = $this->apilib->search('timesheet', ['timesheet_member' => $user_id, 'timesheet_end_time IS NULL']);

        foreach ($timesheets as $timesheet) {

            $this->apilib->edit('timesheet', $timesheet['timesheet_id'], ['timesheet_end_time' => date('Y-m-d H:i:s')]);
        }

        if ($status == 1) {
            $data['tasks_working_on'] = DB_BOOL_TRUE;

            $task = $this->apilib->view('tasks', $task_id);

            $this->apilib->create('timesheet', [
                'timesheet_member' => $user_id,
                'timesheet_project' => $task['tasks_project_id'],
                'timesheet_task' => $task_id,
                'timesheet_start_time' => date('Y-m-d H:i:s'),
                'timesheet_end_time' => null,
                'timesheet_note' => null,
                'timesheet_total_hours' => null,
            ]);
        } else {
            $data['tasks_working_on'] = DB_BOOL_FALSE;
        }

        $this->apilib->edit('tasks', $task_id, $data);
        echo json_encode(array('status' => 2));
    }
}
