<?php

declare(strict_types=1);

namespace Sukhoykin\App\Component;

use Sukhoykin\App\Component;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;

class Configurator implements Component
{
    private $config;

    public function __construct(string $main, ?string $overload = null, ?array $override = null)
    {
        $config = new Section($main);

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
                $component->configurate($this->config->getSection(get_class($component)));
            }
        }
    }
}
