<?php

declare(strict_types=1);

use Framework\Application;

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

// Register service providers
$providersFile = __DIR__ . '/providers.php';
if (file_exists($providersFile)) {
    $providers = require $providersFile;
    $app->registerProviders($providers);
}

// Register ErrorHandler
$app->singleton(\Framework\Exceptions\ErrorHandler::class, function () {
    return new \Framework\Exceptions\ErrorHandler();
});

// Boot all registered providers
$app->boot();

return $app;

