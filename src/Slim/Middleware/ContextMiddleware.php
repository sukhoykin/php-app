<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Middleware;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

use Monolog\Logger;
use Sukhoykin\App\Interfaces\Service;

class ContextMiddleware implements Service
{
    private $transaction;
    private $address;

    public function setRegistry(ContainerInterface $registry)
    {
        $log = $registry->get(LoggerInterface::class);

        if ($log instanceof Logger) {

            $log->pushProcessor(function ($record) {
                return $this->write($record);
            });
        }
    }

    private function getRemoteAddress()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->transaction = uniqid();
        $this->address = $this->getRemoteAddress();

        return $handler->handle($request);
    }

    public function write(array $record): array
    {
        $record['context']['transaction'] = $this->transaction ? $this->transaction : '-';
        $record['context']['address'] = $this->address ? $this->address : '-';

        return $record;
    }
}
