<?php

declare(strict_types=1);

namespace Framework\Routing;

class RouteDefinition
{
    public function __construct(
        protected Router $router,
        protected string $method,
        protected string $uri,
        protected mixed $action
    ) {}

    public function name(string $name): self
    {
        $this->router->setRouteName($name, $this->method, $this->uri);
        return $this;
    }
}