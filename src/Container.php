<?php

declare(strict_types=1);

namespace Sukhoykin\App;

use Exception;
use Psr\Container\ContainerInterface;
use Sukhoykin\App\Interfaces\Provider;

class DefaultProvider implements Provider
{
    private $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function provide(string $class, ContainerInterface $registry): object
    {
        return new $this->class();
    }
}

class Container implements ContainerInterface
{
    private $classes = [];
    private $providers = [];
    private $services = [];

    public function define(string $class, string $implementClass)
    {
        if (isset($this->classes[$class])) {
            throw new Exception("Class implementation of service '$class' already defined as '$this->classes[$class]'");
        }

        $this->classes[$class] = $implementClass;
    }

    public function provide(string $class, Provider $provider)
    {
        if (isset($this->providers[$class])) {
            throw new Exception("Provider of service '$class' already defined");
        }

        $this->providers[$class] = $provider;
    }

    public function put(string $class, object $service)
    {
        if (isset($this->services[$class])) {
            throw new Exception("Service '$class' already exists");
        }

        $this->services[$class] = $service;
    }

    public function add(object $service)
    {
        $class = get_class($service);
        $this->put($class, $service);
    }

    public function get($class)
    {
        if (isset($this->services[$class])) {
            return $this->services[$class];
        }

        $provider = null;

        if (isset($this->classes[$class])) {
            $provider = new DefaultProvider($this->classes[$class]);
        }

        if (isset($this->providers[$class])) {
            $provider = $this->providers[$class];
        }

        if (!$provider) {
            throw new Exception("Service '$class' is not defined");
        }

        $service = $provider->provide($class, $this);

        $this->put($class, $service);

        return $this->get($class);
    }

    public function has($class)
    {
        return isset($this->services[$class]) || isset($this->providers[$class]) || isset($this->classes[$class]);
    }
}
