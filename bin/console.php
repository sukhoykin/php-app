#!/usr/bin/env php
<?php

declare(strict_types=1);

foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

use Sukhoykin\App\Composite;
use Sukhoykin\App\Component\Config;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Component\Console;
use Sukhoykin\App\Console\Arguments;
use Sukhoykin\App\Provider\MonologProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$ROOT = dirname(__DIR__);

$CONFIG_MAIN = $ROOT . '/main.php';
$CONFIG_LOCAL = $ROOT . '/main.local.php';

if (!file_exists($CONFIG_MAIN)) {
    die(sprintf("Config not defined: %s\n", $CONFIG_MAIN));
}

$arguments = new Arguments($argv);

$config = new Config(
    $CONFIG_MAIN,
    $CONFIG_LOCAL,
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
