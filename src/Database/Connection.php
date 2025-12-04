<?php

declare(strict_types=1);

namespace Framework\Database;

use PDO;
use PDOException;

class Connection
{
    protected PDO $pdo;

    public function __construct(string $dsn, ?string $user = null, ?string $password = null, array $options = [])
    {
        $defaults = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $options = $options + $defaults;

        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}