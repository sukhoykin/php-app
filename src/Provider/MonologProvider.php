<?php

declare(strict_types=1);

namespace Sukhoykin\App\Provider;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

use Psr\Container\ContainerInterface;
use Sukhoykin\App\Config\Monolog;

class MonologProvider extends ConfigurableProvider
{
    public function provide(string $class, ContainerInterface $registry): Logger
    {
        $monolog = $this->getConfig(Monolog::class);

        $formatter = new LineFormatter($monolog->format, $monolog->datetime);
        $formatter->includeStacktraces();

        $handler = new StreamHandler($monolog->stream, $monolog->level);
        $handler->setFormatter($formatter);

        $log = new Logger($monolog->name);
        $log->pushHandler($handler);

        return $log;
    }
}
