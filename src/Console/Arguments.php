<?php

declare(strict_types=1);

namespace Sukhoykin\App\Console;

class Arguments
{
    private $argv;

    public function __construct(array $argv = [], $shiftScript = true)
    {
        $this->argv = $argv;

        if ($shiftScript) {
            $this->shift();
        }
    }

    public function values(): array
    {
        return $this->argv;
    }

    public function has(string $value): bool
    {
        foreach ($this->values() as $arg) {
            if ($arg == $value) {
                return true;
            }
        }

        return false;
    }

    public function first(): ?string
    {
        return $this->argv[0] ?? null;
    }

    public function shift(): ?string
    {
        return array_shift($this->argv);
    }

    public function count(): int
    {
        return count($this->argv);
    }
}
