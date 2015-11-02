'use strict';

/* App Module */

var churchmembersApp = angular.module('churchmembersApp', [
    'ngRoute', 'ngDialog', 'ui.bootstrap', 'angularFileUpload',
    'churchmembersAnimations', 'ng-slide-down', 'ngSanitize', 'mgcrea.ngStrap',
    'ngAnimate', 'toaster',
    'churchmembersControllers',
    
    'churchmembersServices'
]);


churchmembersApp.config(['$routeProvider',
    function ($routeProvider) {
        $routeProvider.
                when('/churchs', {
                    templateUrl: 'partials/churchmembers-list.html',
                    controller: 'ChurchmembersListCtrl'
                }).
                when('/addGroup', {
                    templateUrl: 'partials/addmember.html',
                    controller: 'AddUserCtrl'
                }).
        //when('/', {
        //                    templateUrl: 'partials/addmember.html',
        //                    controller: 'AddUserCtrl'
        //                }).
                when('/users', {
                    templateUrl: 'partials/SearchUserProfile.html',
                    controller: 'EmailCtrl'
                }).
                when('/user/:user_id', {
                    templateUrl: 'partials/UserProfile.html',
                    controller: 'ProfileCtrl'
                }).//added by mufaddal
                when('/group/:group_id', {
                    templateUrl: 'partials/GroupProfile.html',
                    controller: 'GroupCtrl'
                }).// end mufaddal
                when('/churchs/:churchId', {
                    templateUrl: 'partials/churchmember-detail.html',
                    controller: 'ChurchmemberDetailCtrl'
                }).
                when('/', {
                    templateUrl: 'partials/home.html',
                    controller: 'HomeCtrl',
                    access: {
                        requiredLogin: false
                    }
                }).
                otherwise({
                    redirectTo: '/'
                });
    } ]);
churchmembersApp.config(['ngDialogProvider', function (ngDialogProvider) {
    ngDialogProvider.setDefaults({
        className: 'ngdialog-theme-default',
        plain: false,
        showClose: true,
        closeByDocument: true,
        closeByEscape: true,
        appendTo: false,
        preCloseCallback: function () {

        }
    });
} ]);

churchmembersApp.config(function ($dropdownProvider) {
    angular.extend($dropdownProvider.defaults, {
        html: true
    });
});
churchmembersApp.config(function ($asideProvider) {
    angular.extend($asideProvider.defaults, {
        animation: 'am-fadeAndSlideLeft',
        placement: 'left'
    });
});
churchmembersApp.config(function ($modalProvider) {
    angular.extend($modalProvider.defaults, {
        animation: 'am-flip-x'
    });
});
churchmembersApp.config(function ($popoverProvider) {
    angular.extend($popoverProvider.defaults, {
        animation: 'am-flip-x',
        trigger: 'hover'
    });
});
churchmembersApp.run(function ($rootScope, $location, Data, $routeParams) {

    $rootScope.$on("$routeChangeStart", function (event, next, current) {
        $rootScope.authenticated = false;
        Data.post('session').then(function (results) {
            $rootScope.logoutBtn = false;
            $rootScope.signinBtn = true;
            $rootScope.signupBtn = true;
            $rootScope.addItemTag = false;
            $rootScope.addItemLink = false;
            $rootScope.userArrowBtn = false;

            if (results.user_id !== "Not_Logged-in") {
                $rootScope.userArrowBtn = true;
                $rootScope.username = results.username;
                $rootScope.userid = results.user_id;
                $rootScope.userimage = results.user_image;
                Data.post('getUserSearch', { user_id: results.user_id }).then(function (user_results) {
                    $rootScope.savedgroup = user_results;
                });
                Data.post('getUserGroup', { user_id: results.user_id }).then(function (results) {
                    $rootScope.groups = results;
                });
                $rootScope.userArrowBtn = true;

                Data.post('checkUserId', {
                    user_id: results.user_id
                }).then(function (data) {

                    if (data.type === "success") {
                        if (data.userid !== "") {
                            $rootScope.useremail = false;
                        }
                    }
                });
                $rootScope.logoutBtn = true;
                $rootScope.signinBtn = false;
                $rootScope.signupBtn = false;
                $rootScope.authenticated = true;
            }
            else { }
        });
    });
});