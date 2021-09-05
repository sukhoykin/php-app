<?php

declare(strict_types=1);

namespace Sukhoykin\App;

use Sukhoykin\App\Composite;
use Sukhoykin\App\Component\Config;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Slim\Component\SlimApplication;

class Service extends Composite
{
    public function __construct(string $main, ?string $local = null)
    {
        $this->add(new Config($main, $local));
        $this->add(new Registry());
        $this->add(new SlimApplication());
    }

    public function start()
    {
        $this->invoke($this);
    }
}
