<?php

use Laravel\Lumen\Routing\Router;

// export a route declaration function.
return function (Router $router) {
    // main route.
    $router->get('/', function () {
        return response()->json(["ping" => "pong"]);
    });

    // avatar default route.
    $router->get('/a/{username}', 'AvatarController@show');
    // alias route for image extension (compat mode).
    $router->get('/a/{username}.{any}', 'AvatarController@show');
};