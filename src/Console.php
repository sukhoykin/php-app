<?php

declare(strict_types=1);

namespace Sukhoykin\App;

use Sukhoykin\App\Composite;
use Sukhoykin\App\Component\Config;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Console\Arguments;
use Sukhoykin\App\Provider\MonologProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Console extends Composite
{
    public function __construct(string $main, ?string $local = null, string $logfile = null)
    {
        $arguments = new Arguments($_SERVER['argv']);

        $config = new Config(
            $main,
            $local,
            [
                Registry::class => [
                    LoggerInterface::class => [
                        MonologProvider::class => [
                            'stream' => $logfile ?? 'php://stdout',
                            'format' => "%level_name% %message%\n",
                            'level' => $arguments->has('-v') ? Logger::DEBUG : Logger::INFO
                        ]
                    ]
                ]
            ]
        );

        $this->add($config);
        $this->add(new Registry());
        $this->add(new \Sukhoykin\App\Component\Console($arguments));
    }

    public function start()
    {
        $this->invoke($this);
    }
}
