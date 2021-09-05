<?php

declare(strict_types=1);

namespace Sukhoykin\App;

use Exception;

class Composite implements Component
{
    private $components = [];

    public function add(Component $component)
    {
        $this->components[get_class($component)] = $component;
    }

    public function get(string $class): Component
    {
        if (!isset($this->components[$class])) {
            throw new Exception(sprintf('Component "%s" required but not found', $class));
        }

        return $this->components[$class];
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function invoke(Composite $root)
    {
        foreach ($this->components as $component) {
            $component->invoke($root);
        }
    }
}
