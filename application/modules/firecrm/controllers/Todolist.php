<?php
class Todolist extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create()
    {
        $data = $this->input->post();

        $todolist_text = $data['todolist_text'];
        $todolist_user = $data['todolist_user'];
        $todolist_customer_id = $data['todolist_customer_id'] ?? null;
        $todolist_project_id = $data['todolist_project_id'] ?? null;

        try {
            $response = $this->apilib->create('todolist', [
                'todolist_text' => $todolist_text,
                'todolist_user' => $todolist_user,
                'todolist_customer_id' => $todolist_customer_id,
                'todolist_project_id' => $todolist_project_id
            ]);

            echo json_encode(['status' => 1, 'data' => $response]);
        } catch (Exception $e) {
            $error = json_encode([
                'status' => 0,
                'error' => 'An error has occurred'
            ]);
            die($error);
        }
    }

    public function edit($todo_id)
    {
        $data = $this->input->post();

        try {
            $response = $this->apilib->edit('todolist', $todo_id, [
                'todolist_deleted' => $data['todolist_deleted']
            ]);

            echo json_encode(['status' => 1, 'data' => $response]);
        } catch (Exception $e) {
            $error = json_encode([
                'status' => 0,
                'error' => 'An error has occurred'
            ]);
            die($error);
        }
    }

    public function delete($todo_id)
    {
        try {
            $response = $this->apilib->delete('todolist', $todo_id);

            echo json_encode(['status' => 1, 'data' => $response]);
        } catch (Exception $e) {
            $error = json_encode([
                'status' => 0,
                'error' => 'An error has occurred'
            ]);
            die($error);
        }
    }
}
