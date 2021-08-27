<?php

declare(strict_types=1);

namespace App\Mapper;

use PDO;

class Mapper
{
    private $pdo;
    private $datasource;

    public function __construct(PDO $pdo, ?Datasource $datasource = null)
    {
        $this->pdo = $pdo;
        $this->datasource = $datasource;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    public function query(?string $sql = null, ?array $params = null): Query
    {
        $query = new Query($this->pdo, $this->datasource);

        if ($sql) {
            $query->append($sql, $params);
        }

        return $query;
    }

    protected function getTableOfEntity(Entity $entity): string
    {
        return $entity->getTable();
    }

    protected function getTableOfEntityClass(string $class): string
    {
        return $this->getTableOfEntity(new $class());
    }

    public function findAll(string $class, ?array $where = null)
    {
        $query = $this->query('SELECT * FROM')->append($this->getTableOfEntityClass($class));

        if ($where) {
            $query->append('WHERE')->assign($where, 'AND');
        }

        return $query->execute()->fetchAll($class);
    }

    public function find(string $class, array $where)
    {
        $query = $this->query('SELECT * FROM')
            ->append($this->getTableOfEntityClass($class))
            ->append('WHERE')
            ->assign($where, 'AND');

        $query->execute()->fetch($class);
    }

    public function exists(string $class, array $where): bool
    {
        $query = $this->query('SELECT 1 FROM')
            ->append($this->getTableOfEntityClass($class))
            ->append('WHERE')
            ->assign($where, 'AND');

        return (bool) $query->execute()->fetch();
    }

    private function notNull(&$map)
    {
        foreach ($map as $key => $value) {
            if (is_null($value)) {
                unset($attributes[$key]);
            }
        }
    }

    public function insert(Entity $entity, $skipConflict = false): int
    {
        $tuple = $relation->tuple()->notNull();

        $query = $this->query('INSERT INTO')
            ->append($this->getTableOfEntity($entity))
            ->append('(')
            ->concat($tuple->names(), ',')
            ->append(') VALUES (')
            ->values($tuple->values(), ', ')
            ->append(')');

        if ($skipConflict) {
            $query->append('ON CONFLICT DO NOTHING');
        }

        $returning = $relation->keys()->nulls()->names();

        if (count($returning)) {
            $query->append('RETURNING')->concat(', ', $returning);
        }

        $query->execute();

        if (count($returning)) {

            $row = $this->fetch();

            foreach ($returning as $name) {
                $relation->$name = $row[$name];
            }
        }

        return $this->rowCount();
    }

    public function update(Relation $relation): int
    {
        $tuple = $relation->values()->notNull();

        if (!$tuple->size()) {
            throw new InvalidArgumentError('Update is empty');
        }

        $this->query('UPDATE')
            ->append($this->tableOfRelation($relation))
            ->append('SET')
            ->assign(', ', $tuple->items())
            ->append('WHERE')
            ->assign(' AND ', $relation->keys()->items())
            ->execute();

        return $this->rowCount();
    }

    public function delete(Relation $relation): int
    {
        $this->query('DELETE FROM')
            ->append($this->tableOfRelation($relation))
            ->append('WHERE')
            ->assign(' AND ', $relation->keys()->items())
            ->execute();

        return $this->rowCount();
    }
}
