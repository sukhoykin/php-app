<?php

declare(strict_types=1);

namespace Sukhoykin\App\Mapper;

use PDO;

use Sukhoykin\App\Util\Profiler;
use Psr\Log\LoggerInterface;

class Query
{
    private $pdo;
    private $datasource;

    private $enabled = true;
    private $query = [];
    private $params = [];

    private $statement;
    private $sql;

    private $profiler;
    private $log;

    public function __construct(PDO $pdo, ?Datasource $datasource = null)
    {
        $this->pdo = $pdo;
        $this->datasource = $datasource;

        $this->profiler = new Profiler();
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

    public function ifonly(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
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
        if (!$this->enabled) {
            return $this;
        }

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
        if (!$this->enabled) {
            return $this;
        }

        $assign = [];
        $values = [];

        foreach ($map as $attribute => $value) {

            $assign[] = $attribute . ' = ?';
            $values[] = $value;
        }

        $this->append(
            implode($separator, $assign),
            $values
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
        if (!$this->enabled) {
            return $this;
        }

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
        if (!$this->enabled) {
            return $this;
        }

        $query = array_fill(0, count($params), '?');

        $this->query[] = implode($separator, $query);
        $this->appendParams($params);

        return $this;
    }

    public function prepare(): Query
    {
        if ($this->statement) {
            return $this;
        }

        $sql = implode(' ', $this->query);

        $this->profiler->start('prepare');

        if ($this->log) {
            $this->log->debug($sql);
        }

        $this->statement = $this->pdo->prepare($sql);
        $this->sql = $sql;

        return $this;
    }

    public function execute(?array $params = null): Result
    {
        $this->prepare();

        if ($params) {
            $this->params = $params;
        }

        $this->profiler->start('execute');

        if ($this->log && $this->params) {
            $this->log->debug($this->sql . ' ' . json_encode($this->params, JSON_UNESCAPED_UNICODE));
        }

        $this->statement->execute($this->params);

        if ($this->log) {

            $this->log->info(
                sprintf(
                    '%s (%d) %02.3fs %02.3fs',
                    $this->sql,
                    $this->statement->rowCount(),
                    $this->profiler->took('execute'),
                    $this->profiler->took('prepare')
                )
            );
        }

        return new Result($this->statement, $this->datasource);
    }
}
