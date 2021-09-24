<?php

declare(strict_types=1);

namespace Sukhoykin\App\Provider;

use Sukhoykin\App\Provider\ConfigurableProvider;
use Psr\Container\ContainerInterface;
use Sukhoykin\App\Mapper\Datasource;
use Psr\Log\LoggerInterface;

class DatasourceProvider extends ConfigurableProvider
{
    public function provide(string $class, ContainerInterface $registry): object
    {
        $datasource = new Datasource();

        foreach ($this->getConfig()->getMap() as $name => $dsn) {
            $datasource->define($dsn, $name);
        }

        if ($registry->has(LoggerInterface::class)) {
            $datasource->setLogger($registry->get(LoggerInterface::class));
        }

        return $datasource;
    }
}
