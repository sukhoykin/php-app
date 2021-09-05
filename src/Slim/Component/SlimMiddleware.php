<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Component;

use Psr\Log\LoggerInterface;
use Sukhoykin\App\Component;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;
use Sukhoykin\App\Slim\Middleware\DefaultErrorHandler;
use Sukhoykin\App\Slim\Middleware\ShutdownHandler;

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

        $default = $registry->get(DefaultErrorHandler::class);
        $default->setResponseFactory($app->getResponseFactory());
        $default->setLogger($registry->get(LoggerInterface::class));

        $shutdown = new ShutdownHandler($slim->getRequest(), $default);

        register_shutdown_function($shutdown);
        set_error_handler([$this, 'phpErrorHandler']);
        error_reporting(0);

        $middleware = $app->addErrorMiddleware(false, false, false);
        $middleware->setDefaultErrorHandler($default);

        if ($this->config->has('error')) {

            foreach ($this->config->getArray('error') as $type => $class) {

                $handler = $registry->get($class);

                $handler->setResponseFactory($app->getResponseFactory());
                $handler->setLogger($registry->get(LoggerInterface::class));

                $middleware->setErrorHandler($type, $handler);
            }
        }
    }
}
