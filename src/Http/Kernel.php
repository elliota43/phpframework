<?php

namespace Framework\Http;

use Framework\Routing\Router;
use Framework\Application;
use Framework\Http\Request;
use Framework\Http\Response;
class Kernel 
{
    public function __construct(protected Application $app, protected Router $router)
    {}

    /**
     * List of global middleware.
     * @var string[]
     */
    protected array $middleware = [
        \App\Http\Middleware\LogRequests::class
    ];

    public function handle(Request $request): Response
    {
        try {
            $pipeline = array_reduce(
                array_reverse($this->middleware),
                function (callable $next, string $middlewareClass) {
                    return function (Request $request) use ($next, $middlewareClass): Response {
                        $middleware = $this->app->make($middlewareClass);
                        return $middleware->handle($request, $next);
                    };
                },
                fn (Request $request): Response => $this->router->dispatch($request)
            );
            return $pipeline($request);
        } catch (\Throwable $e) {
            $handler = $this->app->make(\Framework\Exceptions\ErrorHandler::class);

            return $handler->handle($e);
        }
    }
}