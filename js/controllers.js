'use strict';

/* Controllers */
var queryStr = "";

/*var app = angular.module('churchmembers', ['UserValidation']);*/


var churchmembersControllers = angular.module('churchmembersControllers', ['angularFileUpload']);


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

churchmembersControllers.controller('ChurchmembersListCtrl', ['$scope', 'Church', '$http', 'ngDialog', 'Data','$rootScope',
    function ($scope, Church, $http, ngDialog, Data,$rootScope) {
        if (angular.isDefined($rootScope.userid)) {

            Data.post('checkUserEmail', { user_id: $rootScope.userid }).then(function (results) {
                if (results.email !== "") {
                    $scope.rightPanel = true;
                }
                else {
                    $scope.rightPanel = false;
                }
            });


        }

        Data.get('getMemberlist').then(function (results) {
            //console.log("getMemberlist " + JSON.stringify(results));
            $scope.churchmembers = results;
        });


        $scope.dosearchQuery = function (query, event) {

            var content = "";
            $("#loader").css({
                display: "block", float: "right", marginTop: "-4%"
            });

            content += query;
            //Data.post('../test/test.php',{search : content}).then(function(results) {
            Data.post('getAutoComplete', { query: content }).then(function (results) {

                var data = JSON.stringify(results);
                // console.log("COMPLETER DATA  "+data);
                $scope.contentData = results;
            });
        };


        //        ;
        $scope.dosearchdata = function () {

            Data.get('getMemberlist').then(function (results) {
                //console.log("getMemberlist " + JSON.stringify(results));
                $scope.churchmembers = results;
            });
        };

        $scope.orderProp = 'age';

        $scope.checkAll = function () {
            $scope.user.savedgroup = angular.copy($scope.savedgroup);
        };
        $scope.uncheckAll = function () {
            $scope.user.savedgroup = [];
        };
        $scope.checkFirst = function () {
            $scope.user.savedgroup.splice(0, $scope.user.savedgroup.length);
            $scope.user.savedgroup.push($scope.savedgroup[0]);
        };

        $scope.checkAll = function () {
            $scope.user.displayinfo = angular.copy($scope.displayinfo);
        };
        $scope.uncheckAll = function () {
            $scope.user.displayinfo = [];
        };
        $scope.checkFirst = function () {
            $scope.user.displayinfo.splice(0, $scope.user.displayinfo.length);
            $scope.user.displayinfo.push($scope.displayinfo[0]);
        };

    } ]);

