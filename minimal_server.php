<?php
// minimal_server.php - минимальный HTTP-сервер

$port = $_ENV['PORT'] ?? 8000;

// Создаем TCP-сервер
$sock = stream_socket_server("tcp://0.0.0.0:{$port}", $errno, $errstr);
if (!$sock) {
    die("Could not create socket: {$errstr} ({$errno})\n");
}

echo "Server listening on port {$port}\n";

while (true) {
    $conn = stream_socket_accept($sock, -1);
    if ($conn) {
        $req = fread($conn, 1024); // Читаем запрос
        
        // Отправляем простой ответ
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/plain\r\n";
        $response .= "Connection: close\r\n";
        $response .= "\r\n";
        $response .= "Server is running! PHP version: " . PHP_VERSION . "\n";
        $response .= "Port: " . $port . "\n";
        $response .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        fwrite($conn, $response);
        fclose($conn);
    }
}