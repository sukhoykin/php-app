<?php

declare(strict_types=1);

namespace App\Interfaces;

use Psr\Container\ContainerInterface;

interface RegistryInterface extends ContainerInterface
{
    function lookup(string $class);
}
