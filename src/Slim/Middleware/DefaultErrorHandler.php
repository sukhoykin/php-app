<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Sukhoykin\App\Interfaces\ErrorHandler;
use Exception;
use Throwable;

class DefaultErrorHandler implements ErrorHandler, LoggerAwareInterface
{
    private $log;
    private $responseFactory;

    public function setResponseFactory(ResponseFactoryInterface $factory)
    {
        $this->responseFactory = $factory;
    }

    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
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
