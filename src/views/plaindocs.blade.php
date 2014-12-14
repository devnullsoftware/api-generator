@extends(Config::get('api.layout-override', 'ApiGenerator::layouts.default'))

@section('content')
    <div ng-app="myApp" class="plain-docs">
        <h1>Api Docs</h1>
        <ul class="api-groups" ng-controller="apiDocController">
            <li ng-repeat="apiGroup in apiGroups" ><h5 ng-bind="apiGroup.name"></h5>
                <table>
                    <tr><th>Method<th>Route<th>Title<th>Request Properties<th>Example Response<th>Description
                    <tr ng-repeat="api in apis | apiGroupFilter:apiGroup">
                        <td ng-bind="api.httpMethod">&nbsp;</td>
                        <td ng-bind="api.path">&nbsp;</td>
                        <td ng-bind="api.title">&nbsp;</td>
                        <td>
                            <table ng-show="api.properties.length">
                                <tr><th>Property<th>Description<th>Restrictions
                                <tr ng-repeat="input in api.properties">
                                    <td ng-bind="input.name">&nbsp;
                                    <td ng-bind="intput.description">&nbsp;
                                    <td>
                                        <ul><li ng-repeat="restriction in input.restrictions" ng-bind-html="restriction">
                            </table>
                        </td>
                        <td><textarea class="api-response" ng-show="api.response.length" ng-bind="api.response" rows="5"></textarea>&nbsp;
                        <td ng-bind="api.description">&nbsp;
                    </tr>
                </table>
        </ul>
    </div>
@stop

@section('header-scripts')
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>

    @if(Config::get('app.debug'))
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.js"></script>
    @else
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular.min.js"></script>
    @endif

    <script src="/packages/devnullsoftware/api-generator/js/main.js"></script>
    <link rel="stylesheet" type="text/css" href="/packages/devnullsoftware/api-generator/css/main.css?v=1">
    
    <script src="/packages/devnullsoftware/api-generator/js/ngClip.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/status-codes.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/zeroclipboard/ZeroClipboard.min.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/apiDocController.js"></script>
@stop
