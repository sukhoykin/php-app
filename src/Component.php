<?php

declare(strict_types=1);

namespace Sukhoykin\App;

interface Component
{
    function invoke(Composite $root);
}
