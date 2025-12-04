<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Application;
use PDO;
use ArrayAccess;
use Framework\Support\Collection;

abstract class Model implements ArrayAccess
{
    protected static ?PDO $pdo = null;

    protected static string $table;

    protected bool $timestamps = true;

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    // called once during bootstrap
    public static function setConnection(Connection $connection): void
    {
        static::$pdo = $connection->pdo();
    }
    

    // query builder entry
    public static function query(): Builder
    {
        return new Builder(static::$pdo, static::$table, static::class);
    }

    // basic query shortcuts

    public static function all(): Collection
    {
        return static::query()->get();
    }

    public static function find(int|string $id, string $column = 'id')
    {
        /** @var static|null $result */
        $result = static::query()
        ->where($column,'=', $id)
        ->first();

        return $result;
    }

    public static function where(string $column, string $operator,  mixed $value = null): Collection
    {
        return static::query()->where($column, $operator, $value)->get();
    }



    public function save(): bool 
    {
        // naive: if "id" exists, update, else insert
        if (isset($this->attributes['id'])) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    protected function performInsert(): bool
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $this->attributes['created_at'] = $this->attributes['created_at'] ?? $now;
            $this->attributes['updated_at'] = $this->attributes['updated_at'] ?? $now; 
        }

        $columns = array_keys($this->attributes);
        $placeholders = array_map(fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::$pdo->prepare($sql);
        $ok = $stmt->execute($this->attributes);

        if ($ok) {
            $this->attributes['id'] = (int) static::$pdo->lastInsertId();
        }

        return $ok;
    }

    protected function performUpdate(): bool
    {
        if ($this->timestamps) {
            $this->attributes['updated_at'] = $this->attributes['updated_at'] ?? date('Y-m-d H:i:s');
        }
        $columns = array_keys($this->attributes);
        $assignments = [];

        foreach ($columns as $column) {
            if ($column == 'id') {
                continue;
            } 
            $assignments[] = $column . ' = :'.$column;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id = :id',
            static::$table,
            implode(', ', $assignments)
        );

        $stmt = static::$pdo->prepare($sql);
        return $stmt->execute($this->attributes);
    }

    public function delete(): bool
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }

        $sql = 'DELETE FROM ' . static::$table . ' WHERE id = :id';
        $stmt = static::$pdo->prepare($sql);

        return $stmt->execute(['id' => $this->attributes['id']]);
    }
    
    // relationships

    protected function hasMany(string $related, string $foreignKey, string $localKey = 'id') 
    {
        $localValue = $this->attributes[$localKey] ?? null;
        if ($localValue === null) {
            return new \Framework\Support\Collection();
        }

        /** @var Model $related */
        return $related::query()
            ->where($foreignKey, '=', $localValue)
            ->get();
    }

    protected function belongsTo(string $related, string $foreignKey, string $ownerKey = 'id'): ?Model
    {
        $foreignValue = $this->attributes[$foreignKey] ?? null;
        if ($foreignValue === null) {
            return null;
        }

        /** @var Model $related */
        return $related::query()
            ->where($ownerKey, '=', $foreignValue)->first();
    }


    // Accessors

    public function getAttribute(string $key): mixed
    {
        // Accessor: getFullNameAttribute()
        if ($this->hasGetMutator($key)) {
            $method = $this->getGetMutatorName($key);
            return $this->{$method}();
        }
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {

        // Mutator: setEmailAttribute($value)
        if ($this->hasSetMutator($key)) {
            $method = $this->getSetMutatorName($key);
            $this->{$method}($value);
            return;
        }
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected function keyToStudly(string $key): string
    {
        // "full_name" -> "FullName"
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    protected function getGetMutatorName(string $key): string
    {
        return 'get' . $this->keyToStudly($key) . 'Attribute';
    }

    protected function getSetMutatorName(string $key): string
    {
        return 'set' . $this->keyToStudly($key) . 'Attribute';
    }

    protected function hasGetMutator(string $key): bool
    {
        return method_exists($this, $this->getGetMutatorName($key));
    }

    protected function hasSetMutator(string $key): bool
    {
        return method_exists($this, $this->getSetMutatorName($key));
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function offsetExists(mixed $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->attributes[$key]);
    }
}