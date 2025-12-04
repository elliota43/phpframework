<?php

use Framework\Routing\Router;
use Framework\Http\Request;
use Framework\Http\Response;

return function (Router $router): void {
    
    $router->get('/', 'HomeController@index');

    $router->get('/hello/{name}', 'HomeController@hello');


    $router->get('/inject/{name}', function (Request $request, string $name): Response {
        $method = $request->getMethod(); // should be "GET"
        return new Response("Hello {$name}, via {$method} (closure)");
    });

    $router->get('/posts/{id}', 'PostController@show')->name('posts.show');
};