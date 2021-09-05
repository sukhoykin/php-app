<?php

declare(strict_types=1);

namespace Sukhoykin\App\Config;

class Monolog extends Section
{
    public $name, $stream, $datetime, $format, $level;

    public function __construct(?string $path = null, ?array $config = null)
    {
        parent::__construct($path, $config);

        $this->name = $this->getString('name');
        $this->stream = $this->getString('stream');
        $this->datetime = $this->getString('datetime');
        $this->format = $this->getString('format');
        $this->level = $this->getInt('level');
    }
}
