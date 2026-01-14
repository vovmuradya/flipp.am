<?php

// config/railway_database.php
return [
    'mysql' => [
        'host' => env('MYSQLHOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('MYSQLPORT', env('DB_PORT', '3306')),
        'database' => env('MYSQLDATABASE', env('DB_DATABASE', 'laravel')),
        'username' => env('MYSQLUSER', env('DB_USERNAME', 'root')),
        'password' => env('MYSQLPASSWORD', env('DB_PASSWORD', '')),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ],
    ],
];