@extends(Config::get('api.layout-override', 'ApiGenerator::layouts.default'))

@section('content')
    <div ng-app="myApp">
        <h2>Api Docs</h2>
        <ul class="api-groups" ng-controller="apiDocController">
        <li class="api-group" ng-repeat="apiGroup in apiGroups" ><span class="noselect" slide-toggle="#group-{{ apiGroup.hash }}" ng-bind="apiGroup.name" ng-s></span>
            <ul class="apis slideable" id="group-{{ apiGroup.hash }}" duration="200ms">
                <li ng-repeat="api in apis | apiGroupFilter:apiGroup">
                    <div class="noselect api {{ api.httpMethod | lowercase }}">
                        <div class="header" slide-toggle="#a{{api.hash}}">
                            <span class="method" ng-bind="api.httpMethod"></span>
                            <span class="path" ng-bind="api.path"></span>
                            <span class="description match-color" ng-bind="api.title"></span>
                            <span class="handler" ng-bind="api.handler"></span>
                        </div>

                        <div class="body slideable" id="a{{api.hash}}" duration="200ms">
                            <form ng-submit="apisubmit(api)" ng-model="form" >
                                <table border="0" ng-show="api.properties.length">
                                    <tr><th>Name<th class="value-col">Value<th>Restrictions<th>Raw
                                    <th><span class="eg-response" ng-show="!api.results.raw">Example </span>Response <span class="response-code-{{(api.results.code.code).toString().charAt(0)}}" ng-bind=" api.results.code.code" ng-show="api.results.code.code">&nbsp;</span></th>

                                    <tr class="row-{{ $index + 1 }}" ng-repeat="input in api.properties">
                                        <td ng-bind="input.name">
                                        <td class="inputs">
                                            <select title="{{ input.description }}" ng-if=" input.datamap.length " ng-model="input.selected" name="{{ input.name }}"><option ng-repeat="option in input.datamap" value="{{ option.key }}" ng-bind="option.value"></option></select>
                                            <input title="{{ input.description }}" ng-if=" !input.datamap.length " type="{{ input.type }}" class="{{ input.class }}" name="{{ input.name }}" {{ input.isrequired }} ng-model="api.form[input.name]" />
                                            <div  class="error" ng-show="input.error" ng-bind="input.error">&nbsp;</div>
                                        <td><ul class="restrictions"><li ng-repeat="restriction in input.restrictions" ng-bind-html="restriction">
                                        <td ng-if="$first" class="raw-data match-color" 
                                        rowspan="{{ api.properties.length + 4 }}" ><textarea tabindex="-1" clip-copy="renderForm(api) " class="match-color" rows="{{ (api.properties.length) + 6 }}"  ng-bind="renderForm(api)"></textarea>

                                        <td ng-show="!api.results.raw" ng-if="$first" class="response" rowspan="{{ api.properties.length + 4 }}" >
                                        <textarea tabindex="-1" class="match-color" rows="{{ (api.properties.length) + 6 }}" ng-bind="api.response"></textarea></td>
                                        
                                        <td ng-show="api.results.raw" ng-if="$first" class="response" rowspan="{{ api.properties.length + 4 }}" >
                                        <textarea tabindex="-1" class="match-color" rows="{{ (api.properties.length) + 6 }}" ng-bind="api.results.raw"></textarea></td>
                                    </tr>

                                </table>
                                <div class="submit"> <input type="submit" value="Send Request" /></div>
                            </form>
                            <div class="results" ng-show="api.results">
                                    <h5 class="match-color">Response Code</h5>
                                    <div class="results-data"><pre class="code">
                                        <div ng-bind="(api.results.code.code) + ':' + (api.results.code.phrase)" class="short response-code-{{api.results.code.code}} response-code-{{(api.results.code.code).toString().charAt(0)}}"></div>
                                        <div class="description" ng-bind="api.results.code.description"></div>
                                    </pre></div>
        
                                    <h5 class="match-color">Request URL</h5>
                                    <div class="results-data"><pre ng-bind="api.results.url"></pre></div>
        
                                </div>
                            </div>
                        </div>
            </ul>
        </li>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ngStorage/0.3.5/ngStorage.min.js"></script>
    <script src="/packages/devnullsoftware/api-generator/js/apiDocController.js"></script>
@stop
