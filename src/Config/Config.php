<?php

declare(strict_types=1);

namespace Sukhoykin\App\Config;

use Exception;

class Config
{
    private $config = [];

    public function __construct(?string $path = null, ?array $config = null)
    {
        if ($path) {
            $this->overload($path);
        }

        if ($config) {
            $this->override($config);
        }
    }

    public function overload(string $path)
    {
        if (!file_exists($path)) {
            throw new Exception(sprintf('Config "%s" not found', $path));
        }

        $config = include $path;

        if (!is_array($config)) {
            throw new Exception(sprintf('Config "%s" must return an array', $path));
        }

        $this->override($config);
    }

    public function override(array $config)
    {
        foreach ($config as $section => $data) {

            if (is_array($data)) {

                foreach ($config[$section] as $key => $value) {
                    $this->config[$section][$key] = $value;
                }

            } else {
                $this->config[$section] = $data;
            }
        }
    }

    public function has(string $section): bool
    {
        return isset($this->config[$section]);
    }

    public function get(string $section)
    {
        if (!$this->has($section)) {
            throw new Exception(sprintf('Section "%s" not found', $section));
        }

        return $this->config[$section];
    }

    private $checks = [
        'bool' => function ($val) {
            return is_bool($val);
        },
        'int' => function ($val) {
            return is_int($val);
        },
        'string' => function ($val) {
            return is_string($val);
        },
        'array' => function ($val) {
            return is_array($val);
        }
    ];

    private function getType(string $type, string $section)
    {
        if (!isset($this->checks[$type])) {
            throw new Exception(sprintf('Invalid check type: ' . $type));
        }

        $check = $this->checks[$type];

        if (!$check($this->get($section))) {
            throw new Exception(sprintf('Section "%s" must be "%s"', $section, $type));
        }

        return $this->get($section);
    }

    public function getBool(string $section): bool
    {
        return $this->getType('bool', $section);
    }

    public function getInt(string $section): int
    {
        return $this->getType('int', $section);
    }

    public function getString(string $section): string
    {
        return $this->getType('string', $section);
    }

    public function getArray(string $section): array
    {
        return $this->getType('array', $section);
    }

    public function getSection(string $section): Config
    {
        return new Config(null, $this->getArray($section));
    }
}
