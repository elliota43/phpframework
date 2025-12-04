<?php

namespace Framework\Console;

use Framework\Database\Connection;
use Framework\Database\Migration;
use PDO;

class MigrateCommand
{
    public function handle(array $args = []): void
    {
        $dsn = 'sqlite:' .getcwd() .'/database.sqlite';

        $connection = new Connection($dsn);
        $pdo = $connection->pdo();
         // Ensure migrations table exists
        $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration TEXT NOT NULL UNIQUE,
    batch INTEGER NOT NULL,
    ran_at TEXT NOT NULL
);
SQL);

        $migrationsDir = getcwd() . '/database/migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0777, true);
        }

        $files = glob($migrationsDir . '/*.php');
        sort($files);

        if (!$files) {
            echo "No migration files found in database/migrations.\n";
            return;
        }

        $applied = $pdo->query('SELECT migration from migrations')->fetchAll(PDO::FETCH_COLUMN);

        $currentBatch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn();
        $nextBatch = $currentBatch + 1;

        $ranSomething = false;

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                continue; // already ran
            }

            $migration = require $file;

            if (!$migration instanceof Migration) {
                echo "Skipping {$name}: file did not return a Migration instance.\n";
                continue;
            }

            echo "Migrating: {$name}...\n";

            $migration->up($connection);

            $stmt = $pdo->prepare(
                'INSERT INTO migrations (migration, batch, ran_at) VALUES (:migration, :batch, :ran_at)'
            );

            $stmt->execute([
                ':migration' => $name,
                ':batch' => $nextBatch,
                ':ran_at' => date('c'),
            ]);

            $ranSomething = true;
        }

        if (!$ranSomething) {
            echo "Nothing to migrate.\n";
        } else {
            echo "Migrations completed.\n";
        }
    }
}