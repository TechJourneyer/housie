<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket {
	public function __construct(){
		$this->columnIndexes = range(1, 9);
        $this->filledColumnsCount = 5;
	}

	function getColumnIndexes(){
        shuffle($this->columnIndexes);
        $slice = array_slice($this->columnIndexes, 0, $this->filledColumnsCount);
        return $slice;
    }

    function getTicketNumbers(){
        $row = [];
        $numbers = [];
        for($i=0; $i<3; $i++){
            $filledColumns =  $this->getColumnIndexes();
            for($j=0; $j<9; $j++){
                if(in_array($j+1,$filledColumns)){
                    $rangeStart = (($j*10) == 0) ? 1 : ($j*10);
                    $rangeEnd = ($j*10) + 9;
                    $number =  $this->fetchRandomNumber($rangeStart,$rangeEnd,$numbers);
                    $numbers[] = $number;
                }
                else{
                    $numbers[] = 'x';
                }
            }
        }
        return $numbers;
	}
	
	public function getRandamLines(){
		$quotes = "SELECT * FROM table_name ORDER BY RAND() LIMIT 1;";
		return 'Hey...';
    }
    
    public function fetchRandomNumber($start,$end,$exclude=[]){
        while( in_array( ($n = mt_rand($start,$end)), $exclude ) );
        return $n;
    }

    public function announceNumber($announcedNumbers){
        return $this->fetchRandomNumber(1,89,$announcedNumbers);
    }

    public function arrangeTicketNumbers($numbers,$announcedNumbers,$marked_numbers){
        $rows = [];
        $index= 0;
        for($i=0; $i<3; $i++){
            $rows[$i] = [];
            for($j=0; $j<9; $j++){
                $number = $numbers[$index];
                $announced = in_array($number,$announcedNumbers);
                $marked = in_array($number,$marked_numbers);
                $empty = $number == 'x';
                if($empty ){
                    $class = 'blank_cell';
                }
                else if($marked){
                    $class = 'marked_cell';
                }
                else{
                    $class = 'unmarked_cell';
                }
                $rows[$i][] = [
                    'number' =>  $empty ? '' : $number,
                    'announced' => $announced,
                    'marked' => $marked,
                    'empty' => $empty,
                    'class' => "ticket_cell $class",
                    'title' => (!$empty & !$marked) ? 'Mark This number' : '',
                ];
                $index++;
            }
        }
        return $rows;
    }

    public function checkClaim($ticketNumbers,$criteria){
        switch($criteria){
            case 'first_five' : return $this->checkFirstN($ticketNumbers,5);
            case 'early_seven' : return $this->checkFirstN($ticketNumbers,7);
            case 'full_house' : return $this->checkFullHouse($ticketNumbers);
            case 'top_line' : return $this->checkLine($ticketNumbers,0);
            case 'middle_line' : return $this->checkLine($ticketNumbers,1);
            case 'bottom_line' : return $this->checkLine($ticketNumbers,2);
            case 'line_complete' : return ($this->checkLine($ticketNumbers,0) || $this->checkLine($ticketNumbers,1) || $this->checkLine($ticketNumbers,2));
            default:return false;
        }
    }

    public function checkFirstN($ticketNumbers,$firstN){
        $markedCount = 0;
        foreach ($ticketNumbers as $rowNo => $row) {
            foreach ($row as $colNo => $box) {
                if($box['marked']){
                    $markedCount++;
                }
            }   
        }
        if($markedCount >=$firstN){
            return true;
        }
        return false;
    }

    public function checkFullHouse($ticketNumbers){
        $unmarked = 0;
        foreach ($ticketNumbers as $rowNo => $row) {
            foreach ($row as $colNo => $box) {
                if(!$box['empty'] && !$box['marked']){
                    $unmarked++;
                }
            }   
        }
        if($unmarked ==0){
            return true;
        }
        return false;
    }

    public function checkLine($ticketNumbers,$rowNo){
        $unmarked = 0;
        $ticketLine = $ticketNumbers[$rowNo];
        foreach ($ticketLine as $colNo => $box) {
            if(!$box['empty'] && !$box['marked']){
                $unmarked++;
            }
        }   
        if($unmarked ==0){
            return true;
        }
        return false;
    }
    
    public function prizeCalculations($totalTicketsCount,$ticketPrice){
        $totalTicketPrize = $totalTicketsCount * $ticketPrice;
        $tickiCountWisePrizes = [
            '2' => [
                'full_house' => 100
            ],
            '3' => [
                'full_house' => 70,
                'line_complete' => 30
            ],
            '4' => [
                'full_house' => 50,
                'line_complete' => 30,
                'early_seven' => 20
            ],
            '5' => [
                'full_house' => 50,
                'line_complete' => 20,
                'early_seven' => 15,
                'first_five' => 15
            ], 
        ];

        $defaultPrizePercent = [
            'full_house' => 35,
            'first_five' => 20,
            'top_line' => 15,
            'middle_line' => 15,
            'bottom_line' => 15
        ];

        if($totalTicketsCount >5){
            $prizePercent = $defaultPrizePercent;
        }
        else{
            $prizePercent = $tickiCountWisePrizes[$totalTicketsCount];
        }

        $divindends = $this->dividendList();
        $divindendPrizeList = [];
        foreach($prizePercent as $key => $percent){
            $divindendPrizeList[$key] = [
                'name' => $divindends[$key],
                'prize_value' => amount_format(($totalTicketPrize * $percent) / 100 ),
                'winners' => [],
                'status' => '',
            ];
        }
        return $divindendPrizeList;
    }

    public function dividendList(){
        return [
            'full_house' => 'Full house',
            'line_complete' => 'Line Complete',
            'first_five' => 'First Five',
            'top_line' => 'Top Line',
            'middle_line' => 'Middle Line',
            'bottom_line' => 'Bottom Line',
            'early_seven' => 'Early Seven',
        ];
    }
}
