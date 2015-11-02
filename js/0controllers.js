'use strict';

/* Controllers */
var queryStr = "";

/*var app = angular.module('churchmembers', ['UserValidation']);*/


var churchmembersControllers = angular.module('churchmembersControllers', []);

/* var compareTo = function() {
 return {
 require: "ngModel",
 scope: {
 otherModelValue: "=compareTo"
 },
 link: function(scope, element, attributes, ngModel) {
 
 ngModel.$validators.compareTo = function(modelValue) {
 return modelValue == scope.otherModelValue;
 };
 
 scope.$watch("otherModelValue", function() {
 ngModel.$validate();
 });
 }
 };
 };
 */
//app.directive("compareTo", compareTo);

churchmembersControllers.controller('ChurchmembersListCtrl', ['$scope', 'Church', '$http', 'ngDialog', 'Data',
    function($scope, Church, $http, ngDialog, Data) {
        if (angular.isDefined($scope.username)) {

            Data.post('checkUserEmail', {user_id: $scope.username}).then(function(results)
            {
                if (results.email !== "")
                {
                    $scope.rightPanel = true;
                }
                else
                {
                    $scope.rightPanel = false;
                }
            });

            //$http.get("http://localhost:8080/Webservice/Church_Web_services.jsp?action=checkEmailId&data="+$scope.username).success(function(response) {
//            alert("LIST "+response.email);
//             if(response.email!==""){
//                 $scope.rightPanel=true;
//                 }
//                 else{
//                      $scope.rightPanel=false;
//                 }
//        });     
        }

        Data.get('getMemberlist').then(function(results) {
            //console.log("getMemberlist " + JSON.stringify(results));
            $scope.churchmembers = results;
        });

//        getAutoComplete();
//        function getAutoComplete() {
//            Data.post('getAutoComplete').then(function(results) {
//                $scope.contentData = results;
//            });
//        }

        $scope.dosearchQuery = function(query, event) {

            var content = "";
            $("#loader").css({
                display: "block", float: "right", marginTop: "-4%"
            });

            content += query;
            //Data.post('../test/test.php',{search : content}).then(function(results) {
            Data.post('getAutoComplete', {query: content}).then(function(results) {

                var data = JSON.stringify(results);
                // console.log("COMPLETER DATA  "+data);
                $scope.contentData = results;
            });
        };


//        ;
        $scope.dosearchdata = function() {
//              alert("http://localhost:8080/Webservice/HomeSearch.jsp?data="+$scope.query.content);  
//            $http.get("http://localhost:8080/Webservice/HomeSearch.jsp?data="+$scope.query.content).success(function(response) {
//            $scope.churchmembers_1 = response;
//            alert("results.id "+response.data);
//        }); 
            //$http.get("http://localhost:8080/Webservice/Church_Web_services.jsp?action=church_memeber_list&data=" + $scope.query.content).success(function(response) {
//                $scope.churchmembers = response;
//            });
            Data.get('getMemberlist').then(function(results) {
                console.log("getMemberlist " + JSON.stringify(results));
                $scope.churchmembers = results;
            });
        };

        $scope.orderProp = 'age';

        $scope.checkAll = function() {
            $scope.user.savedgroup = angular.copy($scope.savedgroup);
        };
        $scope.uncheckAll = function() {
            $scope.user.savedgroup = [];
        };
        $scope.checkFirst = function() {
            $scope.user.savedgroup.splice(0, $scope.user.savedgroup.length);
            $scope.user.savedgroup.push($scope.savedgroup[0]);
        };

        $scope.checkAll = function() {
            $scope.user.displayinfo = angular.copy($scope.displayinfo);
        };
        $scope.uncheckAll = function() {
            $scope.user.displayinfo = [];
        };
        $scope.checkFirst = function() {
            $scope.user.displayinfo.splice(0, $scope.user.displayinfo.length);
            $scope.user.displayinfo.push($scope.displayinfo[0]);
        };

    }]);

