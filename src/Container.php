<?php

declare(strict_types=1);

namespace App;

use App\Interfaces\ProviderInterface;

use Exception;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $providers = [];
    private $services = [];

    protected function provide($class, $providerClass)
    {
        if (!class_exists($providerClass)) {
            throw new Exception('Provider class ' . $providerClass . ' not exists');
        }

        try {

            $provider = new $providerClass();

            if ($provider instanceof ProviderInterface) {
            } else {
                throw new Exception();
            }
            //
        } catch (Exception $e) {
            throw new Exception('Provider ' . $providerClass . ' must implements ' . ProviderInterface::class);
        }

        $service = $provider->provide($class, $this);

        if (!$service) {
            throw new Exception('Provider ' . $providerClass . ' must return not null service instance');
        }

        return $service;
    }

    public function put($name, $service)
    {
        if (isset($this->services[$name])) {
            throw new Exception('Service ' . $name . ' already defined');
        }

        $this->services[$name] = $service;
    }

    public function add($service)
    {
        $class = get_class($service);
        $this->put($class, $service);
    }

    public function define($class, string $provider = null)
    {
        if ($provider) {

            if (isset($this->providers[$class])) {
                throw new Exception('Service ' . $class . ' already defined');
            }

            $this->providers[$class] = $provider;
            //
        } else {
            $this->add(new $class());
        }
    }

    public function get($class)
    {
        if (!isset($this->services[$class])) {

            if (!isset($this->providers[$class])) {
                throw new Exception('Service ' . $class . ' is not defined');
            }

            $service = $this->provide($class, $this->providers[$class]);

            $this->put($class, $service);
        }

        return $this->services[$class];
    }

    public function has($class)
    {
        return isset($this->services[$class]) || isset($this->providers[$class]);
    }
}
