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

    public function routeModels()
    {
        return Config::get('api.models', []);
    }

    /**
     */
    public function apis()
    {
        // This allows us to use braces for angularjs
        Blade::setContentTags('<%', '%>'); 		// for variables and all things Blade
        Blade::setEscapedContentTags('<%%', '%%>'); 	// for escaped data

        if ( ! Request::ajax()) return View::make('ApiGenerator::apidocs');

        $apis = [];
        foreach (Route::getRoutes() as $route)
        {
            // remove non apis and duplicate of PUT (ie PATCH)
            if (stripos($route->uri(), 'api/') === 0 && !in_array('PATCH', $route->getMethods()))
            {
                $apis[] = new Api($route);
            }
        }

        $newApis = Event::fire('api-controller.generated', [$apis]); // maybe someone else will want to add some

        foreach ($newApis as $group)
        {
            $apis = array_merge($apis, $group);
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