churchmembersControllers.controller('rightPanelCtrl', ['$scope', '$http', '$window', '$location', 'Church', 'ngDialog', 'Data', '$rootScope',
    function($scope, $http, $window, $location, Church, ngDialog, Data, $rootScope) {

        Data.post('getAllGroupIcons').then(function(results) {
           $rootScope.groupIcons = results;
        });
        $scope.save_group = function() {
            var d = $scope.query;
            if (angular.isDefined($scope.query)) {
                ngDialog.open({
                    template: 'savedGroupDialogID',
                    controller: 'InsideCtrl',
                    data: {
                        q: $scope.query
                    }
                });
            }
        };
        $scope.openDefault = function() {
            ngDialog.open({
                template: 'signInDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };

    }]);

churchmembersControllers.controller('EmailCtrl', ['$scope', '$routeParams', '$http', 'Church', 'Data', '$location',
    function($scope, $routeParams, $http, Church, Data, $location) {
        $scope.email = function() {
            Data.post('EmailVerification.jsp', {
                //data: $scope.query.content
            }).then(function(results) {
                // alert("results.id "+results.id);
                $scope.churchmembers = results.memberDetails;
                $scope.Items = results.memberaddItemDetails;
                $location.path('/churchs/' + results.id);
            });
        };
    }]);

churchmembersControllers.controller('HomeCtrl', ['$scope', '$routeParams', '$http', 'Church', 'Data', '$location', '$rootScope',
    function($scope, $routeParams, $http, Church, Data, $location, $rootScope) {
        ////////////////// CHNAGES BY PAWAN /////////////////////////// 
        $scope.dosearchQuery = function(query, event) {
            var content = "";
            $("#loader").css({
                display: "block", float: "right", marginTop: "-4%"
            });

            content += query;
            //Data.post('../test/test.php',{search : content}).then(function(results) {
            Data.post('getAutoComplete', {query: content}).then(function(results) {
                
                $scope.contentData = results;

            });
            $("#loader").css({
                display: "none", float: "right", marginTop: "-4%"
            });
        };

        $scope.doSearch = function(data) {
            $rootScope.query = data.content;
            $location.path('churchs');
        };
        ////////////////// END /////////////////////////// 
        //getAutoComplete();
//        function getAutoComplete() {
//            Data.post('getAutoComplete').then(function(results) {
//                $scope.contentData = results;
//            });
//        }

    }]);

churchmembersControllers.controller('ChurchmemberDetailCtrl', ['$scope', '$routeParams', '$http', 'Church', 'ngDialog', 'Data', '$window',
    function($scope, $routeParams, $http, Church, ngDialog, Data, $window) {
        Data.get('getMemberlist').then(function(results) {
            $scope.churchmembersDetails = results;
        });
        $scope.rightPanel = true;
        Data.post('memberDetails', {
            user_id: $routeParams.churchId,
            action: 'none'
        }).then(function(results) {
            //console.log("member details "+JSON.stringify(results));
            if (results.emailid == $scope.username)
            {
                $scope.addItemTag = true;
                $scope.addItemLink = true;
                $scope.rightPanel = true;
            }
            else
            {
                $scope.rightPanel = false;
                $scope.addItemTag = false;
                $scope.addItemLink = false;
            }
        });
////////////////// CHNAGES BY PAWAN ///////////////////////////
        $scope.user_profile_div = true;

        $scope.querySearch = function(query, event, content) {
            if (event === 13) {
                console.log("IN 13 >> " + content + "   queryData  " + $scope.query.content);
                //$scope.querytest=content+" TEST";
            }
            if (query === "") {
                //console.log("IN IF >> "+query);
                $scope.search_result_div = false;
                $scope.user_profile_div = true;
            } else {
                //  console.log("IN ESLEZZ >> "+query);
                $scope.search_result_div = true;
                $scope.user_profile_div = false;
            }
        };
        $scope.newMember = function() {
            ngDialog.open({
                template: 'addmemberDialogID',
                controller: 'ChurchmemberDetailCtrl',
                data: {
                    email: $scope.username
                }
            });
        };
        $scope.ok = function(text) {
            console.log("ckEditor Data: ", text);
        };

        $scope.Reset = function() {
            $scope.ck_data = 'Tets';
        };
        $scope.label_name = true;
        $scope.access_div = true;
        $scope.label_value = true;
        $scope.typeSelect = function(type) {
            
            console.log("TYPE  "+type);
            if (type === "group") {
                $scope.ck_div = false;
                $scope.group_div = true;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = false;
                $scope.label_value = false;
                $scope.access_div = false;
                $scope.increment_div = false;
                $scope.date_div=false;
                
            }
            else if(type==="date"){
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = false;
                $scope.access_div = false;
                $scope.increment_div = true;
                 $scope.date_div=true;
            }
            else if(type==="number"){
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = true;
                $scope.access_div = false;
                $scope.increment_div = true;
                 $scope.date_div=false;
            }
            else if (type === 'blob') {
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.ck_div = true;
                $scope.group_div = false;
                $scope.label_value = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.increment_div = false;
                 $scope.date_div=false;
            }
            else if (type === "individual") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = true;
                $scope.label_name = false;
                $scope.access_div = false;
                $scope.label_value = false;
                $scope.increment_div = false;
                 $scope.date_div=false;
            }
            else if (type === "image") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = true;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = false;
                $scope.increment_div = false;
                 $scope.date_div=false;
            }
            else {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = true;
                $scope.increment_div = false;
                $scope.date_div=false;
            }

            //alert("type  "+type);

        };

////////////////////// END /////////////////////////////////////


        if (!angular.isDefined($scope.username))
        {
            Data.post('memberDetails', {
                user_id: $routeParams.churchId,
                action: 'public'
            }).then(function(results) {
                //console.log("INSIDE IF " + JSON.stringify(results.memberDetails.id));
                $scope.churchmembers = results;
                $scope.Items = results.memberaddItemDetails;
            });
        }
        else
        {
            Data.post('memberDetails', {
                user_id: $routeParams.churchId, //$scope.username,
                action: 'private'
            }).then(function(results) {
                //console.log("INSIDE ELSE  " + JSON.stringify(results.memberDetails.id));
                $scope.churchmembers = results;
                $scope.Items = results.memberaddItemDetails;
            });
        }

        $scope.ckEditors = [];
//        $scope.labels = true;
//        $scope.access_div = true;
//        $scope.typeSelect = function(type) {
//            if (type === "group") {
//
//                $scope.group_div = true;
//                $scope.image = false;
//                $scope.addmember_div = false;
//                $scope.labels = false;
//                $scope.access_div = false;
//            }
//            else if (type === "individual") {
//                $scope.group_div = false;
//                $scope.image = false;
//                $scope.addmember_div = true;
//                $scope.labels = false;
//                $scope.access_div = false;
//            }
//            else if (type === "image") {
//                $scope.group_div = false;
//                $scope.image = true;
//                $scope.addmember_div = false;
//                $scope.labels = false;
//                $scope.access_div = true;
//            }
//            else {
//
//                $scope.group_div = false;
//                $scope.image = false;
//                $scope.addmember_div = false;
//                $scope.labels = true;
//                $scope.access_div = false;
//            }
//
//            //alert("type  "+type);
//
//        };
        $scope.accessby = "public";
        var access = $scope.accessby;
        $scope.toggleAddItemSelection = function toggleSelection(acc) {
            if (acc === 'private') {
                $scope.options = true;
            } else {
                $scope.options = false;
            }
            access = acc;
        };
        $scope.togglePrivate = function(data) {

            if (data !== 'Only to me') {
                $scope.options_text = true;
            } else {
                $scope.options_text = false;
            }
        };
         $scope.addItem = function() {
            if (angular.isDefined($scope.username)) {
            ngDialog.open({
                    template: 'addItemDialogID',
                    controller: 'ChurchmemberDetailCtrl',
                    data: {
                        email: $scope.username
                    }
                });

            }else{
                ngDialog.open({
                    template: 'addItemLogoffDialogID',
                    controller: 'ChurchmemberDetailCtrl',
                    data: {
                        email: $scope.username
                    }
                });
            
            }
        };
        $scope.labelname = {};

        $scope.openDefault = function() {
            ngDialog.open({
                template: 'addItemDialogID',
                controller: 'ChurchmemberDetailCtrl',
                className: 'ngdialog-theme-default'
            });
        };

        $scope.setImage = function(imageUrl) {
            $scope.mainImageUrl = imageUrl;
        };

        $scope.images = [];
        $scope.imagesGroup = [];
        $scope.addImage = function() {
            var reader = new FileReader(),
                    $img = $("#img")[0],
                    index = $scope.images.length;
            reader.onload = function(e) {
                if ($scope.images.indexOf(e.target.result) > -1)
                    return;
                $scope.images.push(e.target.result);
                if (!$scope.$$phase)
                    $scope.$apply();
                $("#imagePreview" + index).attr('src', e.target.result);
                $("#imagePreview1" + index).attr('src', e.target.result);
                //var d=$("#imagePreview" + index).attr('src', e.target.result);
                //dataimg = e.target.result;
                $scope.uploadImage(index);
            };
            reader.readAsDataURL($img.files[0]);
        };

        $scope.addImageInGroup = function() {

            var reader = new FileReader(),
                    $img = $("#img1")[0],
                    index = $scope.imagesGroup.length;
            reader.onload = function(e) {

                if ($scope.imagesGroup.indexOf(e.target.result) > -1)
                    return;
                $scope.imagesGroup.push(e.target.result);
                if (!$scope.$$phase)
                    $scope.$apply();
                $("#imagePreview1" + index).attr('src', e.target.result);
                $scope.uploadImage(index);
            };
            reader.readAsDataURL($img.files[0]);
        };

        $scope.getGroupName = function() {


        };
        
        // for adding items 
        $scope.doAddItem = function(addItem) {
            Data.post('addItemById', {
                label: addItem.labelname,
                fvalue: addItem.fvalue,
                type: addItem.type,
                passkey: addItem.passkey,
                access: "public",
                email: $scope.ngDialogData.email
                members : 
                
            }).then(function(results) {
                console.log("qeury  " + JSON.stringify(results));
                $window.location.reload();
                ngDialog.close();
                Data.toast(results);
                if (results.type === 'success') {

                }


            });

        };



    }]);

churchmembersControllers.controller('HeaderCtrl', ['$scope', 'ngDialog', '$timeout', 'Data', '$rootScope', '$window',
    function($scope, ngDialog, $timeout, Data, $rootScope, $window) {
        $scope.logout = function() {
            $rootScope.username = '';
            Data.post('logout', {
            }).then(function(results) {

                Data.toast(results);
            });
            $window.location.reload();
        };

//    $scope.dropdown = [
//    {text: ' <div class="form-design"><div class="img-left"><img src='+$rootScope.userimage+' style="width:100px;height:100px;">'},
//    {text: '</div><div class="content-right"><p class="Uname">'+$rootScope.userid+'</p><p class="Uemail">'+$rootScope.username+'</p><a href="#">Change Password</a></div></div>'},
////    {text: '<i class="fa fa-external-link"></i>&nbsp;External link', href: '/auth/facebook', target: '_self'},
//    {divider: true},
//    {text: '<p class="btn btn-primary btn-secondary" style="width:100%;">View Profile</p>'},
//    {text: '<p  class="btn btn-primary btn-secondary" style="width:100%;>Sign out</p>'}
//  ];

        $scope.dropdown = [
            {text: ' <div class="form-design"><div class="img-left"><img src=' + $rootScope.userimage + ' style="width:100px;height:100px;">', href: '#'},
            {text: '</div><div class="content-right"><p class="Uname">' + $rootScope.userid + '</p><p class="Uemail">' + $rootScope.username + '</p><a href="#">Change Password</a></div></div>', click: '$alert("Holy guacamole!")'},
//    {text: '<i class="fa fa-external-link"></i>&nbsp;External link', href: '/auth/facebook', target: '_self'},
            {divider: true},
            {text: '<p class="btn btn-primary btn-secondary" style="width:100%;">View Profile</p>', click: 'logout()'},
            {text: '<p  class="btn btn-primary btn-secondary" style="width:100%;>Sign out</p>', click: 'logout()'}
        ];
        $scope.signin = function() {

            ngDialog.openConfirm({
                template: 'signInDialogID',
                controller: 'InsideCtrl',
                data: {
                    q: 'some data'
                }
            });
        };

        $scope.openDefault = function() {
            ngDialog.open({
                template: 'signInDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };

        $scope.signup = function() {
            ngDialog.openConfirm({
                template: 'signupDialogID',
                controller: 'InsideCtrl',
                data: {
                    foo: 'some data'
                }
            });
        };

        $scope.openDefault = function() {
            ngDialog.open({
                template: 'signupDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };
        //    $scope.isLoggedIn = loginService.isLoggedIn();
        //    $scope.session = loginService.getSession();
    }]);
churchmembersControllers.controller('SecondModalCtrl', ['$scope', 'ngDialog', '$http', 'fileUpload', function($scope, ngDialog, $http, fileUpload) {
        $scope.closeSecond = function() {
            ngDialog.close();
        };
        $scope.model = {
            name: "",
            comments: ""
        };
//
//        //an array of files selected
//        $scope.files = [];
//
//        //listen for the file selected event
//        $scope.$on("fileSelected", function(event, args) {
//            $scope.$apply(function() {
//                //add the file object to the scope's files collection
//                $scope.files.push(args.file);
//            });
//        });
//        
//        ///  DO NOT DELETE  ////////////
//        $scope.uploadFile = function() {
//            var email = $scope.ngDialogData.email;
//            var name = $scope.ngDialogData.fname + "-" + $scope.ngDialogData.lname;
//            var file = $scope.myFile;
//            var data = "e=" + email + "&n=" + name;
//            var uploadUrl = "http://localhost:8080/Webservice/UploadImage?" + data;//"fileUpload.jsp";
//            //     alert(email+" ::  "+name+" ::  "+uploadUrl);
//            fileUpload.uploadFileToUrl(file, uploadUrl);
//
//        };

    }]);
churchmembersControllers.controller('InsideCtrl', ['$scope', 'ngDialog', '$rootScope', 'Data', '$window', 'fileUpload','FileUploader',
    function($scope, ngDialog, $rootScope, Data, $window, fileUpload,FileUploader) {
//        $scope.$watch('files', function() {
//            $scope.upload($scope.files);
//        });
//            $scope.upload = function (files) {
//        if (files && files.length) {
//            for (var i = 0; i < files.length; i++) {
//                var file = files[i];
//                $upload.upload({
//                    url: 'https://angular-file-upload-cors-srv.appspot.com/upload',
//                    fields: {
//                        'username': $scope.username
//                    },
//                    file: file
//                }).progress(function (evt) {
//                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
//                    console.log('progress: ' + progressPercentage + '% ' +
//                                evt.config.file.name);
//                }).success(function (data, status, headers, config) {
//                    console.log('file ' + config.file.name + 'uploaded. Response: ' +
//                                JSON.stringify(data));
//                });
//            }
//        }
//    };
 

// $scope.uploadFile = function(user){
//        var file = user.myFile;
//        
//        console.log('file is ' + JSON.stringify(file));
//        var uploadUrl = "dataapi/fileUploadTest";
//        fileUpload.uploadFileToUrl(file, uploadUrl);
//    };
    
        $scope.images = [];
        $scope.addImage = function() {
            var reader = new FileReader(),
                    $img = $("#img")[0],
                    index = $scope.images.length;
            reader.onload = function(e) {
                if ($scope.images.indexOf(e.target.result) > -1)
                    return;
                $scope.images.push(e.target.result);
                if (!$scope.$$phase)
                    $scope.$apply();
                $("#imagePreview" + index).attr('src', e.target.result);
                //var d=$("#imagePreview" + index).attr('src', e.target.result);
                //dataimg = e.target.result;
                $scope.uploadImage(index);
            };
            reader.readAsDataURL($img.files[0]);
        };
        
        $scope.doAdd = function(user) {
            $("#loader").css({
                display: "block"

            });
            
            //var uploader = $scope.uploader = new FileUploader({
//            url: 'upload/signup.php'
//        });
//        uploader.filters.push({
//            name: 'imageFilter',
//            fn: function(item /*{File|FileLikeObject}*/, options) {
//                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
//                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
//            }
//        });

        // CALLBACKS

        

        //console.info('uploader', uploader);
            var file = user.myFile;
            Data.post('upload', {
                my_file:file,
            }).then(function(results) {
                console.log("file upload" + JSON.stringify(results));
                if (results.type === "success") 
                {
                    Data.toast(results);
                }
            });
            
//            var file = user.myFile;
//            Data.post('signup', {
//                emailid: user.emailid,
//                my_file:file,
//                password: user.password,
//                cnpassword: user.cn_password,
//                fname: user.fname,
//                lname: user.lname
//            }).then(function(results) {
//                console.log("qeury  " + JSON.stringify(results));
//                if (results.type === "success") {
////                       ngDialog.openConfirm({
////                template: 'imageUploadDialogID',
////                controller: 'SecondModalCtrl',
////                data: {
////                    foo: 'some data'
////                }
//                    //  });
//                }
//                //ngDialog.close();
//                Data.toast(results);
//                // $location.path('churchs');
//
//            });

        };
        $scope.openDefault = function() {
            ngDialog.open({
                template: 'imageUploadDialogID',
                controller: 'SecondModalCtrl',
                className: 'ngdialog-theme-default'
            });
        };
        $scope.doLogin = function(customer) {

            $("#loader").css({
                display: "block"

            });
            Data.post('authentication', {
                username: customer.email,
                password: customer.password
            }).then(function(results) {
                var sdg = JSON.stringify(results);
                console.log('test ' + JSON.stringify(results));
                // ngDialog.close();

                Data.toast(results);

                if (results.type == "success")
                {
                    $rootScope.username = results.uid;
                    ngDialog.close();
                    //$location.path('churchs');
                    $window.location.reload();
                }
                else
                {
                    ngDialog.close();
                    if (results.type == "warning") {
                        $("#err_p").text(results.status);
                    }
                    if (results.type == "error") {
                        $("#err_p").text(results.status);
                    }

                }
            });

        };
        $scope.selection = [];
        $scope.toggleSelection = function toggleSelection(icon) {
            queryStr = icon;
        };


        $scope.doSaveNewGroup = function(group) {
            Data.post('saveUserSearch', {
                group: group.name,
                query: $scope.ngDialogData.q,
                id: $scope.username,
                icon: group.iconid
            }).then(function(results) {
                ngDialog.close();
                Data.toast(results);
                if (results.type === 'success') {
                    Data.post('preparedata').then(function(results) {
                    });
                    //window.location.href=window.location.pathname+"#/";
                    }
                    $window.location.reload();
            });


        };

    }]);
churchmembersControllers.controller('MainCtrl', ['$scope', 'ngDialog', '$http', '$location', '$rootScope', '$routeParams', 'Data',
    function($scope, ngDialog, $http, $location, $rootScope, $routeParams, Data) {


    }]);
churchmembersControllers.controller('AddUserCtrl', ['$scope', '$http',
    function($scope, $http) {
        var dataimg = "";
        $scope.items = [];
        $scope.add = function() {
            $scope.items.push({
                inlineChecked: false,
                children: "",
                childrenPH: "name of children",
                text: ""
            });
        };
        $scope.images = [];
        // //Don't let the same file to be added again
        $scope.removeImage = function(index) {
            $scope.images.splice(index, 1);
        }

        $scope.single = function(image) {
            var formData = new FormData();
            formData.append('image', image, image);
            $http.post('http://localhost:8080/Webservice/Church_Web_services.jsp?action=add_User_Image', formData, {
                headers: {
                    'Content-Type': false
                },
                transformRequest: angular.identity
            }).success(function(result) {
                $scope.uploadedImgSrc = result.src;
                $scope.sizeInBytes = result.size;
            });
        };



        $scope.addImage = function() {

            var
                    reader = new FileReader(),
                    $img = $("#img")[0],
                    index = $scope.images.length;

            reader.onload = function(e) {

                if ($scope.images.indexOf(e.target.result) > -1)
                    return;

                $scope.images.push(e.target.result);
                if (!$scope.$$phase)
                    $scope.$apply();

                $("#imagePreview" + index).attr('src', e.target.result);
                //var d=$("#imagePreview" + index).attr('src', e.target.result);
                dataimg = e.target.result
                $scope.uploadImage(index);

            }
            reader.readAsDataURL($img.files[0]);

        }
        //$scope.add();
        $scope.addNewUser = function() {
            $scope.user = null;
            var param = "fname=" + $scope.fname + "&lname=" + $scope.lname + "&group_profile=" + $scope.group_profile + "&group_img=" + dataimg;
            param += "&displayngroup=" + $scope.displayngroup + "&children=" + $scope.children + "&group_membership=" + $scope.group_membership;
            param += "&mobile_text=" + $scope.mobile_text + "&long_form_text=" + $scope.long_form_text + "&voice=" + $scope.voice + "&image_mail=" + $scope.image_mail + "&videochat=" + $scope.video_chat;

            $http({
                method: 'POST',
                url: "http://localhost:8080/Webservice/Church_Web_services.jsp?action=add_User_Profile",
                data: param,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }

            })
            window.location.href = window.location.pathname + "#/churchs";
        }

    }]);
