<?php

declare(strict_types=1);

namespace App\Interfaces;

use Psr\Container\ContainerInterface;

interface ProviderInterface
{
    function provide(string $class, ContainerInterface $container);
}
