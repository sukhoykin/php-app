<?php

declare(strict_types=1);

namespace App\Database;

class Relation
{
    public function __construct(Tuple $tuple = null)
    {
        if ($tuple) {

            $items = $tuple->items();

            foreach ($this->tuple()->names() as $name) {
                $this->$name = $items[$name] ?? null;
            }
        }
    }

    protected function defineKeys(): array
    {
        $name = $this->tuple()->names()[0] ?? null;
        return $name ? [$name] : [];
    }

    /**
     * Return a whole tuple of relation including keys and values.
     */
    public function tuple(): Tuple
    {
        return new Tuple(get_object_vars($this));
    }

    /**
     * Return only tuple conataining keys.
     */
    public function keys(): Tuple
    {
        return $this->tuple()->extract($this->defineKeys());
    }

    /**
     * Return only tuple conataining values excluding keys.
     */
    public function values(): Tuple
    {
        return $this->tuple()->reduce($this->defineKeys());
    }

    public function extract(string $class, array $names = null): Relation
    {
        if ($names) {
            return new $class($this->tuple()->extract($names));
        } else {
            return new $class($this->tuple());
        }
    }

    public function reduce(string $class, array $names): Relation
    {
        return new $class($this->tuple()->reduce($names));
    }
}
