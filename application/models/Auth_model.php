<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {
    public function __construct(){
        parent::__construct();
    }

    public function createUser($uid,$name,$emailId,$phoneNumber,$photoUrl){
        $this->db->insert('users' , [
            'uid' => $uid,
            'name' => $name,
            'email_id' => $emailId,
            'phone_number' => $phoneNumber,
            'photo_url' => $photoUrl,
        ]);
        return $this->db->insert_id();
    }

    public function updateUser($uid , $data){
        $this->db->where('uid',$uid);
        return $this->db->update('users',$data);
    }

    public function fetchUserDetails($where , $tableName = 'users'){
        $this->db->select("u.*");
        $this->db->from("$tableName AS u");
        foreach ($where as $column => $value) {
            $this->db->where($column , $value);
        }
        $result =  $this->db->get()->row_array();
        return $result;
    }

    public function fetchGameDetails(){
        $usertable = userTable();
        $userType = loginAs();
        $this->db->select('gs.id, u.name,u.group_session_id,gs.status, gs.group_name , gs.host_id , gs.dividends,
            gs.ticket_price,gs.closed_criterias, gsu.id as game_session_users_id, gs.announced_numbers');
        $this->db->from("$usertable u");
        $this->db->join('game_session gs' , 'u.group_session_id = gs.group_session_id','inner');
        $this->db->join('game_session_users gsu' , 
            "gsu.game_session_id = gs.id and u.id = gsu.user_id and gsu.user_type = '$userType'",'inner');
        $this->db->where('u.session_id' ,sessionId());
        $this->db->where('gs.session_end' ,0);
        $this->db->where('gsu.back_out' ,0);
        return $this->db->get()->row_array();
    }

    public function setSessionDetails($userDetails, $sessionId, $loginAs = 'user'){
        $userdata = [];
        $userdata['name']       = $userDetails['name'];
        $userdata['session_id'] = $sessionId;
        $userdata['is_login']   = true;
        $userdata['login_as']   = $loginAs;
        $userdata['uid']        = $userDetails['uid'];
        $userdata['photo_url']  = $userDetails['photo_url'];

        if($loginAs == 'user'){
            $userdata['email_id'] = $userDetails['email_id'];
            $userdata['username'] = $userDetails['username'];
        }

        # User session etails
        $this->session->set_userdata($userdata);

        # Group Session Details
        $gameDetails = $this->fetchGameDetails();
        $this->updateGroupSessionDetails($userDetails,$gameDetails);
    }

    public function updateGroupSessionDetails($userDetails,$gameDetails){
        $userdata = [];
        if(!empty($gameDetails)){
            $userdata['game_on'] = true;
            $userdata['group_session_id'] = $gameDetails['group_session_id'];
            $userdata['group_name'] = $gameDetails['group_name'];
            $userdata['game_host'] = ($userDetails['id'] == $gameDetails['host_id']); 
            $userdata['game_status'] = $gameDetails['status'];
            # update users
            $this->session->set_userdata($userdata);
            
            # Update Game Session
            $this->db->set('last_active_at',date('Y-m-d H:i:s'))
            ->where('group_session_id' , $gameDetails['group_session_id'])
            ->update('game_session');
        }
        else{
            $userdata['game_on'] = false;
            $userdata['group_session_id'] = null;
            $userdata['group_name'] = null;
            $userdata['game_host'] = false; 
            $userdata['game_status'] = null;
            
            # update users
            $this->session->set_userdata($userdata);
        }
    }

    public function createGuestUser($name,$sessionId){
        $this->db->insert('guests' , [
            'name' => $name,
            'session_id' => $sessionId,
        ]);
        return $this->db->insert_id();
    }
    
    public function updateSessionId($sessionId,$userId,$loginAs='user'){
        if($loginAs == 'user'){
            return $this->db->set('session_id',$sessionId)
            ->where('id' , $userId)
            ->update('users');
        }
        else{
            return $this->db->set('session_id',$sessionId)
            ->where('id' , $userId)
            ->update('guests');
        }
    }

    public function updateOnlineStatus($userId){
        if(loginAs() == 'user'){
            return $this->db->set('online',1)
            ->set('last_online',date('Y-m-d H:i:s'))
            ->where('id' , $userId)
            ->update('users');
        }
        else{
            return $this->db->set('online',1)
            ->set('last_online',date('Y-m-d H:i:s'))
            ->where('id' , $userId)
            ->update('guests');
        }
    }

    public function logout(){
        $this->session->sess_destroy();
    }
}
?>