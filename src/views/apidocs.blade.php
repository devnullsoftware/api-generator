@extends('ApiGenerator::layouts.default')

@section('content')
    <script type="text/javascript" src="/packages/devnullsoftware/api-generator/js/json-pretty.js"></script>

    <div class="col-sm-6 col-sm-offset-3 col-md-9 col-md-offset-2 main" ng-controller="requestController">
        <div>
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
        </div>

        <hr />
        <div class="col-md-4">


            <pre id="response"><h3 class="flush-top">Request Response <small><span class="spinner" ng-show="inRequest"></span><span ng-bind="response.status" ng-if="response.status && !inRequest"></span></small></h3><span ng-bind="sending" ng-if="makingRequest"></span><code ng-if="!makingRequest" ng-bind-html="response.body"></code></pre>

            <pre id="response"><h3 class="flush-top">Example Response</h3><code></code></pre>
            <script type="text/javascript">jQuery('#response code').html(library.json.prettyPrint(JSON.parse(<?=json_encode($api->response) ?>)))</script>

        </div>
        <form ng-submit="doRequest()">
            <div class="col-md-8 the-request">
            <button class="btn btn-primary btn-request">&#8678; Try A Request</button>
            <br/>
            <br/>
            @if($api->inputProps)
                <div >
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
                                    <td style="width: 250px;">
                                        @if(substr_count($validators, 'array'))
                                            @foreach([0, 1,2,3,4] as $num)
                                                <input class="array-input form-control" ng-model="request.params.{{$name}}[{{$num}}]" type="text" name="{{$name}}[{{$num}}]" ng-if="{{$num}} == 0 || request.params.{{$name}}[{{$num-1}}]"/>
                                            @endforeach
                                        @elseif(substr_count($validators, 'password'))
                                            <input ng-model="request.params.{{$name}}" type="password" name="{{$name}}" class="form-control"/>
                                        @elseif(substr_count($validators, 'boolean'))
                                            <select ng-model="request.params.{{$name}}" name="{{$name}}" class="form-control">
                                                <option></option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        @else
                                            <input ng-model="request.params.{{$name}}" type="text" name="{{$name}}" class="form-control"/>
                                        @endif

                                    </td>
                                    <td>{{$validators}}</td>
                                    <td>{{$description}}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        </form>

    </div>
@stop

