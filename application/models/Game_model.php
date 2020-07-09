<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game_model extends CI_Model {
    public function __construct(){
        parent::__construct();
    }

    public function startGameSession($userId,$groupName,$ticketPrice){
        $groupSessionId = encrypt("group_".date('YmdHis').rand());
        $loginAs = loginAs();
        $createGameSession = $this->db->insert('game_session' , [
            'group_session_id' => $groupSessionId,
            'group_name' => $groupName,
            'ticket_price' => $ticketPrice,
            'host_type' => $loginAs,
            'host_id' => $userId,
            'last_active_at' => date('Y-m-d H:i:s')
        ]);
        $gameSessionId = $this->db->insert_id();
        $joinGroup =  $this->joinGroup($gameSessionId,$userId,$groupSessionId);
        return $joinGroup;
    }

    public function joinGroup($groupId,$userId,$groupSessionId){
        if($groupId != false && !empty($groupId)){
            $addUserToGameSession = $this->db->insert('game_session_users' , [
                'game_session_id' => $groupId,
                'user_id' => $userId,
                'user_type' => loginAs()
            ]);
            if($addUserToGameSession !== false){
                $this->db->update( userTable() , 
                    ['group_session_id' => $groupSessionId ],
                    ['id' => $userId ]
                );
                return true;
            }
        }
        return false;
    }

    public function fetchGameDetailsById($gameId,$userId){
        $userType = loginAs();
        $this->db->select('gs.group_session_id,gs.status, gs.group_name , gs.session_end , gs.host_id ,  gs.dividends,
            gs.closed_criterias,gs.host_type , gsu.user_id , gsu.user_type,gsu.back_out');
        $this->db->from("game_session gs");
        $this->db->join('game_session_users gsu' , 
            "gsu.game_session_id = gs.id AND  gsu.user_id = '$userId' AND gsu.user_type = '$userType'",
            'left');
        $this->db->where('gs.id' ,$gameId);
        return $this->db->get()->row_array();
    }

    public function fetchUserDetailsById($gameId){
        $this->db->select("gs.group_session_id, gs.status, gs.group_name, gs.session_end, gs.host_id, gs.dividends,
            gs.host_type,gs.ticket_price,gs.announced_numbers");
        $this->db->select("CASE WHEN gsu.user_type = 'user' THEN u.name ELSE g.name END AS user_name");
        $this->db->select("CASE WHEN gsu.user_type = 'user' THEN u.online ELSE g.online END AS online");
        $this->db->select("gsu.id as game_session_user_id , gsu.user_id , gsu.user_type,gsu.back_out");
        $this->db->select("t.id as ticket_id , t.ticket_closed , t.ticket_numbers , t.marked_numbers");
        $this->db->select("w.id as prize_id ,w.criteria , w.announced_number as claim_after_number , w.prize , w.ticket_claim_status");
        $this->db->from("game_session gs");
        $this->db->join('game_session_users gsu' , "gsu.game_session_id = gs.id ",'inner');
        $this->db->join('tickets t' , "gsu.id = t.game_session_users_id ",'left');
        $this->db->join('winners w' , "w.ticket_id = t.id ",'left');
        $this->db->join('users u' , "u.id = gsu.user_id ", 'left');
        $this->db->join('guests g' , "g.id = gsu.user_id ", 'left');
        $this->db->where('gs.id' ,$gameId);
        return $this->db->get()->result_array();
    }

    public function setLiveGameDetails($gameUsersDetails,$currentUserId,$announcedNumbers){
        $winners = [];
        $players = [];
        $tickets = [];
        $allTickets = [];
        $winnings = [];
        $totalTickets = 0;

        $prizes = json_decode($gameUsersDetails[0]['dividends'],true);
        foreach ($gameUsersDetails as $key => $value) {
            $gsuId = $value['game_session_user_id'];
            $userId = $value['user_id'];
            $username = $value['user_name'];
            $userType = $value['user_type'];
            $hostId = $value['host_id'];
            $hostType = $value['host_type'];
            $ticketId = $value['ticket_id'];
            $criteria = $value['criteria'];
            $online = ($value['online'] == 1);
            $backOut = ($value['back_out'] == 1);
            $host = ($hostId == $userId) && ($userType == $hostType);
            
            # Players
            $players[$gsuId] = [
                "gsu_id" => $gsuId,
                "name" => $username,
                "user_type" => $userType,
                "userId" => $userId,
                "host" => $host,
                "online" => $online,
                "back_out" => $backOut,
                "prizes" =>  []
            ];

            # Tickets
            if(!empty($ticketId)){
                #MyTickets
                if($currentUserId == $value['user_id'] && $userType == loginAs()){
                    if(!isset($tickets[$ticketId])){
                        $numbers = getArray($value['ticket_numbers']);
                        $markedNumbers = getArray($value['marked_numbers']);
                        $tickets[$ticketId] = [
                            'numbers' => $numbers,
                            'ticket_numbers' => $this->ticket->arrangeTicketNumbers($numbers,$announcedNumbers,$markedNumbers),
                            'marked_numbers' => $markedNumbers,
                            'ticket_id' => $ticketId,
                            'closed' => ($value['ticket_closed'] == 1),
                            'criteria' => $value['criteria'],
                            'prize' => $value['prize']
                        ];
                    }
                }

                # All Tickets
                if(!isset($allTickets[$gsuId][$ticketId])){
                    if(!isset($allTickets[$gsuId])){
                        $allTickets[$gsuId] = [];
                    }
                    $numbers = getArray($value['ticket_numbers']);
                    $markedNumbers = getArray($value['marked_numbers']);
                    $allTickets[$gsuId][$ticketId] = [
                        'numbers' => $numbers,
                        'marked_numbers' => $markedNumbers,
                        'ticket_id' => $ticketId,
                        'closed' => ($value['ticket_closed'] == 1),
                        'criteria' => $value['criteria'],
                        'prize' => $value['prize']
                    ];
                    $totalTickets++;
                }
            }

            # Winners & Prizes
            if(!empty($ticketId) && !empty($value['prize_id'])){
                if(!isset($winners[$userId][$ticketId])){
                    if(!isset($winners[$userId])){
                        $winners[$userId] = [];
                    }
                    $winners[$userId][$ticketId] = [
                        'prize_id' => $value['prize_id'],
                        'criteria' => $criteria,
                        'criteria_name' => $prizes[$criteria]['name'],
                        'closed' => $value['closed'],
                        'claim_after_number' => $value['claim_after_number'],
                        'prize' => $value['prize'],
                        'name' => $username,
                        'ticket_id' => $value['ticket_id']
                    ];
                    $winnings[$criteria] = $criteria;
                    $prizes[$criteria]['winners'][] = $winners[$userId][$ticketId];
                }
            }
        }

        return [
            'winners' => $winners,
            'players' => $players,
            'prizes' => $prizes,
            'tickets' => $tickets, 
            'all_tickets' => $allTickets,
            'total_ticket_count' => $totalTickets,
            'winnings' => $winnings
        ];
    }

    public function leaveGroup($gsuId,$userId){
        # update game_session_users
        $this->db->set('back_out',1);
        $this->db->where('id',$gsuId);
        $update =   $this->db->update('game_session_users');

        $userdata = [];
        $userdata['game_on'] = false;
        $userdata['group_session_id'] = null;
        $userdata['group_name'] = null;
        $userdata['game_host'] = false; 
        $userdata['game_status'] = null;

        # update session
        $this->session->set_userdata($userdata);

        # update users
        $tableName = loginAs() == 'user' ? 'users' : 'guests';
        $this->db->set('last_online',date('Y-m-d H:i:s'));
        $this->db->set('group_session_id',null);
        $this->db->where('id',$userId);
        $update =   $this->db->update( $tableName );
    }

    public function buyTickets($ticketCount,$gsuId){
        for($i = 1; $i <= $ticketCount; $i++){
            $insert = $this->db->insert('tickets',[
                'game_session_users_id' => $gsuId,
                'ticket_numbers' => implode("," , $this->ticket->getTicketNumbers())
            ]);
        }
    }

    public function startGame($groupId,$dividendPrizes){
        $this->db->set('last_active_at',date('Y-m-d H:i:s'));
        $this->db->set('status','game_start');
        $this->db->set('dividends',json_encode($dividendPrizes));
        $this->db->where('id',$groupId);
        $update =   $this->db->update( 'game_session' );

        # update session
        $userdata = [];
        $userdata['game_status'] = 'game_start';
        $this->session->set_userdata($userdata);
    }

    public function gameOver($groupId){
        $this->db->set('last_active_at',date('Y-m-d H:i:s'));
        $this->db->set('status','game_over');
        $this->db->where('id',$groupId);
        $update =   $this->db->update( 'game_session' );

        # update session
        $userdata = [];
        $userdata['game_status'] = 'game_over';
        $this->session->set_userdata($userdata);
    }

    public function announceNextNumber($groupId,$announcedNumbers){
        $number = $this->ticket->announceNumber($announcedNumbers);
        $announcedNumbers[] = $number;
        $numbers = implode(",",$announcedNumbers);
        $this->db->set('last_active_at',date('Y-m-d H:i:s'));
        $this->db->set('announced_numbers',$numbers);
        $this->db->where('id',$groupId);
        $update = $this->db->update( 'game_session' );
        return $number;
    }

    public function fetchAllTickets($groupId){
        $userType = loginAs();
        $this->db->select('t.game_session_users_id,t.ticket_closed,t.ticket_numbers,t.marked_numbers');
        $this->db->select('w.ticket_id,w.criteria,w.announced_number,w.prize,w.claimed_at,w.ticket_claim_status');
        $this->db->select('gsu.user_type,gsu.user_id');
        $this->db->select("CASE WHEN gsu.user_type = 'user' THEN u.name ELSE g.name END AS user_name");
        $this->db->from("tickets t");
        $this->db->join('winners w' , "w.ticket_id = t.id",'left');
        $this->db->join('game_session_users gsu' , "gsu.id = t.game_session_users_id",'inner');
        $this->db->join('users u' , "u.id = gsu.user_id ", 'left');
        $this->db->join('guests g' , "g.id = gsu.user_id ", 'left');
        $this->db->where('gsu.game_session_id' ,$groupId);
        $this->db->group_by('t.id' ,$groupId);
        return $this->db->get()->result_array();
    }

    public function markTicketNumber($gsuId,$ticketId,$number){
        $this->db->where('id',$ticketId);
        $this->db->where('game_session_users_id',$gsuId);
        $details = $this->db->get('tickets')->row_array();
        if(!empty($details)){
            $ticketNumbers = getArray($details['ticket_numbers']);
            if(in_array($number,$ticketNumbers )){
                $markedNumbers = getNumbersArray($details['marked_numbers']);
                $markedNumbers[] = $number;
                $this->db->set('marked_numbers',getString($markedNumbers));
                $this->db->where('id',$ticketId);
                $this->db->where('game_session_users_id',$gsuId);
                return $this->db->update('tickets');
            }

        }
        return false;
    }

    public function replaceGroupAdminAccess($groupId,$gsuId){
        $this->db->select('user_id,user_type');
        $this->db->where('id !=',$gsuId);
        $otherPlayerDetails = $this->db->get('game_session_users')->row_array();
        if(!empty($otherPlayerDetails)){
            $userId = $otherPlayerDetails['user_id'];
            $userType = $otherPlayerDetails['user_type'];
            $this->db->set('host_type',$userType);
            $this->db->set('host_id',$userId);
            $this->db->where('id',$groupId);
            return $update = $this->db->update('game_session');
        }
        return false;
    }

    public function updateClosedWinnings($groupId,$winnings){
        $this->db->where('id',$groupId);
        $this->db->set('closed_criterias',getString($winnings));
        $updateGameSession =  $this->db->update('game_session');

        $sql = "UPDATE winners w
            INNER JOIN  tickets t ON t.id = w.ticket_id
            INNER JOIN  game_session_users gsu ON gsu.id = t.game_session_users_id
            INNER JOIN  game_session gs ON gs.id = gsu.game_session_id
            SET w.ticket_claim_status = 2
            WHERE gs.id = '$groupId' AND  w.ticket_claim_status = 1";
        $updateWinners = $this->db->query($sql);
    }

    public function addWinner($ticketId,$criteria,$actualPrizeValue,$announced_number){
        # Update Winner 
        $this->db->insert('winners',[
            'ticket_id' => $ticketId,
            'criteria' => $criteria,
            'announced_number' => $announced_number,
            'prize' => $actualPrizeValue
        ]);

        # Update ticket status
        $this->db->set('ticket_closed',1);
        $this->db->where('id',$ticketId);
        $this->db->update('tickets');
    }

    public function updatePrizeWinners($prizeWinners,$actualPrizeValue){
        $ticketIds = [];
        foreach ($prizeWinners as $value) {
            $ticketIds[] = $value['ticket_id'];
        }
        if(!empty($ticketIds)){
            # Update ticket status
            $this->db->set('prize',$actualPrizeValue);
            $this->db->where_in('ticket_id',$ticketIds);
            $this->db->update('winners');
        }
        return true;
    }
}
?>