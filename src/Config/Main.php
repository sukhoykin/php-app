<?php

declare(strict_types=1);

namespace Sukhoykin\App\Config;

class Main extends Section
{
    const DEBUG = 'debug';

    public $debug = false;

    public function __construct(?string $path = null, ?array $config = null)
    {
        parent::__construct($path, $config);

        $this->debug = $this->getBool(self::DEBUG, $this->debug);
    }
}
