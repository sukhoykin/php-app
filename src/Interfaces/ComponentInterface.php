<?php

declare(strict_types=1);

namespace App\Interfaces;

interface ComponentInterface
{
    function register(RegistryInterface $registry);
}
