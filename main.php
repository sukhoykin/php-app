<?php

declare(strict_types=1);

use Sukhoykin\App\Config\Main;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Component\Console;
use Sukhoykin\App\Provider\DatasourceProvider;
use Sukhoykin\App\Provider\MonologProvider;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim\App;
use Sukhoykin\App\Mapper\Datasource;
use Sukhoykin\App\Console\SchemaCommand;
use Sukhoykin\App\Slim\SlimApplication;
use Sukhoykin\App\Slim\SlimMiddleware;
use Sukhoykin\App\Slim\SlimRoute;

return [

    Main::DEBUG => true,

    Registry::class => [
        Datasource::class => [
            DatasourceProvider::class => [
                Datasource::DEFAULT_NAME => 'pgsql:host=localhost;dbname=devar-market;user=homestead;password=secret'
            ]
        ],
        LoggerInterface::class => [
            MonologProvider::class => [
                'name' => 'api',
                'stream' => __DIR__ . '/../var/test.log',
                'datetime' => 'Y-m-d H:i:s.u',
                'format' => "%datetime% %context.transaction%:%context.address% [%level_name%] %message%\n",
                'level' => Logger::DEBUG
            ]
        ]
    ],

    Console::class => [
        SchemaCommand::class => [
            'path' => __DIR__ . '/schema'
        ]
    ],

    SlimApplication::class => [
        SlimMiddleware::class => [
            'middleware' => [
                ContentTypeMiddleware::class,
                ContentLengthMiddleware::class,
                AccessLogMiddleware::class,
                ContextMiddleware::class
            ],
            'error' => []
        ],
        SlimRoute::class => [
            'define' => function (App $app) {
                $app->get('/', 'HelloWorld.get');
            }
        ]
    ]
];
