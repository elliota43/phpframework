<?php

declare(strict_types=1);

use Framework\Routing\Router;
use Framework\Http\Response;
use Framework\View\View;

return function (Router $router): void {
    $router->get('/', function () {
        return View::make('welcome');
    });
};

