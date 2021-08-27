<?php

declare(strict_types=1);

namespace App\Mapper;

use PDO;
use PDOStatement;

class Result
{
    private $statement;
    private $datasource;

    public function __construct(PDOStatement $statement, ?Datasource $datasource = null)
    {
        $this->statement = $statement;
        $this->datasource = $datasource;
    }

    public function rowCount()
    {
        return $this->statement->rowCount();
    }

    public function fetch(?string $class = null)
    {
        $row = null;

        if ($class) {

            if ($class == stdClass::class) {
                $row = $this->statement->fetch(PDO::FETCH_OBJ);
            } else {
                $row = $this->statement->fetchObject($class);
            }

            if ($row instanceof Entity) {
                $row->setDatasource($this->datasource);
            }
        } else {
            $row = $this->statement->fetch();
        }

        return $row ? $row : null;
    }

    public function fetchAll(?string $class = null)
    {
        $rows = null;

        if ($class) {

            if ($class == stdClass::class) {
                $rows = $this->statement->fetchAll(PDO::FETCH_OBJ, $class);
            } else {
                $rows = $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
            }

            foreach ($rows as $row) {
                if ($row instanceof Entity) {
                    $row->setDatasource($this->datasource);
                }
            }
        } else {
            $rows = $this->statement->fetchAll();
        }

        return $rows;
    }

    public function fetchAssoc()
    {
        $result = $this->statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    public function fetchAllAssoc()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllColumn(string $column)
    {
        $all = [];

        foreach ($this->statement->fetchAll() as $row) {
            $all[] = $row[$column];
        }

        return $all;
    }

    public function closeCursor()
    {
        if ($this->statement) {
            $this->statement->closeCursor();
        }
    }
}
