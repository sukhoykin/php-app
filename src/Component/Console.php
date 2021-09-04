<?php

declare(strict_types=1);

namespace Sukhoykin\App\Component;

use Sukhoykin\App\Component;
use Sukhoykin\App\Composite;
use Sukhoykin\App\Config\Section;
use Sukhoykin\App\Interfaces\Configurable;
use Sukhoykin\App\Interfaces\Executable;

use Psr\Log\LoggerInterface;
use Sukhoykin\App\Console\Arguments;

class Console implements Component, Configurable, Executable
{
    const USAGE_ERROR = 1;

    private $config, $arguments, $registry, $log;

    public function __construct(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }

    public function configurate(Section $config)
    {
        $this->config = $config;
    }

    public function invoke(Composite $root)
    {
        echo "EXEC\n";
        /** @var Registry */
        $this->registry = $root->get(Registry::class);
        $this->log = $this->registry->get(LoggerInterface::class);

        $this->execute($this->arguments);
    }

    public function getDescription(): string
    {
        return 'Console commands and applications';
    }

    public function getUsage(): string
    {
        return '<command> [args] [-v]';
    }

    public function execute(Arguments $arguments): int
    {
        
        $command = $arguments->shift();

        if (is_null($command)) {
            return self::USAGE_ERROR;
        }

        if (!isset($this->config[$command])) {
            $this->log->error("Invalid command '$command'");
            return self::USAGE_ERROR;
        }

        $instance = $this->registry->get(Registry::class);
        return $instance->execute($arguments);
    }
}
