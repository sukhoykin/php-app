<?php

declare(strict_types=1);

namespace Sukhoykin\App\Console;

use Sukhoykin\App\Interfaces\Executable;
use Exception;

class UsageError extends Exception
{
    private $executable;

    public function __construct(string $message, Executable $executable)
    {
        parent::__construct($message);
        $this->executable = $executable;
    }

    public function getExecutable(): Executable
    {
        return $this->executable;
    }
}
