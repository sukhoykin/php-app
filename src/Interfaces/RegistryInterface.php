<?php

declare(strict_types=1);

namespace App\Interfaces;

interface RegistryInterface
{
    function lookup(string $class);
}
