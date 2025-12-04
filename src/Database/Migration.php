<?php

declare(strict_types=1);

namespace Framework\Database;

abstract class Migration
{
    abstract public function up(Connection $connection): void;
    abstract public function down(Connection $connection): void;
}