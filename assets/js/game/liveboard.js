myapp.controller('liveboard', ['$http', '$scope', '$timeout', '$window', '$location', function ($http, $scope, $timeout, $window, $location) {
    $scope.join_url = null;
    $scope.pageLoaded = false;
    $scope.announced_numbers = [];
    $scope.last_announced_number = null;
    $scope.game_admin = false;
    $scope.game_status = 'booking_open';
    $scope.start_game_loading = false;
    $scope.announce_number_loading = false;
    $scope.all_tickets = [];
    $scope.total_ticket_count = 0;
    $scope.markNumberLoading = false;
    $scope.chatOpen = false;
    $scope.group_chats_seen_msgs = 0;
    $scope.group_chats_msgs = 0;
    setInterval(function() {
        $scope.$apply() ;
    }, 500);
    $scope.dataloadCount = 1;
    $scope.user = [];
    $scope.group_id = null;

    $scope.players = [];
    $scope.prizes = [];
    $scope.winners = [];
    $scope.tickets = [];

    firebase.auth().onAuthStateChanged( function (user){
        $scope.user = user ;
    });

    dataLoad();
    function dataLoad(){
        $scope.loading = true;
        let postdata = [];
        $http.post('/Api/Game/fetchLiveDetails', postdata).then(function successCallback(response) {
            let data = response.data;
            console.log(data);
            if(data.status == 'success'){
                let resultdata = data.result;
                $scope.loading = false;
                $scope.group_name = resultdata.group_name;
                $scope.join_url = resultdata.join_url;
                $scope.announced_numbers = resultdata.announced_numbers;
                $scope.last_announced_number = resultdata.last_announced_number;
                $scope.game_admin = resultdata.game_admin;
                $scope.game_status = resultdata.game_status;
                $scope.players = resultdata.players;
                $scope.prizes = resultdata.prizes;
                $scope.winners = resultdata.winners;
                $scope.tickets = resultdata.tickets;
                $scope.all_tickets = resultdata.all_tickets;
                $scope.total_ticket_count = resultdata.total_ticket_count;
                $scope.group_id = resultdata.group_id;
                $scope.pageLoaded = true;
                
                // Load read functions at first
                if($scope.dataloadCount == 1){
                    readUpdates();
                    readGroupChat();
                }
                $scope.dataloadCount++;
            }
            else{
                return false;
            }
        }, function errorCallback(response) {
            console.log(response);
            alert('something went wrong!');
        });
    }

    $scope.announceNextNumber = function(){
        let postdata = [];
        $scope.announce_number_loading = true;
        $http.post('/Api/Game/announceNextNumber', postdata).then(function successCallback(response) {
            $scope.announce_number_loading = false;
            let data = response.data;
            if(data.status == 'success'){

            }
            else{
                alert(data.message);
            }
        }, function errorCallback(response) {
            console.log(response);
            alert('something went wrong!');
            $scope.announce_number_loading = false;
        });
    };

    $scope.markTicketNumber = function(ticket_id,ticket_no){
        let ticketDetails = $scope.tickets[ticket_id];
        let alreadyMarked = (ticketDetails.marked_numbers).indexOf(ticket_no) !== -1 ;
        if(ticket_no=='' || $scope.game_status == 'booking_open' || ticketDetails.closed || alreadyMarked){
            return false;
        }
        let postdata = {
            ticket_id : ticket_id,
            ticket_no : ticket_no,
        };
        $scope.markNumberLoading = true;
        $http.post('/Api/Game/markTicketNumber', postdata).then(function successCallback(response) {
            let data = response.data;
            if(data.status == 'success'){
                let resultdata = data.result;
                $scope.tickets = resultdata.tickets;
            }
            else{
                alert(data.message);
            }
            $scope.markNumberLoading = false;
        }, function errorCallback(response) {
            console.log(response);
            alert('something went wrong!');
            $scope.markNumberLoading = false;
        });
    };

    $scope.claimPrize = function(ticket_id,criteria){
        if(!confirm("Each ticket can be used for only one claim. Click OK if you want to proceed.")){
            return false;
        }
        if($scope.game_status == 'game_start'){
            let postdata = {
                'ticket_id' : ticket_id,
                'criteria' : criteria,
            };
            $http.post('/Api/Game/claimPrize', postdata).then(function successCallback(response) {
                let data = response.data;
                if(data.status == 'success'){
                    alert('Congrats! you have win this prize');
                }
                else{
                    alert(data.message);
                }
            }, function errorCallback(response) {
                console.log(response);
                alert('something went wrong!');
            });
        }
        else{
            alert('Game is over');
        }
    };

    $scope.objectSize = function(obj){
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    }

    $scope.leaveGame = function(){
        if(!confirm("Do you really want to leave this game ?")){
            return false;
        }
        $('#leaveGameBtn').hide();
        $('#leaveGameLoader').show();
        let postdata = [];
        $http.post('/Api/Game/leave', postdata).then(function successCallback(response) {
            let data = response.data;
            if(data.status == 'success'){
                location.reload();
            }
            else{
                alert(data.message);
            }
        }, function errorCallback(response) {
            console.log(response);
            alert('something went wrong!');
            $('#leaveGameLoader').hide();
            $('#leaveGameBtn').show();
        });
    };

    $scope.startGame = function(){
        $scope.start_game_loading = true;
        $http.post('/Api/Game/startGame').then(function successCallback(response) {
            $scope.start_game_loading = false;
            let data = response.data;
            if(data.status == 'success'){
                alert('Game has been started. Announce first number');
            }
            else{
                alert(data.message);
            }
        }, function errorCallback(response) {
            alert('something went wrong!');
            $scope.start_game_loading = false;
        });
    }

    $scope.copyJoinLink = function(){
        const el = document.createElement('textarea');
        el.value = $scope.join_url;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        alert("Link Copied!");
    }

    $scope.buyTickets = function(){
        $('#buyTicketBtn').hide();
        $('#buyTicketLoader').show();
        let ticket_count = $('#ticket_count').val();
        let postdata = {
            'ticket_count' : ticket_count 
        };
        $http.post('/Api/Game/buyTickets', postdata).then(function successCallback(response) {
            let data = response.data;
            if(data.status == 'success'){
                alert('You have bought ' + ticket_count + ' tickets');
            }
            else{
                alert(data.message);
            }
            $('#buyTicketLoader').hide();
            $('#buyTicketBtn').show();
        }, function errorCallback(response) {
            alert('something went wrong!');
            $('#buyTicketLoader').hide();
            $('#buyTicketBtn').show();
        });
    }

    // Reading Groupchat
    function readGroupChat(){
        firebase.database().ref('groupchat/' + $scope.group_id + '/' ).on('value', function(snapshot) {
            $scope.group_chats = snapshot.val();
            $scope.group_chats_msgs = $scope.objectSize($scope.group_chats);
            console.log( 'group_chats_msgs', $scope.group_chats_msgs );
            showLatestChat();
            readGroupChatStatus();
            if($scope.chatOpen){
                updateSeenStatus();
            }
        });
    }
    
    // Reading Group Chat Status
    function readGroupChatStatus(){
        if($scope.group_chats_status  == undefined){
            firebase.database().ref('groupchatStatus/' + $scope.group_id + '/' + $scope.user.uid + '/').on('value', function(snapshot) {
                $scope.group_chats_status = snapshot.val();
                $scope.group_chats_seen_msgs = $scope.objectSize($scope.group_chats_status);
                console.log( 'group_chats_seen_msgs', $scope.group_chats_seen_msgs );
                playSound('/assets/tones/msg.mp3');
                // if($scope.group_chats_msgs!=0 &&  $scope.group_chats_msgs > $scope.group_chats_seen_msgs){
                    
                // }
            });
        }
    }

    // Reading Groupchat
    function readUpdates(){
        firebase.database().ref('groupUpdates/' + $scope.group_id + '/' ).on('value', function(snapshot) {
            dataLoad();
        });
    }

    // Send Group Message
    $scope.sendMessage = function(){
        var message = $('#text_message').val();
        if(message.trim() !==''){
            $('#text_message').val('');
            let postdata = {
                photoURL :  $scope.user.photoURL,
                displayName :  $scope.user.displayName,
                uid :  $scope.user.uid,
                text : message,
                timestamp : firebase.database.ServerValue.TIMESTAMP,
            };
            var groupChatRef = 'groupchat/' + $scope.group_id ;
            var groupChatRefKey = firebase.database().ref().child(groupChatRef).push().key;
            var groupChatStatusRef = 'groupchatStatus/' + $scope.group_id + '/' + $scope.user.uid + '/' + groupChatRefKey;
            firebase.database().ref(groupChatRef + '/' + groupChatRefKey).update(postdata);
            firebase.database().ref(groupChatStatusRef).update({
                'seen' : true
            });
        }
    }


    
    function showLatestChat(){
        $(".chat-list").stop().animate({ scrollTop: $(".chat-list")[0].scrollHeight}, 1000);   
    }

    $scope.toggleChat = function(){
        if($scope.chatOpen){
            document.getElementById("chatWindow").style.display = "none";
            $scope.chatOpen = false;
        }
        else{
            document.getElementById("chatWindow").style.display = "block";
            $scope.chatOpen = true;
            updateSeenStatus(); 
        }
    }
    
    function updateSeenStatus (){
        if($scope.objectSize($scope.group_chats) > 0){
            var size = 0, key;
            for (key in $scope.group_chats) {
                if($scope.group_chats_status[key] == undefined){
                    var groupChatStatusRef = 'groupchatStatus/' + $scope.group_id + '/' + $scope.user.uid + '/' + key;
                    firebase.database().ref(groupChatStatusRef).update({
                        'seen' : true
                    });
                }
            }
        }
    }

    var element = document.getElementById('text_message');
    element.onkeydown = function (e) {
        if (!e) {
            e = window.event;
        }
        var charCode = (e.which) ? e.which : e.keyCode
        // Enter key
        if(charCode == 13) {
            e.preventDefault();
            $scope.sendMessage();
            return false;
        }
    };
}]);