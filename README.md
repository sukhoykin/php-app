# PHP Application Toolkit
> Incubation

## Usage
* entry:
```php

$config = [
    ConfigProvider::DEFAULT => '/path/to/config.php',
    ConfigProvider::LOCAL => '/path/to/config.local.php'
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

* config:
```php
return [
    
    'debug' => {boolean},

    Database::CONFIG => 'pgsql:host={};dbname={};user={};password={}',

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

