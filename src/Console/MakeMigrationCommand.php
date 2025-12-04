<?php

namespace Framework\Console;

class MakeMigrationCommand
{
    public function handle(array $args = []): void
    {
        $name = $args[0] ?? null;

        if (!$name) {
            echo "Usage: php mini make:migration MigrationName\n";
            echo "Example: php mini make:migration create_users_table\n";
            return;
        }

        // ensure migrations directory exists
        $migrationsDir = getcwd() . '/database/migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0777, true);
        }

        // timestamped filename like 20241204194500_create_users_table.php
        $timestamp = date('YmdHis');
        $fileName = $timestamp . '_'.$name.'.php';
        $path = $migrationsDir. '/' .$fileName;

        if (file_exists($path)) {
            echo "Migration already exists: {$path}\n";
            return;
        }

        $template = <<<PHP
<?php

use Framework\Database\Migration;
use Framework\Database\Connection;

return new class extends Migration
{
    public function up(Connection \$connection): void
    {
        // TODO: Write migration logic here.
        // Example:
        // \$connection->pdo()->exec(<<<SQL
        // CREATE TABLE example (
        //     id INTEGER PRIMARY KEY AUTOINCREMENT
        // );
        // SQL);
    }

    public function down(Connection \$connection): void
    {
        // TODO: Write rollback logic here.
        // Example:
        // \$connection->pdo()->exec('DROP TABLE example;');
    }
};

PHP;

        file_put_contents($path, $template);

        echo "Migration created: {$path}\n";
    }
}