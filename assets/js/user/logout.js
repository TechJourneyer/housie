myapp.controller('user_logout', ['$http', '$scope', '$timeout', '$window', '$location', function ($http, $scope, $timeout, $window, $location) {
    $scope.is_login = false;
    firebase.auth().signOut();
    
    firebase.auth().onAuthStateChanged( function (user){
        $scope.loading = true;
        if(!user){
            window.location.href='/';
        }
    });
}]);

