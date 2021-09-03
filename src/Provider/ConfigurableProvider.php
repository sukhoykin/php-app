<?php

declare(strict_types=1);

namespace Sukhoykin\App\Provider;

use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;
use Sukhoykin\App\Interfaces\Provider;
use Exception;

abstract class ConfigurableProvider implements Provider, Configurable
{
    private $config;

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    protected function getConfig(?string $class): Section
    {
        if (!$this->config) {
            throw new Exception(sprintf('Provider "%s" is not configured (call configurate() first)', get_class($this)));
        }

        return $class ? $this->config->cast($class) : $this->config;
    }
}
