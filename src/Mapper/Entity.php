<?php

declare(strict_types=1);

namespace App\Mapper;

use Exception;

class Entity extends Relation
{
    const ATTRIBUTE_PRIMARY_KEY =   0b00010000;
    const ATTRIBUTE_REGULAR =       0b00100000;

    private $datasource;

    private $table;
    private $primaryKey;

    protected function __construct(string $class, array $primaryKey)
    {
        parent::__construct($class);

        $this->primaryKey = $primaryKey;

        $className = preg_replace('/.*\\\/', '', $this->getClass());
        preg_match_all('/[A-Z][a-z0-9]+/', $className, $matches);

        $this->table =  strtolower(implode('_', $matches[0]));
    }

    protected function filterAttribute(int $filter, string $attribute)
    {
        if (!parent::filterAttribute($filter, $attribute)) {
            return false;
        }

        if ($filter & self::ATTRIBUTE_PRIMARY_KEY && !in_array($attribute, $this->getPrimaryKey())) {
            return false;
        }

        if ($filter & self::ATTRIBUTE_REGULAR && in_array($attribute, $this->getPrimaryKey())) {
            return false;
        }

        return true;
    }

    public function setDatasource(?Datasource $datasource, ?Result $result = null)
    {
        $this->datasource = $datasource;

        if ($result) {

            $row = $result->fetchAssoc();

            foreach ($row as $attribute => $value) {
                $this->{$attribute} = $value;
            }
        }
    }

    public function getDatasource(): Datasource
    {
        if (!$this->datasource) {
            throw new Exception('Datasource is not set');
        }

        return $this->datasource;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): array
    {
        return $this->primaryKey;
    }
}
