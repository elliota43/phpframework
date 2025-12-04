<?php

use Framework\Database\Migration;
use Framework\Database\Connection;

return new class extends Migration
{
    public function up(Connection $connection): void
    {
        // TODO: Write migration logic here.
        // Example:
        // $connection->pdo()->exec(<<<SQL
        // CREATE TABLE example (
        //     id INTEGER PRIMARY KEY AUTOINCREMENT
        // );
        // SQL);
        $connection->pdo()->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            created_at TEXT,
            updated_at TEXT
        );
        SQL);
    }

    public function down(Connection $connection): void
    {
        // TODO: Write rollback logic here.
        // Example:
        $connection->pdo()->exec('DROP TABLE posts;');
    }
};
