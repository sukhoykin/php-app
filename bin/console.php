#!/usr/bin/env php
<?php

declare(strict_types=1);

use Sukhoykin\App\Composite;
use Sukhoykin\App\Component\Config;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Component\Console;
use Sukhoykin\App\Console\Arguments;
use Sukhoykin\App\Provider\MonologProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

const ROOT = __DIR__ . '/..';

require ROOT . '/vendor/autoload.php';

$arguments = new Arguments($argv);

$config = new Config(
    ROOT . '/main.php',
    ROOT . '/main.local.php',
    [
        Registry::class => [
            LoggerInterface::class => [
                MonologProvider::class => [
                    'stream' => 'php://stdout',
                    'format' => "%level_name% %message%\n",
                    'level' => $arguments->has('-v') ? Logger::DEBUG : Logger::INFO
                ]
            ]
        ]
    ]
);

$console = new Composite();

$console->add($config);
$console->add(new Registry());
$console->add(new Console($arguments));

$console->invoke($console);
