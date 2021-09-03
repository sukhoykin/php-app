<?php

declare(strict_types=1);

namespace Sukhoykin\App\Component;

use ReflectionClass;
use Psr\Container\ContainerInterface;

use Sukhoykin\App\Component;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Container;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;

use Exception;

class Registry extends Container implements Component, Configurable
{
    private $config;

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        foreach ($this->config->getSections() as $classOfService) {

            $providers = $this->config->getSection($classOfService);

            foreach ($providers->getSections() as $classOfProvider) {

                $provider = new $classOfProvider();

                if ($provider instanceof Configurable) {
                    $provider->configurate($providers->getSection($classOfProvider));
                }

                break;
            }

            $this->define($classOfService, $provider);
        }
    }

    public function get($class)
    {
        if ($this->has($class)) {
            return parent::get($class);
        }

        $reflection = new ReflectionClass($class);
        $parameters = $reflection->getConstructor()->getParameters();

        if (!count($parameters)) {

            $this->add(new $class());
            return parent::get($class);
        }

        if (count($parameters) == 1 && $parameters[0]->getType()->getName() == ContainerInterface::class) {

            $this->add(new $class($this));
            return parent::get($class);
        }

        throw new Exception("Unsupported constructor for class '$class'");
    }
}
