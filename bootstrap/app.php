<?php

declare(strict_types=1);

use Framework\Application;
use Framework\Database\Connection;
use Framework\Http\Kernel;
use Framework\Routing\Router;
use Framework\Database\Model as BaseModel;
use Framework\View\View;

$app = new Application();

View::setBasePath(__DIR__.'/../resources/views');
// DB Connection (SQLite example)
$app->singleton(Connection::class, function() {
    // db file in project root // adjust as needed
    $dsn = 'sqlite:' . __DIR__. '/../database.sqlite';

    return new Connection($dsn);
});

BaseModel::setConnection($app->make(Connection::class));


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

$app->singleton(\Framework\Exceptions\ErrorHandler::class, function () {
    return new \Framework\Exceptions\ErrorHandler();
});
// later, bind config, views, db, etc.

return $app;
