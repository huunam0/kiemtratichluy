<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `tichluy_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `tichluy_db` created or already exists.\n";
    
    $pdo->exec("USE `tichluy_db`");

    $sqlFile = __DIR__ . '/schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        echo "Schema SQL executed successfully!\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
