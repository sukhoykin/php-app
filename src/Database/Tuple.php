<?php

declare(strict_types=1);

namespace App\Database;

class Tuple
{
    private $tuple;

    public function __construct(array $tuple)
    {
        $this->tuple = $tuple;
    }

    public function extract(array $names): Tuple
    {
        $this->tuple = array_filter(
            $this->tuple,
            function ($name) use ($names) {
                return in_array($name, $names);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this;
    }

    public function reduce(array $names): Tuple
    {
        $this->tuple = array_filter(
            $this->tuple,
            function ($name) use ($names) {
                return !in_array($name, $names);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this;
    }

    public function nulls(): Tuple
    {
        $this->tuple = array_filter(
            $this->tuple,
            function ($value) {
                return is_null($value);
            }
        );

        return $this;
    }

    public function notNull(): Tuple
    {
        $this->tuple = array_filter(
            $this->tuple,
            function ($value) {
                return !is_null($value);
            }
        );

        return $this;
    }

    public function size(): int
    {
        return count($this->tuple);
    }

    public function items(): array
    {
        return $this->tuple;
    }

    public function names(): array
    {
        return array_keys($this->items());
    }

    public function values(): array
    {
        return array_values($this->items());
    }
}
