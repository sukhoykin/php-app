<?php

declare(strict_types=1);

namespace App\Component;

use App\Component;
use App\Util\Config;

use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;
use Psr\Container\ContainerInterface;

class SlimRoute extends Component implements ComponentInterface
{
    const CONFIG = 'routes';

    public function register(RegistryInterface $registry, ContainerInterface $container)
    {
        $slim = $registry->lookup(SlimApp::class);
        $app = $slim->getApp();

        $config = $container->get(Config::class);
        $routes = require $config->{self::CONFIG};

        $routes($app);
    }
}
