<?php

use Framework\Routing\Router;
use Framework\View\View;
use Framework\Http\Response;
use Examples\Blog\Models\Post;
use Examples\Blog\Models\User;
use Examples\Blog\Http\Controllers\HomeController;
use Examples\Blog\Http\Controllers\PostController;

return function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/posts/{id}', [PostController::class, 'show']);
    $router->get('/debug', function () {
        $posts = Post::all();
        return new Response('<pre>' . json_encode(array_map(fn($p) => $p->toArray(), $posts), JSON_PRETTY_PRINT) . '</pre>');
    });
};
