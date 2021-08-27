<?php

declare(strict_types=1);

namespace App\Mapper;

use ReflectionClass;
use ReflectionProperty;

class Relation
{
    const ATTRIBUTE_NOT_NULL = 0b00000001;
    const ATTRIBUTE_NULL = 0b00000010;

    private static $REFLECTIONS;

    private $class;

    private $properties;
    private $attributes;

    protected function __construct(string $class)
    {
        $this->class = $class;
    }

    protected function getClass(): string
    {
        return $this->class;
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

    protected function getClassPropertyNames(string $class, ?int $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED): array
    {
        return array_map(
            function (ReflectionProperty $property) {
                return $property->getName();
            },
            $this->getReflection($class)->getProperties($filter)
        );
    }

    /**
     * @param ?int $filter - Relation::ATTRIBUTE_*
     */
    public function attributes(?int $filter = 0): array
    {
        if (!isset($this->properties)) {
            $this->properties = $this->getClassPropertyNames($this->getClass());
        }

        if (!isset($this->attributes[$filter])) {

            $this->attributes = array_filter($this->properties, function (string $attribute) use ($filter) {

                if ($filter & self::ATTRIBUTE_NOT_NULL && is_null($this->{$attribute})) {
                    return false;
                }

                if ($filter & self::ATTRIBUTE_NULL && !is_null($this->{$attribute})) {
                    return false;
                }

                return true;
            });
        }

        return $this->attributes[$filter];
    }

    /**
     * @param ?int $filter - Relation::ATTRIBUTE_*
     */
    public function values(?int $filter = 0): array
    {
        return array_map(
            function (string $attribute) {
                return $this->{$attribute};
            },
            $this->attributes($filter)
        );
    }

    /**
     * @param ?int $filter - Relation::ATTRIBUTE_*
     */
    public function map(?int $filter = 0): array
    {
        $map = [];

        foreach ($this->attributes($filter) as $attribute) {
            $map[$attribute] = $this->{$attribute};
        }

        return $map;
    }

    public function extract(?array $attributes = null, ?string $class = null): Relation
    {
        $relation = $class ? new $class() : new $this->getClass();

        foreach ($attributes ?? $this->attributes() as $attribute) {
            $relation->{$attribute} = $this->{$attribute};
        }

        return $relation;
    }

    public function reduce(array $attributes, ?string $class = null): Relation
    {
        $relation = $class ? new $class() : new $this->getClass();

        foreach ($this->attributes() as $attribute) {

            if (!in_array($attribute, $attributes)) {
                $relation->{$attribute} = $this->{$attribute};
            }
        }

        return $relation;
    }
}
