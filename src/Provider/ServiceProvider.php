<?php

declare(strict_types=1);

namespace App\Provider;

use App\Interfaces\ProviderInterface;
use Psr\Container\ContainerInterface;

class ServiceProvider implements ProviderInterface
{
    public function provide(string $class, ContainerInterface $container)
    {
        return new $class($container);
    }
}
