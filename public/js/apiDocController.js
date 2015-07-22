var spins = [
    "←↖↑↗→↘↓↙",
    "▁▃▄▅▆▇█▇▆▅▄▃",
    "▉▊▋▌▍▎▏▎▍▌▋▊▉",
    "▖▘▝▗",
    "┤┘┴└├┌┬┐",
    "◢◣◤◥",
    "◰◳◲◱",
    "◴◷◶◵",
    "◐◓◑◒",
    "|/-\\",
    [
        '*...........',
        '.*..........',
        '..*.........',
        '...*........',
        '....*.......',
        '.....*......',
        '......*.....',
        '.......*....',
        '........*...',
        '.........*..',
        '..........*.',
        '...........*',
        '..........*.',
        '.........*..',
        '........*...',
        '.......*....',
        '......*.....',
        '.....*......',
        '....*.......',
        '...*........',
        '..*.........',
        '.*..........',
    ]
];

var spin = spins[9];
var i=0;

setInterval(function() {
    i = i==spin.length-1 ? 0 : ++i;
    jQuery('.spinner').text(spin[i]);
},200);

angular.module('myApp', ['ngStorage', 'ngSanitize'])
    .config(function ($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    })
    .config(function($interpolateProvider){
        $interpolateProvider.startSymbol('%%').endSymbol('%%');
    })

    .filter('apiGroupFilter', function () {
        return function(apis, group) {
            return apis.filter(function (api) {
                return api.group == group.name;
            });
        }
    })
    .controller('requestController', function($scope, $http, $rootScope, $sessionStorage) {
        // Model for storing necessary data to make the request
        $scope.request = {
            path: '',
            method: '',
            routeParams: {},
            params: {}
        }

        $scope.$watch(function(scope) {return scope.handler; }, function() {
            if (!$scope.handler || $scope.request.params.length) return;

            $scope.request.routeParams = $sessionStorage[$scope.handler].routeParams;
            $scope.request.params = $sessionStorage[$scope.handler].params;
        });

        $scope.response = {
            status: false,
            body: ''
        }

        $scope.doRequest = function() {
            // store the request so future page loads has data
            $sessionStorage[$scope.handler] = $scope.request;

            var realPath = (function(r) {
                var realPath = '/'+r.path;

                // move user params into route
                angular.forEach(r.routeParams, function(value, param) {
                    realPath = realPath.replace('{'+param.trim()+'}', value);
                });

                // Make all route params required
                if (realPath.indexOf('{') > 0) {
                    alert('Must enter all route variables.');
                    return;
                }

                r.method = r.method.toLowerCase();

                if (r.method == 'get') {
                    realPath = realPath+'?'+jQuery.param(r.params);
                }

                return realPath;
            })($scope.request);


            $scope.makingRequest = true;
            var dotdotdot = function(count) {
                if (!$scope.makingRequest) {
                    $scope.sendingStr = false;
                    return;
                }

                if (count % 6) count = 0; // Reset dots after certain point

                // Build the string with dots
                $scope.sendingStr = 'Sending';
                for(var x=1; x < count; x++) {
                    $scope.sendingStr += '.';
                }

                dotdotdot(count + 1);
            };
            // fix up the array types to not have blanks and not be keyed
            angular.forEach($scope.request.params, function(value, key) {
                if (typeof value == 'number' || typeof value == 'string') return;

                $scope.request.params[key] = [];

                angular.forEach(value, function(innerVal) {
                    if (!innerVal.length) return; // skip empty

                    $scope.request.params[key].push(innerVal);
                });
            });

            $scope.inRequest = true;
            // make the request
            $http[$scope.request.method](realPath, $scope.request.params)
                .success(function (res, code) {
                    if (typeof res == 'string' && res.indexOf('<html') > -1) {
                        $scope.response.body = res;
                    } else {
                        $scope.response.body = library.json.prettyPrint(res);
                    }
                    $scope.response.status = code;

                    $scope.makingRequest = false;
                })
                .error(function(res, code) {
                    if (typeof res == 'string' && res.indexOf('<html') > -1) {
                        $scope.response.body = res;
                    } else {
                        $scope.response.body = library.json.prettyPrint(res);
                    }

                    $scope.response.status = code;

                    $scope.makingRequest = false;
                })
                .finally(function() {
                    $scope.inRequest = false;
                });
        }

    })
    .controller('apiDocController', function ($scope, $http, $sce, $sessionStorage) {
        $scope.apis = [];
        $scope.apiGroups = [];
        $scope.apiRouteModels = [];

        $scope.currentHandler = (function() {
            var parts = window.location.pathname.split('/');

            return parts[parts.length-1];
        })();

        console.log($scope.currentHandler);
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
        var fixaction = function (action, data, method) {
            if ( ~method.indexOf('GET') ) {
                action = action + '?';
            }

            angular.forEach(data, function (value, key) {
                action = action.replace(key, value);

                if ( ~method.indexOf('GET') ) {
                    action = action + key + '=' + value + '&';
                }
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

            methodwrapper(fixaction(action, data, api.httpMethod), api.httpMethod, data).success(function (data, status, headers, config) {
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

        // Local store for these before reloading to make it seem quick
        $scope.apis = $sessionStorage.apis;
        $scope.apiGroups = $sessionStorage.apiGroups;

        $http.get('/apis/data').success(function (apis) {
            $scope.apis = apis;

            angular.forEach(apis, function(api) {
                api.sort = parseInt(api.sort);
            });

            $scope.apiGroups = getGroupsFromApis();
            attachPropertiesToApis();

            $sessionStorage.apis = $scope.apis;
            $sessionStorage.apiGroups = $scope.apiGroups;
        });

        var getGroupsFromApis = function () {
            var seen = [];

            var groups = [];
            angular.forEach($scope.apis, function(api) {
                var groupObject = {name: api.group, hash: api.groupHash, groupSort:parseInt(api.groupSort)};

                if (seen.indexOf(api.group) == -1) {
                    seen.push(api.group);
                    groups.push(groupObject);
                }
            });

            return groups;
        };

        var attachPropertiesToApis = function () {
            // TODO: refacor this to be done when api is expanded?
            angular.forEach($scope.apis, function(api) {
                api.properties = [];

                // make url parameters an input
                api.path.replace(/\{(\w+?)\}/g, function (match, key) {
                    var element = {
                        'name': match,
                        'restrictions': ['required'],
                        'isrequired': ' required="required" ',
                        'type': 'text',
                        'class': ''
                    };

                    if (api.urlIdMap[match]) {
                        element.datamap = api.urlIdMap[match];
                    }

                    api.properties.push(element);

                    return match;
                });

                angular.forEach(api.inputProps, function (aboutProperty, property) {
                    api.visible = false;

                    var restrictions = aboutProperty[0];

                    var element = {
                        'name': property,
                        'isrequired' : restrictions.indexOf('required') != -1 ? ' required="required" ' : '',
                        'type' : 'text',
                        'class': '',
                        'description': aboutProperty[1] || ''
                    };

                    // link the restrictions
                    if (restrictions.length) {
                            element.restrictions = restrictions.split('|'),

                            angular.forEach(element.restrictions, function (restriction, key) {
                                var normalized = restriction.split(':')[0];
                                element.restrictions[key] = '<a target="_blank" href="http://laravel.com/docs/4.2/validation#rule-'+normalized+'">'+restriction+'</a>';
                                element.restrictions[key] = $sce.trustAsHtml(element.restrictions[key]);
                            });
                    }

                    // figure out the type for the input
                    if ( ~restrictions.indexOf('integer') ) element.type = 'number';
                    if ( ~restrictions.indexOf('min:') ) element.type = 'number';
                    if ( ~restrictions.indexOf('max:') ) element.type = 'number';
                    if ( ~restrictions.indexOf('date') ) element.type = 'date';
                    if ( ~restrictions.indexOf('before:') ) element.type = 'date';
                    if ( ~restrictions.indexOf('after:') ) element.type = 'date';

                    if ( ~restrictions.indexOf('required') ) element.class += ' required';

                    api.properties.push(element);
                });
            });
        };
    });
    
