<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Middleware;

use Sukhoykin\App\Slim\SlimApplication;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;

use Slim\Interfaces\ErrorHandlerInterface;
use Exception;
use Throwable;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    private $log;
    private $responseFactory;

    public function __construct(SlimApplication $slim, LoggerInterface $log)
    {
        $app = $slim->getApp();
        $request = $slim->getRequest();

        $this->log = $log;
        $this->responseFactory = $app->getResponseFactory();

        $shutdown = new ShutdownHandler($request, $this);

        register_shutdown_function($shutdown);
        set_error_handler([$this, 'phpErrorHandler']);
        error_reporting(0);
    }

    public function phpErrorHandler($severity, $message, $file, $line)
    {
        $this->log->warning(sprintf('(%s) %s in file %s %d', $severity, $message, $file, $line));
    }

    public function __invoke(ServerRequestInterface $request, Throwable $e, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
    {
        $status = 500;
        $message = "INTERNAL_SERVER_ERROR";

        if ($e instanceof HttpException) {

            $status = $e->getCode();
            $message = $e->getMessage();
        }

        if ($status >= 500) {
            $this->log->error($e);
        }

        $cause = $e->getPrevious();

        if ($cause) {
            $logMessage = $message . ' (' . $cause->getMessage() . ')';
        } else {
            $logMessage = $message;
        }

        try {
            $this->log->info(sprintf(
                '%s %s %d %s',
                $request->getMethod(),
                $request->getUri()->getPath(),
                $status,
                $logMessage
            ));
        } catch (Exception $e) {
            $status = 500;
            $message = $e->getMessage();
        }

        $payload = json_encode(["message" => $message]);

        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
