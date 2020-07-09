<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src='/assets/js/game/liveboard.js?v=1.1'></script>
<link rel="stylesheet" href="/assets/css/game/liveboard.css" >

<div ng-controller='liveboard' class='p-4' ng-cloak>
    <!-- Page Headers -->
    <span class='text-center'>
        <div class="row">
            <div class="col-sm-4">
                <div class='text-center' ng-if="game_status=='booking_open' && game_admin">
                    <div class="input-group input-group-lg ">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa  fa-share-alt" aria-hidden="true"></i></span>
                        </div>
                        <a  title='Share on whatsapp' class="bg-light p-2 text-success" href="whatsapp://send?text=Click below link to join a group {{join_url}}">
                            <i class="fa fa-2x fa-whatsapp" aria-hidden="true"></i>
                        </a>
                        <a  title='Copy to clipboard' class="bg-light p-2" ng-click='copyJoinLink()'>
                            <i class="fa fa-2x fa-clipboard" aria-hidden="true"></i>
                        </a>
                    </div>      
                </div>
            </div>
            <div class="col-sm-4">
                <div class='text-center p-2'>
                    <h4><i class="fa fa-users" aria-hidden="true"></i>
                    <strong>{{group_name}}</strong></h4>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="text-center">
                    <button id='leaveGameBtn' title='Leave game' ng-click='leaveGame()' class='btn btn-sm btn-danger'><i class="fa fa-2x fa-times" aria-hidden="true"></i></button>
                    <div id='leaveGameLoader' style='display:none' class="spinner-border text-danger" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </span>

    <hr>

    <div ng-if='pageLoaded' class="row">
        <!-- LEFT PANEL -->
        <div class="col-xl-3 col-md-12 p-2">
            <!-- Announced Numbers -->
            <div  class=" box-shadow-style bg-light ">
                <div class="box-title text-center p-2">
                    <strong>Announced numbers</strong>
                </div>
                <div class="bg-light table-responsive">
                    <table class="table table-sm table-condensed table-bordered">
                        <tr ng-repeat="x in [].constructor(9) track by $index" ng-init="row=$index">
                            <td ng-repeat="y in [].constructor(10) track by $index" 
                                ng-init="box_no = (row*10)+ ($index + 1) "
                                ng-class="{'text-white number-box': announced_numbers.indexOf(box_no) !== -1 }" 
                                class='text-center'>
                                {{box_no}}
                            </td>
                        </tr>
                    </table>
                </div>    
            </div> 
            <br>
            <!-- Winners -->
            <div ng-if="game_status != 'booking_open'"  class="winners-box box-shadow-style bg-light shadow-sm ">
                <div class="box-title p-2 text-center ">
                    <strong>
                        <i class="fas text-success fa-2x fa-medal"></i><br><label> Winners</label>
                    </strong>  
                </div>
                <div class="winners-list box-body p-2">
                    <div ng-repeat='users in winners' class="media pt-3 border-bottom border-gray">
                        <div class="media-body pb-1 mb-0">
                            <strong class="d-block text-gray-dark">
                                {{users[0].name}}                            
                            </strong>
                            <ul>
                                <li ng-repeat='w in users' class=''>{{w.criteria_name}} (₹ {{w.prize}})<br></li>
                            </ul>                         
                        </div>
                    </div>
                    <p ng-if='objectSize(winners) == 0' class='text-center text-danger'>Empty</p>
                </div>
            </div>
        </div>

        <!-- MIDDLE PANEL -->
        <div class="col-xl-6 col-md-12 p-2">
            <!-- Notice Board / Number Annoucements     -->
            <div class="announcement text-center  p-3 ">
                <div ng-if="game_status=='booking_open'">
                    <strong ng-if="objectSize(tickets)==0" class='text-center '>Please buy tickets!</strong>
                    <div ng-if="objectSize(tickets) > 0">
                        <button ng-if="game_admin" ng-disabled='start_game_loading' ng-click='startGame()' class='btn btn-md btn-success'>Start Game</button>
                        
                        <strong ng-if="!game_admin" class='text-center'>Please wait for admin to start the game.</strong>
                    </div>
                </div>
                <div ng-if="game_status=='game_start'">
                    <span ng-if='last_announced_number!=null'>
                        <strong class='h5'>Current Number </strong>
                        <span  class="p-1 latest-announced-number">
                            &nbsp;{{last_announced_number}}&nbsp;
                        </span>
                    </span>

                    <span ng-if='last_announced_number==null' class="p-2">
                        <span ng-if='game_admin'>Click on next button to anoounce first number </span>
                        <span ng-if='!game_admin'>Numbers has not been accounced yet</span>
                    </span>
                    <br>
                    <span ng-if='game_admin'>
                        <br>
                        <button 
                            title='announce next number'
                            ng-click='announceNextNumber()'
                            class='btn btn-sm btn-primary fas fa-arrow-circle-right fa-2x'>
                            <i class=""></i>
                        </button>    
                    </span>
                </div>
                <div ng-if="game_status=='game_over'">
                    <strong class='text-center'>Game over!</strong>
                </div>
            </div>
            
            <br>
            <!-- Tickets -->
            <div class="tickets-box bg-light box-shadow-style ">
                <div class="box-title p-2 text-center ">
                    <strong>
                        <i class="fas text-secondary fa-2x fa-ticket-alt"></i><br><label> Tickets</label>
                    </strong>  
                </div>
                <div class="box-body p-2">
                    <div class='p-2' ng-if="game_status == 'booking_open' && objectSize(tickets) == 0">
                        <!-- Buy Tickets -->
                        <h5>Buy Tickets</h5>
                        <hr>
                        <div class="row container form-group">
                            <label class='col-sm-3' for="">Quantity</label>
                            <select id='ticket_count' name='ticket_count' class='col-sm-3 form-control-sm' >
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="row  container form-group ">
                            <button id='buyTicketBtn' class=' m-2 btn btn-md btn-primary' ng-click='buyTickets()' >Buy Ticket</button>
                            <span id='buyTicketLoader' style='display:none'>....</span>
                        </div>
                        <br>
                    </div>
                    <div class='border m-3 p-2 ticket-box'  ng-repeat='ticket in tickets track by $index' 
                        ng-init='ticket_no = $index +1 ; ticket_nos = ticket.numbers'>
                        <div class="row pb-2 " >
                            <div class="col-sm-9">
                                <p class='text-center'><strong>Ticket {{ticket_no}}</strong></p>   
                                <div class="ticket p-2 text-center">
                                    <table class='ticket-table table table-sm  table-bordered' >
                                        <tr ng-repeat='ticket_row in ticket.ticket_numbers'>
                                            <td ng-repeat='ticket_box in ticket_row'
                                                class='{{ticket_box.class}}'
                                                ng-disabled="game_status=='booking_open'"
                                                ng-click="markTicketNumber(ticket.ticket_id,ticket_box.number)"
                                                title='{{ticket_box.title}}'
                                                >
                                                {{ticket_box.number}} 
                                                <span ng-if="ticket_box.empty">&nbsp;&nbsp;</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div ng-if="game_status != 'booking_open' && !ticket.closed" class="col-sm-3 ">
                                <p class='text-center'><strong>Claim prize</strong></p>   
                                <div class="claim-list p-2">
                                    <a
                                        ng-repeat='(key, p) in prizes'
                                        ng-click='claimPrize(ticket.ticket_id,key)'
                                        class='claim-btn form-control btn btn-primary m-1'>
                                        {{p.name}}
                                    </a>
                                </div>
                            </div>
                            <div ng-if="game_status != 'booking_open' && ticket.closed" class="col-sm-3 p-3 text-center">
                                <br>
                                <span class='p-1 text-center'>
                                    <i class="fas text-warning fa-2x fa-trophy" aria-hidden="true"></i>
                                    <br>
                                    <p class='text-success'>
                                        <strong>Congrats ! <br>You have won <br>Rs. {{ticket.prize}}</strong>
                                    </p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>

        <!-- RIGHT PANEL -->
        <div class="col-xl-3 col-md-12 p-2">
            <div class="players-box box-shadow-style bg-light   ">
                <div class="box-title p-2 text-center ">
                    <strong>
                        <i class="fas fa-2x text-info fa-users"></i> <br>
                        <strong >Players <span class="badge badge-circle badge-dark">{{objectSize(players)}}</span></strong>
                    </strong>  
                </div>
                <div class="players-list box-body p-2">
                    <div ng-repeat='p in players' class="media pt-3 border-bottom border-gray">
                       
                        <span class='mr-2 ' >
                            <i title='Guest user' ng-if="p.user_type=='guest'"  class="fas fa-user fa-2x text-info" ></i>
                            <i title='Registered user' ng-if="p.user_type=='user'"  class="fas fa-user fa-2x text-primary" ></i>
                        </span>
                        
                        <p class="media-body pb-3 mb-0">
                            <strong class="d-block text-gray-dark">
                                <span ng-if="!p.online" style="color: Tomato" title='offline'>
                                    <i class="far fa-sm  fa-dot-circle"></i>
                                </span>
                                <span ng-if="p.online" style="color:Dodgerblue" title='online'>
                                    <i class="far fa-sm  fa-dot-circle"></i>
                                </span>
                                {{p.name}}  
                                <span ng-if='p.host' class='text-muted'>(admin)</span>
                            </strong>
                            <span ng-if='p.prizes.length > 0' class='text-success'>
                                Won {{p.prizes.length}} prizes
                            </span>
                            <span ng-if='p.back_out' class='text-muted'>
                                Left the game
                            </span>
                            <span title='Tickets' ng-class="(objectSize(all_tickets[p.gsu_id]) == 0 )? 'bg-danger' : 'bg-secondary'"
                                class="p-1 text-white rounded border float-right">
                                <i class="fas fa-1x fa-ticket-alt"></i>
                                <span> {{objectSize(all_tickets[p.gsu_id])}}</span>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <br>
            <div class="prize-box box-shadow-style bg-light shadow-sm ">
                <div class="box-title bg-light  p-2 text-center ">
                    <strong><i class="fas text-warning fa-2x fa-trophy"></i> <br>
                    <label> Prizes</label></strong>  
                </div>
                <div class="prize-list box-body p-2">
                    <div ng-repeat='p in prizes' class="media pt-3 border-bottom border-gray">
                        <span style ='color:#f5cd57 !important' class="material-icons md-36 mr-2  " width="32" height="32" >emoji_events</span>
                        <p class="media-body pb-3 mb-0">
                            <strong class="d-block text-gray-dark">
                                {{p.name}}
                                <span ng-if="p.status=='open'" class='text-info'>(Open)</span>
                                <span ng-if="p.status=='closed'" class='text-success'>(closed)</span>
                                <span ng-if="p.status=='claimed'" class='text-danger'>(claimed)</span>
                            </strong>
                            {{ game_status!='booking_open' ? '₹' : ''}}
                            {{p.prize_value}} 
                            <span class='text-muted'>
                                {{ game_status=='booking_open' ? '% of total ticket collection amount' : ''}}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php  $this->load->view('game/components/chatwindow'); ?>
    <div ng-if='!pageLoaded' id="loader"></div>
</div>