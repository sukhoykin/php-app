# PHP Application Toolkit
> Incubation

## Usage
* entry:
```php

$config = [
    ConfigProvider::DEFAULT => '/path/to/main.php',
    ConfigProvider::LOCAL => '/path/to/main.local.php'
];

$app = new Application($config);

$app->services('/path/to/services.php');
$app->components('/path/to/components.php');

$app->lookup(SlimApp::class)->run();
```

* services:
```php
return [

    Config::class => ConfigProvider::class,
    LoggerInterface::class => MonologProvider::class,
    Database::class => DatabaseProvider::class,
    HttpClient::class => HttpProvider::class,
    ...

];
```

* components:
```php
return [

    SlimApp::class,
    SlimMiddleware::class,
    SlimRoute::class

];
```

* main:
```php
return [
    
    'debug' => {boolean},

    DatabaseProvider::CONFIG => 'pgsql:host={};dbname={};user={};password={}',

    MonologProvider::CONFIG => [
        'name' => '{}',
        'stream' => '{}',
        'datetime' => 'Y-m-d H:i:s.u',
        'format' => "%datetime% %context.transaction%:%context.address% [%level_name%] %message%\n",
        'level' => Logger::DEBUG
    ],

    ConsoleApp::CONFIG => {path to config},
    SlimMiddleware::CONFIG => {path to config},
    SlimRoute::CONFIG => {path to config}
```

* console:
```php
return [

    'schema' => SchemaCommand::class,
    ...

];
```

* slim/middleware:
```php
return [

    'middleware' => [
        ContentTypeMiddleware::class,
        ContentLengthMiddleware::class,
        AccessLogMiddleware::class,
        ContextMiddleware::class
    ],

    'error' => [
        'default' => DefaultErrorHandler::class,
        ServiceError::class => ServiceErrorHandler::class,
        ...
    ]

];
```

* slim/routes:
```php
return function (App $app) {

    $app->get('/', SiteController::class . ':index');
    ...

```

## SchemaCommand

* main:
```php

SchemaCommand::CONFIG => {path to schema directory}

```

* schema directory:
```
config.php
public.1.sql
public.2.sql
...
```

* config (current schema version):
```php
<?php

declare(strict_types=1);

return [
    'public' => 2
];

```
