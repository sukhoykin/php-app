<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim;

use Psr\Log\LoggerInterface;
use Sukhoykin\App\Component;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;
use Sukhoykin\App\Slim\Middleware\DefaultErrorHandler;

class SlimMiddleware implements Configurable, Component
{
    private $config;

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        /** @var Registry */
        $registry = $root->get(Registry::class);

        /** @var SlimApplication */
        $slim = $root->get(SlimApplication::class);
        $app = $slim->getApp();

        if ($this->config->has('middleware')) {

            foreach ($this->config->getArray('middleware') as $class) {
                $app->add($registry->get($class));
            }
        }

        $app->addRoutingMiddleware();

        $default = new DefaultErrorHandler($slim, $registry->get(LoggerInterface::class));

        $middleware = $app->addErrorMiddleware(false, false, false);
        $middleware->setDefaultErrorHandler($default);

        if ($this->config->has('error')) {

            foreach ($this->config->getArray('error') as $type => $class) {
                $middleware->setErrorHandler($type, $registry->get($class));
            }
        }
    }
}
