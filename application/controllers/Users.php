<?php

class Users extends MY_Controller {
    /*
     * the user signs up and is sent an email to confirm registration
     */

    public function register() {

        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('last_name', 'First Name', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[3]|matches[password]');

        if ($this->form_validation->run() == FALSE) {
            $data['main_view'] = 'users/register_view';
            $this->load->view('layouts/main', $data);
        } else {
            /*
             * generate a random temporary key
             */
            $tempkey = $this->generate_random_string(32);
            /*
             * send an email to the user
             * this process could be timed where they have to respond
             * within a given timeframe for the temporary key to remain valid
             */
            $this->load->library('email', array('mailtype' => 'html'));

            $this->load->model('user_model');

            $this->email->from('admin@test.com', "Admin");
            $this->email->to($this->input->post('email'));
            $this->email->subject("Confirm your account");

            // reference to the register_user function is on line 72
            $message = '<p>Thank you for signing up!</p>';
            $message .= "<p><a href='" . base_url() . "users/register_user/$tempkey'>Click here to confirm your account.</a></p>";

            $this->email->message($message);

            if ($this->user_model->add_temp_user($tempkey)) {
                $this->send_email_to_user();
            }
        }
    }

    /*
     * this function is called in the register function
     */

    function send_email_to_user() {
        if ($this->email->send()) {
            echo 'email sent';
        } else {
            echo 'could not send the email';
        }
    }

    /*
     * this function is called in the register function
     * it is used to create a temporary key to be stored in the temp_users database
     */

    function generate_random_string($length) {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $random_string;
    }

    /*
     * this function is called when the user clicks on the link 
     * from within their email to confirm their registration
     */

    function register_user($tempkey) {
        $this->load->model('user_model');

        if ($this->user_model->is_key_valid($tempkey)) {
            $newuser = $this->user_model->process_add_user($tempkey);
            if ($newuser) {
                $this->session->set_flashdata('user_registered', 'You are now registered. Please log in.');
                $this->login();
            } else {
                echo 'failed to add user';
            }
        }
    }

    public function login() {

        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[3]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[3]|matches[password]');

        if ($this->form_validation->run() == FALSE) {
            $data = array(
                'errors' => validation_errors()
            );
            $this->session->set_flashdata($data);
            redirect('home');
        } else {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $user_id = $this->user_model->login_user($username, $password);

            if ($user_id) {
                $user_data = array(
                    'user_id' => $user_id,
                    'username' => $username,
                    'logged_in' => true
                );

                $this->session->set_userdata($user_data);
                $this->session->set_flashdata('login_success', 'You are now logged in');

                redirect('home/index');
            } else {
                $this->session->set_flashdata('login_failed', 'Sorry You are not logged in');
                redirect('home/index');
            }
        }
    }

    public function logout() {

        $this->session->sess_destroy();
        redirect('home/index');
    }

}
