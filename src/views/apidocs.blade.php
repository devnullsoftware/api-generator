@extends('ApiGenerator::layouts.default')

@section('content')
    <script type="text/javascript" src="/packages/devnullsoftware/api-generator/js/json-pretty.js"></script>

    <div>
        <div ng-controller="requestController" class="col-sm-6 col-sm-offset-3 col-md-7 col-md-offset-1 main">
            <h1 class="page-header"><strong>{{$api->group}}\</strong> {{$api->title}} <span class="handler">{{$api->handler}}</span></h1>

            <p>{{$api->description}}</p>

            <h3>Path</h3>
            <input type="hidden" ng-init="request.method='{{ $api->httpMethod }}'" />
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

            @if(!empty($api->responseCodes))
                <h3 class="sub-header">Possible Error Responses</h3>

                <div class="table-resposneive">
                    <table class="table table-striped">
                        <thead>
                        <th>HTTP Status Code</th>
                        <th>Reason</th>
                        </thead>
                        <tbody>
                        @foreach($api->responseCodes as $code => $message)
                            <tr>
                                <td>{{ $code }}</td>
                                <td>{{ $message }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 class="sub-header">Try A Request</h3>
            <div class="the-request">
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

                        @foreach ($api->inputProps as $name => $rules)
                            <?php
                            if (count($rules) > 1)
                            {
                                list($validators, $description) = $rules;
                            }
                            else
                            {
                                $validators = reset($rules);
                                $description = '';
                            }
                            ?>
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
                    <button class="btn btn-primary" ng-click="doRequest()">Send Request</button>
                </div>
            </div>

        </div>
        <div ng-controller="responseController" class="col-md-4">

            <h3>Request Response</h3>
            <p class="response-status" ng-if="response.status">Response Status: <span ng-bind="response.status"></span></p>
            <pre id="response"><code ng-bind-html="response.body"></code></pre>

            <h3>Example Response</h3>
            <pre id="response"><code></code></pre>
            <script type="text/javascript">jQuery('#response code').html(library.json.prettyPrint(JSON.parse(<?=json_encode($api->response) ?>)))</script>

        </div>
    </div>
@stop

