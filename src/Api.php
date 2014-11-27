<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use ReflectionClass;
use ReflectionMethod;

// TODO: add artisan artisan make:api /path/to/api
    // TODO: make resource controller without edit / create page
    // TODO: DI ApiRequest into put and post
    // TODO: Add route for api.validation to config/api.php
    // TODO: Allow for make to take a --model param
        // TODO: look for fillable fields to add to config/api.php
    // TODO: Improve and wrap ParamMatch into lib
    // TODO: request a group option
// TODO: make this an extension

class Api {
    public $path;
    public $httpMethod;
    public $controller;
    public $controllerMethod;
    public $group;
    public $title;
    public $hash;

    public $inputProps = [];
//    public $urlIdMap = [];

    public function __construct(Route $route)
    {
        if ( ! $route ) return;

        list($controller, $method) = explode('@', $route->getAction()['controller']);

        $this->handler = explode('\\', $route->getAction()['controller']);
        $this->handler = end($this->handler);
        $this->hash = md5($this->handler);

        $this->group = $this->getApiGroup($controller, $method);
        $this->groupHash = md5($this->group);

        $this->path = $route->uri();
        $this->httpMethod = $route->getMethods()[0];
        $this->controllerMethod = $method;
        $this->controller = $controller;
        $this->title = $this->getApiTitle($controller, $method);
        $this->description = $this->getApiDescription($controller, $method);

        $requestclass = $this->getRequestClass($controller, $method, $this->path, $this->httpMethod);
        $this->inputProps = $requestclass ? $requestclass->rules() : [];
//        $this->urlIdMap = $this->getUrlIdMap($controller);

    }

//    private function getUrlIdMap($controller) {
//        if (!method_exists($controller, 'urlDataMap')) {
//            // TODO: remove this in favor of implementing some api request class
//            return array();
//        }
//
//        return array_map(function ($datamap) {
//            list($class, $keyField, $valueField) = $datamap;
//            $updateOn = empty($datamap[3]) ? array() : $datamap[3];
//
//            return array_map(function ($obj) use ( $keyField, $valueField, $updateOn ) {
//                return [
//                    'key' => $obj[$keyField],
//                    'value' => $obj[$valueField],
//                    'updateon' => $updateOn
//                ];
//            }, $class::all()->toArray());
//
//        }, $controller::urlDataMap());
//    }

    private function getApiDescription($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match('!@ApiDescription (.*)!i', $docblock, $match);
        return trim(end($match)) ?: '';
    }

    private function getApiTitle($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match('!@ApiTitle (.*)!i', $docblock, $match);
        return trim(end($match)) ?: '';
    }

    private function getApiGroup($controller, $method)
    {
        $docblock = (new ReflectionClass($controller))->getDocComment();

        preg_match('!\@ApiGroup (.*)!i', $docblock, $match);
        return trim(end($match)) ?: 'Uncategoriezed';
    }

    /**
     * @param $controller
     * @param $method
     * @param $path
     * @param $httpMethod
     * @return FormRequest|false
     */
    private function getRequestClass($controller, $method, $path, $httpMethod)
    {
        // get the validator if exists
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach($reflectionMethod->getParameters() as $param)
        {
            $rclass = $param->getClass();
            if ( ! empty($rclass->name) && stripos($rclass->name, 'request'))
            {
                return new $rclass->name($path, $httpMethod);
            }
        }

        return false;
    }
}