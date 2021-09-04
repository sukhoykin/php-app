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

use Sukhoykin\App\Console\UsageError;
use Exception;

class Console implements Component, Configurable, Executable
{
    const USAGE_ERROR = 1;
    const INTERNAL_ERROR = 2;

    private $config, $arguments;
    private $commands = [];

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
        /** @var Registry */
        $registry = $root->get(Registry::class);
        $log = $registry->get(LoggerInterface::class);

        foreach ($this->config->getSections() as $class) {

            $command = $registry->get($class);

            if ($command instanceof Configurable) {
                $command->configurate($this->config->getSection($class));
            }

            $this->commands[$command->getName()] = $command;
        }

        $status = 0;

        try {
            $this->execute($this->arguments);
        } catch (UsageError $e) {
            $status  = self::USAGE_ERROR;
            $log->error(sprintf("%s\nUsage: %s", $e->getMessage(), $e->getExecutable()->getUsage()));
        } catch (Exception $e) {
            $status  = $e->getCode() ? $e->getCode() : self::INTERNAL_ERROR;
            $log->error($e);
        }

        exit($status);
    }

    public function getName(): string
    {
        return 'console';
    }

    public function getUsage(): string
    {
        $usage[] = '<command> [args] [-v]';

        foreach ($this->commands as $name => $command) {
            $usage[] = sprintf('  %s  %s', $name, $command->getDescription());
        }

        return implode("\n", $usage);
    }

    public function getDescription(): string
    {
        return 'Console application set';
    }

    public function execute(Arguments $arguments)
    {
        $command = $this->arguments->shift();

        if (is_null($command)) {
            throw new UsageError('Command name required', $this);
        }

        if ($command == 'help') {
            $this->log->info(sprintf("%s\nUsage: %s", $this->getDescription(), $this->getUsage()));
            return;
        }

        if (!isset($this->commands[$command])) {
            throw new UsageError("Invalid command '$command'", $this);
        }

        $this->commands[$command]->execute($this->arguments);
    }
}
