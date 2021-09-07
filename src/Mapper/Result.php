<?php

declare(strict_types=1);

namespace Sukhoykin\App\Mapper;

use PDO;
use PDOStatement;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Result
{
    private $statement;
    private $datasource;
    private $log;

    public function __construct(PDOStatement $statement, ?Datasource $datasource = null, ?LoggerInterface $log = null)
    {
        $this->statement = $statement;
        $this->datasource = $datasource;
        $this->log = $log;
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
            if ($this->log && $row instanceof LoggerAwareInterface) {
                $row->setLogger($this->log);
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
                if ($this->log && $row instanceof LoggerAwareInterface) {
                    $row->setLogger($this->log);
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
