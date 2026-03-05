<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database '" . DB_NAME . "' created successfully or already exists.\n";
} catch (PDOException $e) {
    die("Error creating database: " . $e->getMessage() . "\n");
}
