<?php

declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Exception;

class Component implements ContainerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function instantiate($class)
    {
        if (!class_exists($class)) {
            throw new Exception('Component class ' . $class . ' not exists');
        }

        try {
            return new $class($this->container);
        } catch (Exception $e) {
            throw new Exception('Class ' . $class . ' must be ' . Component::class . ' instance', 0, $e);
        }
    }

    public function has($class)
    {
        return $this->container->has($class);
    }

    public function get($class)
    {
        return $this->container->get($class);
    }
}
