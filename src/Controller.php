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
        return $this->get(Config::class);
    }

    public function log(): LoggerInterface
    {
        return $this->get(LoggerInterface::class);
    }

    public function profiler(): Profiler
    {
        return $this->get(Profiler::class);
    }
}
