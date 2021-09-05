<?php

declare(strict_types=1);

namespace Sukhoykin\App\Interfaces;

use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\ErrorHandlerInterface;

interface ErrorHandler extends ErrorHandlerInterface
{
    function setResponseFactory(ResponseFactoryInterface $factory);
}
