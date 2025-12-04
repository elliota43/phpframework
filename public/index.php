<?php

declare(strict_types=1);

use Framework\Http\Request;
use Framework\Http\Kernel;
use Framework\Http\Response;

require_once __DIR__. '/../vendor/autoload.php';

$app = require __DIR__ .'/../bootstrap/app.php';

// Build Request from PHP Globals
$request = Request::fromGlobals();

// Receive the HTTP kernel from the app/container
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

// Handle the request, get a Response
$response = $kernel->handle($request);

// send response
$response->send();
