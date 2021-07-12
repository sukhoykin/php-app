<?php

declare(strict_types=1);

namespace App;

use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;
use App\Util\Profiler;
use Exception;

class Application extends Component implements RegistryInterface
{
    const CONFIG = Application::class . ':config';
    const METRIC_APP = 'app';

    private $container;
    private $components = [];

    public function __construct($config = [])
    {
        parent::__construct($this->container = new Container());

        $profiler = new Profiler();
        $profiler->start(self::METRIC_APP);

        $this->container->add($profiler);
        $this->container->put(self::CONFIG, $config);
    }

    private function config($path)
    {
        if (!file_exists($path)) {
            throw new Exception('Config "' . $path . '" not found');
        }

        return include $path;
    }

    public function services($path)
    {
        $config = $this->config($path);

        foreach ($config as $class => $provider) {
            $this->container->define($class, $provider);
        }
    }

    public function components($path)
    {
        $config = $this->config($path);

        foreach ($config as $component) {
            $this->register($component);
        }
    }

    protected function component($class)
    {
        $component = $this->instantiate($class);

        if ($component instanceof ComponentInterface) {
            $component->register($this, $this->container());
        } else {
            throw new Exception('Component' . $class . ' must implement ' . ComponentInterface::class);
        }

        return $component;
    }

    public function register($class)
    {
        if (isset($this->components[$class])) {
            throw new Exception('Component ' . $class . ' already registered');
        }

        $this->components[$class] = $this->component($class);
    }

    public function lookup(string $class)
    {
        if (!isset($this->components[$class])) {
            throw new Exception('Component ' . $class . ' is not registered');
        }

        return $this->components[$class];
    }
}
