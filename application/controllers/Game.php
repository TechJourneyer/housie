<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends CI_Controller {
	
	public function __construct(){
		parent:: __construct();
		$this->load->model('Game_model','game_model');
	}

	public function index(){
		$userDetails = checkLogin();
		if(gameOn()){
			redirect('/Game/play');
		}
		else{
			redirect('/Game/createGroup');
		}
	}

	public function createGroup(){
		$userDetails = checkLogin();
		if(gameOn()){
			redirect('/Game/play');
		}
		$this->load->view('templates/layouts/site_view',[
			"data"  => [
				'user' => $userDetails
			],
			'view_page' => 'game/create_group'
		]);
	}

	public function joinGroup($groupIdEncoded){
		$userDetails 		= checkLogin("/Game/joinGroup/$groupIdEncoded");
		delete_cookie('join_redirect');
		$groupId 			= groupIdDecode(urldecode($groupIdEncoded));
		$leaveOrPlayLink 	= "<a href='/Game/play'>Click here to Leave or play the game</a>";
		$playLink 			= "<a href='/Game/play'>Click here to play</a>";
		$goToHomePage 		= "<a href='/'>Click here to go to homepage</a>";
		$showJoinPage 		= false;

		if(gameOn()){
			$gameDetails = $this->auth_model->fetchGameDetails();
			if(!empty($gameDetails)){
				if($gameDetails['id'] == $groupId){
					$note = showNote('Error' , "You have already entered in this game. $playLink" , 'alert-danger',false);
				}	
				else{
					$note = showNote('Error' , "You are already in middle of game. $leaveOrPlayLink" , 'alert-danger',false);
				}
			}   
			else{
				$note = showNote('Error' , "Something Went Wrong!" , 'alert-danger',false);
			}
		}
		else{
			$gameDetails = $this->game_model->fetchGameDetailsById($groupId,$userDetails['id']);
			if(!empty($gameDetails)){
				$session_end = ($gameDetails['session_end'] == 1);
				$game_status = $gameDetails['status'];
				if($session_end){
					$note = showNote('Error' , "This game session in end.$goToHomePage" , 'alert-danger',false);
				}
				else if($game_status == 'game_over'){
					$note = showNote('Error' , "This game is over.$goToHomePage" , 'alert-danger',false);
				}
				else if($game_status == 'game_start'){
					$note = showNote('Error' , "Ticket booking is closed for this game.$goToHomePage" , 'alert-danger',false);
				}
				else{
					$showJoinPage = true;
					$note = showNote('' , "Ticket booking is open. Please join the group and buy tickets" , 'alert-success',false);
				}
			}
			else{
				$note = showNote('Error' , "Something Went Wrong!" , 'alert-danger',false);
			}
		}
		
		$this->load->view('templates/layouts/site_view',[
			"data"  => [
				'user' => $userDetails,
				'note' => $note,
				'show_join_page' => $showJoinPage,
				'group_id' => $groupId
			],
			'view_page' => 'game/join_group'
		]);
	}

	public function play(){
		$userDetails = checkLogin();
		if(gameOn()){
			$this->load->view('templates/layouts/site_view',[
				"data"  => [
					'user' => $userDetails
				],
				'view_page' => 'game/liveboard'
			]);
		}
		else{
			redirect('/');
		}
	}
}
