<?php

declare(strict_types=1);

namespace Sukhoykin\App;

use Exception;
use Psr\Container\ContainerInterface;
use Sukhoykin\App\Interfaces\Provider;

class Container implements ContainerInterface
{
    private $providers = [];
    private $services = [];

    public function define($class, Provider $provider)
    {
        if (isset($this->providers[$class])) {
            throw new Exception("Provider of service '$class' already defined");
        }

        $this->providers[$class] = $provider;
    }

    public function put($class, $service)
    {
        if (isset($this->services[$class])) {
            throw new Exception("Service '$class' already exists");
        }

        $this->services[$class] = $service;
    }

    public function add($service)
    {
        $class = get_class($service);
        $this->put($class, $service);
    }

    public function get($class)
    {
        if (isset($this->services[$class])) {
            return $this->services[$class];
        }

        if (!isset($this->providers[$class])) {
            throw new Exception("Provider of service '$class' is not defined");
        }

        $provider = $this->providers[$class];
        $service = $provider->provide($class, $this);

        $this->put($class, $service);

        return $this->get($class);
    }

    public function has($class)
    {
        return isset($this->services[$class]) || isset($this->providers[$class]);
    }
}
