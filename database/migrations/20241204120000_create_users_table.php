<?php

use Framework\Database\Migration;
use Framework\Database\Connection;

return new class extends Migration {
    public function up(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL
);
SQL;

        $connection->pdo()->exec($sql);
    }

    public function down(Connection $connection): void
    {
        $connection->pdo()->exec('DROP TABLE IF EXISTS users');
    }
};
