<?php

declare(strict_types=1);

namespace App\Util;

use Exception;
use stdClass;

class Config
{
    public function __construct(string $path)
    {
        $this->overload($path);
    }

    public function overload(string $path)
    {
        if (!file_exists($path)) {
            throw new Exception('Config "' . $path . '" not found');
        }

        $config = include $path;

        if (!is_array($config)) {
            throw new Exception('Config "' . $path . '" must return an array');
        }

        $this->override($config);
    }

    public function override(array $config)
    {
        foreach ($config as $section => $data) {

            if (is_array($data)) {

                if (!isset($this->$section)) {
                    $this->$section = new stdClass();
                }

                foreach ($config[$section] as $key => $value) {
                    $this->$section->$key = $value;
                }
                //
            } else {
                $this->$section = $data;
            }
        }
    }
}
