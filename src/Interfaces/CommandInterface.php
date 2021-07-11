<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CommandInterface
{
    function run($context);
}
