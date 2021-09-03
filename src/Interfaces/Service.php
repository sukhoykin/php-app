<?php

declare(strict_types=1);

namespace Sukhoykin\App\Interfaces;

use Psr\Container\ContainerInterface;

interface Service
{
    function setRegistry(ContainerInterface $registry);
}
