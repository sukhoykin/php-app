<?php

declare(strict_types=1);

namespace Sukhoykin\App\Slim\Component;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Request;

use Sukhoykin\App\Composite;
use Sukhoykin\App\Component\Registry;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;

class SlimApplication extends Composite implements Configurable
{
    private $app;
    private $request;

    /**
     * The variant of default CompositeConfigurable implementation
     */
    public function configurate(Section $config)
    {
        foreach ($config->getSections() as $class) {

            $component = new $class();

            if ($component instanceof Configurable) {
                $component->configurate($config->getSection($class));
            }

            $this->add($component);
        }
    }

    public function invoke(Composite $root)
    {
        /** @var Registry */
        $registry = $root->get(Registry::class);

        AppFactory::setContainer($registry);
        $this->app = AppFactory::create();

        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $this->request = $serverRequestCreator->createServerRequestFromGlobals();

        parent::invoke($root);

        $this->app->run($this->request);
    }

    public function getApp(): App
    {
        return $this->app;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
