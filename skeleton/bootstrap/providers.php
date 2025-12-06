<?php

declare(strict_types=1);

/**
 * Service Providers Configuration
 */

return [
    // Framework core providers
    \Framework\Providers\DatabaseServiceProvider::class,
    \Framework\Providers\ViewServiceProvider::class,
    \Framework\Providers\RoutingServiceProvider::class,
    \Framework\Providers\HttpServiceProvider::class,
    
    // Application providers
    \App\Providers\AppServiceProvider::class,
];

