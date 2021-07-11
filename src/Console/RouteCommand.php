<?php

declare(strict_types=1);

namespace App\Console;

use App\Controller;
use App\Interfaces\CommandInterface;
use App\Error\NoRouteError;
use Exception;

class RouteCommand extends Controller implements CommandInterface
{
    private $commands = [];
    private $instances = [];

    public function define(string $command, $class)
    {
        $this->commands[$command] = $class;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function run($args)
    {
        if ($args instanceof Arguments) {
        } else {
            throw new Exception('Context must be an ' . Arguments::class . ' instance');
        }

        $command = $args->shiftCommand();

        if (!$command) {
            throw new NoRouteError('Command required');
        }

        if (!isset($this->commands[$command])) {
            throw new NoRouteError('Unknown command: ' . $command);
        }

        if (!isset($this->instances[$command])) {
            $this->instances[$command] = $this->instantiate($this->commands[$command]);
        }

        $this->instances[$command]->run($args);
    }
}
