<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Application;
use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;
use Psr\Container\ContainerInterface;

use App\Util\Config;
use App\Util\Profiler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Psr\Log\LoggerInterface;

class AccessLogMiddleware implements ComponentInterface
{
    private $profiler;
    private $log;

    public $debug = false;

    public function register(RegistryInterface $registry, ContainerInterface $container)
    {
        $config = $container->get(Config::class);

        $this->log = $container->get(LoggerInterface::class);
        $this->profiler = $container->get(Profiler::class);

        $this->debug = $config->debug;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->profiler->start('request');

        $query = $request->getUri()->getQuery();

        if ($this->debug) {

            $body = $request->getBody()->getContents();

            $this->log->debug(sprintf(
                "%s %s%s%s%s%s",
                $request->getMethod(),
                $request->getUri()->getPath(),
                $query ? '?' : '',
                $query ? $query : '',
                $body ? "\n" : '',
                $body ? $body : ''
            ));
        }

        $response = $handler->handle($request);

        if ($this->debug) {

            $body = (string) $response->getBody();

            $this->log->info(sprintf(
                '%s %s%s%s %d %02.3fs %02.3fs%s%s',
                $request->getMethod(),
                $request->getUri()->getPath(),
                $query ? '?' : '',
                $query ? $query : '',
                $response->getStatusCode(),
                $this->profiler->took('request'),
                $this->profiler->took(Application::METRIC_APP),
                $body ? "\n" : '',
                $body ? $body : ''
            ));
        } else {

            $this->log->info(sprintf(
                '%s %s%s%s %d %02.3fs %02.3fs',
                $request->getMethod(),
                $request->getUri()->getPath(),
                $query ? '?' : '',
                $query ? $query : '',
                $response->getStatusCode(),
                $this->profiler->took('request'),
                $this->profiler->took(Application::METRIC_APP)
            ));
        }

        return $response;
    }
}
