<?php

declare(strict_types=1);

namespace App\Database;

class Query
{
    private $mapper;

    private $query = [];
    private $params = [];

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
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
     * Append part of the query and parameters for execution.
     * 
     * Example:
     * $query->append('SELECT * FROM "table" WHERE id = ? AND status = ?', [1, 'active']);
     * 
     */
    public function append(string $query, array $params = null): Query
    {
        $this->query[] = $query;

        if ($params) {
            $this->appendParams($params);
        }

        return $this;
    }

    /**
     * Format assigments of tuple elements and append to the query.
     * 
     * Example:
     * $query->assign('AND', ['id' => 1, 'status' => 'active']);
     * 
     * Will result "id = ? AND status = ?" and append tuple values for execution.
     *
     */
    public function assign(string $separator, array $tuple): Query
    {
        $query = array_map(
            function ($name) {
                return $name . ' = ?';
            },
            array_keys($tuple)
        );

        $this->query[] = implode($separator, $query);
        $this->appendParams(array_values($tuple));

        return $this;
    }

    /**
     * Concatenate elements with separator.
     * 
     * Example:
     * $query->concat(', ', ['id', 'name']);
     * 
     * Will result "id, name".
     * 
     */
    public function concat(string $separator, array $names): Query
    {
        $this->query[] = implode($separator, $names);

        return $this;
    }

    /**
     * Format query placeholders for elements and append it to the query.
     * 
     * Example:
     * $query->values(', ', [1, 'active']);
     * 
     * Will result "?, ?" and append values for execution.
     */
    public function values(string $separator, array $params): Query
    {
        $query = array_fill(0, count($params), '?');

        $this->query[] = implode($separator, $query);
        $this->appendParams($params);

        return $this;
    }

    public function build()
    {
        return [implode(' ', $this->query), $this->params];
    }

    public function execute()
    {
        list($query, $params) = $this->build();

        $this->mapper->prepare($query);
        $this->mapper->execute($params);
    }
}
