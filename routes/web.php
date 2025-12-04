<?php

use Framework\Routing\Router;
use Framework\Http\Request;
use Framework\Http\Response;

return function (Router $router): void {
    
    $router->get('/', 'HomeController@index');

    $router->get('/hello/{name}', 'HomeController@hello');

    $router->get('/posts/{id}', function (Request $request, string $id) {
        return new Response("Post ID: {$id}");
    });
};