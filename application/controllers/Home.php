<?php

class Home extends MY_Controller {

    public function index() {
        /*
         * if the user is logged in, get all tasks and projects specific to the user from the database
         * otherwise, show the welcome screen with the login form
         */

        if ($this->session->userdata('logged_in')) {

            $user_id = $this->session->userdata('user_id');
            $data['tasks'] = $this->task_model->get_all_tasks($user_id);
            $data['projects'] = $this->project_model->get_all_projects($user_id);
        }

        $data['main_view'] = "home_view";
        $this->load->view('layouts/main', $data);
    }

}
