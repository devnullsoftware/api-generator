<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Api Documentation</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/packages/devnullsoftware/api-generator/css/twitter-dashboard.css">
    <link rel="stylesheet" type="text/css" href="/packages/devnullsoftware/api-generator/css/main.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular-sanitize.min.js"></script>

    <script src="/packages/devnullsoftware/api-generator/js/status-codes.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/zeroclipboard/ZeroClipboard.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ngStorage/0.3.5/ngStorage.min.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/apiDocController.js"></script>
</head>
<body ng-app="myApp">

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Api Documentation</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div ng-controller="apiDocController" class="col-sm-2 col-md-1 sidebar">
            <form class="form-group-sm">
                <input type="text" class="form-control" placeholder="Search..." ng-model="search">
            </form>
            <ul class="nav nav-sidebar api-nav" ng-repeat="apiGroup in apiGroups | orderBy:'name'">
                <li role="presentation" ng-if="filtered.length"><span><h4 ng-bind="apiGroup.name"></h4></span></li>
                <li ng-if="filtered.length" ng-repeat="api in filtered = (apis | apiGroupFilter:apiGroup | orderBy:'sort' | filter:search)"><a href="/apis/v2/%%api.handler%%"><small ng-bind="api.title || api.handler"></small></a></li>
            </ul>
        </div>

        @yield('content')
    </div>
</div>
</body>
</html>