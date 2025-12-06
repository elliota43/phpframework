<?php

declare(strict_types=1);

namespace Examples\Api\Providers;

use Framework\Support\ServiceProvider;
use Framework\Application;
use Framework\Database\Connection;
use Framework\Database\Model as BaseModel;
use Framework\Routing\Router;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register database connection for API example
        $this->app->singleton(Connection::class, function () {
            $dsn = 'sqlite:' . __DIR__ . '/../../database.sqlite';
            return new Connection($dsn);
        });

        // Register router with API routes
        $this->app->bind(Router::class, function (Application $app) {
            $router = new Router($app);

            $routesFile = __DIR__ . '/../../routes/api.php';
            if (file_exists($routesFile)) {
                $define = require $routesFile;
                $define($router);
            }

            return $router;
        });

        // Register custom Kernel
        $this->app->bind(\Framework\Http\Kernel::class, function(Application $app) {
            return new \Examples\Api\Http\Kernel(
                $app,
                $app->make(Router::class)
            );
        });
    }

    public function boot(): void
    {
        // Attach database connection to models
        BaseModel::setConnection($this->app->make(Connection::class));
    }
}

