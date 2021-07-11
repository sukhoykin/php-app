<?php

declare(strict_types=1);

namespace App\Database;

use Psr\Log\LoggerInterface;
use Exception;

class Database
{
    const CONNECTION_DEFAULT = 'default';

    private $log;

    public $debug = false;

    private $definitions = [];
    private $connections = [];

    private $mappers = [];

    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function define($dsn, $name = self::CONNECTION_DEFAULT)
    {
        $this->definitions[$name] = $dsn;
    }

    public function connection($name = self::CONNECTION_DEFAULT, $id = 0): Connection
    {
        if (!isset($this->definitions[$name])) {
            throw new Exception('Connection "' . $name . '" is not defined');
        }

        if (!isset($this->connections[$name][$id])) {

            $pdo = new \PDO($this->definitions[$name]);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->connections[$name][$id] = new Connection($pdo);
        }

        return $this->connections[$name][$id];
    }

    public function mapper($class, $name = self::CONNECTION_DEFAULT, $id = 0): Mapper
    {
        if (!isset($this->mappers[$name][$id][$class])) {

            $connection = $this->connection($name, $id);

            $mapper = new $class($connection->pdo());
            $mapper->debug = $this->debug;

            if ($this->log) {
                $mapper->setLogger($this->log);
            }

            $this->mappers[$name][$id][$class] = $mapper;
        }

        return $this->mappers[$name][$id][$class];
    }
}
