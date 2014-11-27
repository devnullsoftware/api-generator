angular.module('myApp', ['ngClipboard', 'angularSlideables'])
    .config(function ($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    })

    .filter('apiGroupFilter', function () {
        return function(apis, group) {
            return apis.filter(function (api) {
                return api.group == group.name;
            });
        }
    })

    .controller('apiDocController', function ($scope, $http) {
        $scope.apis = [];
        $scope.apiGroups = [];
        $scope.apiRouteModels = [];

        /**
         * Used by UI to display a json object of the data to submit
         * @param api
         * @returns {string}
         */
        $scope.renderForm = function(api) {
            var dataDup = JSON.parse(JSON.stringify(api.form || {}));

            angular.forEach(dataDup, function (value, key) {
                if (key.indexOf('}') > 1) {
                    delete dataDup[key];
                }
            });

            return JSON.stringify(dataDup).replace(/\{/g, "{\n    ").replace(/,/g, ",\n    ").replace(/\}/g, "\n}");
        };

        /**
         * Helper to make http cleaner
         * @param action
         * @param method
         * @param data
         * @returns {*}
         */
        var methodwrapper = function (action, method, data) {
            data = data || {};

            switch (method) {
                case 'GET' :
                    return $http.get(action);
                case 'DELETE' :
                    return $http.delete(action);
                case 'POST' :
                    return $http.post(action, data);
                case 'PUT' :
                    return $http.put(action, data);
            }
        }

        /**
         * Make the action have real variables.
         * 
         * @param action
         * @param data
         * @returns {*}
         */
        var fixaction = function (action, data) {
            angular.forEach(data, function (value, key) {
                action = action.replace(key, value);
            });

            return action;
        }

        $scope.apisubmit = function (api) {
            var data = api.form;

            var action = api.path;

            angular.forEach(api.properties, function (property) {
                property.error = false;

                // fix dynamic action
                if (property.urlparam) {
                    action = action.replace(property.name, property.selected);
                }
            });

            methodwrapper(fixaction(action, data), api.httpMethod, data).success(function (data, status, headers, config) {
                api.results = {
                    url: action,
                    raw: JSON.stringify(data, null, 2),
                    code: statusCode(status)
                };

            }).error(function (data, status) {
                api.results = {
                    url: action,
                    raw: JSON.stringify(data, null, 2),
                    code: statusCode(status)
                }

                // add error to form
                angular.forEach(data, function (value, key) {
                    angular.forEach(api.properties, function (property) {
                        if (property.name == key) {
                            property.error = value;
                        }
                    });
                });
            });
        };

        $http.get('/apis').success(function (apis) {
            $scope.apis = apis;

            getGroupsFromApis();
            attachPropertiesToApis();
        });

        $http.get('/apis/route-models').success(function(routeModels) {
            angular.forEach(routeModels, function (path, model) {
                $scope.apiRouteModels[model] = {
                    name: model,
                    path: path,
                    data: []
                };

                if ( path.indexOf( '{' ) == -1 ) {
                    $http.get(path).success(function (data) {
                        $scope.apiRouteModels[model].data.push(data);
                        console.log($scope.apiRouteModels);
                    });
                }
            });
        });

        var getGroupsFromApis = function () {
            var seen = [];

            angular.forEach($scope.apis, function(api) {
                var groupObject = {name: api.group, hash: api.groupHash};
    
                if (seen.indexOf(api.group) == -1) {
                    seen.push(api.group);
                    $scope.apiGroups.push(groupObject);
                }
            });
        };

        var attachPropertiesToApis = function () {
            // TODO: refacor this to be done when api is expanded?
            angular.forEach($scope.apis, function(api) {
                api.properties = [];

                angular.forEach(api.inputProps, function (restrictions, property) {
                    api.visible = false;

                    api.properties.push({
                        'name': property,
                        'restrictions': restrictions.split('|'),
                        'isrequired' : restrictions.indexOf('required') != -1 ? ' required="required" ' : ''
                    });
                });
            });
        };
    });
    
