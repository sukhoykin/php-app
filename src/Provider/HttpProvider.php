<?php

declare(strict_types=1);

namespace App\Provider;

use App\Interfaces\ProviderInterface;
use Psr\Container\ContainerInterface;

use App\Util\Config;
use App\Client\HttpClient;
use Psr\Log\LoggerInterface;

class HttpProvider implements ProviderInterface
{
    public function provide(string $class, ContainerInterface $container)
    {
        $config = $container->get(Config::class);
        $logger = $container->get(LoggerInterface::class);

        $client = new HttpClient();

        $client->setLogger($logger);
        $client->debug = $config->debug;

        return $client;
    }
}
