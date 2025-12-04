<?php

declare(strict_types=1);

namespace Framework\Database;

use PDO;
use Framework\Support\Collection;

class Builder
{
    protected PDO $pdo;
    protected string $table;
    protected ?string $modelClass;

    /**
     * Each where: ['AND'|'OR', column, operator, value]
     * @var array<int, array{0:string,1:string,2:string,3:mixed}>
     */
    protected array $wheres = [];

    /**
     * @var array<int, array{0:string,1:string}>
     */
    protected array $orders = [];

    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(PDO $pdo, string $table, ?string $modelClass = null)
    {
        $this->pdo        = $pdo;
        $this->table      = $table;
        $this->modelClass = $modelClass;
    }

    /**
     * where('age', '>', 18)
     * where('status', 'active') // shorthand for '='
     */
    public function where(string $column, string $operator, mixed $value = null): self
    {
        // Allow shorthand: where('status', 'active')
        if ($value === null) {
            $value    = $operator;
            $operator = '=';
        }

        $this->wheres[] = ['AND', $column, $operator, $value];
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value    = $operator;
            $operator = '=';
        }

        $this->wheres[] = ['OR', $column, $operator, $value];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        $this->orders[] = [$column, $direction];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    protected function compileSelect(): array
    {
        $sql      = 'SELECT * FROM ' . $this->table;
        $bindings = [];

        if ($this->wheres) {
            $parts = [];
            foreach ($this->wheres as $index => [$boolean, $column, $operator, $value]) {
                $clause = $column . ' ' . $operator . ' ?';
                if ($index === 0) {
                    $parts[] = $clause;
                } else {
                    $parts[] = $boolean . ' ' . $clause;
                }
                $bindings[] = $value;
            }

            $sql .= ' WHERE ' . implode(' ', $parts);
        }

        if ($this->orders) {
            $orderParts = [];
            foreach ($this->orders as [$column, $dir]) {
                $orderParts[] = $column . ' ' . $dir;
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return [$sql, $bindings];
    }

    public function get(): Collection
    {
        [$sql, $bindings] = $this->compileSelect();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        if ($this->modelClass) {
            $class = $this->modelClass;
            $models = array_map(fn ($row) => new $class($row), $rows);
            return new Collection($models);
        }

        return new Collection($rows);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results->first();
    }

    public function count(): int
    {
        // a very naive count implementation:
        [$sql, $bindings] = $this->compileSelect();
        $sql = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) AS aggregate FROM', $sql);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        $row = $stmt->fetch();
        return (int) ($row['aggregate'] ?? 0);
    }

    public function toSql(): string
    {
        [$sql, ] = $this->compileSelect();
        return $sql;
    }
}
