<?php
namespace Impress\Framework\Http\Route;

use Impress\Framework\Http\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

class RouteMatch
{
    const ROUTE_PARAMETER_PREFIX = '__';
    const ROUTES_KEY_ROUTE_NAME = "route_name";
    const ROUTES_KEY_ROUTE = "route";
    const ROUTE_AUTO_NAME_PREFIX = 'ROUTE:';

    private static $route_name_autoincrement = 0;
    private static $routes = array();
    private static $routesFile;
    private $routeCollection;

    public function __construct($routesFile = null)
    {
        $this->setRoutesFile($routesFile);
    }

    private function setRoutesFile($routesFile)
    {
        if (is_file($routesFile)) {
            self::$routesFile = $routesFile;
        } else {
            self::$routesFile = app_path('Http' . DIRECTORY_SEPARATOR . 'Routes.php');
        }
    }

    private function addRoutesToRouteCollection()
    {
        if (!($this->routeCollection instanceof RouteCollection)) {
            $this->routeCollection = new RouteCollection();
        }

        foreach (self::$routes as $k => $route) {
            $VendorRoute = $route[self::ROUTES_KEY_ROUTE];
            $route_name = $route[self::ROUTES_KEY_ROUTE_NAME];

            $this->routeCollection->add($route_name, $VendorRoute);
        }
    }

    public function work()
    {
        RouteCache::work(self::$routesFile);
        $this->addRoutesToRouteCollection();

        $context = new RequestContext();
        $context->fromRequest(Request::request());

        $matcher = new UrlMatcher($this->routeCollection, $context);
        $parameters = $matcher->matchRequest(Request::request());

        return $parameters;
    }

    public function getController($parameters)
    {
        return isset($parameters[self::ROUTE_PARAMETER_PREFIX . 'controller']) ?
            $parameters[self::ROUTE_PARAMETER_PREFIX . 'controller'] : null;
    }

    public function getMiddleware($parameters)
    {
        return isset($parameters[self::ROUTE_PARAMETER_PREFIX . 'middleware']) ?
            $parameters[self::ROUTE_PARAMETER_PREFIX . 'middleware'] : null;
    }

    public static function addRoute(array $routeArgs)
    {
        foreach ([
                     'controller',
                     'prefix',
                     'middleware',
                     'name',
                     'as',
                     'path',
                     'requirements',
                     'options',
                     'host',
                     'schemes',
                     'methods',
                     'condition'
                 ] as $p) {

            switch ($p) {
                case 'path':
                case 'host':
                case 'condition':
                    $default_value = '';
                    break;
                case 'controller':
                case 'prefix':
                case 'middleware':
                case 'name':
                case 'as':
                    $default_value = null;
                    break;
                default:
                    $default_value = array();
                    break;
            }

            ${$p} = get_array_item($routeArgs, self::ROUTE_PARAMETER_PREFIX . $p, $default_value);

            if (!isset($name) && isset($as)) {
                $name = $as;
            }
            if (isset($middleware)) {
                if (!is_array($middleware)) {
                    $middleware = [$middleware];
                }
            }
        }

        $defaults = [
            self::ROUTE_PARAMETER_PREFIX . 'controller' => $controller,
            self::ROUTE_PARAMETER_PREFIX . 'prefix' => $prefix,
            self::ROUTE_PARAMETER_PREFIX . 'middleware' => $middleware
        ];

        $route = new SymfonyRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $name = self::autoRouteName($name);
        $_route = array(
            self::ROUTES_KEY_ROUTE => $route,
            self::ROUTES_KEY_ROUTE_NAME => $name
        );

        self::$routes[$name] = $_route;
        return $_route;
    }

    private static function autoRouteName($name)
    {
        self::$route_name_autoincrement++;
        return is_null($name) ? self::ROUTE_AUTO_NAME_PREFIX . self::$route_name_autoincrement : $name;
    }

    public static function clearRoutes()
    {
        self::$routes = array();
    }

    public static function setRoutes(array $routes)
    {
        self::$routes = $routes;
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    public static function getRoute($routeName)
    {
        return self::$routes[$routeName]['route'];
    }
}