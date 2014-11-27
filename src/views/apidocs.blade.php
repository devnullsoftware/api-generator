@extends('ApiGenerator::layouts.default')

@section('content')
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
                            <h3 class="match-color" ng-show=" api.properties.length ">Properties</h3>
                            
                            <form ng-submit="apisubmit(api)" ng-model="form" >
                                <table border="0" ng-show="api.properties.length">
                                    <tr><th>Name<th>Value<th>Restrictions<th>&nbsp;<th>Raw</tr>

                                    <tr class="row-{{ $index + 1 }}" ng-repeat="input in api.properties">
                                        <td ng-bind="input.name">
                                        <td>
                                            <select ng-if="input.datamap" ng-model="input.selected"><option ng-repeat="option in input.datamap" value="{{ option.key }}" ng-bind="option.value"></option></select>
                                            <input ng-if=" ! (input.datamap)" type="text" name="{{ input.name }}" {{ input.isrequired }} ng-model="api.form[input.name]" />
                                        <td><ul class="restrictions"><li ng-repeat="restriction in input.restrictions" ng-bind="restriction">
                                        <td  class="error" ng-bind="input.error" ng-show="input.error">&nbsp;</td>
                                        <td ng-if="$first" class="raw-data match-color" 
                                        rowspan="{{ api.properties.length + 3 }}" ><textarea tabindex="-1" clip-copy="renderForm(api) " class="match-color" rows="{{ (api.properties.length) + 6 }}"  ng-bind="renderForm(api)"></textarea>
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
        
                                    <h5 class="match-color">Response</h5>
                                    <div class="results-data"><pre ng-bind="api.results.raw"></pre></div>
        
                                </div>
                            </div>
                        </div>
            </ul>
        </li>
    </ul>
@stop

@section('angular-scripts')
    @parent
    <script src="/devnullsoftware/api-generator/js/ngClip.js"></script>
    <script src="/devnullsoftware/api-generator/js/status-codes.js"></script>
    <script src="/devnullsoftware/api-generator/js/apiDocController.js"></script>
    <script src="/devnullsoftware/api-generator/js/zeroclipboard/ZeroClipboard.min.js"></script>
@stop