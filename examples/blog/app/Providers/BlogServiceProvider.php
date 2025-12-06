<?php

declare(strict_types=1);

namespace Examples\Blog\Providers;

use Framework\Support\ServiceProvider;
use Framework\Application;
use Framework\Database\Connection;
use Framework\Database\Model as BaseModel;
use Framework\Routing\Router;
use Framework\View\View;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register database connection for blog example
        $this->app->singleton(Connection::class, function () {
            $dsn = 'sqlite:' . __DIR__ . '/../../database.sqlite';
            return new Connection($dsn);
        });

        // Register router with blog routes
        $this->app->bind(Router::class, function (Application $app) {
            $router = new Router($app);

            $routesFile = __DIR__ . '/../../routes/web.php';
            if (file_exists($routesFile)) {
                $define = require $routesFile;
                $define($router);
            }

            return $router;
        });

        // Register custom Kernel
        $this->app->bind(\Framework\Http\Kernel::class, function(Application $app) {
            return new \Examples\Blog\Http\Kernel(
                $app,
                $app->make(Router::class)
            );
        });
    }

    public function boot(): void
    {
        // Attach database connection to models
        BaseModel::setConnection($this->app->make(Connection::class));

        // Configure view paths for blog example
        View::setBasePath(__DIR__ . '/../../resources/views');
        View::setCachePath(__DIR__ . '/../../storage/views');
    }
}

