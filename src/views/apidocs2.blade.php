@extends('ApiGenerator::layouts.v2')

@section('content')
    <script type="text/javascript" src="/packages/devnullsoftware/api-generator/js/json-pretty.js"></script>

    <style media="screen" type="text/css">
        pre {
            background-color: ghostwhite;
            border: 1px solid silver;
            padding: 10px 20px;
            margin: 20px;
        }
        .json-key {
            color: brown;
        }
        .json-value {
            color: navy;
        }
        .json-string {
            color: olive;
        }
    </style>
    <div>
        <div ng-controller="requestController" class="col-sm-6 col-sm-offset-3 col-md-7 col-md-offset-1 main">
            <h1 class="page-header"><strong>{{$api->group}}\</strong> {{$api->title}}</h1>

            <p>{{$api->description}}</p>

            <h3>Path</h3>
            <input type="hidden" ng-init="request.method='{{ $api->httpMethod }}'"></input>
            <input type="hidden" ng-init="request.path='{{ $api->path}}'" />
            <input type="hidden" ng-init="handler='{{ $api->handler }}'" />

            <p class="method {!! strtolower($api->httpMethod) !!}"><span>{{$api->httpMethod}}</span>
                {{--Echo each part replacing tokens with an input--}}
                @foreach(explode('/', $api->path) as $part)
                    /
                    @if(!substr_count($part, '{'))
                        {{ $part }}
                    @else
                        <input type="text" ng-model="request.routeParams.{!! str_replace(['{','}'], ['',''], $part) !!}" required class="form-control form-param" placeholder="{{ $part }}" />
                    @endif
                @endforeach
            </p>

            <h3>Controller</h3>
            <p>{{$api->handler}}</p>

            <h3 class="sub-header">Parameters</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Field Name</th>
                        <th></th>
                        <th>Validators</th>
                        <th>Description</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($api->inputProps as $name => list($validators, $description))
                        <tr>
                            <td>{{$name}}</td>
                            <td><input ng-model="request.params.{{$name}}" type="text" name="{{$name}}"/></td>
                            <td>{{$validators}}</td>
                            <td>{{$description}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary" ng-click="doRequest()">Make The Request</button>
            </div>

            @if(!empty($api->errorResponses))
                <h3 class="sub-header">Error Responses</h3>

                <div class="table-resposneive">
                    <table class="table table-striped">
                        <thead>
                        <th>HTTP Status Code</th>
                        <th>Reason</th>
                        </thead>
                        <tbody>
                        @foreach([] as $api->errorResponses)
                            <td></td>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div ng-controller="responseController" class="col-md-4">

        <h2>Request Response</h2>
        <p class="response-status" ng-if="response.status">Response Status: <span ng-bind="response.status"></span></p>
        <pre id="response"><code ng-bind-html="response.body"></code></pre>

        <h2>Example Response</h2>
        <pre id="response"><code></code></pre>
        <script type="text/javascript">jQuery('#response code').html(library.json.prettyPrint(JSON.parse(<?=json_encode($api->response) ?>)))</script>

    </div>
    </div>
@stop

