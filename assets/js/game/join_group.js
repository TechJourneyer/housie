myapp.controller('join_group', ['$http', '$scope', '$timeout', '$window', '$location', function ($http, $scope, $timeout, $window, $location) {

    $scope.groupId = groupId;
    $scope.loading = true;
    $scope.users = '';
    $scope.game_details = {};
    loadingGameDetails();

    function loadingGameDetails(){
        var postdata = {
            group_id : $scope.groupId,
        };

        $http.post('/Api/Game/fetchUserDetailsById', postdata).then(function successCallback(response) {
            console.log(response);
            var data = response.data;
            if(data.status == 'success'){
                $scope.loading = false;
                $scope.game_details = data.result.game_details;
                $scope.users = data.result.users;
            }
            else{
                alert(data.message);
                return false;
            }
        }, function errorCallback(response) {
            console.log(response);
            alert('something went wrong!');
        });
    }

    $scope.joinGroup = function(){
        var postdata = {
            group_id : $scope.groupId,
        };
        
        $http.post('/Api/Game/join', postdata).then(function successCallback(response) {
            console.log(result);
            var result = response.data;
            if(result.status == 'success'){
                alert('You will be redirected to live game page');
                window.location.href = "/Game/play";
            }
            else{
                alert(result.message);
                return false;
            }
        }, function errorCallback(response) {
            alert('something went wrong!');
            location.reload();
        });
    };

}]);