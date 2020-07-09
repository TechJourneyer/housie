<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
	
	public function __construct(){
		parent:: __construct();
        $this->load->model('Game_model','game_model');
        fetchInputs();
	}
    
	public function signIn(){
        if(sessionExist()){
            $this->session->sess_destroy();
        }
        inputValidations(['id_token']);
        $idToken = $_POST['id_token'];
        $details = $this->firebase->verifyToken($idToken);
        if(!empty($details)){
            $status = $details['status'];
            $message = $details['message'];
            $output = $details['output'];
			if($status == 'success'){
                $uid = $output['uid'];
                $email = $output['email'];
                $displayName = $output['displayName'];
                $photoUrl = $output['photoUrl'];
                $phoneNumber = $output['phoneNumber'];
                $emailVerified = $output['emailVerified'];
                
                $userDetails = $this->auth_model->fetchUserDetails(['u.uid' => $uid]);
                $userExist = !empty($userDetails);
                if($userExist){ 
                    $userId = $userDetails['id']; 
                    $update = $this->auth_model->updateUser($uid,[
                        'name' => $displayName,
                        'phone_number' => $phoneNumber,
                        'photo_url' => $photoUrl,
                    ]);
                }
                else{
                    $userId = $this->auth_model->createUser($uid,$displayName,$email,$phoneNumber,$photoUrl);
                    $userDetails = $this->auth_model->fetchUserDetails(['u.id'=> $userId]);
                }
                # Set sign in details
                $sessionId = encrypt("user_".$displayName.date('YmdHis'));
               
                $this->auth_model->updateSessionId($idToken,$userId);
                $this->auth_model->setSessionDetails($userDetails,$idToken);
                response([],'success');
			}
			else{
				response([],'failed',"Failed to fetch details from provided token : $message");
            }
        }
		response([],'failed','Something went wrong');
    }

    public function signOut(){
        $this->auth_model->logout();
        response([],'success');
    }
}
