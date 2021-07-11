<?php

declare(strict_types=1);

namespace App\Component;

use App\Interfaces\ComponentInterface;
use App\Interfaces\RegistryInterface;

use App\Console\Arguments;
use App\Console\RouteCommand;
use App\Util\Config;

use App\Error\NoRouteError;
use App\Error\UsageError;
use Exception;

class ConsoleApp extends RouteCommand implements ComponentInterface
{
    const CONFIG = 'console';

    public function register(RegistryInterface $registry)
    {
        $config = $registry->get(Config::class);
        $commands = require $config->{self::CONFIG};

        foreach ($commands as $command => $class) {
            $this->define($command, $class);
        }
    }

    public function run($args)
    {
        if ($args instanceof Arguments) {
        } else {
            throw new Exception('Console arguments must be instance of ' . Arguments::class);
        }

        if (!$args->count()) {
            exit($this->usage());
        }

        $status = 0;

        try {

            parent::run($args);
            //
        } catch (UsageError $e) {
            $status = $this->usage($e->getCode(), $e->getMessage());
        } catch (NoRouteError $e) {
            $status = $this->error($e->getMessage());
        } catch (Exception $e) {
            $status = $this->error($e);
        }

        exit($status);
    }

    public function usage($status = 0, $usage = null)
    {
        if (!$usage) {

            echo 'Usage: bin/console.php <command> [-v] [args]', "\n";

            foreach ($this->getCommands() as $command => $class) {
                echo '  ', $command, ' (', $class, ')', "\n";
            }
            //
        } else {
            echo 'Usage: bin/console.php ', $usage, "\n";
        }

        return $status;
    }

    public function error($message, $status = 1, $usage = false)
    {
        echo 'ERROR: ', $message, "\n";

        if ($usage) {
            return $this->usage($status);
        } else {
            return $status;
        }
    }
}
