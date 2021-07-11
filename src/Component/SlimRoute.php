<?php

declare(strict_types=1);

namespace App\Component;

use App\Component;
use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;

class SlimRoute extends Component implements ComponentInterface
{
    public function register(RegistryInterface $registry)
    {
        $slim = $registry->lookup(SlimApp::class);
        $app = $slim->getApp();

        $config = $registry->get(Config::class);
        $routes = require $config->routes;

        $routes($app);
    }
}
