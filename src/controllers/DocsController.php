<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use DevnullSoftware\ApiGenerator\Api;
use ReflectionClass;
use Illuminate\Routing\Controller as BaseController;


class DocsController extends BaseController {

    public static  function methodOrder($method) {
        switch (strtolower($method))
        {
            case 'get' :
                return 1;
            case 'put' :
                return 2;
            case 'post' :
                return 3;
            case 'delete':
                return 4;
        }
    }

    public function apis2Welcome()
    {
        return View::make('ApiGenerator::layouts.default');
    }

    public function apis2($action)
    {
        $api = false;
        foreach (Route::getRoutes() as $route)
        {
            if (empty($route->getAction()['controller'])) {
                // Not the droid you are looking for
                continue;
            }

            $controller = $route->getAction()['controller'];
            if (strrpos($controller, $action) != strlen($controller)-strlen($action)) {
                // Not here either since doesn't end with action
                continue;
            }

            $api = new Api($route);
        }

        // make a json entity
        $json = [];
        foreach ($api->inputProps as $name => $rules) {
            if ($api->requestIsArray) {
                $name = '*.'.$name;
            }

            if (!substr_count($name, '.')) {
                continue;
            }

            $this->assignArrayByPath($json, $name, '');

            continue;
            $parts = explode('.', $name);

            while ($part = array_pop($parts)) {
                if (!count($parts)) {
                    $json[$part] = '';
                    continue;
                }


            }
//            $prop = array_pop($parts); // pop of the actual properties


            $pastFirst = 0;
            while ($part = array_pop($parts)) {
//                if (!$pastFirst++) {
//                    $stack = [$part => ''];
//                } elseif($part == '*') {
//                    if (empty($stack))
//                } else {
//                    $stack = [$part => $stack];
//                }
//                if ($part == '*') {
//                    $stack = [$stack];
//                } else {
//                }
            }

            $json = array_merge($json, $stack);
        }

        return view('ApiGenerator::apidocs', ['api' => $api, 'json' => $json]);
    }

    public function assignArrayByPath(&$arr, $path, $value)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            if ($key == '*') {
                $arr = &$arr[0];
            } else {
                $arr = &$arr[$key];
            }
        }

        $arr = $value;
    }

    /**
     */
    public function apis()
    {
        $apis = [];
        foreach (Route::getRoutes() as $route)
        {
            $routeDomain = !empty($route->getAction()['domain']) ? $route->getAction()['domain'] : false;
            if ($routeDomain && $routeDomain != \Request::server('HTTP_HOST')) continue;

            // remove non apis and duplicate of PUT (ie PATCH)
            if ( stripos($route->uri(), 'api/') === 0 && ! in_array('PATCH', $route->getMethods()) && array_key_exists('controller', $route->getAction()) )
            {
                $apis[] = new Api($route);
            }
        }

        uasort($apis, function(Api $a, Api $b) {
            $order = strcasecmp($a->group, $b->group);

            $order = $order ?: strcmp($a->path, $b->path);

            $order = $order ?: strcmp($this->methodOrder($a->httpMethod), $this->methodOrder($b->httpMethod));

            return $order;
        });

        return array_values($apis);
    }
}
