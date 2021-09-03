<?php

declare(strict_types=1);

namespace Sukhoykin\App\Interfaces;

use Psr\Container\ContainerInterface;

interface Provider
{
    function provide(string $class, ContainerInterface $container);
}
