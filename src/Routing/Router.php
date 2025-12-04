<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Application;

class Router 
{
    protected array $routes = [];

    public function __construct(
        protected Application $app
    ){}
    public function get(string $uri, mixed $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, mixed $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, mixed $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, mixed $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, mixed $action): void
    {
        [$regex, $paramNames] = $this->compileUri($uri);

        $this->routes[$method][] = [
            'uri' => $uri,
            'regex' => $regex,
            'params' => $paramNames,
            'action' => $action,
        ];
    }

    protected function compileUri(string $uri): array
    {
        // e.g. "/hello/{name}" -> "#^/hello/([^/]+)$#"
        $paramNames = [];

        $pattern = preg_replace_callback('#\{([^}]+)\}#', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^/]+)';
        }, $uri);

        $regex = '#^' . $pattern . '$#';

        return [$regex, $paramNames];
}

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        if (!isset($this->routes[$method])) {
            return new Response('Not Found', 404);
        };

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                // $matches[0] is the full match, so drop it
                array_shift($matches);

                $params = [];

                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }
                
                return $this->callAction($route['action'], $request, $params);
            }
        }
        

        // fallback
        return new Response('', 200);
    }

    protected function callAction(mixed $action, Request $request, array $routeParams = []): Response
    {
        // First arg is always the Request, then route params in order
        $args = array_merge([$request], array_values($routeParams));

        // 1) "Controller@method" string
        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action, 2);

            $fqcn = 'App\\Http\\Controllers\\' . $controller;

            $instance = $this->app->make($fqcn);

            $result = $instance->$method(...$args);
            return $this->toResponse($result);
        }

        // 2) [HomeController::class, 'show'] type route actions
        if (is_array($action) && is_string($action[0])) {
            // Resolve controller through the container
            $instance = $this->app->make($action[0]);

            $method = $action[1];

            $callable = [$instance, $method];
            $result = $callable(...$args);
            return $this->toResponse($result);
        }

        // 3) Any other callable:
        //     - closure: function (Request $r, ...) {}
        //     - [new HomeController(), 'show']
        if (is_callable($action)) {
            $result = $action(...$args);
            return $this->toResponse($result);
        }

        // fallback if nothing matched
        return new Response('Invalid route action', 500);
    }
    

    public function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return new Response($result);
        }

        // very naive: just cast to string
        return new Response((string) $result);
    }

    
}