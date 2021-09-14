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
use Sukhoykin\App\Interfaces\Service;

use Exception;

class Registry extends Container implements Component, Configurable
{
    private Composite $root;
    private $config;

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        foreach ($this->config->getSections() as $classOfService) {

            if ($this->config->isString($classOfService)) {

                $this->define($classOfService, $this->config->getString($classOfService));
                continue;
            }

            $providers = $this->config->getSection($classOfService);

            foreach ($providers->getSections() as $classOfProvider) {

                $provider = new $classOfProvider();

                if ($provider instanceof Configurable) {
                    $provider->configurate($providers->getSection($classOfProvider));
                }

                break;
            }

            $this->provide($classOfService, $provider);
        }

        $this->root = $root;
    }

    public function get($class)
    {
        if ($this->has($class)) {

            $service = parent::get($class);

            if ($service instanceof Configurable) {

                /** @var Config */
                $config = $this->root->get(Config::class);
                $service->configurate($config->getServiceConfig($class));
            }

            return $service;
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor || !count($parameters = $constructor->getParameters())) {

            $service = new $class();

            if ($service instanceof Service) {
                $service->setRegistry($this);
            }

            $this->add($service);
            return $this->get($class);
        }

        if (count($parameters) == 1 && $parameters[0]->getType()->getName() == ContainerInterface::class) {

            $this->add(new $class($this));
            return $this->get($class);
        }

        throw new Exception("Unsupported constructor for class '$class'");
    }
}
