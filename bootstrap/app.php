<?php

// require autoloader.
require_once __DIR__.'/../vendor/autoload.php';

// imports.
use Illuminate\Contracts\Debug\ExceptionHandler;
use Pixie\Exceptions\Handler;
use Laravel\Lumen\Application;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Pixie\Providers\ImageServiceProvider;
use Pixie\Providers\SteemServiceProvider;
use Illuminate\Redis\RedisServiceProvider;


// load the environment variables.
require_once __DIR__.'/env.php';


// create the Lumen instance.
$app = new Application(realpath(__DIR__.'/../'));


// bind application exception handler.
$app->singleton(ExceptionHandler::class, Handler::class);
// bind the console kernel.
$app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);

// load database configuration (for Redis).
$app->configure('database');


// redis provider.
$app->register(RedisServiceProvider::class);
// intervention/image provider.
$app->register(ImageServiceProvider::class);
// steem provider.
$app->register(SteemServiceProvider::class);


// main router params.
$routerParams = [ 'namespace' => 'Pixie\Http\Controllers' ];
// generate a router loader function.
$routerLoader = require_once __DIR__.'/../routes/web.php';
// register the router params and routes.
$app->router->group($routerParams, $routerLoader);


// return the Lumen application.
return $app;
