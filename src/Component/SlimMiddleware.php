<?php

declare(strict_types=1);

namespace App\Component;

use App\Component;
use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;

class SlimMiddleware extends Component implements ComponentInterface
{
    const CONFIG = 'middleware';

    public function register(RegistryInterface $registry)
    {
        $slim = $registry->lookup(SlimApp::class);
        $app = $slim->getApp();

        $config = $registry->get(Config::class);
        $config = include $config->{self::CONFIG};

        if (isset($config['middleware'])) {

            foreach ($config['middleware'] as $class) {

                $middleware = new $class();

                if ($middleware instanceof ComponentInterface) {
                    $middleware->register($registry);
                }

                $app->add($middleware);
            }
        }

        $app->addRoutingMiddleware();

        if (isset($config['error'])) {

            $middleware = $app->addErrorMiddleware(false, false, false);

            foreach ($config['error'] as $type => $class) {

                $handler = new $class();

                if ($handler instanceof ComponentInterface) {
                    $handler->register($registry);
                }

                if ($type == 'default') {
                    $middleware->setDefaultErrorHandler($handler);
                } else {
                    $middleware->setErrorHandler($type, $handler);
                }
            }
        }
    }
}
