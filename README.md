# PHP Application Toolkit

## Incubation
* config:
```php
return [
    
    'debug' => {boolean},

    'pdo' => 'pgsql:host={};dbname={};user={};password={}',

    'console' => {path to config},
    'middleware' => {path to config},
    'routes' => {path to config}
```
* console:
```php
return [

    'schema' => SchemaCommand::class,
    ...

];
```
* middleware:
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
* routes:
```php
return function (App $app) {

    $app->get('/', SiteController::class . ':index');
    ...

```
