<?php
// diagnostic.php - простой диагностический скрипт

echo "Diagnostic Info:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current directory: " . getcwd() . "\n";
echo "Environment: " . ($_SERVER['APP_ENV'] ?? 'Not set') . "\n";
echo "Port: " . ($_SERVER['PORT'] ?? 'Not set') . "\n";
echo "Database Host: " . ($_SERVER['DB_HOST'] ?? 'Not set') . "\n";
echo "Database Name: " . ($_SERVER['DB_DATABASE'] ?? 'Not set') . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

// Проверим, можем ли мы подключиться к базе данных
if (isset($_SERVER['DB_HOST']) && isset($_SERVER['DB_DATABASE'])) {
    echo "\nTrying to connect to database...\n";
    
    $host = $_SERVER['DB_HOST'];
    $port = $_SERVER['DB_PORT'] ?? 3306;
    $dbname = $_SERVER['DB_DATABASE'];
    $username = $_SERVER['DB_USERNAME'];
    $password = $_SERVER['DB_PASSWORD'];

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "Database connection successful!\n";
        
        // Проверим, можем ли мы выполнить простой запрос
        $result = $pdo->query("SELECT VERSION() as version");
        $version = $result->fetch();
        echo "MySQL version: " . $version['version'] . "\n";
    } catch (Exception $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nDatabase credentials not available\n";
}

echo "\nChecking Laravel files...\n";
if (file_exists('./artisan')) {
    echo "✓ Artisan file exists\n";
} else {
    echo "✗ Artisan file missing\n";
}

if (file_exists('./composer.json')) {
    echo "✓ composer.json exists\n";
} else {
    echo "✗ composer.json missing\n";
}

if (file_exists('./config/app.php')) {
    echo "✓ config/app.php exists\n";
} else {
    echo "✗ config/app.php missing\n";
}

echo "\nDone.\n";