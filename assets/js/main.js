let myapp = angular.module('app',[]);
myapp.controller('header', ['$http', '$scope', '$timeout', '$window', '$location', function ($http, $scope, $timeout, $window, $location) {
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    firebase.analytics();
}]);


function playSound(filename){
    let audio = new Audio(filename);
    audio.play();
}