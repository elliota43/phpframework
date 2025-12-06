<?php

declare(strict_types=1);

use Framework\Exceptions\ErrorPageRenderer;
use Framework\Http\Request;
use Framework\Http\Kernel;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../../vendor/autoload.php';

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    echo ErrorPageRenderer::render($e);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $e = new \ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        );

        http_response_code(500);
        echo ErrorPageRenderer::render($e);
    }
});

$app = require __DIR__ . '/../bootstrap.php';

// Build Request from PHP Globals
$request = Request::fromGlobals();

$app->instance(Request::class, $request);

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$response = $kernel->handle($request);
$response->send();
