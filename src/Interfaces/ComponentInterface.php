<?php

declare(strict_types=1);

namespace App\Interfaces;

use Psr\Container\ContainerInterface;

interface ComponentInterface
{
    function register(RegistryInterface $registry, ContainerInterface $container);
}
