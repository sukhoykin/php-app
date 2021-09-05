<?php

declare(strict_types=1);

namespace Sukhoykin\App\Component;

use Sukhoykin\App\Component;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Main;
use Sukhoykin\App\Interfaces\Configurable;

class Config implements Component
{
    private $config;

    public function __construct(string $main, ?string $overload = null, ?array $override = null)
    {
        $config = new Main($main);

        if ($overload && file_exists($overload)) {
            $config->overload($overload);
        }

        if ($override) {
            $config->override($override);
        }

        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        foreach ($root->getComponents() as $component) {

            if ($component instanceof Configurable) {

                $config = $this->config->getSection(get_class($component));
                $component->configurate($config);
            }
        }
    }
}
