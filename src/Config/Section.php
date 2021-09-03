<?php

declare(strict_types=1);

namespace Sukhoykin\App\Config;

use Exception;

class Section
{
    private const TYPE_BOOL = 'bool';
    private const TYPE_INT = 'int';
    private const TYPE_STRING = 'string';
    private const TYPE_ARRAY = 'array';

    private $config = [];
    private $checks;

    public function __construct(?string $path = null, ?array $config = null)
    {
        if ($path) {
            $this->overload($path);
        }

        if ($config) {
            $this->override($config);
        }

        $this->checks = [
            self::TYPE_BOOL => function ($val) { return is_bool($val); },
            self::TYPE_INT => function ($val) { return is_int($val); },
            self::TYPE_STRING => function ($val) { return is_string($val); },
            self::TYPE_ARRAY => function ($val) { return is_array($val); }
        ];
    }

    public function overload(string $path): Section
    {
        if (!file_exists($path)) {
            throw new Exception(sprintf('Config "%s" is not found', $path));
        }

        $config = include $path;

        if (!is_array($config)) {
            throw new Exception(sprintf('Config "%s" must return an array', $path));
        }

        $this->override($config);

        return $this;
    }

    public function override(array $config): Section
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

        return $this;
    }

    public function has(string $section): bool
    {
        return isset($this->config[$section]);
    }

    public function get(string $section, $default = null)
    {
        if (!$this->has($section)) {

            if (is_null($default)) {
                throw new Exception(sprintf('Section "%s" is not found for "%s"', $section, get_class($this)));
            } else {
                return $default;
            }
        }

        return $this->config[$section];
    }

    private function getType(string $type, string $section, $default = null)
    {
        if (!isset($this->checks[$type])) {
            throw new Exception(sprintf('Invalid check type: ' . $type));
        }

        $check = $this->checks[$type];

        if (!$check($this->get($section, $default))) {
            throw new Exception(sprintf('Section "%s" must be "%s"', $section, $type));
        }

        return $this->get($section, $default);
    }

    public function getBool(string $section, ?bool $default = null): bool
    {
        return $this->getType(self::TYPE_BOOL, $section, $default);
    }

    public function getInt(string $section, ?int $default = null): int
    {
        return $this->getType(self::TYPE_INT, $section, $default);
    }

    public function getString(string $section, ?string $default = null): string
    {
        return $this->getType(self::TYPE_STRING, $section, $default);
    }

    public function getArray(string $section, ?array $default = null): array
    {
        return $this->getType(self::TYPE_ARRAY, $section, $default);
    }

    public function getSection(string $section, ?string $class = null, ?array $default = null): Section
    {
        $data = $this->getArray($section, $default);
        return ($class ? new $class(null, $data) : new Section(null, $data));
    }
}
