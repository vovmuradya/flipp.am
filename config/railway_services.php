<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Railway Environment Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles the environment variables provided by Railway
    | for various services like database, redis, etc.
    |
    */

    'database' => [
        'host' => env('MYSQLHOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('MYSQLPORT', env('DB_PORT', 3306)),
        'database' => env('MYSQLDATABASE', env('DB_DATABASE', 'laravel')),
        'username' => env('MYSQLUSER', env('DB_USERNAME', 'root')),
        'password' => env('MYSQLPASSWORD', env('DB_PASSWORD', '')),
    ],

    'redis' => [
        'host' => env('REDISHOST', env('REDIS_HOST', '127.0.0.1')),
        'port' => env('REDISPORT', env('REDIS_PORT', 6379)),
        'password' => env('REDISPASSWORD', env('REDIS_PASSWORD', null)),
        'database' => env('REDISDB', env('REDIS_DB', 0)),
    ],
];