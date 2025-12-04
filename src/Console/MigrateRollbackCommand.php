<?php

namespace Framework\Console;

use Framework\Database\Connection;
use Framework\Database\Migration;
use PDO;

class MigrateRollbackCommand
{
    public function handle(array $args = []): void
    {
        $dsn = 'sqlite:' . getcwd() .'/database.sqlite';
        $connection = new Connection($dsn);
        $pdo = $connection->pdo();

        // make sure migrations table exists.
        $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration TEXT NOT NULL UNIQUE,
            batch INTEGER NOT NULL,
            ran_at TEXT NOT NULL
        );
        SQL);

        $maxBatch = $pdo->query('SELECT MAX(batch) FROM migrations')->fetchColumn();

        if (!$maxBatch) {
            echo "Nothing to rollback.\n";
            return;
        }

        $stmt = $pdo->prepare(
            'SELECT migration FROM migrations WHERE batch = :batch ORDER BY id desc'
        );
        $stmt->execute([':batch' => $maxBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$migrations) {
            echo "Nothing to rollback.\n";
            return;
        }

        $migrationsDir = getcwd() . '/database/migrations';

        foreach ($migrations as $name) {
            $path = $migrationsDir . '/' . $name;

            if (!file_exists($path)) {
                echo "Skipping {$name}: file not found.\n";
                continue;
            }

            $migration = require $path;

            if (!$migration instanceof Migration) {
                echo "Skipping {$name}: file did not return a Migration instance.\n";
                continue;
            }

            echo "Rolling back: {$name}...\n";

            $migration->down($connection);

            $delete = $pdo->prepare('DELETE from migrations WHERE migration = :migration');
            $delete->execute([':migration' => $name]);
        }

        echo "Rollback of batch {$maxBatch} completed.\n";

    }
}