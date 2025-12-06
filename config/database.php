<?php

declare(strict_types=1);

use Framework\Support\Env;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the default database connection that will be used
    | by the framework. You may change this to any of the connections defined
    | in the "connections" array below.
    |
    */
    'default' => Env::get('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the database connections for your application.
    | Each connection may use a different driver. Available drivers are:
    | sqlite, mysql, pgsql
    |
    */
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => (function() {
                // First check if DB_DATABASE is explicitly set
                $explicit = Env::get('DB_DATABASE');
                if ($explicit !== null && $explicit !== '') {
                    return $explicit;
                }
                
                // Check for database.sqlite in root first (common location)
                // Config file is in config/ directory, so __DIR__ is config/, __DIR__/.. is framework root
                $frameworkRoot = dirname(__DIR__);
                $rootDb = $frameworkRoot . '/database.sqlite';
                if (file_exists($rootDb)) {
                    return $rootDb;
                }
                
                // Fall back to database/database.sqlite
                return $frameworkRoot . '/database/database.sqlite';
            })(),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::get('DB_PORT', 3306),
            'database' => Env::get('DB_DATABASE', 'forge'),
            'username' => Env::get('DB_USERNAME', 'forge'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'options' => [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::get('DB_PORT', 5432),
            'database' => Env::get('DB_DATABASE', 'forge'),
            'username' => Env::get('DB_USERNAME', 'forge'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8'),
            'options' => [],
        ],
    ],
];

