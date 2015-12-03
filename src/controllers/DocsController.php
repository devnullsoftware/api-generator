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

        return view('ApiGenerator::apidocs', ['api' => $api]);
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
