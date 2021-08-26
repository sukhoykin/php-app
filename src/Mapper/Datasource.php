<?php

declare(strict_types=1);

namespace App\Mapper;

use PDO;
use Psr\Log\LoggerInterface;
use Exception;

class Datasource
{
    const DEFAULT_NAME = 'default';
    const DEFAULT_ID = 0;

    private $log;

    public $debug = false;

    private $definitions = [];
    private $connections = [];

    private $mappers = [];

    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function define(string $dsn, string $name = self::DEFAULT_NAME)
    {
        if (isset($this->connections[$name])) {
            throw new Exception('Connection already open: ' . $name);
        }

        $this->definitions[$name] = $dsn;
    }

    public function getConnection($name = self::DEFAULT_NAME, $id = self::DEFAULT_ID): PDO
    {
        if (!isset($this->definitions[$name])) {
            throw new Exception('Connection is not defined: ' . $name);
        }

        if (!isset($this->connections[$name][$id])) {

            $pdo = new \PDO($this->definitions[$name]);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->connections[$name][$id] = $pdo;
        }

        return $this->connections[$name][$id];
    }

    public function getMapper($class, $name = self::DEFAULT_NAME, $id = self::DEFAULT_ID): Mapper
    {
        if (!isset($this->mappers[$name][$id][$class])) {

            $mapper = new $class($this->getConnection($name, $id));
            $mapper->debug = $this->debug;

            if ($this->log) {
                $mapper->setLogger($this->log);
            }

            $this->mappers[$name][$id][$class] = $mapper;
        }

        return $this->mappers[$name][$id][$class];
    }
}
