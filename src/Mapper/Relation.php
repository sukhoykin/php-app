<?php

declare(strict_types=1);

namespace App\Mapper;

use ReflectionClass;

class Relation
{
    private static $REFLECTIONS;

    private $class;
    private $attributes;

    protected function __construct(string $class)
    {
        $this->class = $class;
    }

    private function getReflection(string $class): ReflectionClass
    {
        if (!self::$REFLECTIONS) {
            self::$REFLECTIONS = [];
        }

        if (!isset(self::$REFLECTIONS[$class])) {
            self::$REFLECTIONS[$class] = new ReflectionClass($class);
        }

        return self::$REFLECTIONS[$class];
    }

    protected function getClassPropertyNames(string $class, ?int $filter = null): array
    {
        return $this->properties ? $this->properties : $this->properties = array_map(
            function (\ReflectionProperty $property) {
                return $property->getName();
            },
            $this->getReflection($class)->getProperties($filter)
        );
    }

    public function getAttributes(): array
    {
        return $this->attributes ? $this->attributes : $this->attributes = $this->getClassPropertyNames($this->class);
    }

    public function toMap(): array
    {
        $map = [];

        foreach ($this->getAttributes() as $attribute) {
            $map[$attribute] = $this->{$attribute};
        }

        return $map;
    }
}
