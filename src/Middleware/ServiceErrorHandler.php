<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Slim\Exception\HttpException;
use App\Error\InvalidArgumentError;
use App\Error\NotFoundError;
use App\Error\ConflictError;
use App\Error\HttpError;
use App\Error\ServiceError;
use Throwable;

class ServiceErrorHandler extends DefaultErrorHandler
{
    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
    {
        try {
            throw $exception;
        } catch (InvalidArgumentError $e) {
            $exception = new HttpException($request, $e->getMessage(), 400, $e);
        } catch (NotFoundError $e) {
            $exception = new HttpException($request, $e->getMessage(), 404, $e);
        } catch (ConflictError $e) {
            $exception = new HttpException($request, $e->getMessage(), 409, $e);
        } catch (ServiceError $e) {
            $exception = new HttpException($request, $e->getMessage(), 500, $e);
        } catch (HttpError $e) {
            $exception = new HttpException($request, $e->getMessage(), $e->getCode(), $e);
        }

        return parent::__invoke($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
    }
}
