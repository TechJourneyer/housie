myapp.controller('user_login', ['$http', '$scope', '$timeout', '$window', '$location', function ($http, $scope, $timeout, $window, $location) {
    $scope.is_login = false;
    $scope.loading = false;

    // firebase UI 
    var ui = new firebaseui.auth.AuthUI(firebase.auth());
    var uiConfig = {
        callbacks: {
            signInSuccessWithAuthResult: function(authResult, redirectUrl) {
                console.log(authResult);
                console.log(redirectUrl);
                return false;
            },
            uiShown: function() {
            }
        },
        // Will use popup for IDP Providers sign-in flow instead of the default, redirect.
        signInFlow: 'popup',
        signInSuccessUrl: '/login/',
        signInOptions: [
            // Leave the lines as is for the providers you want to offer your users.
            firebase.auth.GoogleAuthProvider.PROVIDER_ID,
            firebase.auth.EmailAuthProvider.PROVIDER_ID,
        ],
        // Terms of service url.
        tosUrl: '/terms_of_service',
        // Privacy policy url.
        privacyPolicyUrl: '/privacy_policy'
    };

    // Firebase Authentication
    firebase.auth().onAuthStateChanged( function (user){
        if(user){
            $scope.loading = true;
            firebase.auth().currentUser.getIdToken(true).then(function(idToken) {
                $scope.is_login = true;
                signIn(idToken);
            }).catch(function(error) {
                console.log(error);
                $scope.loading = false;
                alert('Something went wrong!');
            });
        }
        else{
            $scope.is_login = false;
            $scope.loading = false;
            ui.start('#firebaseui-auth-container', uiConfig);
        }
    });

    function signIn(idToken){
        let postdata = {
            id_token : idToken
        };
        $http.post('/Api/Auth/signIn', postdata).then(function successCallback(response) {
            var data = response.data;
            if(data.status == 'success'){
                window.location.href = "/";
            }
            else{
                $scope.loading = false;
                alert('something went wrong');
                return false;
            }
        }, function errorCallback(response) {
            console.log(response);
            $scope.loading = false;
        });    
    }


}]);