churchmembersControllers.controller('rightPanelCtrl', ['$scope', '$http', '$window', '$location', 'Church', 'ngDialog', 'Data', '$rootScope',
    function ($scope, $http, $window, $location, Church, ngDialog, Data, $rootScope) {

        Data.post('getAllGroupIcons').then(function (results) {
            $rootScope.groupIcons = results;
        });
        $scope.save_group = function () {
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
        $scope.openDefault = function () {
            ngDialog.open({
                template: 'savedGroupDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };

    } ]);

churchmembersControllers.controller('EmailCtrl', ['$scope', '$routeParams', '$http', 'Church', 'Data', '$location',
    function ($scope, $routeParams, $http, Church, Data, $location) {
        $scope.email = function () {
            Data.post('EmailVerification.jsp', {
                //data: $scope.query.content
            }).then(function (results) {
                // alert("results.id "+results.id);
                $scope.churchmembers = results.memberDetails;
                $scope.Items = results.memberaddItemDetails;
                $location.path('/churchs/' + results.id);
            });
        };
    } ]);

churchmembersControllers.controller('HomeCtrl', ['$scope', '$routeParams', '$http', 'Church', 'Data', '$location', '$rootScope',
    function ($scope, $routeParams, $http, Church, Data, $location, $rootScope) {
        ////////////////// CHNAGES BY PAWAN /////////////////////////// 
        $scope.dosearchQuery = function (query, event) {
            var content = "";
            $("#loader").css({
                display: "block", float: "right", marginTop: "-4%"
            });

            content += query;
            //Data.post('../test/test.php',{search : content}).then(function(results) {
            Data.post('getAutoComplete', { query: content }).then(function (results) {

                $scope.contentData = results;

            });
            $("#loader").css({
                display: "none", float: "right", marginTop: "-4%"
            });
        };

        $scope.doSearch = function (data) {
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

    } ]);

churchmembersControllers.controller('ChurchmemberDetailCtrl', ['$scope', '$upload', '$routeParams', '$http', 'Church', 'ngDialog', 'Data', 
'$window','$rootScope',
    function ($scope, $upload, $routeParams, $http, Church, ngDialog, Data, $window, $rootScope) {
        
        Data.get('getMemberlist').then(function (results) {
            $scope.churchmembersDetails = results;
        });
        
        
        $scope.rightPanel = true;
        Data.post('memberDetails', {
            user_id: $routeParams.churchId,
            action: 'none'
        }).then(function (results) {
            //console.log("member details "+JSON.stringify(results));
            if (results.emailid == $rootScope.userid) {
                $scope.addItemTag = true;
                $scope.addItemLink = true;
                $scope.rightPanel = true;
            }
            else {
                $scope.rightPanel = false;
                $scope.addItemTag = false;
                $scope.addItemLink = false;
            }
        });
        ////////////////// CHNAGES BY PAWAN ///////////////////////////
        $scope.user_profile_div = true;

        $scope.querySearch = function (query, event, content) {
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
        $scope.newMember = function () {
            ngDialog.open({
                template: 'addmemberDialogID',
                controller: 'ChurchmemberDetailCtrl',
                data: {
                    email: $rootScope.userid
                }
            });
        };
        $scope.ok = function (text) {
            console.log("ckEditor Data: ", text);
        };

        $scope.Reset = function () {
            $scope.ck_data = 'Tets';
        };
        $scope.label_name = true;
        $scope.access_div = true;
        $scope.label_value = true;
        $scope.typeSelect = function (type) {
            if (type === "group") {
                $scope.ck_div = false;
                $scope.group_div = true;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = false;
                $scope.label_value = false;
                $scope.access_div = false;
                $scope.date_div = false;

            }
            else if (type === "date") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = false;
                $scope.access_div = true;
                $scope.date_div = true;
            }
            else if (type === "number") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = true;
                $scope.access_div = true;
                $scope.date_div = false;
            }
            else if (type === 'blob') {
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.ck_div = true;
                $scope.group_div = false;
                $scope.label_value = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.date_div = false;
            }
            else if (type === "individual") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = true;
                $scope.label_name = false;
                $scope.access_div = false;
                $scope.label_value = false;
                $scope.date_div = false;
            }
            else if (type === "image") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = true;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = false;
                $scope.date_div = false;
            }
            else {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = true;
                $scope.date_div = false;
            }

            //alert("type  "+type);

        };

        ////////////////////// END /////////////////////////////////////


        if (!angular.isDefined($rootScope.userid)) {
            Data.post('memberDetails', {
                user_id: $routeParams.churchId,
                action: 'public'
            }).then(function (results) {
                //console.log("public " + JSON.stringify(results.memberDetails));
                $scope.churchmembers = results.memberDetails;
                $scope.Items = results.member_profile_fields;
            });
        }
        else {
            Data.post('memberDetails', {
                user_id: $routeParams.churchId, //$rootScope.userid,
                action: 'private'
            }).then(function (results) {
                //console.log("private " + JSON.stringify(results));
                $scope.churchmembers = results.memberDetails;
                $scope.Items = results.member_profile_fields;
            });
        }

        $scope.ckEditors = [];

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
        
        // added by mufaddal
        $scope.viewAccess = function () 
        {
            if ($scope.additem.view_access == 2)
            {
                $scope.view_password = true;
            } 
            else 
            {
                $scope.view_password = false;
            }
        };
        
        $scope.editAccess = function () {
            if ($scope.additem.edit_access == 2)
            {
                $scope.edit_password = true;
            } else {
                $scope.edit_password = false;
            }
        };
        // ends
        
        $scope.togglePrivate = function (data) {

            if (data !== 'Only to me') {
                $scope.options_text = true;
            } else {
                $scope.options_text = false;
            }
        };
        
        
        $scope.addItem = function () {
            if (angular.isDefined($rootScope.userid)) {
                ngDialog.open({
                    template: 'addItemDialogID',
                    controller: 'ChurchmemberDetailCtrl',
                    data: {
                        email: $rootScope.userid
                    }
                });

            } else {
                ngDialog.open({
                    template: 'addItemLogoffDialogID',
                    controller: 'ChurchmemberDetailCtrl',
                    data: {
                        email: $rootScope.userid
                    }
                });

            }
        };
        $scope.labelname = {};

        $scope.openDefault = function () {
            ngDialog.open({
                template: 'addItemDialogID',
                controller: 'ChurchmemberDetailCtrl',
                className: 'ngdialog-theme-default'
            });
        };

        $scope.setImage = function (imageUrl) {
            $scope.mainImageUrl = imageUrl;
        };

        $scope.images = [];
        $scope.imagesGroup = [];
        
        $scope.addImage = function () {
            var reader = new FileReader(),
                    $img = $("#img")[0],
                    index = $scope.images.length;
            reader.onload = function (e) {
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

        $scope.addImageInGroup = function () {

            var reader = new FileReader(),
                    $img = $("#img1")[0],
                    index = $scope.imagesGroup.length;
            reader.onload = function (e) {

                if ($scope.imagesGroup.indexOf(e.target.result) > -1)
                    return;
                $scope.imagesGroup.push(e.target.result);
                if (!$scope.$$phase)
                    $scope.$apply();
                $("#imagePreview1" + index).attr('src', e.target.result);
                //var d=$("#imagePreview" + index).attr('src', e.target.result);
                // dataimg = e.target.result;
                $scope.uploadImage(index);
            };
            reader.readAsDataURL($img.files[0]);
        };

        $scope.getGroupName = function () {


        };
        // for adding items 
        $scope.doAddItem = function (addItem) {

            Data.post('addItemById', {
                added_by_user_id: $rootScope.userid,
                added_to_user_id: $routeParams.churchId,
                type: addItem.type,
                label: addItem.labelname,
                fvalue: addItem.fvalue,
                access_key: addItem.passkey,
                access_by: addItem.accessby,
                ck_data: addItem.ck_data,
                access_to: addItem.access_private,
                date_time: addItem.date_time,
                increment_by: addItem.increment_by,
                increment_when: addItem.increment_when,
                grouplabel: addItem.grouplabel,
                is_private :addItem.is_private,
                member: addItem.member,
                membername: addItem.membername,
                email: addItem.email

            }).then(function (results) {
                if (results.type === 'success') 
                {
                    Data.toast(results);
                    window.location.reload();
                    ngDialog.close();
                }
                else
                {
                    Data.toast(results);
                }
            });
        };
        // upload image START
        $scope.$watch('files', function () {
            $scope.upload($scope.files);
        });
        $scope.upload = function (files) {

            if (files && files.length) {
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    alert(file)
                }
            }
        };
        // upload image END
    } ]);







churchmembersControllers.controller('HeaderCtrl', ['$scope', 'ngDialog', '$timeout', 'Data', '$rootScope', '$window',
    function ($scope, ngDialog, $timeout, Data, $rootScope, $window) {
        
        $scope.logout = function () {
            $rootScope.username = '';
            Data.post('logout', {
            }).then(function (results) {

                Data.toast(results);
            });
            $window.location.href = $window.location.pathname + "";
            //$window.location.reload();
        };


        $scope.dropdown = [
            { text: ' <div class="form-design"><div class="img-left"><img src=' + $rootScope.userimage + ' style="width:100px;height:100px;">', href: '#/user/' + $rootScope.userid + '' },
            { text: '</div><div class="content-right"><p class="Uname">' + $rootScope.username + '</p><p class="Uemail">' + $rootScope.userid + '</p></div><div class="content-right1">Change Password</div>', click: 'ChangePassword()' },
        //    {text: '<i class="fa fa-external-link"></i>&nbsp;External link', href: '/auth/facebook', target: '_self'},
            {divider: true },
            { text: '<p class="btn btn-primary btn-secondary" style="width:100%;">View Profile</p>', href: '#/user/' + $rootScope.userid + '' },
            { text: '<p  class="btn btn-primary btn-secondary" style="width:100%;>Sign out</p>', click: 'logout()' }
        ];
        $scope.signin = function () {

            ngDialog.openConfirm({
                template: 'signInDialogID',
                controller: 'InsideCtrl',
                data: {
                    q: 'some data'
                }
            });
        };

        $scope.openDefault = function () {
            ngDialog.open({
                template: 'signInDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };

        $scope.signup = function () {
            ngDialog.openConfirm({
                template: 'signupDialogID',
                controller: 'InsideCtrl',
                data: {
                    foo: 'some data'
                }
            });
        };

        $scope.openDefault = function () {
            ngDialog.open({
                template: 'signupDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };

        // added by sachin for change password START
        $scope.ChangePassword = function () {

            ngDialog.openConfirm({
                template: 'ChangePasswordDialogID',
                controller: 'InsideCtrl',
                data: {
                    q: 'some data'
                }
            });
        };
        $scope.openDefault = function () {
            ngDialog.open({
                template: 'ChangePasswordDialogID',
                controller: 'InsideCtrl',
                className: 'ngdialog-theme-default'
            });
        };
        // END





        //    $scope.isLoggedIn = loginService.isLoggedIn();
        //    $scope.session = loginService.getSession();
    } ]);
    
churchmembersControllers.controller('SecondModalCtrl', ['$scope', 'ngDialog', '$http', 'fileUpload', function ($scope, ngDialog, $http, fileUpload) {
    $scope.closeSecond = function () {
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

} ]);
churchmembersControllers.controller('InsideCtrl', ['$scope', 'ngDialog', '$rootScope', 'Data', '$window', '$upload',
    function ($scope, ngDialog, $rootScope, Data, $window, $upload) {

        // upload image START
        $scope.$watch('files', function () {
            $scope.upload($scope.files);
        });
        var filee;
        $scope.upload = function (files) {
            if (files && files.length) {
                for (var i = 0; i < files.length; i++) {
                    filee = files[i];
                    //alert(filee);
                }
            }
        };
        // upload image END

        $scope.doAdd = function (user) {
            $("#loader").css({
                display: "block"

            });

            //var uploader = $scope.uploader = new FileUploader({
//                url: 'upload/signup.php'
//            });
           // uploader.filters.push({
//                name: 'imageFilter',
//                fn: function (item /*{File|FileLikeObject}*/, options) {
//                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
//                    return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
//                }
//            });
                $upload.upload({
                url: 'dataapi/signup',
                data: { emailid: user.emailid,
                        password: user.password,
                        cnpassword: user.cn_password,
                        fname: user.fname,
                        lname: user.lname},
                file: filee,
              }).success(function(data, status, headers, config) {
                // file is uploaded successfully
                console.log(JSON.stringify(data));
                    if(data.type == 'error')
                    {
                         Data.toast(data);
                    }
                    else
                    {
                         Data.toast(data);
                    }
              }).error(function(data, status, headers, config) {
                // file is uploaded successfully
                console.log('error'+JSON.stringify(data));
              });


        };
        $scope.openDefault = function () {
            ngDialog.open({
                template: 'imageUploadDialogID',
                controller: 'SecondModalCtrl',
                className: 'ngdialog-theme-default'
            });
        };
        $scope.doLogin = function (customer) {

            $("#loader").css({
                display: "block"

            });
            Data.post('authentication', {
                username: customer.email,
                password: customer.password
            }).then(function (results) {
                var sdg = JSON.stringify(results);
                console.log('test ' + JSON.stringify(results));
                // ngDialog.close();

                Data.toast(results);

                if (results.type == "success") {

                    $rootScope.username = results.username;
                    $rootScope.userimage = results.user_image;
                    $rootScope.userid = results.user_id;

                    ngDialog.close();
                    //$location.path('churchs');
                    $window.location.reload();
                }
                else {
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

        // added by sachin for change passowrd Start
        $scope.Change = function (user) {

            $("#loader").css({
                display: "block"
            });

            Data.post('setNewPassword', {
                user_id: $rootScope.userid,
                old_password: user.old_password,
                new_password: user.new_password,
                con_password: user.con_password
            }).then(function (results) {

                //console.log('test ' + JSON.stringify(results));
                Data.toast(results);

                if (results.type == "success") {
                    ngDialog.close();
                    window.location.reload();
                }
                else {
                    //ngDialog.close();
                    if (results.type == "warning") {
                        $("#err_p").text(results.status);
                    }
                    if (results.type == "error") {
                        $("#err_p").text(results.status);
                    }

                }
            });
        };
        // END

        $scope.selection = [];
        $scope.toggleSelection = function toggleSelection(icon) {
            queryStr = icon;
        };


        $scope.doSaveNewGroup = function (group) {
            Data.post('saveUserSearch', {
                group: group.name,
                query: $scope.ngDialogData.q,
                id: $rootScope.userid,
                icon: group.iconid
            }).then(function (results) {
                ngDialog.close();
                Data.toast(results);
                if (results.type === 'success') {
                    Data.post('preparedata').then(function (results) {
                    });
                    //window.location.href=window.location.pathname+"#/";
                }
                $window.location.reload();
            });


        };

    } ]);
churchmembersControllers.controller('MainCtrl', ['$scope', 'ngDialog', '$http', '$location', '$rootScope', '$routeParams', 'Data',
    function ($scope, ngDialog, $http, $location, $rootScope, $routeParams, Data) {


    } ]);
churchmembersControllers.controller('AddUserCtrl', ['$scope', '$http',
    function ($scope, $http) {
        var dataimg = "";
        $scope.items = [];
        $scope.add = function () {
            $scope.items.push({
                inlineChecked: false,
                children: "",
                childrenPH: "name of children",
                text: ""
            });
        };
        $scope.images = [];
        // //Don't let the same file to be added again
        $scope.removeImage = function (index) {
            $scope.images.splice(index, 1);
        }

        $scope.single = function (image) {
            var formData = new FormData();
            formData.append('image', image, image);
            $http.post('http://localhost:8080/Webservice/Church_Web_services.jsp?action=add_User_Image', formData, {
                headers: {
                    'Content-Type': false
                },
                transformRequest: angular.identity
            }).success(function (result) {
                $scope.uploadedImgSrc = result.src;
                $scope.sizeInBytes = result.size;
            });
        };



        $scope.addImage = function () {

            var 
                    reader = new FileReader(),
                    $img = $("#img")[0],
                    index = $scope.images.length;

            reader.onload = function (e) {

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
        $scope.addNewUser = function () {
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

    } ]);
// added by sachin for view profile, update profile on 08 Apr
churchmembersControllers.controller('ProfileCtrl', ['$scope', 'ngDialog', '$http', '$location', '$rootScope', '$routeParams', 'Data',
    function ($scope, ngDialog, $http, $location, $rootScope, $routeParams, Data) {

        $scope.rightPanel = true;
        Data.post('getUserDetails', {
            user_id: $rootScope.userid
        }).then(function (results) {
            $scope.UserDetails = results;
        });

        $scope.UpdateProfile = function (Details) {
            Data.post('updateUserDetails', {
                user_id: $rootScope.userid,
                first_name: Details.first_name,
                last_name: Details.last_name,
                email_id: Details.email_id,
                contact_1: Details.contact_1,
                contact_2: Details.contact_2,
                contact_3: Details.contact_3,
                address: Details.address,
                city: Details.city,
                state: Details.state,
                zip: Details.zip,
                access_key: Details.access_key
            }).then(function (results) 
            {
                 console.log('test ' + JSON.stringify(results));
                Data.toast(results);         
             //$window.location.reload();
            });
        }
        $scope.Reset = function (Details) {
            window.location.reload();
        }


    } ]);
    
// added by Mufaddal for view group profile, update group profile 
churchmembersControllers.controller('GroupCtrl', ['$scope', 'ngDialog', '$http', '$location', '$rootScope', '$routeParams', 'Data','$upload',
    function ($scope, ngDialog, $http, $location, $rootScope, $routeParams, Data, $upload) {

        $scope.rightPanel = true;
        // get member list for add item dialog in group add item link.
        Data.get('getMemberlistForGroup').then(function (results) {
            $scope.churchmembersDetails = results;
        });
        
        // to make access by public default in group add item login from
        $scope.additem = {
            accessby: 'public'
        };
        //ends public default
        
        // get allowed group list for add item in group. Public group info would be avaialbe to all group
        // private group only to their super groups only. even if its is a public group creted inside a private group.
        Data.post('getAllowedGroup',{group_id : $routeParams.group_id}).then(function (results) {
            //console.log('allowed group '+JSON.stringify(results.data));
            $scope.allowedGroup = results.data.allowed_group;
        });
        //alert('username '+$rootScope.userid);
        // get group details      
        Data.post('getGroupDetails', {
            group_id: $routeParams.group_id,
            user_id : $rootScope.userid 
        }).then(function (results) {
            //alert(JSON.stringify(results.group_details));
            if(results.type === 'success')
            {
                $scope.group_details = results.data.group_details;
            }
            else if(results.type === 'passkeyrequired')
            {
                 ngDialog.open({
                    template: 'partials/viewPrivateGroup.html',
                    controller: 'GroupCtrl',
                    data: {
                        user_id: $rootScope.userid,
                        group_id: $routeParams.group_id
                    }
                });
                
            }
            else if(results.type === 'error')
            {
                Data.toast(results);
            }
        });
        
        //get group fields by group id and user id
        Data.post('getGroupFields', {
            user_id : $rootScope.userid,
            group_id: $routeParams.group_id
        }).then(function (results) {
            //console.log(JSON.stringify(results));
            if(results.type === 'success')
            {
                $scope.group_fields = results.data.group_fields;
            }
            else
            {
                 ngDialog.open({
                    template: 'addItemDialogID',
                    controller: 'GroupCtrl',
                    data: {
                        email: $rootScope.userid
                    }
                });
                 Data.toast(results);  
            }
        });
        
        $scope.UpdateGroupProfile = function (Details) {
            Data.post('updateUserDetails', {
                user_id: $rootScope.userid,
                first_name: Details.first_name,
                last_name: Details.last_name,
                email_id: Details.email_id,
                contact_1: Details.contact_1,
                contact_2: Details.contact_2,
                contact_3: Details.contact_3,
                address: Details.address,
                city: Details.city,
                state: Details.state,
                zip: Details.zip,
                access_key: Details.access_key
            }).then(function (results) 
            {
                console.log('test ' + JSON.stringify(results));
                Data.toast(results);         
             //$window.location.reload();
            });
        }
        
        $scope.Reset = function (Details) {
            window.location.reload();
        }

        $scope.addItem = function () {
            if (angular.isDefined($rootScope.userid)) {
                ngDialog.open({
                    template: 'addItemDialogID',
                    controller: 'GroupCtrl',
                    data: {
                        email: $rootScope.userid
                    }
                });

            } else {
                ngDialog.open({
                    template: 'addItemLogoffDialogID',
                    controller: 'GroupCtrl',
                    data: {
                        email: $rootScope.userid
                    }
                });

            }
        };
        
        $scope.typeSelect1 = function (type) {
            if (type === "group") {
                $scope.ck_div = false;
                $scope.group_div = true;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = false;
                $scope.label_value = false;
                $scope.access_div = false;
                $scope.date_div = false;

            }
            else if (type === "date") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = false;
                $scope.access_div = true;
                $scope.date_div = true;
                
            }
            else if (type === "number") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.label_value = true;
                $scope.access_div = true;
                $scope.date_div = false;
            }
            else if (type === 'blob') {
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.ck_div = true;
                $scope.group_div = false;
                $scope.label_value = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.date_div = false;
            }
            else if (type === "individual") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = true;
                $scope.label_name = false;
                $scope.access_div = false;
                $scope.label_value = false;
                $scope.date_div = false;
            }
            else if (type === "image") {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = true;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = false;
                $scope.date_div = false;
            }
            else {
                $scope.ck_div = false;
                $scope.group_div = false;
                $scope.image = false;
                $scope.addmember_div = false;
                $scope.label_name = true;
                $scope.access_div = true;
                $scope.label_value = true;
                $scope.date_div = false;
            }


        };
        $scope.toggleAddItemSelection = function toggleSelection(acc) {
            if (acc === 'private') {
                $scope.options = true;
            } else {
                $scope.options = false;
            }
        };
        
        $scope.viewAccess = function () {
            if ($scope.additem.view_access == 2)
            {
                $scope.view_password = true;
            } 
            else 
            {
                $scope.view_password = false;
            }
            if($scope.additem.view_access == 3)
            {
                $scope.view_allowed_group = true;
            }
            else
            {
                $scope.view_allowed_group = false;
            }
        };
        
        $scope.editAccess = function () {
            if ($scope.additem.edit_access == 2)
            {
                $scope.edit_password = true;
            } else {
                $scope.edit_password = false;
            }
            if($scope.additem.edit_access == 3)
            {
                $scope.edit_allowed_group = true;
            }
            else
            {
                $scope.edit_allowed_group = false;
            }
        };
        //file upload function for additem group start
        $scope.$watch('files', function () {
            $scope.upload($scope.files);
        });
        var filee;
        $scope.upload = function (files) {
            if (files && files.length) {
                for (var i = 0; i < files.length; i++) {
                    filee = files[i];
                    alert(filee);
                }
            }
        };
        
        $scope.added_image = null; // variable to name of uploaded file 
        // file upload ends
        
        // for adding items in Group  
        $scope.doAddItem = function (addItem) 
        {
            if(addItem.type === 'image')
            {
                 $upload.upload({
                    url: 'dataapi/uploadImage',
                    data: { location: '../img/group_images/'},
                    file: filee,
                  }).success(function(data, status, headers, config) {
                    // file is uploaded successfully
                    console.log('file upload'+JSON.stringify(data));
                        if(data.type == 'error')
                        {
                             Data.toast(data);
                        }
                        else
                        {
                            $scope.added_image = data.file_name;
                            Data.post('addGroupItem', {
                                    added_by_user_id: $rootScope.userid,
                                    added_to_group_id: $routeParams.group_id,
                                    type: addItem.type,
                                    label: addItem.labelname,
                                    fvalue: addItem.fvalue,
                                    file_name : 'img/group_images/'+$scope.added_image,
                                    access_key: addItem.passkey,
                                    access_by: addItem.accessby,
                                    ck_data: addItem.ck_data,
                                    access_to: addItem.access_private,
                                    date_time: addItem.date_time,
                                    increment_by: addItem.increment_by,
                                    increment_when: addItem.increment_when,
                                    grouplabel: addItem.grouplabel,
                                    is_private :addItem.is_private,
                                    member: addItem.member,
                                    membername: addItem.membername,
                                    email: addItem.email,
                                    view_access: addItem.view_access,
                                    view_passkey: addItem.view_passkey,
                                    view_group: addItem.view_group,
                                    edit_access: addItem.edit_access,
                                    edit_passkey :addItem.edit_passkey,
                                    edit_group :addItem.edit_group
                                    
                                }).then(function (results) {
                                    //console.log('groups itemddd ' + JSON.stringify(results));
                                    if (results.type === 'success') {
                                        ngDialog.close();
                                        Data.toast(results);
                                        $scope.Reset();
                                        
                                    }
                                    else
                                    {
                                        Data.toast(results);
                                    }
                                });
                        }
                  }).error(function(data, status, headers, config) {
                    // file is uploaded successfully
                    console.log('errordd '+JSON.stringify(data));
                  });
            }
            else
            { 
                Data.post('addGroupItem', {
                    added_by_user_id: $rootScope.userid,
                    added_to_group_id: $routeParams.group_id,
                    type: addItem.type,
                    label: addItem.labelname,
                    fvalue: addItem.fvalue,
                    access_key: addItem.passkey,
                    access_by: addItem.accessby,
                    ck_data: addItem.ck_data,
                    access_to: addItem.access_private,
                    date_time: addItem.date_time,
                    increment_by: addItem.increment_by,
                    increment_when: addItem.increment_when,
                    grouplabel: addItem.grouplabel,
                    is_private :addItem.is_private,
                    member: addItem.member,
                    membername: addItem.membername,
                    email: addItem.email,
                    view_access: addItem.view_access,
                    view_passkey: addItem.view_passkey,
                    view_group: addItem.view_group,
                    edit_access: addItem.edit_access,
                    edit_passkey :addItem.edit_passkey,
                    edit_group :addItem.edit_group
                    
                }).then(function (results) {
                    console.log('groups itemddd ' + JSON.stringify(results));
                    if (results.type === 'success') {
                        ngDialog.close();
                        Data.toast(results);
                        $window.location.reload();
                        
                    }
                    else
                    {
                        Data.toast(results);
                    }
                });
            }
        };
        
        // View Item in group. Items with password or general right now for login only
        $scope.viewItem = function (viewItemWithId) {
                //console.log(viewItemWithId.view_access );
            if(viewItemWithId.view_access == 2)
            {
                ngDialog.open({
                    template: 'partials/viewGroupItemPass.html',
                    controller: 'GroupCtrl',
                    data: {
                        field_type      : viewItemWithId.field_type,
                        field_value     : viewItemWithId.field_value,
                        field_label     : viewItemWithId.field_name,
                        field_id        : viewItemWithId.field_id,
                        view_access     : viewItemWithId.view_access
                    }
                });
            }
            else
            {
                ngDialog.open({
                    template: 'viewGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type      : viewItemWithId.field_type,
                        field_value     : viewItemWithId.field_value,
                        field_label     : viewItemWithId.field_name,
                        field_id        : viewItemWithId.field_id
                    }
                });
            }
            //if (angular.isDefined($rootScope.userid)) {
                    // DO NOT DELETE THIS CONDITION YET TO USE IT                
            //}
        };
        // for group item edit
        	
        /** Functin to call dropdown change event START **/
        $scope.Select = function (selected) {
            $scope.typeSelect1(selected);
        }
        /** END **/
        // edit item select function 
         $scope.editItem = function (editItemWithId) {
            //var type = $scope.field_type;
            var type = editItemWithId.field_type;
            //if (angular.isDefined($rootScope.userid)) { // DONOT DELETE yet to use it.


            if (type === "date") {
                $scope.count = 0;
                ngDialog.open({
                    template: 'editGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type: editItemWithId.field_type,
                        date_time: new Date(editItemWithId.field_value.substring(0, 10)),
                        field_label: editItemWithId.field_name,
                        field_id: editItemWithId.field_id,
                        view_access: editItemWithId.view_access,
                        edit_access: editItemWithId.edit_access
                    }
                });
            }
            else if (type === "number") {

                ngDialog.open({
                    template: 'editGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type: editItemWithId.field_type,
                        field_value: editItemWithId.field_value,
                        field_label: editItemWithId.field_name,
                        field_id: editItemWithId.field_id,
                        view_access: editItemWithId.view_access,
                        edit_access: editItemWithId.edit_access
                    }
                });
            }
            else if (type === 'blob') {


                ngDialog.open({
                    template: 'editGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type: editItemWithId.field_type,
                        ck_data: editItemWithId.field_value,
                        field_label: editItemWithId.field_name,
                        field_id: editItemWithId.field_id,
                        edit_access: editItemWithId.edit_access
                    }
                });
            }

            else if (type === "image") {

                ngDialog.open({
                    template: 'editGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type: editItemWithId.field_type,
                        field_value: editItemWithId.field_value,
                        field_label: editItemWithId.field_name,
                        field_id: editItemWithId.field_id,
                        edit_access: editItemWithId.edit_access
                    }
                });
            }
            else {

                ngDialog.open({
                    template: 'editGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type: editItemWithId.field_type,
                        field_value: editItemWithId.field_value,
                        field_label: editItemWithId.field_name,
                        field_id: editItemWithId.field_id,
                        edit_access: editItemWithId.edit_access
                    }
                });
            }

            //}
        };
        
        // group item edit ends
        // View Item in group ends
        /** check password for view **/
         $scope.checkpasswordview = function (viewpassitem) {
            Data.post('checkpassword', {
                passkey : viewpassitem.key,
                field_id : viewpassitem.field_id ,
                type:'view' 
                }
            ).then(function (results) {
                //console.log('key ' + JSON.stringify(results.data.group_fields[0]['field_name']));
                if (results.type === 'success') 
                {
                    ngDialog.close();
                    ngDialog.open({
                    template: 'viewGroupItem',
                    controller: 'GroupCtrl',
                    data: {
                        field_type      : results.data.group_fields[0]['field_type'],
                        field_value     : results.data.group_fields[0]['field_value'],
                        field_label     : results.data.group_fields[0]['field_name'],
                        field_id        : results.data.group_fields[0]['field_id']
                    }
                    });
                }
                else
                {
                    Data.toast(results);
                }
            });
        };
        /** check password for view ends **/
        /** for updating item in group **/
        
        // for adding items in Group  
        $scope.updateGroupItem = function (updateItem) {
            Data.post('updateGroupItem', {
                added_by_user_id: $rootScope.userid,
                added_to_group_id: $routeParams.group_id,
                type: updateItem.type,
                label: updateItem.labelname,
                fvalue: updateItem.fvalue,
                access_key: updateItem.passkey,
                access_by: updateItem.accessby,
                ck_data: updateItem.ck_data,
                access_to: updateItem.access_private,
                date_time: updateItem.date_time,
                increment_by: updateItem.increment_by,
                increment_when: updateItem.increment_when,
                grouplabel: updateItem.grouplabel,
                is_private :updateItem.is_private,
                member: updateItem.member,
                membername: updateItem.membername,
                email: updateItem.email,
                view_access: updateItem.view_access,
                view_passkey: updateItem.view_passkey,
                edit_access: updateItem.edit_access,
                edit_passkey :updateItem.edit_passkey
            }).then(function (results) {
                console.log('groups itemddd ' + JSON.stringify(results));
                //$window.location.reload();
                if (results.type === 'success') 
                {
                    Data.toast(results);
                    ngDialog.close();
                    $scope.Reset();
                    
                }
                else
                {
                    Data.toast(results);
                }
            });
        };
        /** update ends **/
        /** uddate group profile **/
        $scope.UpdateGroupProfile = function(item)
        {
            Data.post('updateGroupProfile',{
                group_id : item.group_id,
                group_name: item.group_name,
                group_title: item.group_title,
                pass_key: item.pass_key,
                access: item.access,
                
            }).then(function (results)
            {
                if(results.type === 'success')
                {
                    Data.toast(results);
                    $scope.Reset();
                }
                else
                {
                    Data.toast(results);
                }
            });
        };
        /** group profile update ends**/
        
        /** delete item **/
        $scope.DeleteGroupItem = function (deleteitem) {
            console.log('groups itemddd ' + JSON.stringify(deleteitem));

            Data.post('deleteGroupItem', {
                added_by_user_id: $rootScope.userid,
                added_to_group_id: $routeParams.group_id,
                type: deleteitem.type,
                field_id: deleteitem.field_id


            }).then(function (results) {
                //console.log('groups itemddd ' + JSON.stringify(results));
                if (results.type === 'success') {
                    Data.toast(results);
                }
                else {
                    Data.toast(results);
                }
            });
        };
        /** delete ends **/
        
        /** generate random passkeys **/
        $scope.generatePassKey = function () 
        {
            Data.post('randomPassKey').then(function(results)
            {
                if(results.type === 'success')
                {
                    $scope.item.pass_key = results.data;
                }
                else
                {
                    Data.toast(results);
                }
            });  
          
        };
        /** ends passkey **/
        
        
        
        
        
    } ]);