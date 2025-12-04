<?php

namespace App\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

class LogRequests
{
    public function handle(Request $request, callable $next): Response
    {
        error_log('Incoming ' . $request->getMethod() . ' ' . $request->getPath());

        $response = $next($request);

        error_log('Outgoing response ' . $response->getStatusCode());

        return $response;
    }
}