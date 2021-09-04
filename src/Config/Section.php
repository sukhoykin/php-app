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

    private function merge_recursive(array &$config, array $merge)
    {
        foreach ($merge as $section => $data) {
            
            if (!is_array($data)) {
                $config[$section] = $data;
                continue;
            }

            if (!isset($config[$section])) {
                $config[$section] = [];
            }

            $this->merge_recursive($config[$section], $data);
        }
    }

    public function override(array $config): Section
    {
        $this->merge_recursive($this->config, $config);
        return $this;
    }

    public function getSections(): array
    {
        return array_keys($this->config);
    }

    public function getMap(): array
    {
        return $this->config;
    }

    public function cast(string $classOfSection)
    {
        return new $classOfSection(null, $this->getMap());
    }

    public function has(string $section): bool
    {
        return isset($this->config[$section]);
    }

    private function check(string $section, $value, string $type)
    {
        if (!isset($this->checks[$type])) {
            throw new Exception(sprintf('Invalid check type: ' . $type));
        }

        $check = $this->checks[$type];

        if (!$check($value)) {
            throw new Exception(sprintf('Section "%s" must be "%s"', $section, $type));
        }
    }

    public function get(string $section, $default = null, ?string $type = null)
    {
        $value = $this->has($section) ? $this->config[$section] : $default;

        if (is_null($value)) {
            if ($type) {
                throw new Exception(sprintf('Section "%s:%s" is not found in "%s"', $section, $type, get_class($this)));
            } else {
                throw new Exception(sprintf('Section "%s" is not found in "%s"', $section, get_class($this)));
            }
        }

        if ($type) {
            $this->check($section, $value, $type);
        }

        return $value;
    }

    public function getBool(string $section, ?bool $default = null): bool
    {
        return $this->get($section, $default, self::TYPE_BOOL);
    }

    public function getInt(string $section, ?int $default = null): int
    {
        return $this->get($section, $default, self::TYPE_INT);
    }

    public function getString(string $section, ?string $default = null): string
    {
        return $this->get($section, $default, self::TYPE_STRING);
    }

    public function getArray(string $section, ?array $default = null): array
    {
        return $this->get($section, $default, self::TYPE_ARRAY);
    }

    public function getSection(string $section, ?array $default = null, ?string $class = null): Section
    {
        $data = $this->getArray($section, $default);
        return ($class ? new $class(null, $data) : new Section(null, $data));
    }
}
