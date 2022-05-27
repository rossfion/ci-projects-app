<?php

class User_model extends CI_Model {
    
    /*
     * The following was part of the original code as provided by Edwin Diaz
     */

//    public function create_user() {
//
//        $options = ['cost' => 12];
//
//        $encrypted_pass = password_hash($this->input->post('password'), PASSWORD_BCRYPT, $options);
//
//        $data = array(
//            'first_name' => $this->input->post('first_name'),
//            'last_name' => $this->input->post('last_name'),
//            'email' => $this->input->post('email'),
//            'username' => $this->input->post('username'),
//            'password' => $encrypted_pass
//        );
//
//        $insert_data = $this->db->insert('users', $data);
//        return $insert_data;
//    }

    public function login_user($username, $password) {

        $this->db->where('username', $username);
        $result = $this->db->get('users');

        $db_password = $result->row(2)->password;

        if (password_verify($password, $db_password)) {
            return $result->row(0)->id;
        } else {
            return false;
        }
    }

    /*
     * this function relates to the register function in the Users controller
     * add them to the temp_users db
     */
    
    public function add_temp_user($tempkey) {
        $options = ['cost' => 12];

        //$hashed_pass = password_hash($this->input->post('password'), PASSWORD_BCRYPT, $options);

        $data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
            'username' => $this->input->post('username'),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT, $options),
            'temp_key' => $tempkey
        );
        $query = $this->db->insert('temp_users', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function is_key_valid($tempkey) {
        $this->db->where('temp_key', $tempkey);
        $query = $this->db->get('temp_users');

        if ($query->num_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * this function completes the registration process once the temporary key 
     * is validated
     * the user's details are transferred from the temp_users db to the 
     * users db 
     */
    public function process_add_user($tempkey) {
        $this->db->where('temp_key', $tempkey);
        $temp_user = $this->db->get('temp_users');

        if ($temp_user) {
            $row = $temp_user->row();

            $data = array(
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'email' => $row->email,
                'username' => $row->username,
                'password' => $row->password,
                'register_date' => $row->register_date
            );
            $did_add_user = $this->db->insert('users', $data);
        }

        if ($did_add_user) {
            $this->db->where('temp_key', $tempkey);
            $this->db->delete('temp_users');
            return $data['username'];
        } else {
            return false;
        }
    }

}
