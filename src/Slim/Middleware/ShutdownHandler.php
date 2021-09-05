<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\ResponseEmitter;

use Exception;

class ShutdownHandler
{
    private $request;
    private $handler;

    public function __construct(Request $request, ErrorHandlerInterface $handler)
    {
        $this->request = $request;
        $this->handler = $handler;
    }

    public function __invoke()
    {

        $error = error_get_last();

        if ($error) {

            switch ($error['type']) {
                case E_USER_ERROR:
                    $type = 'FATAL';
                    break;

                case E_USER_WARNING:
                    $type = 'WARNING';
                    break;

                case E_USER_NOTICE:
                    $type = 'NOTICE';
                    break;

                default:
                    $type = 'ERROR';
                    break;
            }

            $e = new Exception(sprintf(
                '(%s) %s in file %s:%d',
                $type,
                $error['message'],
                $error['file'],
                $error['line']
            ));

            $response = $this->handler->__invoke($this->request, $e, false, true, false);

            if (ob_get_contents()) {
                ob_clean();
            }

            $emitter = new ResponseEmitter();
            $emitter->emit($response);
        }
    }
}
