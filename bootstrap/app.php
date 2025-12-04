<?php

declare(strict_types=1);

use Framework\Application;
use Framework\Http\Kernel;
use Framework\Routing\Router;

$app = new Application();

$app->bind(Router::class, function (Application $app) {
    $router = new Router($app);

    // load routes/web.php
    $routesFile = __DIR__ . '/../routes/web.php';

    if (file_exists($routesFile)) {
        $defineRoutes = require $routesFile;
        $defineRoutes($router);
    }

    return $router;
});

// Bind Kernel
$app->bind(Kernel::class, function(Application $app) {
    return new Kernel(
        $app,
        $app->make(Router::class)
    );
});

// later, bind config, views, db, etc.

return $app;
