<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Util\Profiler;
use App\Util\Stdout;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;

class Query
{
    private $pdo;
    private $datasource;

    private $query = [];
    private $params = [];

    private $statement;
    private $sql;

    private $profiler;
    private $log;

    public $debug = false;
    public $silent = false;

    public function __construct(PDO $pdo, ?Datasource $datasource = null)
    {
        $this->pdo = $pdo;
        $this->datasource = $datasource;

        $this->profiler = new Profiler();
        $this->log = new Stdout();
    }

    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }

    private function appendParams(array $params)
    {
        foreach ($params as $param) {

            switch (gettype($param)) {

                case 'boolean':
                    $this->params[] = $param ? 'true' : 'false';
                    break;

                default:
                    $this->params[] = $param;
                    break;
            }
        }
    }

    /**
     * Append part of query and parameters.
     * 
     * Example:
     * $query->append('SELECT * FROM "table" WHERE id = ? AND status = ?', [1, 'active']);
     * 
     */
    public function append(string $sql, ?array $params = null): Query
    {
        $this->query[] = $sql;

        if ($params) {
            $this->appendParams($params);
        }

        return $this;
    }

    /**
     * Format and append assigments of map elements.
     * 
     * Example:
     * $query->assign(['id' => 1, 'status' => 'active'], 'AND');
     *
     */
    public function assign(array $map, string $separator): Query
    {
        $query = array_map(
            function ($name) {
                return $name . ' = ?';
            },
            array_keys($map)
        );

        $this->append(
            implode($separator, $query),
            array_values($map)
        );

        return $this;
    }

    /**
     * Concatenate and append list elements.
     * 
     * Example:
     * $query->concat(['id', 'name'], ', ');
     * 
     * Will result "id, name".
     * 
     */
    public function concat(array $list, string $separator): Query
    {
        $this->query[] = implode($separator, $list);

        return $this;
    }

    /**
     * Format and append parameter placeholders.
     * 
     * Example:
     * $query->values([1, 'active'], ', ');
     * 
     * Will result "?, ?".
     */
    public function values(array $params, string $separator): Query
    {
        $query = array_fill(0, count($params), '?');

        $this->query[] = implode($separator, $query);
        $this->appendParams($params);

        return $this;
    }

    private function checkStatement()
    {
        if (!$this->statement) {
            throw new Exception('Illegal state: query is not prepared (call prepare first)');
        }
    }

    public function prepare(): Query
    {
        if ($this->statement) {
            return $this;
        }

        $sql = implode(' ', $this->query);

        $this->profiler->start('prepare');

        if ($this->debug && !$this->silent) {
            $this->log->debug($sql);
        }

        $this->statement = $this->pdo->prepare($sql);
        $this->sql = $sql;

        return $this;
    }

    public function execute(?array $params = null): Query
    {
        $this->prepare();

        $this->profiler->start('execute');

        if ($this->debug && !$this->silent && $params) {
            $this->log->debug(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->statement->execute($params ?? $this->params);

        if ($this->debug && !$this->silent) {

            $this->log->debug(
                sprintf(
                    '%s %02.3fs %02.3fs',
                    $this->sql,
                    $this->profiler->took('execute'),
                    $this->profiler->took('prepare')
                )
            );
        }

        return $this;
    }

    public function rowCount()
    {
        $this->checkStatement();
        return $this->statement->rowCount();
    }

    public function fetch(?string $class = null)
    {
        $this->checkStatement();

        $result = null;

        if ($class) {

            if ($class == stdClass::class) {
                $result = $this->statement->fetch(PDO::FETCH_OBJ);
            } else {
                $result = $this->statement->fetchObject($class);
            }

            if ($result instanceof Entity) {
                $result->setDatasource($this->datasource);
            }
        } else {
            $result = $this->statement->fetch();
        }

        return $result ? $result : null;
    }

    public function fetchAll(?string $class = null)
    {
        $this->checkStatement();

        if ($class) {
            if ($class == stdClass::class) {
                return $this->statement->fetchAll(PDO::FETCH_OBJ, $class);
            }
            return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
        } else {
            return $this->statement->fetchAll();
        }
    }

    public function fetchAssoc()
    {
        $this->checkStatement();
        $result = $this->statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    public function fetchAllAssoc()
    {
        $this->checkStatement();
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllColumn(string $column)
    {
        $this->checkStatement();

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
            $this->statement = null;
        }
    }
}
