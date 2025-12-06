<?php

declare(strict_types=1);

use Framework\Support\Env;

return [
    /*
    |--------------------------------------------------------------------------
    | Frontend Framework
    |--------------------------------------------------------------------------
    |
    | Specify which frontend framework you're using: 'react', 'vue', or null
    | for no frontend framework integration.
    |
    */
    'framework' => Env::get('FRONTEND_FRAMEWORK', null),

    /*
    |--------------------------------------------------------------------------
    | Vite Dev Server
    |--------------------------------------------------------------------------
    |
    | The URL of the Vite development server. This is automatically detected
    | but can be overridden here.
    |
    */
    'vite_server' => Env::get('VITE_SERVER', 'http://localhost:5173'),

    /*
    |--------------------------------------------------------------------------
    | Entry Point
    |--------------------------------------------------------------------------
    |
    | The main entry point for your frontend application.
    |
    */
    'entry' => Env::get('FRONTEND_ENTRY', 'resources/js/app.jsx'),

    /*
    |--------------------------------------------------------------------------
    | Public Path
    |--------------------------------------------------------------------------
    |
    | The public path where built assets are served from.
    |
    */
    'public_path' => Env::get('FRONTEND_PUBLIC_PATH', '/build'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The default layout template for SPA responses.
    |
    */
    'layout' => Env::get('FRONTEND_LAYOUT', 'app'),

    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for API routes when using SPA mode.
    |
    */
    'api_prefix' => Env::get('FRONTEND_API_PREFIX', '/api'),
];

