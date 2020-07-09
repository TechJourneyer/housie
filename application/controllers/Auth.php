<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct(){
		parent:: __construct();
		$this->load->library('form_validation');
	}

	public function login(){
		if(!checkLogin(false)){
			$this->load->view('templates/layouts/auth_view',[
				"data"  => [],
				'view_page' => 'user/login'
			]);
		}
		else{
			$this->auth_model->logout();
			redirect(base_url()); 
		}
	}
	
	public function logout(){
		$this->auth_model->logout();
		flashSuccess('You have been successfully logout');
		$this->load->view('templates/layouts/auth_view',[
			"data"  => [],
			'view_page' => 'user/logout'
		]);
	}

}
