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

    public function __construct(Route $route = null)
    {
        if ( ! $route || ! array_key_exists('controller', $route->getAction()) ) return; // route will be null when not called by documentor

        list($controller, $method) = explode('@', $route->getAction()['controller']);

        $this->handler = explode('\\', $route->getAction()['controller']);
        $this->handler = end($this->handler);
        $this->hash = md5($this->handler);

        $this->group = $this->getApiGroup($controller, $method);
        $this->groupHash = md5($this->group);

        $this->sort = $this->getApiSort($controller, $method);

        $this->path = $route->uri();
        $this->httpMethod = $route->getMethods()[0];
        $this->controllerMethod = $method;
        $this->controller = $controller;
        $this->title = $this->getApiTitle($controller, $method);
        $this->description = $this->getApiDescription($controller, $method);

        $requestclass = $this->getRequestClass($controller, $method, $this->path, $this->httpMethod);
        $this->inputProps = $requestclass ? $requestclass->apiFields() : [];

        $this->response = $requestclass ? $requestclass->exampleResponse() : '';

        $this->urlIdMap = $this->getUrlIdMap($controller);

        $this->responseCodes = $this->getResponseCodes($controller, $method, !!$this->inputProps);
    }

    public function getResponseCodes($controller, $method, $hasProperties) {
        $codes = [];
        if ($hasProperties) {
            $codes = [422 => 'Missing fields or validation problems.'];
        }

        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match_all('!@ApiErrorCode(\d\d\d) (.*)!i', $docblock, $matches);

        if (empty($matches[1])) return $codes;

        return $codes += array_combine($matches[1], $matches[2]);
    }

    private function getUrlIdMap($controller) {
        if (!method_exists($controller, 'urlDataMap')) {
            // TODO: remove this in favor of implementing some api request class
            return [];
        }

        $items = array_map(function ($datamap) {
            list($class, $keyField, $valueField) = $datamap;

            $updateOn = empty($datamap[3]) ? [] : $datamap[3];

            $item = [];
            foreach ($class::all() as $obj) {
                $item[] = [
                    'key' => $obj->$keyField,
                    'value' => $obj->$valueField,
                    'updateon' => $updateOn
                ];
            }
            return $item;

        }, $controller::urlDataMap());

        return array_combine(array_keys($controller::urlDataMap()), $items);
    }

    private function getApiSort($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match('!@ApiSort (.*)!i', $docblock, $match);
        return trim(end($match)) ?: '';
    }

    private function getApiDescription($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        // TODO: Make this smarter about more lines
        preg_match('!@ApiDescription (.+?)[@]!is', $docblock, $match);

        return str_replace('*', '', trim(end($match))) ?: '';
    }

    private function getApiTitle($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match('!@ApiTitle (.*)!i', $docblock, $match);
        return trim(end($match)) ?: '';
    }

    private function getApiGroup($controller, $method)
    {
        $docblock = (new \ReflectionMethod($controller, $method))->getDocComment();

        preg_match('!@ApiGroup (.*)!i', $docblock, $match);

        $group = trim(end($match));

        if (!empty($group)) return $group;

        $docblock = (new ReflectionClass($controller))->getDocComment();

        preg_match('!\@ApiGroup (.*)!i', $docblock, $match);

        $group = trim(end($match));

        return $group ?: 'Uncategorized';
    }

    /**
     * @param $controller
     * @param $method
     * @param $path
     * @param $httpMethod
     * @return ApiRequest|false
     */
    private function getRequestClass($controller, $method, $path, $httpMethod)
    {
        // get the validator if exists
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach($reflectionMethod->getParameters() as $param)
        {
            // make sure we found a class
            if (!($rclass = $param->getClass()) || empty($rclass->name)) {
                continue;
            }

            // make sure it is something we can use
            if (!is_subclass_of($rclass->name, ApiRequest::class)) {
                continue;
            }

            return new $rclass->name($path, $httpMethod);
        }

        return false;
    }
}