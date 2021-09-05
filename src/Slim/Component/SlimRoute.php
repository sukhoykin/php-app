<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Component;

use Sukhoykin\App\Component;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;

class SlimRoute implements Configurable, Component
{
    private $config;

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        /** @var SlimApplication */
        $slim = $root->get(SlimApplication::class);
        $app = $slim->getApp();

        $this->config->get('define')($app);
    }
}
