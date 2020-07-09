<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends CI_Controller {
	
	public function __construct(){
		parent:: __construct();
		$this->load->model('Game_model','game_model');
		fetchInputs();
	}
	
	public function groupUpdate($groupId,$message,$type){
		$uid = getUid();
		$post = $this->firebase->post("groupUpdates/$groupId/12",[
			'uid' => $uid,
			'type' => $type,
			'message' => $message,
			'timestamp' => time(),
		]);
		return $post;
	}

	public function join(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(!gameOn()){
				$userId = $userDetails['id'];
				# Validations (Mandatory Inputs)
				inputValidations(['group_id']);
				$groupId = $_POST['group_id'];

				$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
                if(!empty($gameUsersDetails)){
					$groupSessionId = $gameUsersDetails[0]['group_session_id'];
					$joinGroup = $this->game_model->joinGroup($groupId,$userId,$groupSessionId);
					if($joinGroup){
						$gameDetails = $this->auth_model->fetchGameDetails();
						$this->auth_model->updateGroupSessionDetails($userDetails,$gameDetails);
						$this->groupUpdate($groupId,"New player joined","join_group");
						response([],'success','Game session has been created successfuly');
					}
				}
			}
			else{
				response([],'failed','Game session already exist');
			}
		}
		response([],'failed','Something went wrong');
    }

	public function createGroup(){
		$userDetails = apiAuthentication();
		if(!empty($userDetails)){
			if(empty(groupSessionId())){
				$userId = $userDetails['id'];
				# Validations (Mandatory Inputs)
				inputValidations(['group_name','ticket_prize']);
				
				$groupName = $_POST['group_name'];
				$ticketPrice = $_POST['ticket_prize'];
				$startGameSession = $this->game_model->startGameSession($userId,$groupName,$ticketPrice);

				if($startGameSession){
					$gameDetails = $this->auth_model->fetchGameDetails();
					$this->auth_model->updateGroupSessionDetails($userDetails,$gameDetails);
					$this->groupUpdate($gameDetails['id'],"New group created","create_group");
					response([ 'group_id' => $gameDetails['id'] ],'success','Game session has been created successfuly');
				}
				response([],'failed','Something went wrong');
			}
			else{
				response([],'failed','Game session already exist');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function fetchUserDetailsById(){
        $userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(!gameOn()){
				$userId = $userDetails['id'];
				# Validations (Mandatory Inputs)
				inputValidations(['group_id']);
				$groupId = $_POST['group_id'];
				
				$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
                if(!empty($gameUsersDetails)){
                    $gameDetails = [
                        'group_session_id' => $gameUsersDetails[0]['group_session_id'],
                        'status' => $gameUsersDetails[0]['status'],
                        'group_name' => $gameUsersDetails[0]['group_name'],
                        'session_end' => $gameUsersDetails[0]['session_end'],
                        'host_id' => $gameUsersDetails[0]['host_id'],
                        'host_type' => $gameUsersDetails[0]['host_type'],
                        'ticket_price' => $gameUsersDetails[0]['ticket_price']
                    ];

                    $joinedUsers = [];
                    foreach ($gameUsersDetails as $value) {
                        $admin = ($value['host_type'] == $value['user_type'] ) && ($value['host_id'] == $value['user_id'] );
						$gsuId = $value['game_session_user_id'];

						$joinedUsers[$gsuId] = [
                            'user_name' => $value['user_name'],
                            'user_type' => $value['user_type'],
                            'back_out' => $value['back_out'],
                            'admin' => $admin,
                        ];
                    }
                    $output = [
                        'users' => array_values($joinedUsers),
                        'game_details' => $gameDetails,
                    ];
                    response($output,'success');    
                }
			}
			else{
				response([],'failed','Game session already exist');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function fetchLiveDetails(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				$gameDetails = $this->auth_model->fetchGameDetails();
				$userId = $userDetails['id'];
				$groupId = $gameDetails['id'];
				$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
                if(!empty($gameUsersDetails)){
					$ticketPrice = $gameUsersDetails[0]['ticket_price']; 
					$hostId = $gameUsersDetails[0]['host_id']; 
					$hostType = $gameUsersDetails[0]['host_type']; 
					$status = $gameUsersDetails[0]['status']; 
					$groupName = $gameUsersDetails[0]['group_name']; 

					$announcedNumbers = getNumbersArray($gameUsersDetails[0]['announced_numbers']);
					$lastAnnouncedNumbers = !empty($announcedNumbers) ? end($announcedNumbers) : null; 
					$details = $this->game_model->setLiveGameDetails($gameUsersDetails,$userId,$announcedNumbers);
					
					$totalCollectedAmount = ($status == 'booking_open') ? 100 :  ($details['total_ticket_count'] * $ticketPrice);
					foreach ($details['prizes'] as $key => $value) {
						$details['prizes'][$key]['prize_value'] = amount_format($totalCollectedAmount * $value['prize_value'] / 100); 
					}
					
					$output = [
						'join_url' => base_url() . "/Game/joinGroup/" . urlencode(groupIdEncode($groupId)),
						'announced_numbers' => $announcedNumbers,
						'last_announced_number' => $lastAnnouncedNumbers,
						'group_name' => $groupName,
						'game_admin' => ($hostId == $userId && $hostType == loginAs()),
						'game_status' => $status,
						'players' => $details['players'],
						'prizes' => $details['prizes'],
						'winners' => $details['winners'],
						'tickets' => $details['tickets'],
						'all_tickets' => $details['all_tickets'],
						'total_ticket_count' => $details['total_ticket_count'],
						'group_id' => $groupId 
					] ;
					response($output,'success');

				}
			}
			else{
				response([],'failed','Game session expired');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function startGame(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				$gameDetails = $this->auth_model->fetchGameDetails();
				$userId = $userDetails['id'];
				$groupId = $gameDetails['id'];
				$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
                if(!empty($gameUsersDetails)){
					$ticketPrice = $gameUsersDetails[0]['ticket_price']; 
					$hostId = $gameUsersDetails[0]['host_id']; 
					$hostType = $gameUsersDetails[0]['host_type']; 
					$status = $gameUsersDetails[0]['status']; 
					$gameAdmin = ($hostId == $userId && $hostType == loginAs());
					$announcedNumbers = getNumbersArray($gameUsersDetails[0]['announced_numbers']); 
					$lastAnnouncedNumbers = !empty($announcedNumbers) ? end($announcedNumbers) : null; 
					
					if(!$gameAdmin){
						response([],'failed','Only game admin allowed to start the game');
					}
					# Check if game is already started
					if($status != 'booking_open'){
						response([],'failed','Game is already started');		
					}

					$details = $this->game_model->setLiveGameDetails($gameUsersDetails,$userId,$announcedNumbers);
					$players = $details['players'];
					$all_tickets = $details['all_tickets'];
					if(count($players) <=1){
						response([],'failed','Atleast two or more players needed to start the game');		
					}
					if(count($all_tickets) <=1){
						response([],'failed','Atleast two or more players should buy tickets to start the game');		
					}
					$totalTicketsCount = $details['total_ticket_count'];
					$dividendPrizes = $this->ticket->prizeCalculations($totalTicketsCount,$ticketPrice);
					$startGame = $this->game_model->startGame($groupId,$dividendPrizes);
					$output = [] ;
					$this->groupUpdate($groupId,"Game has been started","start_game");
					response($output,'success');
				}
			}
			else{
				response([],'failed','Game session expired');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function announceNextNumber(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				$gameDetails = $this->auth_model->fetchGameDetails();
				$userId = $userDetails['id'];
				$groupId = $gameDetails['id'];
				$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
                if(!empty($gameUsersDetails)){
					$ticketPrice = $gameUsersDetails[0]['ticket_price']; 
					$hostId = $gameUsersDetails[0]['host_id']; 
					$hostType = $gameUsersDetails[0]['host_type']; 
					$status = $gameUsersDetails[0]['status']; 
					$gameAdmin = ($hostId == $userId && $hostType == loginAs());
					$announcedNumbers = getNumbersArray($gameUsersDetails[0]['announced_numbers']); 
					if(!$gameAdmin){
						response([],'failed','Only game admin allowed to announce the number');
					}
					# Check if game is already started
					if($status != 'game_start'){
						response([],'failed','Game is not started yet');		
					}
					$announceNumber = $this->game_model->announceNextNumber($groupId,$announcedNumbers);
					$details = $this->game_model->setLiveGameDetails($gameUsersDetails,$userId,$announcedNumbers);
					$winnings = $details['winnings'];
					if(!empty($winnings)){
						$this->game_model->updateClosedWinnings($groupId,$winnings);
					}
					$output = [
						'announced_number' => $announceNumber 
					] ;
					$this->groupUpdate($groupId,"New number has been announced $announceNumber","announce_number");
					response($output,'success');
				}
			}
			else{
				response([],'failed','Game session expired');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function leave(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				$gameDetails = $this->auth_model->fetchGameDetails();
				$gameStatus = $gameDetails['status'];
				$groupId = $gameDetails['id'];
				$gsuId 		= $gameDetails['game_session_users_id'];
				$userId 	= $userDetails['id'];
				$game_host 	= ($userDetails['id'] == $gameDetails['host_id']); 
				if($game_host && $gameStatus !='game_over'){
					$gameDetails = $this->game_model->replaceGroupAdminAccess($groupId,$gsuId);
				}
				$leaveGroup = $this->game_model->leaveGroup($gsuId,$userId);
				$username = $_SESSION['name'];
				$this->groupUpdate($groupId,"$username has left the game","leave_game");
				response([],'success');
			}
			else{
				response([],'failed','Game session already expired');
			}
		}
		response([],'failed','Something went wrong');
	}

	public function buyTickets(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				inputValidations(['ticket_count']);
				$ticketCount = $_POST['ticket_count'];
				if($ticketCount > 3){
					response([],'failed','More than 3 tickets are not allowed');
				}
				$gameDetails = $this->auth_model->fetchGameDetails();
				$gameStatus  = $gameDetails['status'];
				$gsuId = $gameDetails['game_session_users_id'];
				if($gameStatus =='booking_open'){
					$this->game_model->buyTickets($ticketCount,$gsuId);
					$username = $_SESSION['name'];
					$this->groupUpdate($gameDetails['id'],"$username has bought $ticketCount tickets","buy_ticket");
					response([],'success');
				}
				else{
					response([],'failed','Sorry ticket booking is closed');
				}
			}
			else{
				response([],'failed','Game session already expired');
			}
		}
		response([],'failed','Something Went wrong');
	}

	public function markTicketNumber(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				inputValidations(['ticket_id','ticket_no']);
				$ticketId = $_POST['ticket_id'];
				$number = $_POST['ticket_no'];
				$gameDetails = $this->auth_model->fetchGameDetails();
				$groupId = $gameDetails['id'];
				$gameStatus  = $gameDetails['status'];
				$userId = $userDetails['id'];
				$announcedNumbers  = getNumbersArray($gameDetails['announced_numbers']);
				if(in_array($number , $announcedNumbers)){
					$gsuId = $gameDetails['game_session_users_id'];
					if($gameStatus =='game_start'){
						$result = $this->game_model->markTicketNumber($gsuId,$ticketId,$number);
						if($result !==false){
							$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
							$details = $this->game_model->setLiveGameDetails($gameUsersDetails,$userId,$announcedNumbers);
							$this->groupUpdate($groupId,"New number marked","mark_nuber");
							response([
								'tickets' => $details['tickets']
							],'success');
						}
					}
					else{
						if($gameStatus =='booking_open'){
							response([],'failed','Sorry! Game is not started yet.');
						}
						response([],'failed','Sorry! Game is over.');
					}
				}
				else{
					response([],'failed','Number is not announced yet');
				}
			}
			else{
				response([],'failed','Game session already expired');
			}
		}
		response([],'failed','Something Went wrong');
	}

	public function claimPrize(){
		$userDetails = apiAuthentication();
        if(!empty($userDetails)){
			if(gameOn()){
				inputValidations(['ticket_id','criteria']);
				$ticketId = $_POST['ticket_id'];
				$criteria = $_POST['criteria'];
				$gameDetails = $this->auth_model->fetchGameDetails();
				$gameStatus = $gameDetails['status'];
				$closedWinnnigs = getArray($gameDetails['closed_criterias']);
				$groupId 	= $gameDetails['id'];
				$userId 	= $userDetails['id'];
				$announcedNumbers  = getNumbersArray($gameDetails['announced_numbers']);
				if($gameStatus =='game_start'){
					if(!in_array($criteria,$closedWinnnigs)){
						$gameUsersDetails = $this->game_model->fetchUserDetailsById($groupId);
						$details = $this->game_model->setLiveGameDetails($gameUsersDetails,$userId,$announcedNumbers);
						$tickets = $details['tickets'];
						
						if(isset($tickets[$ticketId])){
							$ticketDetails = $tickets[$ticketId];
							if(!$ticketDetails['closed']){
								$checkClaim = $this->ticket->checkClaim($ticketDetails['ticket_numbers'],$criteria);
								if($checkClaim){
									$ticketPrice = $gameDetails['ticket_price']; 
									$totalCollectedAmount = $details['total_ticket_count'] * $ticketPrice;
									foreach ($details['prizes'] as $key => $value) {
										$details['prizes'][$key]['prize_value'] = amount_format($totalCollectedAmount * $value['prize_value'] / 100); 
									}
									$lastAnnouncedNumber = !empty($announcedNumbers) ? end($announcedNumbers) : null; 
									$totalPrizeValue 	= $details['prizes'][$criteria]['prize_value'];
									$prizeWinners 		= $details['prizes'][$criteria]['winners'];
									$totalWinners 		= count($prizeWinners) + 1;
									$actualPrizeValue 	= amount_format($totalPrizeValue / $totalWinners);

									$this->game_model->addWinner($ticketId,$criteria,$actualPrizeValue,$lastAnnouncedNumber);
									$this->game_model->updatePrizeWinners($prizeWinners,$actualPrizeValue);
									$username = $_SESSION['name'];
									$this->groupUpdate($groupId,"$username has won prize","claim_prize");
									response([],'success','Congrats! You have won this prize.');
								}
								response([],'failed','Ticket is not valid for this criteria');
							}
							response([],'failed','Ticket Already closed');
						}
						response([],'failed','Ticket not found');
					}
					response([],'failed','This winning criteria is already closed');				
				}
				response([],'failed','Something went wrong!');
			}
			response([],'failed','Game session already expired');
		}
	}
}
