<?php

declare(strict_types=1);

use Framework\Application;

require_once __DIR__ . '/../../vendor/autoload.php';

// Simple autoloader for example namespace
spl_autoload_register(function ($class) {
    $prefix = 'Examples\\Blog\\';
    $baseDir = __DIR__ . '/app/';

    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

$app = new Application();

// Set global instance for helper functions
Application::setInstance($app);

// Register service providers
$providersFile = __DIR__ . '/providers.php';
if (file_exists($providersFile)) {
    $providers = require $providersFile;
    $app->registerProviders($providers);
}

// Boot all registered providers
$app->boot();

return $app;
