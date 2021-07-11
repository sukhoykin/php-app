<?php

declare(strict_types=1);

namespace App\Provider;

use App\Application;
use App\Interfaces\ProviderInterface;
use Psr\Container\ContainerInterface;

use App\Util\Config;

class ConfigProvider implements ProviderInterface
{
    const DEFAULT = 'default';
    const LOCAL = 'local';

    public function provide(string $class, ContainerInterface $container)
    {
        $config = $container->get(Application::CONFIG);

        $default = $config[self::DEFAULT];
        $local = $config[self::LOCAL] ?? null;

        $config = new Config($default);

        if (file_exists($local)) {
            $config->overload($local);
        }

        $config->override($container->get(Application::CONFIG));

        return $config;
    }
}
