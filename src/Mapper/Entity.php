<?php

declare(strict_types=1);

namespace App\Mapper;

use Exception;
use stdClass;

class Entity extends Relation
{
    private $datasource;

    private $table;
    private $primaryKey;

    public function setDatasource(Datasource $datasource)
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
        if (!$this->table) {

            $name = preg_replace('/.*\\\/', '', $this->getClass());
            preg_match_all('/[A-Z][a-z0-9]+/', $name, $matches);

            $this->table =  strtolower(implode('_', $matches[0]));
        }

        return $this->table;
    }

    public function getPrimaryKey(): array
    {
        if (!$this->primaryKey) {
            $this->primaryKey = count($this->getAttributes()) ? [$this->getAttributes()[0]] : [];
        }

        return $this->primaryKey;
    }
}
