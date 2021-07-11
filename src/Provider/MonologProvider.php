<?php

declare(strict_types=1);

namespace App\Provider;

use App\Interfaces\ProviderInterface;
use Psr\Container\ContainerInterface;

use App\Util\Config;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MonologProvider implements ProviderInterface
{
    public function provide(string $class, ContainerInterface $container)
    {
        $config = $container->get(Config::class);

        $monolog = $config->monolog;

        $formatter = new LineFormatter($monolog->format, $monolog->datetime);
        $formatter->includeStacktraces();

        $handler = new StreamHandler($monolog->stream, $monolog->level);
        $handler->setFormatter($formatter);

        $log = new Logger($monolog->name);
        $log->pushHandler($handler);

        return $log;
    }
}
