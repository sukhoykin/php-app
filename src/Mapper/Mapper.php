<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Error\InvalidArgumentError;

class Mapper
{
    public function tableOfClass(string $class)
    {
        $name = preg_replace('/.*\\\/', '', $class);
        preg_match_all('/[A-Z][a-z0-9]+/', $name, $matches);

        return strtolower(implode('_', $matches[0]));
    }

    public function tableOfRelation(Relation $entity)
    {
        return $this->tableOfClass(get_class($entity));
    }

    public function query(string $query, array $params = null): Query
    {
        $instance = new Query($this);
        return $instance->append($query, $params);
    }

    public function findAll(string $class, array $where = null)
    {
        $query = $this->query('SELECT * FROM')->append($this->tableOfClass($class));

        if ($where) {
            $query->append('WHERE')->assign('AND', $where);
        }

        $query->execute();

        return $this->fetchAll($class);
    }

    public function find(string $class, array $where)
    {
        $this->query('SELECT * FROM')
            ->append($this->tableOfClass($class))
            ->append('WHERE')
            ->assign(' AND ', $where)
            ->execute();

        return $this->fetch($class);
    }

    public function exists(string $class, array $where): bool
    {
        $this->query('SELECT 1 FROM')
            ->append($this->tableOfClass($class))
            ->append('WHERE')
            ->assign(' AND ', $where)
            ->execute();

        return (bool) $this->fetch();
    }

    public function insert(Relation $relation, $skipConflict = false): int
    {
        $tuple = $relation->tuple()->notNull();

        $query = $this->query('INSERT INTO')
            ->append($this->tableOfRelation($relation))
            ->append('(')
            ->concat(', ', $tuple->names())
            ->append(') VALUES (')
            ->values(', ', $tuple->values())
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
