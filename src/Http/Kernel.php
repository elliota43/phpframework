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
            // simple version: show a generic 500
            $body = 'Internal service error';

            // in dev, dump message/trace
            if (getenv('APP_DEBUG') == 'true') {
                $body .= "\n\n" . $e->getMessage();
            }

            return new Response($body, 500);
        }
    }
}