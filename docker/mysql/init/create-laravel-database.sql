#!/usr/bin/env bash

set -e

echo "Creating laravel database and sail user..."

mysql --user=root --password="${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS laravel;
    CREATE USER IF NOT EXISTS 'sail'@'%' IDENTIFIED BY 'password';
    GRANT ALL PRIVILEGES ON \`laravel%\`.* TO 'sail'@'%';
    FLUSH PRIVILEGES;
EOSQL

echo "Database and user created successfully!"