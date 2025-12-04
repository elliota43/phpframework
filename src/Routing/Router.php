<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Application;
use ReflectionFunction;
use ReflectionMethod;

class Router
{
    /**
     * @var array<string, array<int, array{uri:string,regex:string,params:array,action:mixed}>>
     */
    protected array $routes = [];

    public function __construct(
        protected Application $app
    ) {}

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
            'uri'    => $uri,
            'regex'  => $regex,
            'params' => $paramNames,
            'action' => $action,
        ];
    }

    protected function compileUri(string $uri): array
    {
        // e.g. "/hello/{name}" -> "#^/hello/([^/]+)$#"
        $paramNames = [];

        $pattern = preg_replace_callback(
            '#\{([^}]+)\}#',
            function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $uri
        );

        $regex = '#^' . $pattern . '$#';

        return [$regex, $paramNames];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();

        if (!isset($this->routes[$method])) {
            return new Response('Not Found', 404);
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                // $matches[0] is the full match, so drop it
                array_shift($matches);

                $params = [];

                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                return $this->callAction($route['action'], $params);
            }
        }

        // no matching route
        return new Response('Not Found', 404);
    }

    protected function callAction(mixed $action, array $routeParams = []): Response
    {
        // 1) "Controller@method" string
        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action, 2);

            $fqcn = 'App\\Http\\Controllers\\' . $controller;

            $instance = $this->app->make($fqcn);

            $result = $this->callWithDependencies([$instance, $method], $routeParams);

            return $this->toResponse($result);
        }

        // 2) [HomeController::class, 'show'] type route actions
        if (is_array($action) && isset($action[0], $action[1]) && is_string($action[0])) {
            // Resolve controller through the container
            $instance = $this->app->make($action[0]);
            $method   = $action[1];

            $result = $this->callWithDependencies([$instance, $method], $routeParams);

            return $this->toResponse($result);
        }

        // 3) Any other callable:
        //     - closure: function (Request $r, ...) {}
        //     - [new HomeController(), 'show']
        if (is_callable($action)) {
            $result = $this->callWithDependencies($action, $routeParams);

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

    protected function callWithDependencies(callable $callable, array $routeParams = []): mixed
    {
        // figure out what kind of callable we have
        if (is_array($callable)) {
            // [object, 'method'] or [class-string, 'method']
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
        } else {
            // closure or function name
            $reflection = new ReflectionFunction($callable);
        }

        $resolvedArgs = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $name = $param->getName();

            // 1) Class type-hint -> resolve via container
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                $resolvedArgs[] = $this->app->make($className);
                continue;
            }

            // 2) Match by parameter name from route params: /posts/{id}
            if (array_key_exists($name, $routeParams)) {
                $resolvedArgs[] = $routeParams[$name];
                continue;
            }

            // 3) Default value
            if ($param->isDefaultValueAvailable()) {
                $resolvedArgs[] = $param->getDefaultValue();
                continue;
            }

            // 4) nothing worked -> fail
            throw new \RuntimeException(
                "Cannot resolve parameter \${$name} on {$reflection->getName()}"
            );
        }

        // finally call the controller/closure with the resolved arguments
        return $callable(...$resolvedArgs);
    }
}
