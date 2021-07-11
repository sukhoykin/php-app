<?php

declare(strict_types=1);

namespace App\Console;

class Arguments
{
    private $argv;

    private $command;

    public function __construct($argv = [], $shiftScript = true)
    {
        if ($shiftScript) {
            array_shift($argv);
        }

        $this->argv = $argv;
    }

    public function argv(): array
    {
        return $this->argv;
    }

    public function count(): int
    {
        return count($this->argv);
    }

    public function shift(): ?string
    {
        return array_shift($this->argv);
    }

    public function has($name): bool
    {
        foreach ($this->argv as $arg) {
            if ($arg == $name) {
                return true;
            }
        }

        return false;
    }

    public function option($name): ?string
    {
        for ($i = 0; $i < count($this->argv); $i++) {

            if ($this->argv[$i] == $name) {

                if ($i + 1 < count($this->argv)) {
                    return $this->argv[$i + 1];
                } else {
                    break;
                }
            }
        }

        return null;
    }

    public function shiftCommand()
    {
        return $this->command = $this->shift();
    }

    public function getCommand()
    {
        return $this->command;
    }
}
