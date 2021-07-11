<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Psr\Log\LoggerInterface;
use App\Util\Profiler;
use App\Util\Stdout;
use Exception;
use PDOStatement;

class Connection
{
    private $pdo;

    private $profiler;
    private $log;

    public $debug = false;
    public $silent = false;

    private $currentStatement;
    private $currentQuery;

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

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function getStatement(): ?PDOStatement
    {
        return $this->currentStatement;
    }

    private function checkStatement()
    {
        if (!$this->getStatement()) {
            throw new Exception('Illegal state: statement is not prepared (call prepare first)');
        }
    }

    public function beginTransaction()
    {
        $this->pdo()->beginTransaction();
    }

    public function commit()
    {
        $this->pdo()->commit();
    }

    public function rollBack()
    {
        $this->pdo()->rollBack();
    }

    public function prepare(string $query)
    {
        $this->profiler->start('pdo.prepare');

        if ($this->debug && !$this->silent) {
            $this->log->debug($query);
        }

        $this->currentStatement = $this->pdo()->prepare($query);
        $this->currentQuery = $query;
    }

    public function execute(array $params = null)
    {
        $this->checkStatement();
        $this->profiler->start('pdo.execute');

        if ($this->debug && !$this->silent && $params) {
            $this->log->debug(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->currentStatement->execute($params);

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
    }

    public function rowCount()
    {
        $this->checkStatement();
        return $this->currentStatement->rowCount();
    }

    public function fetch(string $class = null)
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

    public function fetchAll(string $class = null)
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
