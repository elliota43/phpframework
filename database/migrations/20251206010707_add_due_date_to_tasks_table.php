<?php

use Framework\Database\Migration;
use Framework\Database\Connection;

return new class extends Migration
{
    public function up(Connection $connection): void
    {
        // Add due_date column to existing tasks table
        $this->table($connection, 'tasks', function ($table) {
            $table->date('due_date')->nullable();
        });
    }

    public function down(Connection $connection): void
    {
        // Remove due_date column
        $this->table($connection, 'tasks', function ($table) {
            $table->dropColumn('due_date');
        });
    }
};
