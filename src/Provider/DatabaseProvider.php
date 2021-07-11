<?php

declare(strict_types=1);

namespace App\Provider;

use App\Database\Database;

use App\Interfaces\ProviderInterface;
use Psr\Container\ContainerInterface;

use App\Util\Config;
use Psr\Log\LoggerInterface;

class DatabaseProvider implements ProviderInterface
{
    const CONFIG = 'pdo';
    const DATABASE_DEFAULT = 'default';

    public function provide(string $class, ContainerInterface $container)
    {
        $config = $container->get(Config::class);
        $logger = $container->get(LoggerInterface::class);

        $database = new Database();

        $database->define($config->{self::CONFIG});
        $database->setLogger($logger);

        $database->debug = $config->debug;

        return $database;
    }
}
