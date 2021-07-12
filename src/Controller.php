<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use App\Util\Profiler;
use App\Util\Config;

class Controller extends Component
{
    public function config(): Config
    {
        return $this->container()->get(Config::class);
    }

    public function log(): LoggerInterface
    {
        return $this->container()->get(LoggerInterface::class);
    }

    public function profiler(): Profiler
    {
        return $this->container()->get(Profiler::class);
    }
}
