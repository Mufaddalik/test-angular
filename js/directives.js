 churchmembersApp.directive('focus', function() {
    return function(scope, element) {
        element[0].focus();
    };      
});
//churchmembersApp.directive('animateMe', function() {
//   return function(scope, element, attrs) {
//      scope.$watch(attrs.animateMe, function() {
//         element.show(300).delay(900)
//      })
//   }
//});

churchmembersApp.directive('uiDate', function() {
    return {
      require: '?ngModel',
      link: function($scope, element, attrs, controller) {
        var originalRender, updateModel, usersOnSelectHandler;
        if ($scope.uiDate == null) $scope.uiDate = {};
        if (controller != null) {
          updateModel = function(value, picker) {
            return $scope.$apply(function() {
              return controller.$setViewValue(element.datepicker("getDate"));
            });
          };
          if ($scope.uiDate.onSelect != null) {
            usersOnSelectHandler = $scope.uiDate.onSelect;
            $scope.uiDate.onSelect = function(value, picker) {
              updateModel(value);
              return usersOnSelectHandler(value, picker);
            };
          } else {
            $scope.uiDate.onSelect = updateModel;
          }
          originalRender = controller.$render;
          controller.$render = function() {
            originalRender();
            return element.datepicker("setDate", controller.$viewValue);
          };
        }
        return element.datepicker($scope.uiDate);
      }
    };
  });


churchmembersApp.directive('ngThumb', ['$window', function($window) {
        var helper = {
            support: !!($window.FileReader && $window.CanvasRenderingContext2D),
            isFile: function(item) {
                return angular.isObject(item) && item instanceof $window.File;
            },
            isImage: function(file) {
                var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
            }
        };

        return {
            restrict: 'A',
            template: '<canvas/>',
            link: function(scope, element, attributes) {
                if (!helper.support) return;

                var params = scope.$eval(attributes.ngThumb);

                if (!helper.isFile(params.file)) return;
                if (!helper.isImage(params.file)) return;

                var canvas = element.find('canvas');
                var reader = new FileReader();

                reader.onload = onLoadFile;
                reader.readAsDataURL(params.file);

                function onLoadFile(event) {
                    var img = new Image();
                    img.onload = onLoadImage;
                    img.src = event.target.result;
                }

                function onLoadImage() {
                    var width = params.width || this.width / this.height * params.height;
                    var height = params.height || this.height / this.width * params.width;
                    canvas.attr({ width: width, height: height });
                    canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
                }
            }
        };
    }]);
churchmembersApp.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            
            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);

churchmembersApp.directive('passwordMatch', [function () {
    return {
        restrict: 'A',
        scope:true,
        require: 'ngModel',
        link: function (scope, elem , attrs,control) {
            var checker = function () {
 
                //get the value of the first password
                var e1 = scope.$eval(attrs.ngModel); 
 
                //get the value of the other password  
                var e2 = scope.$eval(attrs.passwordMatch);
                if(e2!=null)
                return e1 == e2;
            };
            scope.$watch(checker, function (n) {
 
                //set the form control to valid if both 
                //passwords are the same, else invalid
                control.$setValidity("passwordNoMatch", n);
            });
        }
    };
}]);

churchmembersApp.directive('ckEditor', [function () {
    return {
        require: '?ngModel',
        restrict: 'C',
        link: function (scope, elm, attr, model) {
            var isReady = false;
            var data = [];
            var ck = CKEDITOR.replace(elm[0],{
                allowedContent: true
            });

            function setData() {
                if (!data.length) {
                    return;
                }

                var d = data.splice(0, 1);
                ck.setData(d[0] || '<span></span>', function () {
                    setData();
                    isReady = true;
                });
            }

            ck.on('instanceReady', function (e) {
                if (model) {
                    setData();
                }
            });

            elm.bind('$destroy', function () {
                ck.destroy(false);
            });

            if (model) {
                ck.on('change', function () {
                    scope.$apply(function () {
                        var data = ck.getData();
                        if (data == '<span></span>') {
                            data = null;
                        }
                        model.$setViewValue(data);
                    });
                });

                model.$render = function (value) {
                    if (model.$viewValue === undefined) {
                        model.$setViewValue(null);
                        model.$viewValue = null;
                    }

                    data.push(model.$viewValue);

                    if (isReady) {
                        isReady = false;
                        setData();
                    }
                };
            }

        }
    };
}]);
//churchmembersApp.directive('fileUpload', function () {
//    return {
//        scope: true,        //create a new scope
//        link: function (scope, el, attrs) {
//            el.bind('change', function (event) {
//                var files = event.target.files;
//                //iterate files since 'multiple' may be specified on the element
//                for (var i = 0;i<files.length;i++) {
//                    //emit event upward
//                    scope.$emit("fileSelected", { file: files[i] });
//                }                                       
//            });
//        }
//    };
//});
//churchmembersApp.directive('ngThumb', ['$window', function($window) {
//        var helper = {
//            support: !!($window.FileReader && $window.CanvasRenderingContext2D),
//            isFile: function(item) {
//                return angular.isObject(item) && item instanceof $window.File;
//            },
//            isImage: function(file) {
//                var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
//                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
//            }
//        };
//
//        return {
//            restrict: 'A',
//            template: '<canvas/>',
//            link: function(scope, element, attributes) {
//                if (!helper.support) return;
//
//                var params = scope.$eval(attributes.ngThumb);
//
//                if (!helper.isFile(params.file)) return;
//                if (!helper.isImage(params.file)) return;
//
//                var canvas = element.find('canvas');
//                var reader = new FileReader();
//
//                reader.onload = onLoadFile;
//                reader.readAsDataURL(params.file);
//
//                function onLoadFile(event) {
//                    var img = new Image();
//                    img.onload = onLoadImage;
//                    img.src = event.target.result;
//                }
//
//                function onLoadImage() {
//                    var width = params.width || this.width / this.height * params.height;
//                    var height = params.height || this.height / this.width * params.width;
//                    canvas.attr({ width: width, height: height });
//                    canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
//                }
//            }
//        };
//    }]);
