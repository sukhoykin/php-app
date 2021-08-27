<?php

declare(strict_types=1);

namespace App\Mapper;

use Exception;

class Entity extends Relation
{
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

    public function setDatasource(?Datasource $datasource)
    {
        $this->datasource = $datasource;
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
