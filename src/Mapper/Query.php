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

    private $query = [];
    private $params = [];

    private $currentStatement;
    private $currentQuery;

    private $profiler;
    private $log;

    public $debug = false;
    public $silent = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

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

    private function checkStatement()
    {
        if (!$this->currentStatement) {
            throw new Exception('Illegal state: query is not prepared (call prepare first)');
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

    public function prepare(): Query
    {
        $query = implode(' ', $this->query);

        $this->profiler->start('pdo.prepare');

        if ($this->debug && !$this->silent) {
            $this->log->debug($query);
        }

        $this->currentStatement = $this->pdo->prepare($query);
        $this->currentQuery = $query;

        return $this;
    }

    public function execute(?array $params = null): Query
    {
        if (!$this->currentStatement) {
            $this->prepare();
        }

        $this->profiler->start('pdo.execute');

        if ($this->debug && !$this->silent && $params) {
            $this->log->debug(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->currentStatement->execute($params ?? $this->params);

        if ($this->debug && !$this->silent) {

            $this->log->debug(
                sprintf(
                    '%s %02.3fs %02.3fs',
                    $this->currentQuery,
                    $this->profiler->took('pdo.execute'),
                    $this->profiler->took('pdo.prepare')
                )
            );
        }

        return $this;
    }

    public function rowCount()
    {
        $this->checkStatement();
        return $this->currentStatement->rowCount();
    }

    public function fetch(?string $class = null)
    {
        $this->checkStatement();

        $result = false;

        if ($class) {
            if ($class == stdClass::class) {
                $result = $this->currentStatement->fetch(PDO::FETCH_OBJ);
            } else {
                $result = $this->currentStatement->fetchObject($class);
            }
        } else {
            $result = $this->currentStatement->fetch();
        }

        return $result ? $result : null;
    }

    public function fetchAll(?string $class = null)
    {
        $this->checkStatement();

        if ($class) {
            if ($class == stdClass::class) {
                return $this->currentStatement->fetchAll(PDO::FETCH_OBJ, $class);
            }
            return $this->currentStatement->fetchAll(PDO::FETCH_CLASS, $class);
        } else {
            return $this->currentStatement->fetchAll();
        }
    }

    public function fetchAssoc()
    {
        $this->checkStatement();
        $result = $this->currentStatement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    public function fetchAllAssoc()
    {
        $this->checkStatement();
        return $this->currentStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllColumn(string $column)
    {
        $this->checkStatement();

        $all = [];

        foreach ($this->currentStatement->fetchAll() as $row) {
            $all[] = $row[$column];
        }

        return $all;
    }

    public function closeCursor()
    {
        if ($this->currentStatement) {
            $this->currentStatement->closeCursor();
            $this->currentStatement = null;
        }
    }
}
