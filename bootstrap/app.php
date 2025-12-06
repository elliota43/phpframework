<?php

declare(strict_types=1);

use Framework\Application;

// Load Composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load helper functions
if (file_exists(__DIR__ . '/../src/Support/helpers.php')) {
    require_once __DIR__ . '/../src/Support/helpers.php';
}

$app = new Application();

// Set global instance for helper functions
Application::setInstance($app);

// Register service providers
$providersFile = __DIR__ . '/providers.php';
if (file_exists($providersFile)) {
    $providers = require $providersFile;
    $app->registerProviders($providers);
}

// Register ErrorHandler (can be moved to a provider later)
$app->singleton(\Framework\Exceptions\ErrorHandler::class, function () {
    return new \Framework\Exceptions\ErrorHandler();
});

// Boot all registered providers
$app->boot();

return $app;
