<!doctype html>
<html lang="en" ng-app="myApp">
    <head>
        <meta charset="UTF-8">
        <title>Registry</title>
        <link rel="stylesheet" type="text/css" href="/packages/devnullsoftware/api-generator/css/main.css">

        @section('angular-scripts')
            @if(Config::get('app.debug'))
                <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.js"></script>
            @else
                <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular.min.js"></script>
            @endif
            <script src="/packages/devnullsoftware/api-generator/js/main.js"></script>
        @show
    </head>
    <body>
        <div class="content">
            @yield('content')
        </div>
    </body>
</html>