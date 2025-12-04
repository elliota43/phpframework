<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass;
use ReflectionParameter;

class Application
{
    protected array $bindings = [];

    protected array $singletons = [];

    protected array $instances = [];

    public function debugBindings(): array
    {
        return [
            'bindings' => array_keys($this->bindings),
            'singletons' => array_keys($this->singletons),
            'instances' => array_keys($this->instances),
        ];
    }

    public function bind(string $abstract, \Closure|string $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => false,
        ];
    }
    
    public function singleton(string $abstract, \Closure|string $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => true,
        ];
    }
    
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }
    public function make(string $abstract): mixed
    {
        // if we already have a singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // if it's bound
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];

            // closure binding
            if ($concrete instanceof \Closure) {
                $object = $concrete($this);
            } else {
                // string class name
                $object = $this->build($concrete);
            }

            if ($this->bindings[$abstract]['singleton']) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        // no binding: assume $abstract is a class name
        return $this->build($abstract);
    }

    protected function build(string $concrete): mixed
    {
        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new \RuntimeException("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (! $constructor) {
            return new $concrete();
        }

        $dependencies = array_map(
            fn (ReflectionParameter $param) => $this->resolveParameter($param),
            $constructor->getParameters()
        );

        return $reflector->newInstanceArgs($dependencies);
    }

    protected function resolveParameter(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        // only handle class types for now
        if ($type && !$type->isBuiltin()) {
            $className = $type->getName();
            return $this->make($className);
        }

        // if there's a default value, use it
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new \RuntimeException(
            "Cannot resolve parameter \${$param->getName()} on {$param->getDeclaringClass()->getName()}"
        );
    }
}