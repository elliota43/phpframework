<?php

declare(strict_types=1);

/**
 * Service Providers Configuration
 * 
 * This file lists all service providers that should be registered with the application.
 * Providers are registered in the order they appear here.
 */

return [
    // Framework core providers
    \Framework\Providers\ConfigServiceProvider::class,
    \Framework\Providers\DatabaseServiceProvider::class,
    \Framework\Providers\ViewServiceProvider::class,
    \Framework\Providers\RoutingServiceProvider::class,
    \Framework\Providers\HttpServiceProvider::class,
    \Framework\Providers\LoggingServiceProvider::class,
    
    // Application providers
    \App\Providers\AppServiceProvider::class,
    \Framework\Providers\FrontendServiceProvider::class,
];

