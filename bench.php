<?php
use exussum12\Phruit\Phruit;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

$options = [];

$nRoutes = 100;
$nMatches = 30000;


$router = FastRoute\simpleDispatcher(function($router) use ($nRoutes, &$lastStr) {
    for ($i = 0, $str = 'a', $str2 = 'b'; $i < $nRoutes; $i++, $str++, $str2++) {
        $router->addRoute('GET', '/' . $str . '/' . $str2 . '/{arg}', 'handler' . $i);
        $lastStr = $str . '/' . $str2;
    }
}, $options);

// first route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $router->dispatch('GET', '/a/b/foo');
}
printf("FastRoute first route: %f\n", microtime(true) - $startTime);
//var_dump($res);

// last route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $router->dispatch('GET', '/' . $lastStr . '/foo');
}
printf("FastRoute last route: %f\n", microtime(true) - $startTime);
//var_dump($res);

// unknown route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $router->dispatch('GET', '/foobar/bar');
}
printf("FastRoute unknown route: %f\n", microtime(true) - $startTime);

$route = new Phruit();

for ($i = 0, $str = 'a', $str2 = 'b'; $i < $nRoutes; $i++, $str++, $str2++) {
    $route->add('/' . $str . '/' . $str2 . '/foo', function(){});
    $lastStr = $str . '/' . $str2;
}

// first route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $route->route('/a/b/foo');
}
printf("Phruit first route: %f\n", microtime(true) - $startTime);
//var_dump($res);
// last route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $route->route('/' . $lastStr . '/foo');
}
printf("Phruit last route: %f\n", microtime(true) - $startTime);
//var_dump($res);

// unknown route
$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $route->route('GET', '/foobar/bar');
}
printf("Phruit unknown route: %f\n", microtime(true) - $startTime);
//var_dump($res);

$routes = new RouteCollection();
//////////
for ($i = 0, $str = 'a', $str2 = 'b'; $i < $nRoutes; $i++, $str++, $str2++) {
    $route = new Route('/' . $str . '/' . $str2 . '/foo', array('_controller' => 'MyController'));
    $routes->add($str . '/' . $str2, $route);
    $lastStr = $str . '/' . $str2;
}

$context = new RequestContext('/');

$matcher = new UrlMatcher($routes, $context);

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $matcher->match('/a/b/foo');
}

printf("Symphony first route: %f\n", microtime(true) - $startTime);

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $res = $matcher->match('/' . $lastStr . '/foo');
}
printf("Symphony last route: %f\n", microtime(true) - $startTime);

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    try {
    $res = $matcher->match('/foobar/foo');
    } catch (Exception $e) {

    }
}
printf("Symphony unknown route: %f\n", microtime(true) - $startTime);



///////////////////////////////////////////////


require 'vendor/autoload.php';
require 'vendor/illuminate/support/helpers.php';

$basePath = str_finish(dirname(__FILE__), '/');
$controllersDirectory = $basePath . 'Controllers';
$modelsDirectory = $basePath . 'Models';

$app = new Illuminate\Container\Container;
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$app['app'] = $app;
$app['env'] = 'production';

with(new Illuminate\Events\EventServiceProvider($app))->register();
with(new Illuminate\Routing\RoutingServiceProvider($app))->register();

//require $basePath . 'routes.php';

for ($i = 0, $str = 'a', $str2 = 'b'; $i < $nRoutes; $i++, $str++, $str2++) {
    Illuminate\Support\Facades\Route::get('/' . $str . '/' . $str2 .'/foo', function() {});
    $lastStr = $str . '/' . $str2;
}

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $request = Illuminate\Http\Request::create('/a/b/foo');
    $response = $app['router']->dispatch($request);
}

printf("Laravel first route: %f\n", microtime(true) - $startTime);

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    $request = Illuminate\Http\Request::create('/' . $lastStr . '/foo');
    $response = $app['router']->dispatch($request);
}

printf("Laravel last route: %f\n", microtime(true) - $startTime);

$startTime = microtime(true);
for ($i = 0; $i < $nMatches; $i++) {
    try {
        $request = Illuminate\Http\Request::create('/foobar/foo');
        $response = $app['router']->dispatch($request);
    } catch (Exception $e) {

    }
}

printf("Laravel unknown route: %f\n", microtime(true) - $startTime);
