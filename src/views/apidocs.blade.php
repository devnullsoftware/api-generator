@extends('ApiGenerator::layouts.default')

@section('content')
    <script type="text/javascript" src="/packages/devnullsoftware/api-generator/js/json-pretty.js"></script>
    <script type="text/javascript" src="/packages/devnullsoftware/api-generator/js/extras.js"></script>

    <script>
        {{--window.defaultRequestParams = {!! json_encode($json) !!};--}}
    </script>
    <div class="col-sm-6 col-sm-offset-3 col-md-9 col-md-offset-2 main" ng-controller="requestController">
        <div>
            <h1 class="page-header"><strong>{{$api->group}}\</strong> {{$api->title}} <span class="handler">{{$api->handler}}</span></h1>

            <p>{!! $api->description !!}</p>

            <h3>Path</h3>
            <input type="hidden" ng-init="request.method='{{ $api->httpMethod }}'" />
            <input type="hidden" ng-init="request.path='{{ $api->path}}'" />
            <input type="hidden" ng-init="handler='{{ $api->handler }}'" />

            <p class="method {!! strtolower($api->httpMethod) !!}"><span>{{$api->httpMethod}}</span>
                {{--Echo each part replacing tokens with an input--}}
                @foreach(explode('/', $api->path) as $part)
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
                <span class="request-type-group">
                    <label for="show-form">As Form</label>
                    <input ng-model="inputType" type="radio" checked name="toggle" id="show-form" class="toggle-input"/>

                    <label for="show-json">As Json</label>
                    <input ng-model="inputType" type="radio" checked name="toggle" id="show-json" value="json" class="toggle-input"/>
                </span>
                <br/>
                <br/>
            @if($api->inputProps)
                    <div class="user-input json hidden">
                        <script>
                            var jsonInputData = {!! json_encode($json, JSON_PRETTY_PRINT) !!};
                        </script>
                        <textarea ng-model="jsonInputData"></textarea>
                    </div>
                    <div class="user-input table-responsive">
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

                            <input type="hidden" name="isArray" ng-value="{!! intval(!empty($api->isArray)) !!}" />
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

                                    $required = substr_count($validators, 'required');
                                    $validators = preg_replace('#([\w]+?):([^|]*)#', '<span style="cursor:pointer; text-decoration:underline" title="$2">$1</span>*', $validators);

                                ?>
                                <tr>

                                    <td>
                                        {!! str_repeat('&nbsp;', substr_count($name, '.')* 4) !!} {{$name}}
                                    </td>
                                    <td style="width: 250px;" class="form-field">
                                        {{--
                                        @if(substr_count($validators, 'array'))
                                            <input
                                                    class="array-input form-control"
                                                    ng-model="request.params['{{$name}}[{{$num}}]']"
                                                    ng-class="{'required': {{$required}}}"
                                                    type="text"
                                                    name="{{$name}}[{{$num}}]"
                                                    ng-if="{{$num}} == 0 || request.params['{{$name}}[{{$num-1}}]']"
                                            />
                                            @foreach([0,1,2,3,4] as $num)
                                            
                                            @endforeach
                                        --}}
                                        @if(substr_count($name, 'password'))
                                            <input
                                                    ng-model="request.params['{{$name}}']"
                                                    ng-class="{'required': {{$required}}}"
                                                    type="password"
                                                    name="{{$name}}"
                                                    class="form-control"
                                            />
                                        @elseif(substr_count($validators, 'boolean'))
                                            <select  
                                                ng-model="request.params['{{$name}}']"
                                                ng-class="{'required': {{$required}}}" 
                                                name="{{$name}}" 
                                                class="form-control"
                                            >
                                                <option></option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        @else
                                            <input
                                                ng-model="request.params['{{$name}}']"
                                                ng-class="{'required': {{$required}}}"
                                                type="text"
                                                name="{{$name}}"
                                                class="form-control"/>
                                        @endif

                                    </td>
                                    <td>{!! $validators !!}</td>
                                    <td>{{$description}}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </form>

    </div>
@stop

