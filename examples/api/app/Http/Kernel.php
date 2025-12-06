<?php

declare(strict_types=1);

namespace Examples\Api\Http;

use Framework\Http\Kernel as BaseKernel;
use Framework\Application;
use Framework\Routing\Router;

class Kernel extends BaseKernel
{
    /**
     * List of global middleware.
     * @var string[]
     */
    protected array $middleware = [
        // Empty - no middleware for API example
    ];
}

