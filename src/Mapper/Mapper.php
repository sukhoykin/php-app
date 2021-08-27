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

    public function insert(Entity $entity, $skipConflict = false): int
    {
        $attributes = $entity->attributes(Relation::ATTRIBUTE_NOT_NULL);
        $values = $entity->values(Relation::ATTRIBUTE_NOT_NULL);
        $returning = $entity->attributes(Entity::ATTRIBUTE_PRIMARY_KEY | Relation::ATTRIBUTE_NULL);

        $result = $this->query('INSERT INTO')
            ->append($this->getTableOfEntity($entity))
            ->ifonly(count($attributes) > 0)
            ->append('(')
            ->concat($attributes, ',')
            ->append(') VALUES (')
            ->values($values, ',')
            ->append(')')
            ->ifonly($skipConflict)
            ->append('ON CONFLICT DO NOTHING')
            ->ifonly(count($returning) > 0)
            ->append('RETURNING')
            ->concat($returning, ',')
            ->execute();

        $entity->setDatasource($this->datasource, count($returning) ? $result : null);

        return $result->rowCount();
    }

    public function update(Entity $entity): int
    {
        $keyMap = $entity->map(Entity::ATTRIBUTE_PRIMARY_KEY);
        $valueMap = $entity->map(Relation::ATTRIBUTE_NOT_NULL | Entity::ATTRIBUTE_REGULAR);

        $result = $this->query('UPDATE')
            ->append($this->getTableOfEntity($entity))
            ->ifonly(count($valueMap) > 0)
            ->append('SET')
            ->assign($valueMap, ',')
            ->ifonly(count($keyMap) > 0)
            ->append('WHERE')
            ->assign($keyMap, ' AND ')
            ->execute();

        return $result->rowCount();
    }

    public function delete(Entity $entity): int
    {
        $keyMap = $entity->map(Entity::ATTRIBUTE_PRIMARY_KEY);

        $result = $this->query('DELETE FROM')
            ->append($this->getTableOfEntity($entity))
            ->ifonly(count($keyMap) > 0)
            ->append('WHERE')
            ->assign($keyMap, ' AND ')
            ->execute();

        return $result->rowCount();
    }
}
