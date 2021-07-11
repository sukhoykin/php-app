<?php

declare(strict_types=1);

namespace App\Component;

use App\Component;
use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Request;

class SlimApp extends Component implements ComponentInterface
{
    private $app;
    private $request;

    public function register(RegistryInterface $registry)
    {
        AppFactory::setContainer($registry);
        $this->app = AppFactory::create();

        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $this->request = $serverRequestCreator->createServerRequestFromGlobals();
    }

    public function getApp(): App
    {
        return $this->app;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function run()
    {
        $this->app->run($this->request);
    }
}
