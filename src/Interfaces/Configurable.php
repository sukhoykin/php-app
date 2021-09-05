<?php

declare(strict_types=1);

namespace Sukhoykin\App\Interfaces;

use Sukhoykin\App\Config\Section;

interface Configurable
{
    function configurate(Section $config);
}
