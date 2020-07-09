
myapp.controller('create_group', ['$http', '$scope' , function($http,$scope) {
    $scope.clear = function(){
        $('#group_name').val('');
        $('#ticket_prize').val('');
    };
    
    $scope.createGroup = function(){
        let group_name = $('#group_name').val(); 
        let ticket_prize = $('#ticket_prize').val(); 
        
        if(group_name.trim() == ''){
            alert('Please enter group name');
            return false;
        }

        if(ticket_prize.trim() == ''){
            alert('Please enter ticket prize');
            return false;
        }
        else{
            if(ticket_prize  < 10 || ticket_prize  > 1000){
                alert('Ticket prize should be in range of 10 to 1000');
                return false;
            }
        }
        var postdata = {
            group_name : group_name,
            ticket_prize : ticket_prize,
        };
        
        $.ajax({
            type: 'POST',
            url: '/Api/Game/createGroup',
            data: postdata,
            success: function(result) {
                console.log(result);
                if(result.status == 'success'){
                    window.location.href = "/Game/play";
                }
                else{
                    alert(result.message);
                    return false;
                }
            },
            error: function(result){
                alert('something went wrong!');
                location.reload();
            }
        });
        
    };
}]);

